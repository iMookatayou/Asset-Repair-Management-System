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

    public function evaluateList()
    {
        /** @var User $user */
        $user = Auth::user();

        $pendingRequests = MaintenanceRequest::with(['technician', 'rating'])
            ->where('reporter_id', $user->id)
            ->whereIn('status', [
                MaintenanceRequest::STATUS_RESOLVED,
                MaintenanceRequest::STATUS_CLOSED,
            ])
            ->whereDoesntHave('rating')
            ->get()
            ->filter(fn (MaintenanceRequest $req) => $this->withinRatingWindow($req))
            ->values();

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

    public function create(MaintenanceRequest $maintenanceRequest)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($redirect = $this->guardRatingAccess($maintenanceRequest, $user)) {
            return $redirect;
        }

        // ส่ง technician ที่ระบบ "เลือกได้จริง" จาก assignments ไปให้หน้า form ใช้แสดงผล (ถ้าต้องการ)
        $technicianId = $this->resolveTechnicianIdForRating($maintenanceRequest);

        return view('maintenance.rating.form', [
            'req'          => $maintenanceRequest,
            'technicianId' => $technicianId,
        ]);
    }

    public function store(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($redirect = $this->guardRatingAccess($maintenanceRequest, $user)) {
            return $redirect;
        }

        $data = $this->validateRating($request);

        // ✅ ดึงช่างจาก assignments ของงานนี้จริง
        $technicianId = $this->resolveTechnicianIdForRating($maintenanceRequest);
        if (! $technicianId) {
            return redirect()
                ->route('maintenance.requests.show', $maintenanceRequest)
                ->with('toast', [
                    'type'    => 'warning',
                    'message' => 'ยังไม่พบช่างที่เกี่ยวข้องกับงานนี้ จึงยังให้คะแนนไม่ได้',
                ]);
        }

        // ✅ กันกดซ้ำ/ยิงพร้อมกัน: ไม่พัง 500
        MaintenanceRating::firstOrCreate(
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

    protected function guardRatingAccess(MaintenanceRequest $maintenanceRequest, User $user): ?RedirectResponse
    {
        // ✅ แก้บั๊ก: ให้แน่ใจว่า rating ถูกโหลด (หรือเช็ค exists แบบ query)
        $maintenanceRequest->loadMissing('rating');

        if ($maintenanceRequest->reporter_id !== $user->id) {
            abort(403, 'คุณไม่มีสิทธิ์ให้คะแนนงานนี้');
        }

        if (! in_array($maintenanceRequest->status, [
            MaintenanceRequest::STATUS_RESOLVED,
            MaintenanceRequest::STATUS_CLOSED,
        ], true)) {
            abort(403, 'สามารถให้คะแนนได้เฉพาะงานที่ปิดแล้วเท่านั้น');
        }

        // ✅ กันซ้ำแบบชัวร์ (ไม่พึ่ง relation อย่างเดียว)
        $alreadyRated = MaintenanceRating::where('maintenance_request_id', $maintenanceRequest->id)
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

        // ✅ ต้องมีช่างที่มาจาก assignments จริง (ไม่งั้นไม่ให้รีวิว)
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

    /**
     * ✅ เลือกช่างที่จะถูกรีวิวจาก assignments ของงานนี้ (แหล่งความจริง)
     * priority: is_lead=1 ก่อน -> status=done ก่อน -> assigned_at ล่าสุด
     */
    protected function resolveTechnicianIdForRating(MaintenanceRequest $maintenanceRequest): ?int
    {
        // ต้องมี relation assignments() ใน MaintenanceRequest model
        $assignment = $maintenanceRequest->assignments()
            ->orderByDesc('is_lead')
            ->orderByRaw("CASE WHEN status = 'done' THEN 1 ELSE 0 END DESC")
            ->orderByDesc('assigned_at')
            ->first();

        return $assignment?->user_id;
    }
}
