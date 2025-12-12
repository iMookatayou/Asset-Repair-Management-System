<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceRating;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MaintenanceRatingApiController extends Controller
{
    protected int $ratingDeadlineDays = 7;

    public function store(Request $request, MaintenanceRequest $maintenanceRequest): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // ตรวจสิทธิ์คล้าย ๆ web controller
        if ($maintenanceRequest->reporter_id !== $user->id) {
            return response()->json([
                'message' => 'คุณไม่มีสิทธิ์ให้คะแนนงานนี้',
            ], 403);
        }

        if (! in_array($maintenanceRequest->status, [
            MaintenanceRequest::STATUS_RESOLVED,
            MaintenanceRequest::STATUS_CLOSED,
        ], true)) {
            return response()->json([
                'message' => 'สามารถให้คะแนนได้เฉพาะงานที่ปิดแล้วเท่านั้น',
            ], 422);
        }

        if ($maintenanceRequest->rating) {
            return response()->json([
                'message' => 'งานนี้มีการให้คะแนนไปแล้ว',
            ], 409); // conflict
        }

        if (! $this->withinRatingWindow($maintenanceRequest)) {
            return response()->json([
                'message' => 'เลยระยะเวลาที่สามารถให้คะแนนงานนี้ได้แล้ว',
            ], 422);
        }

        // validate
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

        if ($validator->fails()) {
            return response()->json([
                'message' => 'ข้อมูลไม่ถูกต้อง',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $rating = MaintenanceRating::create([
            'maintenance_request_id' => $maintenanceRequest->id,
            'rater_id'               => $user->id,
            'technician_id'          => $maintenanceRequest->technician_id,
            'score'                  => $data['score'],
            'comment'                => $data['comment'] ?? null,
        ]);

        return response()->json([
            'message' => 'บันทึกคะแนนเรียบร้อย',
            'data'    => $rating,
        ], 201);
    }

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
