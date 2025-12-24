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
    protected int $ratingDeadlineDays = 7;

    /**
     * หน้า "ให้คะแนนงาน" แยกเป็น:
     * - pendingRequests: งานที่ปิดแล้ว แต่ user คนนี้ยังไม่ให้คะแนน และยังอยู่ในช่วงเวลาให้คะแนน
     * - ratedRequests: งานที่ user คนนี้ให้คะแนนแล้ว
     */
    public function evaluateList()
    {
        /** @var User $user */
        $user = Auth::user();

        $baseQuery = MaintenanceRequest::query()
            ->where('reporter_id', $user->id)
            ->whereIn('status', [
                MaintenanceRequest::STATUS_RESOLVED,
                MaintenanceRequest::STATUS_CLOSED,
            ]);

        // งานที่ยังไม่ให้คะแนน (ของ user คนนี้จริง ๆ)
        $pendingRequests = (clone $baseQuery)
            ->with(['technician:id,name', 'assignments.user:id,name,role'])
            ->whereDoesntHave('ratings', function ($q) use ($user) {
                $q->where('rater_id', $user->id);
            })
            ->get()
            ->filter(fn (MaintenanceRequest $req) => $this->withinRatingWindow($req))
            ->values();

        // งานที่ให้คะแนนแล้ว (ของ user คนนี้จริง ๆ)
        $ratedRequests = (clone $baseQuery)
            ->with([
                'technician:id,name',
                'ratings' => function ($q) use ($user) {
                    $q->where('rater_id', $user->id);
                },
            ])
            ->whereHas('ratings', function ($q) use ($user) {
                $q->where('rater_id', $user->id);
            })
            ->latest('closed_at')
            ->get();

        return view('maintenance.rating.evaluate', [
            'pendingRequests' => $pendingRequests,
            'ratedRequests'   => $ratedRequests,
        ]);
    }

    /**
     * Dashboard คะแนนของช่าง (avg, count)
     * ต้องมี relation ใน User: technicianRatings() -> hasMany(MaintenanceRating::class, 'technician_id')
     */
    public function technicianDashboard()
    {
        $technicians = User::query()
            ->where('role', 'technician')
            ->withAvg('technicianRatings', 'score')
            ->withCount('technicianRatings')
            ->get(['id', 'name']);

        return view('maintenance.rating.technicians-dashboard', [
            'technicians' => $technicians,
            'chartLabels' => $technicians->pluck('name'),
            'chartAvg'    => $technicians->pluck('technician_ratings_avg_score'),
            'chartCount'  => $technicians->pluck('technician_ratings_count'),
        ]);
    }

    /**
     * ฟอร์มให้คะแนน
     */
    public function create(MaintenanceRequest $maintenanceRequest)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($redirect = $this->guardRatingAccess($maintenanceRequest, $user)) {
            return $redirect;
        }

        // เลือกช่างจาก assignments (แหล่งความจริง) และกรองเฉพาะ role=technician
        $technicianId = $this->resolveTechnicianIdForRating($maintenanceRequest);

        return view('maintenance.rating.form', [
            'req'          => $maintenanceRequest,
            'technicianId' => $technicianId,
        ]);
    }

    /**
     * บันทึกคะแนน
     * - ใช้ updateOrCreate กัน race condition + กดซ้ำแล้วค่าหาย
     * - ยืนยันช่างจาก assignments (ไม่รับ technician_id จาก request เพื่อกันปลอม)
     */
    public function store(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($redirect = $this->guardRatingAccess($maintenanceRequest, $user)) {
            return $redirect;
        }

        $data = $this->validateRating($request);

        $technicianId = $this->resolveTechnicianIdForRating($maintenanceRequest);
        if (! $technicianId) {
            return redirect()
                ->route('maintenance.requests.show', $maintenanceRequest)
                ->with('toast', [
                    'type'    => 'warning',
                    'message' => 'ยังไม่พบช่างที่เกี่ยวข้องกับงานนี้ จึงยังให้คะแนนไม่ได้',
                ]);
        }

        MaintenanceRating::updateOrCreate(
            [
                'maintenance_request_id' => $maintenanceRequest->id,
                'rater_id'               => $user->id,
            ],
            [
                'technician_id'          => $technicianId,
                'score'                  => (int) $data['score'],
                'comment'                => $data['comment'] ?? null,
            ]
        );

        return redirect()
            ->route('maintenance.requests.show', $maintenanceRequest)
            ->with('toast', [
                'type'    => 'success',
                'message' => 'บันทึกคะแนนเรียบร้อย',
            ]);
    }

    /**
     * กันสิทธิ์/เงื่อนไขการให้คะแนนให้ครบ:
     * - ต้องเป็นคนแจ้งงาน
     * - ต้องเป็นงานสถานะ resolved/closed
     * - ต้องยังอยู่ในช่วง 7 วัน
     * - ต้องยังไม่เคยให้คะแนน (เช็คด้วย query ชัวร์)
     * - ต้องมีช่างจาก assignments (ไม่งั้นไม่ให้รีวิว)
     */
    protected function guardRatingAccess(MaintenanceRequest $maintenanceRequest, User $user): ?RedirectResponse
    {
        if ((int) $maintenanceRequest->reporter_id !== (int) $user->id) {
            abort(403, 'คุณไม่มีสิทธิ์ให้คะแนนงานนี้');
        }

        if (! in_array($maintenanceRequest->status, [
            MaintenanceRequest::STATUS_RESOLVED,
            MaintenanceRequest::STATUS_CLOSED,
        ], true)) {
            abort(403, 'สามารถให้คะแนนได้เฉพาะงานที่ปิดแล้วเท่านั้น');
        }

        $alreadyRated = MaintenanceRating::query()
            ->where('maintenance_request_id', $maintenanceRequest->id)
            ->where('rater_id', $user->id)
            ->exists();

        if ($alreadyRated) {
            return redirect()
                ->route('maintenance.requests.show', $maintenanceRequest)
                ->with('toast', [
                    'type'    => 'info',
                    'message' => 'งานนี้มีการให้คะแนนไปแล้ว',
                ]);
        }

        if (! $this->withinRatingWindow($maintenanceRequest)) {
            return redirect()
                ->route('maintenance.requests.show', $maintenanceRequest)
                ->with('toast', [
                    'type'    => 'warning',
                    'message' => 'เลยระยะเวลาที่สามารถให้คะแนนงานนี้ได้แล้ว',
                ]);
        }

        if (! $this->resolveTechnicianIdForRating($maintenanceRequest)) {
            return redirect()
                ->route('maintenance.requests.show', $maintenanceRequest)
                ->with('toast', [
                    'type'    => 'warning',
                    'message' => 'ยังไม่มีการมอบหมายช่างในงานนี้ จึงยังให้คะแนนไม่ได้',
                ]);
        }

        return null;
    }

    /**
     * validate + rule เพิ่ม: ถ้าให้ 1–2 ดาว ต้องมี comment
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
            $comment = trim((string) ($data['comment'] ?? ''));

            if ($score !== null && $score <= 2 && $comment === '') {
                $v->errors()->add('comment', 'ถ้าให้ 1–2 ดาว กรุณาระบุความคิดเห็นเพิ่มเติม');
            }
        });

        return $validator->validate();
    }

    protected function withinRatingWindow(MaintenanceRequest $maintenanceRequest): bool
    {
        $base = $maintenanceRequest->closed_at
            ?? $maintenanceRequest->resolved_at
            ?? $maintenanceRequest->completed_date;

        if (! $base) return false;

        return $base->isPast() && now()->diffInDays($base) <= $this->ratingDeadlineDays;
    }

    protected function resolveTechnicianIdForRating(MaintenanceRequest $maintenanceRequest): ?int
    {
        $assignment = $maintenanceRequest->assignments()
            ->whereHas('user', fn($q) => $q->where('role', 'technician'))
            ->orderByDesc('is_lead')
            ->orderByRaw("CASE WHEN status = 'done' THEN 1 ELSE 0 END DESC")
            ->orderByDesc('assigned_at')
            ->first();

        return $assignment?->user_id;
    }
}
