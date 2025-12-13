<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\MaintenanceRating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MaintenanceRatingController extends Controller
{
    /**
     * กำหนดช่วงเวลาที่อนุญาตให้ให้คะแนน (หน่วย: วัน)
     * เช่น 7 วันหลังจากปิดงาน
     */
    protected int $ratingDeadlineDays = 7;

    /**
     * หน้า Evaluate: งานที่รอการให้คะแนน + งานที่เคยให้คะแนนแล้ว
     *
     * GET /maintenance/requests/rating/evaluate
     * route name: maintenance.requests.rating.evaluate
     */
    public function evaluateList()
    {
        /** @var User $user */
        $user = Auth::user();

        // งานที่ปิดแล้ว + เป็นคนแจ้ง + ยังไม่มี rating + ยังอยู่ใน window
        $pendingRequests = MaintenanceRequest::with(['technician', 'rating'])
            ->where('reporter_id', $user->id)
            ->whereIn('status', [
                MaintenanceRequest::STATUS_RESOLVED,
                MaintenanceRequest::STATUS_CLOSED,
            ])
            ->whereDoesntHave('rating')
            ->get()
            ->filter(function (MaintenanceRequest $req) {
                return $this->withinRatingWindow($req);
            })
            ->values();

        // งานที่เคยให้คะแนนแล้ว
        $ratedRequests = MaintenanceRequest::with(['technician', 'rating'])
            ->where('reporter_id', $user->id)
            ->whereIn('status', [
                MaintenanceRequest::STATUS_RESOLVED,
                MaintenanceRequest::STATUS_CLOSED,
            ])
            ->whereHas('rating')
            ->latest('closed_at')
            ->get();

        return view('maintenance.rating.evaluate', [
            'pendingRequests' => $pendingRequests,
            'ratedRequests'   => $ratedRequests,
        ]);
    }

    /**
     * Dashboard ช่าง (กราฟ + การ์ด) ตามที่คุณทำ blade ไว้
     *
     * GET /maintenance/requests/rating/technicians
     * route name: maintenance.requests.rating.technicians
     */
    public function technicianDashboard()
    {
        $technicians = User::where('role', 'technician')
            ->withAvg('technicianRatings', 'score')
            ->withCount('technicianRatings')
            ->get();

        $chartLabels = $technicians->pluck('name');
        $chartAvg    = $technicians->pluck('technician_ratings_avg_score');
        $chartCount  = $technicians->pluck('technician_ratings_count');

        return view('maintenance.rating.technicians-dashboard', [
            'technicians' => $technicians,
            'chartLabels' => $chartLabels,
            'chartAvg'    => $chartAvg,
            'chartCount'  => $chartCount,
        ]);
    }

    /**
     * แสดงฟอร์มให้คะแนนใบงานเดียว
     *
     * GET /maintenance/requests/{maintenanceRequest}/rating
     * route name: maintenance.requests.rating.create
     */
    public function create(MaintenanceRequest $maintenanceRequest)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($redirect = $this->guardRatingAccess($maintenanceRequest, $user)) {
            return $redirect;
        }

        return view('maintenance.rating.form', [
            'req' => $maintenanceRequest,
        ]);
    }

    /**
     * บันทึกคะแนนใบงานเดียว
     *
     * POST /maintenance/requests/{maintenanceRequest}/rating
     * route name: maintenance.requests.rating.store
     */
    public function store(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($redirect = $this->guardRatingAccess($maintenanceRequest, $user)) {
            return $redirect;
        }

        $data = $this->validateRating($request);

        MaintenanceRating::create([
            'maintenance_request_id' => $maintenanceRequest->id,
            'rater_id'               => $user->id,
            'technician_id'          => $maintenanceRequest->technician_id,
            'score'                  => $data['score'],
            'comment'                => $data['comment'] ?? null,
        ]);

        return redirect()
            ->route('maintenance.requests.show', $maintenanceRequest)
            ->with('toast', [
                'type'    => 'success',
                'message' => 'บันทึกคะแนนเรียบร้อย',
            ]);
    }

    /**
     * รวมเงื่อนไขสิทธิ์ + เงื่อนไขเชิงธุรกิจของการให้คะแนน
     */
    protected function guardRatingAccess(MaintenanceRequest $maintenanceRequest, User $user): ?RedirectResponse
    {
        // ต้องเป็นคนแจ้งซ่อม
        if ($maintenanceRequest->reporter_id !== $user->id) {
            abort(403, 'คุณไม่มีสิทธิ์ให้คะแนนงานนี้');
        }

        // งานต้องปิดแล้ว (RESOLVED/CLOSED)
        if (! in_array($maintenanceRequest->status, [
            MaintenanceRequest::STATUS_RESOLVED,
            MaintenanceRequest::STATUS_CLOSED,
        ], true)) {
            abort(403, 'สามารถให้คะแนนได้เฉพาะงานที่ปิดแล้วเท่านั้น');
        }

        // ถ้ามี rating แล้ว ห้ามให้ซ้ำ
        if ($maintenanceRequest->rating) {
            return redirect()
                ->route('maintenance.requests.show', $maintenanceRequest)
                ->with('toast', [
                    'type'    => 'info',
                    'message' => 'งานนี้มีการให้คะแนนไปแล้ว',
                ]);
        }

        // ตรวจ window เวลา
        if (! $this->withinRatingWindow($maintenanceRequest)) {
            return redirect()
                ->route('maintenance.requests.show', $maintenanceRequest)
                ->with('toast', [
                    'type'    => 'warning',
                    'message' => 'เลยระยะเวลาที่สามารถให้คะแนนงานนี้ได้แล้ว',
                ]);
        }

        return null;
    }

    /**
     * validate ฟอร์มคะแนน
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
            return false;
        }

        return now()->diffInDays($base) <= $this->ratingDeadlineDays;
    }
}
