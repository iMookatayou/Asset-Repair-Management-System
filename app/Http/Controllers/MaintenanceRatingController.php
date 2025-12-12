<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\MaintenanceRating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;

class MaintenanceRatingController extends Controller
{
    /**
     * กำหนดช่วงเวลาที่อนุญาตให้ให้คะแนน (หน่วย: วัน)
     * เช่น 7 วันหลังจากปิดงาน
     */
    protected int $ratingDeadlineDays = 7;

    /**
     * แสดงฟอร์มให้คะแนน
     *
     * GET /maintenance/{maintenanceRequest}/rating
     */
    public function create(MaintenanceRequest $maintenanceRequest)
    {
        /** @var User $user */
        $user = Auth::user();

        // รวม logic ตรวจสิทธิ์ + เงื่อนไขอื่น ๆ ไว้ที่เดียว
        if ($redirect = $this->guardRatingAccess($maintenanceRequest, $user)) {
            return $redirect;
        }

        // แสดง view ฟอร์มให้คะแนน เช่น resources/views/maintenance/rating/form.blade.php
        return view('maintenance.rating.form', [
            'req' => $maintenanceRequest,
        ]);
    }

    /**
     * บันทึกคะแนน
     *
     * POST /maintenance/{maintenanceRequest}/rating
     */
    public function store(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        /** @var User $user */
        $user = Auth::user();

        // ใช้ guard เดียวกับ create เพื่อกันเคสข้ามหน้าจอมาตี endpoint ตรง
        if ($redirect = $this->guardRatingAccess($maintenanceRequest, $user)) {
            return $redirect;
        }

        // validate + rule: ถ้าให้ 1–2 ดาว ต้องกรอก comment
        $data = $this->validateRating($request);

        // สร้าง rating ใหม่ (ให้คะแนนได้ครั้งเดียว ไม่เปิด edit)
        MaintenanceRating::create([
            'maintenance_request_id' => $maintenanceRequest->id,
            'rater_id'               => $user->id,
            'technician_id'          => $maintenanceRequest->technician_id,
            'score'                  => $data['score'],
            'comment'                => $data['comment'] ?? null,
        ]);

        return redirect()
            ->route('maintenance.show', $maintenanceRequest)
            ->with('toast', [
                'type'    => 'success',
                'message' => 'บันทึกคะแนนเรียบร้อย ขอบคุณสำหรับความคิดเห็นค่ะ',
            ]);
    }

    /**
     * รวมเงื่อนไขสิทธิ์ + เงื่อนไขเชิงธุรกิจของการให้คะแนน
     *
     * - ต้องเป็นผู้แจ้งซ่อม
     * - งานต้องอยู่สถานะ RESOLVED / CLOSED
     * - ยังไม่ได้ให้คะแนนมาก่อน
     * - ยังอยู่ใน rating window
     *
     * ถ้าไม่ผ่าน → คืน RedirectResponse (พร้อม toast)
     * ถ้าผ่าน → คืน null
     */
    protected function guardRatingAccess(MaintenanceRequest $maintenanceRequest, User $user): ?RedirectResponse
    {
        // 1) ต้องเป็นคนแจ้งซ่อมเท่านั้น (reporter ในระบบ)
        if ($maintenanceRequest->reporter_id !== $user->id) {
            abort(403, 'คุณไม่มีสิทธิ์ให้คะแนนงานนี้');
        }

        // 2) สถานะต้องอยู่ใน resolved หรือ closed ถึงจะให้คะแนนได้
        if (! in_array($maintenanceRequest->status, [
            MaintenanceRequest::STATUS_RESOLVED,
            MaintenanceRequest::STATUS_CLOSED,
        ], true)) {
            abort(403, 'สามารถให้คะแนนได้เฉพาะงานที่ปิดแล้วเท่านั้น');
        }

        // 3) ถ้ามี rating แล้ว ห้ามให้ซ้ำ
        if ($maintenanceRequest->rating) {
            return redirect()
                ->route('maintenance.show', $maintenanceRequest)
                ->with('toast', [
                    'type'    => 'info',
                    'message' => 'งานนี้มีการให้คะแนนไปแล้ว',
                ]);
        }

        // 4) เช็คว่าอยู่ในช่วงเวลาที่ให้คะแนนได้มั้ย
        if (! $this->withinRatingWindow($maintenanceRequest)) {
            return redirect()
                ->route('maintenance.show', $maintenanceRequest)
                ->with('toast', [
                    'type'    => 'warning',
                    'message' => 'เลยระยะเวลาที่สามารถให้คะแนนงานนี้ได้แล้ว',
                ]);
        }

        return null;
    }

    /**
     * รวม logic validate ฟอร์มการให้คะแนน
     *
     * rule หลัก:
     *  - score: 1–5
     *  - comment: ไม่บังคับ ยกเว้นให้ 1–2 ดาว ต้องกรอก
     */
    protected function validateRating(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'score'   => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $validator->after(function ($v) {
            $data    = $v->getData();
            $score   = isset($data['score']) ? (int) $data['score'] : null;
            $comment = trim($data['comment'] ?? '');

            if ($score !== null && $score <= 2 && $comment === '') {
                $v->errors()->add('comment', 'ถ้าให้ 1–2 ดาว กรุณาระบุความคิดเห็นเพิ่มเติม');
            }
        });

        return $validator->validate();
    }

    /**
     * เช็คว่าตอนนี้ยังอยู่ในช่วงเวลาที่ให้คะแนนได้ไหม
     * ใช้ลำดับเวลา: closed_at > resolved_at > completed_date
     */
    protected function withinRatingWindow(MaintenanceRequest $maintenanceRequest): bool
    {
        $base = $maintenanceRequest->closed_at
            ?? $maintenanceRequest->resolved_at
            ?? $maintenanceRequest->completed_date;

        if (! $base) {
            // ถ้าไม่มีข้อมูลเวลาเลย ก็ไม่อนุญาตให้ให้คะแนน
            return false;
        }

        return now()->diffInDays($base) <= $this->ratingDeadlineDays;
    }
}
