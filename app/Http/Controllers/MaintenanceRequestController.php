<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest as MR;
use App\Models\MaintenanceAssignment;
use App\Models\Attachment;
use App\Models\User;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class MaintenanceRequestController extends Controller
{
    public function create()
    {
        return $this->createPage();
    }

    public function indexPage(Request $request)
    {
        $user     = Auth::user();
        $status   = $request->string('status')->toString();
        $priority = $request->string('priority')->toString();
        $q        = trim($request->string('q')->toString());
        $assetId  = $request->integer('asset_id');

        // ---- ใช้ helper ดึงค่าการเรียง + จัดการ session ต่อ user ----
        [$sortBy, $sortDir] = $this->resolveSort($request);

        $query = MR::query()
            ->with([
                'asset',
                'reporter:id,name,email',
                'technician:id,name',
                'attachments' => fn($qq) => $qq
                    ->select('id','attachable_id','attachable_type','file_id','original_name','is_private','order_column')
                    ->with(['file:id,path,disk,mime,size']),
            ])

            // จำกัดเฉพาะผู้ใช้ระดับ Member ให้เห็นงานที่ตนแจ้งเท่านั้น
            ->when(
                ($user && !$user->isAdmin() && !$user->isSupervisor() && !$user->isTechnician()),
                fn($qb) => $qb->where('reporter_id', $user->id)
            )

            // filter อื่น ๆ
            ->when($assetId, fn($qb) => $qb->where('asset_id', $assetId))
            ->when($status, fn($qb) => $qb->where('status', $status))
            ->when($priority, fn($qb) => $qb->where('priority', $priority))

            ->when($q !== '', fn($qb) => $qb->search($q));

        if ($q !== '') {
            // กันผลสลับแถว + ทำให้ผลคงที่
            // (ถ้าใน scopeSearch ของคุณมี orderByRaw ranking อยู่แล้ว ตัวนี้เป็นแค่ tie-break)
            $query->orderByDesc('id');
        } else {
            if ($sortBy === 'request_no') {
                $dir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

                // 1) เอา request_no ว่าง/NULL ไปท้ายเสมอ
                $query->orderByRaw("CASE WHEN request_no IS NULL OR request_no = '' THEN 1 ELSE 0 END ASC");

                // 2) เรียง request_no ตามทิศทางที่เลือก
                $query->orderBy('request_no', $dir);

                // 3) tie-breaker กันสลับแถว
                $query->orderBy('id', $dir);
            } else {
                // safety: กัน sort_by หลุดเงื่อนไข (เผื่อ resolveSort โดนแก้ในอนาคต)
                $allowed = ['request_no', 'id', 'request_date'];
                if (!in_array($sortBy, $allowed, true)) {
                    $sortBy = 'request_no';
                }
                $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

                $query->orderBy($sortBy, $sortDir);
            }
        }

        $list = $query
            ->paginate(20)
            ->withQueryString();

        return view('maintenance.requests.index', compact('list','status','priority','q','sortBy','sortDir'));
    }

    public function queuePage(Request $request)
    {
        \Gate::authorize('view-repair-dashboard');
        $status = (string) $request->string('status');
        $q      = (string) $request->string('q');
        $just   = (int) $request->query('just');

        $base = MR::query()
            ->with(['asset','reporter:id,name,email','technician:id,name'])
            ->whereIn('status', ['pending','accepted','in_progress','on_hold']);

        $list = (clone $base)
            ->when($status, fn($qb) => $qb->where('status', $status))
            ->when($q, function ($qb) use ($q) {
                $qb->where(function ($w) use ($q) {
                    $w->where('title','like',"%{$q}%")
                        ->orWhere('description','like',"%{$q}%")
                        ->orWhere('request_no','like',"%{$q}%")
                        ->orWhereHas('reporter', fn($qr) => $qr->where('name','like',"%{$q}%")->orWhere('email','like',"%{$q}%"))
                        ->orWhereHas('asset', fn($qa) => $qa->where('name','like',"%{$q}%")->orWhere('asset_code','like',"%{$q}%"));
                });
            })
            ->orderByRaw("FIELD(priority,'urgent','high','medium','low')")
            ->orderByDesc('request_date')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total'       => (clone $base)->count(),
            'pending'     => (clone $base)->where('status','pending')->count(),
            'in_progress' => (clone $base)->where('status','in_progress')->count(),
            'completed'   => MR::query()->whereIn('status', ['resolved','closed'])->count(),
        ];

        return view('repair.queue', compact('list','stats','just'));
    }

    public function myJobsPage(Request $request)
    {
        \Gate::authorize('view-my-jobs');

        $user   = Auth::user();
        $userId = $user->id;

        $filter = $request->string('filter')->toString() ?: 'all'; // my|available|all
        $status = $request->string('status')->toString();
        $q      = trim($request->string('q')->toString());
        $tech   = $request->integer('tech'); // optional technician filter

        // ✅ ระบบใหม่: exclude แค่ cancelled ก็พอ
        $excluded = [MR::STATUS_CANCELLED];

        // ✅ sort toggle
        $sortBy  = $request->string('sort_by')->toString() ?: '';
        $sortDir = strtolower($request->string('sort_dir')->toString() ?: 'desc');
        $sortDir = in_array($sortDir, ['asc', 'desc'], true) ? $sortDir : 'desc';

        // ✅ เพิ่ม sort ที่ควรมีจริง (ให้ตรง “ใหม่→เก่า / เก่า→ใหม่” และ “เลขใบงาน”)
        $allowedSort = ['id', 'created_at', 'updated_at', 'request_date', 'request_no'];
        if (!in_array($sortBy, $allowedSort, true)) {
            $sortBy = '';
        }

        /**
         * ✅ Base query for stats/list consistency
         * - หากใช้ SoftDeletes: ใช้ whereNull('deleted_at')
         * - ถ้า MR model ใช้ SoftDeletes อยู่แล้ว query จะ exclude deleted ให้เอง
         */
        $base = MR::query();

        // ===== Stats (consistent with excluded) =====
        $stats = [
            'pending'     => (clone $base)
                ->where('status', MR::STATUS_PENDING)
                ->whereNotIn('status', $excluded)
                ->count(),

            'in_progress' => (clone $base)
                ->whereIn('status', [MR::STATUS_ACCEPTED, MR::STATUS_IN_PROGRESS, MR::STATUS_ON_HOLD])
                ->whereNotIn('status', $excluded)
                ->count(),

            'completed'   => (clone $base)
                ->whereIn('status', [MR::STATUS_RESOLVED, MR::STATUS_CLOSED])
                ->count(),

            'my_active'   => (clone $base)
                ->where('technician_id', $userId)
                ->whereNotIn('status', array_merge([MR::STATUS_RESOLVED, MR::STATUS_CLOSED], $excluded))
                ->count(),

            'cancelled'   => (clone $base)
                ->where('status', MR::STATUS_CANCELLED)
                ->count(),
        ];

        // ===== Team (active technicians) =====
        $activeTechIds = (clone $base)
            ->whereNotIn('status', array_merge([MR::STATUS_RESOLVED, MR::STATUS_CLOSED], $excluded))
            ->whereNotNull('technician_id')
            ->pluck('technician_id')
            ->unique()
            ->values()
            ->all();

        $team = \App\Models\User::query()
            ->where(function ($qq) use ($activeTechIds) {
                $qq->inRoles(\App\Models\User::teamRoles());
                if (!empty($activeTechIds)) {
                    $qq->orWhereIn('id', $activeTechIds);
                }
            })
            ->where('role', '!=', \App\Models\User::ROLE_ADMIN)
            ->withCount([
                'assignedRequests as active_count' => function ($q) use ($excluded) {
                    $q->whereNotIn('maintenance_requests.status', array_merge([MR::STATUS_RESOLVED, MR::STATUS_CLOSED], $excluded));
                },
                'assignedRequests as total_count' => function ($q) {
                    // no extra filter
                },
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        // ===== Main list query =====
        $query = MR::query()
            ->select([
                'id',
                'request_no',
                'request_date',
                'title',
                'description',
                'status',
                'priority',
                'updated_at',
                'created_at',
                'asset_id',
                'department_id',
                'location_text',
                'reporter_id',
                'reporter_name',
                'reporter_phone',
                'technician_id',
            ])
            ->with([
                'asset:id,name,asset_code',
                'department',
                'reporter:id,name,email',
                'technician:id,name',
            ])
            ->when($filter === 'my', fn($qb) => $qb->where('technician_id', $userId))
            ->when($filter === 'available', fn($qb) => $qb->whereNull('technician_id')->where('status', MR::STATUS_PENDING))
            ->when($tech, fn($qb) => $qb->where('technician_id', $tech))
            ->when($status !== '', fn($qb) => $qb->where('status', $status))
            ->when($q !== '', fn($qb) => $qb->search($q))
            ->whereNotIn('status', $excluded);

        /**
         * ✅ ลำดับใหม่ (แก้ “เรียงมั่ว”):
         * - ถ้าผู้ใช้เลือก sort_by/sort_dir → เคารพ sort ของผู้ใช้ก่อน (ไม่เอา status/priority มาขวาง)
         * - ถ้าไม่เลือกอะไรเลย → default เป็น “คิวงานราชการ”
         */
        $userSorting = ($sortBy !== '');

        if ($userSorting) {

            // กรณีเรียงตามเลขใบงาน: ดันค่าว่างไปท้ายเพื่อไม่ให้ปนด้านบน
            if ($sortBy === 'request_no') {
                $query->orderByRaw("CASE WHEN request_no IS NULL OR request_no = '' THEN 1 ELSE 0 END ASC");
            }

            // เรียงตามที่ user เลือก
            $query->orderBy($sortBy, $sortDir)
                ->orderByDesc('updated_at')
                ->orderByDesc('id');

        } else {

            // default: “คิวงานราชการ”
            $query->orderByRaw("FIELD(status,'pending','accepted','in_progress','on_hold','resolved','closed')")
                ->orderByRaw("FIELD(priority,'urgent','high','medium','low')")
                ->orderByDesc('request_date')
                ->orderByDesc('updated_at')
                ->orderByDesc('id');
        }

        $list = $query->paginate(20)->withQueryString();

        return view('repair.my-jobs', compact(
            'list',
            'stats',
            'team',
            'filter',
            'status',
            'q',
            'tech',
            'sortBy',
            'sortDir'
        ));
    }

    public function acceptCase(Request $request, MR $req)
    {
        \Gate::authorize('accept', $req);

        $userId = Auth::id();

        try {
            DB::transaction(function () use ($req, $userId) {

                $locked = MR::query()
                    ->whereKey($req->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // ✅ ต้องเป็นงานว่างจริงเท่านั้น
                // (กันกรณีงานถูกปิด/ยกเลิก/รับไปแล้ว)
                if ($locked->status !== MR::STATUS_PENDING) {
                    abort(409, 'งานนี้ไม่อยู่ในสถานะที่รับได้');
                }

                // ✅ กัน race condition: มีคนอื่นรับไปก่อน
                if (!empty($locked->technician_id) && (int) $locked->technician_id !== (int) $userId) {
                    abort(409, 'งานนี้ถูกรับไปแล้ว');
                }

                // ✅ รับงานผ่าน transition กลาง
                $this->applyTransition(
                    $locked,
                    ['status' => MR::STATUS_ACCEPTED],
                    $userId
                );
            });
        } catch (\Throwable $e) {

            // 409 = business rule (แสดง message ได้)
            $msg = ((int) $e->getCode() === 409)
                ? ($e->getMessage() ?: 'งานนี้ไม่อยู่ในสถานะที่รับได้')
                : 'เกิดข้อผิดพลาดในการรับเรื่อง';

            return back()->with(
                'toast',
                \App\Support\Toast::warning($msg, 2200)
            );
        }

        return back()->with(
            'toast',
            \App\Support\Toast::success('รับเรื่องเรียบร้อย', 1800)
        );
    }

    public function rejectCase(Request $request, MR $req)
    {
        \Gate::authorize('reject', $req);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $actorId = Auth::id();

        try {
            DB::transaction(function () use ($req, $actorId, $data) {

                $locked = MR::query()
                    ->whereKey($req->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // ✅ ต้องเป็นงานว่างจริงเท่านั้น (ยังอยู่ในคิว)
                if ($locked->status !== MR::STATUS_PENDING || !empty($locked->technician_id)) {
                    abort(409, 'งานนี้ไม่อยู่ในสถานะที่ไม่รับเรื่องได้');
                }

                // ✅ ไม่รับเรื่อง = งานยังอยู่ในคิว (pending)
                // เก็บเหตุผลไว้เพื่อ audit / analytics
                $locked->remark = $data['reason'];
                $locked->save();

                // ✅ log การไม่รับเรื่อง (ไม่เปลี่ยน status)
                \App\Models\MaintenanceLog::create([
                    'request_id' => $locked->id,
                    'action'     => 'decline', // ชัดกว่า reject ในเชิงความหมาย
                    'note'       => 'ไม่รับเรื่อง: ' . $data['reason'],
                    'user_id'    => $actorId,
                ]);
            });
        } catch (\Throwable $e) {

            $msg = ((int) $e->getCode() === 409)
                ? ($e->getMessage() ?: 'งานนี้ไม่อยู่ในสถานะที่ไม่รับเรื่องได้')
                : 'เกิดข้อผิดพลาดในการทำรายการ';

            return back()->with(
                'toast',
                \App\Support\Toast::warning($msg, 2200)
            );
        }

        return back()->with(
            'toast',
            \App\Support\Toast::success('บันทึกไม่รับเรื่องแล้ว (งานยังอยู่ในคิว)', 1800)
        );
    }

    public function cancelCase(Request $request, MR $req)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $actorId = Auth::id();

        try {
            DB::transaction(function () use ($req, $actorId, $data) {

                $locked = MR::query()
                    ->whereKey($req->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // ✅ งานจบแล้ว/ถูกยกเลิกแล้ว ห้ามทำซ้ำ
                if (in_array($locked->status, [
                    MR::STATUS_RESOLVED,
                    MR::STATUS_CLOSED,
                    MR::STATUS_CANCELLED,
                ], true)) {
                    abort(409, 'งานนี้อยู่ในสถานะที่ทำรายการไม่ได้');
                }

                // ✅ 1) ผู้แจ้ง / แอดมิน = ยกเลิกใบงานจริง
                if (\Gate::check('cancelByReporter', $locked)) {

                    $this->applyTransition(
                        $locked,
                        [
                            'status' => MR::STATUS_CANCELLED,
                            'note'   => 'ยกเลิกซ่อม: ' . $data['reason'],
                        ],
                        $actorId
                    );

                    return;
                }

                // ✅ 2) ช่าง = คืนงานเข้าคิว (return to pool)
                \Gate::authorize('cancelByTech', $locked);

                // คืนงานเข้าคิว = pending + ไม่มีช่าง
                $locked->update([
                    'status'        => MR::STATUS_PENDING,
                    'technician_id' => null,
                    'remark'        => $data['reason'],

                    // เคลียร์ timeline ที่สะท้อนการรับ/เริ่มงาน เพื่อไม่ให้ข้อมูลหลอก
                    'accepted_at'   => null,
                    'started_at'    => null,
                    'on_hold_at'    => null,
                ]);

                // log audit
                \App\Models\MaintenanceLog::create([
                    'request_id' => $locked->id,
                    'action'     => 'returned_to_pool',
                    'note'       => 'คืนงานเข้าคิว: ' . $data['reason'],
                    'user_id'    => $actorId,
                ]);
            });
        } catch (\Throwable $e) {

            $msg = ((int) $e->getCode() === 409)
                ? ($e->getMessage() ?: 'งานนี้อยู่ในสถานะที่ทำรายการไม่ได้')
                : 'เกิดข้อผิดพลาดในการทำรายการ';

            return back()->with('toast', \App\Support\Toast::warning($msg, 2200));
        }

        return back()->with('toast', \App\Support\Toast::success('ทำรายการเรียบร้อย', 1800));
    }


    public function acceptJobQuick(Request $request, MR $req)
    {
        // โหมดที่รองรับ:
        // - accepted (รับเรื่อง) -> ใช้ acceptCase เดิม
        // - in_progress (กำลังดำเนินการ + เลือกช่าง)

        $decision = strtolower((string) $request->input('decision', 'accepted'));

        // 1) รับเรื่องแบบเดิม: ส่งต่อให้ acceptCase (คง behavior เดิมทั้งหมด)
        if ($decision === 'accepted' || $decision === '') {
            return $this->acceptCase($request, $req);
        }

        // 2) กำลังดำเนินการ: ต้องเลือกช่าง
        if ($decision !== 'in_progress') {
            return back()->with('toast', \App\Support\Toast::warning('รูปแบบการดำเนินการไม่ถูกต้อง', 2200));
        }

        // 권한: รับเรื่อง + มอบหมาย (ถ้า policy คุณแยก)
        \Gate::authorize('accept', $req);
        \Gate::authorize('assign', $req);

        $data = $request->validate([
            'technician_id' => ['required', 'integer', 'exists:users,id'],
            // 'position' => ['nullable', 'string', 'max:255'], // ถ้าคุณจะเก็บตำแหน่งจริง ค่อยเปิดใช้
        ]);

        $actorId = Auth::id();
        $technicianId = (int) $data['technician_id'];

        try {
            DB::transaction(function () use ($req, $actorId, $technicianId) {

                $locked = MR::query()
                    ->whereKey($req->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // ✅ ต้องเป็นงานว่างจริงเท่านั้น
                if ($locked->status !== MR::STATUS_PENDING) {
                    abort(409, 'งานนี้ไม่อยู่ในสถานะที่รับได้');
                }

                // ✅ กัน race condition: มีคนอื่นรับไปก่อน
                if (!empty($locked->technician_id) && (int) $locked->technician_id !== (int) $actorId) {
                    abort(409, 'งานนี้ถูกรับไปแล้ว');
                }

                // ✅ เริ่มดำเนินการทันที + ระบุช่างหลัก
                $this->applyTransition(
                    $locked,
                    [
                        'status' => MR::STATUS_IN_PROGRESS,
                        'technician_id' => $technicianId,
                        // 'position' => ... (ถ้าคุณมี field นี้จริง ค่อยใส่)
                    ],
                    $actorId
                );
            });
        } catch (\Throwable $e) {

            $msg = ((int) $e->getCode() === 409)
                ? ($e->getMessage() ?: 'งานนี้ไม่อยู่ในสถานะที่รับได้')
                : 'เกิดข้อผิดพลาดในการรับเรื่อง';

            return back()->with('toast', \App\Support\Toast::warning($msg, 2200));
        }

        return back()->with('toast', \App\Support\Toast::success('เริ่มดำเนินการและมอบหมายช่างเรียบร้อย', 1800));
    }

    public function showPage(MR $maintenanceRequest)
    {
        \Gate::authorize('view', $maintenanceRequest);

        $maintenanceRequest->loadMissing([
            'asset',
            'department',
            'reporter:id,name,email',
            'technician:id,name',

            'assignments.user:id,name,role,profile_photo_path,profile_photo_thumb',

            'attachments' => fn($q) => $q->with('file'),
            'logs.user:id,name',
            'rating',
            'rating.rater:id,name',
            'operationLog.user:id,name',
        ]);

        $techUsers = \App\Models\User::query()
            ->inRoles(\App\Models\User::teamRoles())
            ->orderBy('name')
            ->get(['id','name']);

        return view('maintenance.requests.show', [
            'req' => $maintenanceRequest,
            'techUsers' => $techUsers,
        ]);
    }

    public function createPage()
    {
        $assets = \App\Models\Asset::orderBy('asset_code')->get(['id','asset_code','name']);
        $users  = \App\Models\User::orderBy('name')->get(['id','name']);
        $depts  = \App\Models\Department::orderBy('name_th')->get(['id','code','name_th','name_en']);

        return view('maintenance.requests.create', compact('assets','users','depts'));
    }

    public function index(Request $request)
    {
        $status   = $request->string('status')->toString();
        $priority = $request->string('priority')->toString();
        $q        = trim($request->string('q')->toString());
        $assetId  = $request->integer('asset_id');

        $user = $request->user();

        // ---- ใช้ helper ดึงค่าการเรียง + จัดการ session ต่อ user ----
        [$sortBy, $sortDir] = $this->resolveSort($request);

        $query = MR::query()
            ->with(['asset','reporter:id,name,email','technician:id,name'])

            // API/Web: บังคับ filter เหมือนกัน สำหรับ Member เท่านั้น
            ->when(
                ($user && !$user->isAdmin() && !$user->isSupervisor() && !$user->isTechnician()),
                fn($qb) => $qb->where('reporter_id', $user->id)
            )

            ->when($assetId, fn($qb) => $qb->where('asset_id', $assetId))
            ->when($status, fn($qb) => $qb->where('status', $status))
            ->when($priority, fn($qb) => $qb->where('priority', $priority))

            ->when($q !== '', fn($qb) => $qb->search($q));

        if ($q !== '') {
            // กันผลสลับแถว + ทำให้ผลคงที่
            $query->orderByDesc('id');
        } else {
            if ($sortBy === 'request_no') {
                $dir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

                // 1) เอา request_no ว่าง/NULL ไปท้ายเสมอ
                $query->orderByRaw("CASE WHEN request_no IS NULL OR request_no = '' THEN 1 ELSE 0 END ASC");

                // 2) เรียง request_no ตามทิศทางที่เลือก
                $query->orderBy('request_no', $dir);

                // 3) tie-breaker กันสลับแถว
                $query->orderBy('id', $dir);
            } else {
                // safety
                $allowed = ['request_no', 'id', 'request_date'];
                if (!in_array($sortBy, $allowed, true)) {
                    $sortBy = 'request_no';
                }
                $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

                $query->orderBy($sortBy, $sortDir);
            }
        }

        $list = $query
            ->paginate(20)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $list->items(),
                'meta' => [
                    'current_page' => $list->currentPage(),
                    'per_page'     => $list->perPage(),
                    'total'        => $list->total(),
                    'last_page'    => $list->lastPage(),
                ],
                'toast' => [
                    'type' => 'info',
                    'message' => 'โหลดรายการคำขอบำรุงรักษาแล้ว',
                    'position' => 'tc',
                    'timeout' => 1200,
                    'size' => 'sm',
                ],
            ]);
        }

        return view('maintenance.requests.index', compact('list','status','priority','q','sortBy','sortDir'));
    }

    public function store(Request $request)
    {
        $rules = [
            'title'        => ['required','string','max:255'],
            'priority'     => ['required', Rule::in(['low','medium','high','urgent'])],

            'asset_id'      => ['nullable','integer','exists:assets,id'],
            'department_id' => ['nullable','integer','exists:departments,id'],
            'location_text' => ['nullable','string','max:255'],

            'reporter_name'  => ['nullable','string','max:255'],
            'reporter_phone' => ['nullable','string','max:30'],
            'reporter_email' => ['nullable','email','max:255'],

            'description'   => ['nullable','string','max:5000'],
        ];

        $data = Validator::make($request->all(), $rules)->validate();
        $user = $request->user();

        $req = MR::create([
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'priority'     => $data['priority'],
            'status'       => 'pending',
            'request_date' => now(),

            'asset_id'      => $data['asset_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'location_text' => $data['location_text'] ?? null,

            // ผู้แจ้ง
            'reporter_id'    => $user?->id,
            'reporter_name'  => $data['reporter_name'] ?? $user?->name,
            'reporter_email' => $data['reporter_email'] ?? $user?->email,
            'reporter_phone' => $data['reporter_phone'] ?? null,

            'technician_id' => null,
        ]);

        return redirect()->route('maintenance.requests.show', $req);
    }

    public function update(Request $request, MR $req)
    {
        \Gate::authorize('update', $req);

        $user    = $request->user();
        $actorId = $user?->id;
        $isTeam  = $user && ($user->isAdmin() || $user->isSupervisor() || $user->isTechnician());

        $maxKb     = config('uploads.max_kb', 10240);
        $mimetypes = implode(',', config('uploads.mimetypes', ['image/*','application/pdf']));
        $fileRules = ['file', 'max:'.$maxKb, 'mimetypes:'.$mimetypes];

        $rules = [
            'title'        => ['sometimes','required','string','max:255'],
            'description'  => ['nullable','string','max:5000'],
            'asset_id'     => ['nullable','integer','exists:assets,id'],
            'priority'     => ['nullable', Rule::in(['low','medium','high','urgent'])],
            'request_date' => ['nullable','date'],

            'reporter_name'  => ['nullable','string','max:255'],
            'reporter_phone' => ['nullable','string','max:30'],
            'reporter_email' => ['nullable','email','max:255'],

            'department_id'   => ['nullable','integer','exists:departments,id'],
            'location_text'   => ['nullable','string','max:255'],
            'resolution_note' => ['nullable','string','max:5000'],
            'cost'            => ['nullable','numeric','min:0','max:99999999.99'],
            'files.*'         => $fileRules,

            'technician_id' => array_values(array_filter([
                'bail',
                Rule::prohibitedIf(!$isTeam),
                'nullable',
                'integer',
                'exists:users,id',
            ])),

            'status' => $isTeam
                ? ['nullable', Rule::in(['pending','accepted','in_progress','on_hold','resolved','closed','cancelled'])]
                : ['nullable', Rule::in(['cancelled'])],

            // ---- operation log ----
            'operation_date'   => ['nullable','date'],
            'operation_method' => ['nullable', Rule::in(['requisition','service_fee','other'])],
            'property_code'    => ['nullable','string','max:100'],
            'require_precheck' => ['nullable','boolean'],
            'remark'           => ['nullable','string','max:5000'],
            'issue_software'   => ['nullable','boolean'],
            'issue_hardware'   => ['nullable','boolean'],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('toast', \App\Support\Toast::warning('ข้อมูลไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง', 2600));
        }

        $data = $validator->validated();

        DB::transaction(function () use ($data, $request, $req, $isTeam, $actorId) {

            $originalStatus = $req->status;
            $originalTechId = (int) ($req->technician_id ?? 0);

            // ✅ สมาชิกทั่วไป: จำกัดสิทธิ์เหมือนเดิม
            if (!$isTeam) {
                if (($data['status'] ?? null) === 'cancelled') {
                    if (!in_array($req->status, ['pending','accepted'], true) || !empty($req->technician_id)) {
                        unset($data['status']);
                    }
                } else {
                    unset($data['status']);
                }

                unset(
                    $data['technician_id'],
                    $data['cost'],
                    $data['resolution_note'],
                    $data['operation_date'],
                    $data['operation_method'],
                    $data['property_code'],
                    $data['require_precheck'],
                    $data['remark'],
                    $data['issue_software'],
                    $data['issue_hardware']
                );
            }

            // fill fields
            $req->fill($data);

            // auto-assign เมื่อ accepted
            if (
                $isTeam &&
                (($data['status'] ?? null) === 'accepted') &&
                empty($req->technician_id) &&
                $actorId
            ) {
                $req->technician_id = $actorId;
            }

            $req->save();

            /* ---------- timeline ---------- */
            if (array_key_exists('status', $data) && $originalStatus !== $req->status) {
                $now = now();
                match ($req->status) {
                    'accepted'    => $req->accepted_at ??= $now,
                    'in_progress' => $req->started_at  ??= $now,
                    'on_hold'     => $req->on_hold_at  ??= $now,
                    'resolved'    => $req->resolved_at ??= $now,
                    'closed'      => [
                        $req->closed_at      ??= $now,
                        $req->completed_date ??= $now,
                    ],
                    default => null,
                };
                if ($req->status === 'accepted' && empty($req->assigned_date)) {
                    $req->assigned_date = $now;
                }
                $req->save();
            }

            /* ---------- assignment ---------- */
            $newTechId     = (int) ($req->technician_id ?? 0);
            $techChanged   = $isTeam && $originalTechId !== $newTechId;
            $statusChanged = array_key_exists('status', $data) && $originalStatus !== $req->status;

            if ($isTeam && ($techChanged || $statusChanged) && $newTechId > 0) {
                $this->syncAssignment($req, $newTechId, $actorId, true);
            }

            /* ---------- log ---------- */
            if (class_exists(\App\Models\MaintenanceLog::class)) {
                \App\Models\MaintenanceLog::create([
                    'request_id'  => $req->id,
                    'action'      => ($statusChanged
                        ? \App\Models\MaintenanceLog::ACTION_TRANSITION
                        : \App\Models\MaintenanceLog::ACTION_UPDATE),
                    'note'        => $statusChanged
                        ? $this->defaultNoteForStatus($req->status, $actorId, $req)
                        : null,
                    'user_id'     => $actorId,
                    'from_status' => $statusChanged ? $originalStatus : null,
                    'to_status'   => $statusChanged ? $req->status : null,
                ]);
            }

            /* ---------- remove attachments ---------- */
            $toRemove = array_filter(
                (array) $request->input('remove_attachments', []),
                fn($v) => is_numeric($v)
            );

            if (!empty($toRemove)) {
                $req->attachments()->whereIn('id', $toRemove)->get()
                    ->each(fn($att) => $att->deleteAndCleanup(true));
            }

            /* ---------- upload attachments ---------- */
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $up) {
                    $disk = 'public';
                    $storedPath = $up->store("maintenance/{$req->id}", $disk);

                    $sha = hash_file('sha256', $up->getRealPath());

                    $file = File::firstOrCreate(
                        ['checksum_sha256' => $sha],
                        [
                            'path'      => $storedPath,
                            'disk'      => $disk,
                            'mime'      => $up->getClientMimeType(),
                            'size'      => $up->getSize(),
                            'path_hash' => hash('sha256', $storedPath),
                        ]
                    );

                    $existing = $req->attachments()->withTrashed()->where('file_id', $file->id)->first();
                    if ($existing) {
                        if ($existing->trashed()) $existing->restore();
                        continue;
                    }

                    $req->attachments()->create([
                        'file_id'       => $file->id,
                        'original_name' => $up->getClientOriginalName(),
                        'extension'     => $up->getClientOriginalExtension() ?: null,
                        'uploaded_by'   => $actorId,
                        'source'        => 'web',
                        'is_private'    => false,
                        'order_column'  => 0,
                    ]);
                }
            }

            //operation log
            $hasOp =
                array_key_exists('operation_date', $data) ||
                array_key_exists('operation_method', $data) ||
                array_key_exists('property_code', $data) ||
                array_key_exists('remark', $data) ||
                array_key_exists('require_precheck', $data) ||
                array_key_exists('issue_software', $data) ||
                array_key_exists('issue_hardware', $data) ||
                $req->operationLog()->exists();

            if ($hasOp) {
                $opDate = $data['operation_date'] ?? null;
                if (!empty($opDate)) {
                    $opDate = \Carbon\Carbon::parse($opDate)->toDateString();
                }

                $req->operationLog()->updateOrCreate(
                    ['maintenance_request_id' => $req->id],
                    [
                        'operation_date'   => $opDate,
                        'operation_method' => $data['operation_method'] ?? null,
                        'property_code'    => $data['property_code'] ?? null,
                        'require_precheck' => (bool) ($data['require_precheck'] ?? false),
                        'remark'           => $data['remark'] ?? null,
                        'issue_software'   => (bool) ($data['issue_software'] ?? false),
                        'issue_hardware'   => (bool) ($data['issue_hardware'] ?? false),
                        'user_id'          => $actorId,
                    ]
                );
            }
        });

        $req->load(['attachments.file','operationLog']);

        return $this->respondWithToast(
            $request,
            \App\Support\Toast::success('อัปเดตคำขอเรียบร้อย', 1600),
            redirect()->route('maintenance.requests.show', $req),
            ['data' => $req]
        );
    }

    public function transition(Request $request, MR $req)
    {
        \Gate::authorize('transition', $req);

        $user = $request->user();
        $actorId = optional($user)->id;

        $isTeam = $user && ($user->isAdmin() || $user->isSupervisor() || $user->isTechnician());

        $rules = [
            'status' => $isTeam
                ? ['bail','required', Rule::in(['pending','accepted','in_progress','on_hold','resolved','closed','cancelled'])]
                : ['prohibited'],

            'note' => ['nullable','string','max:2000'],

            'technician_id' => array_values(array_filter([
                Rule::prohibitedIf(!$isTeam),
                'nullable','integer','exists:users,id',
            ])),
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $fieldsHuman = ['status' => 'สถานะ','technician_id' => 'รหัสช่าง','note'=>'บันทึก'];
            $bad = collect(array_keys($errors->toArray()))
                ->map(fn($f) => $fieldsHuman[$f] ?? $f)
                ->implode(', ');
            $msg = $bad ? ('ข้อมูลไม่ถูกต้อง: '.$bad) : 'ข้อมูลไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง';

            if (!$request->expectsJson()) {
                return redirect()->back()->withErrors($validator)->withInput()
                    ->with('toast', \App\Support\Toast::warning($msg, 2200));
            }

            return response()->json([
                'errors' => $errors,
                'toast'  => \App\Support\Toast::warning($msg, 2200),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $validator->validated();

        $req = $this->applyTransition($req, $data, $actorId);

        return $this->respondWithToast(
            $request,
            \App\Support\Toast::success('บันทึกสถานะเรียบร้อย', 1800),
            redirect()->back(),
            ['data' => $req]
        );
    }

    public function transitionFromBlade(Request $request, MR $req)
    {
        \Gate::authorize('transition', $req);
        $action = (string) $request->string('action');

        if ($action) {
            $map = [
                'accept' => 'accepted',
                'assign' => 'accepted',
                'start'  => 'in_progress',
            ];
            $status = $map[$action] ?? null;

            if ($status) {
                $payload = [
                    'status' => $status,
                    'note'   => $request->string('note')->toString(),
                ];
                if (in_array($action, ['accept','assign'], true)) {
                    $payload['technician_id'] = $request->integer('technician_id') ?: optional(Auth::user())->id;
                }

                $updated = $this->applyTransition($req, $payload, optional(Auth::user())->id);

                $toastMessage = match ($action) {
                    'accept' => 'รับงานแล้ว',
                    'assign' => 'มอบหมายให้ '.($updated->technician->name ?? 'คุณ')." แล้ว",
                    'start'  => 'เริ่มงานแล้ว',
                    default  => 'บันทึกสถานะเรียบร้อย',
                };

                return $this->respondWithToast(
                    $request,
                    \App\Support\Toast::success($toastMessage, 1800),
                    redirect()->route('repairs.queue', ['just' => $updated->id]),
                    ['data' => $updated]
                );
            }
        }

        return $this->transition($request, $req);
    }

    protected function applyTransition(MR $req, array $data, ?int $actorId = null): MR
    {
        DB::transaction(function () use ($req, $data, $actorId) {

            $originalStatus = $req->status;
            $originalTechId = (int) ($req->technician_id ?? 0);

            // ต้องมี status เสมอ
            $req->status = $data['status'];

            // เปลี่ยนช่างจาก payload (เฉพาะทีมงาน/ผ่าน policy แล้ว)
            if (!empty($data['technician_id']) && (int)$req->technician_id !== (int)$data['technician_id']) {
                $req->technician_id = (int) $data['technician_id'];
            }

            // รับงาน แต่ยังไม่มีช่าง -> ตั้งเป็นผู้กดรับ
            if ($req->status === 'accepted' && empty($req->technician_id) && $actorId) {
                $req->technician_id = (int) $actorId;
            }

            // ---- timeline ----
            $now = now();
            switch ($req->status) {
                case 'accepted':
                    if (empty($req->accepted_at)) $req->accepted_at = $now;
                    if (empty($req->assigned_date)) $req->assigned_date = $now;
                    break;

                case 'in_progress':
                    if (empty($req->started_at)) $req->started_at = $now;
                    break;

                case 'on_hold':
                    if (empty($req->on_hold_at)) $req->on_hold_at = $now;
                    break;

                case 'resolved':
                    if (empty($req->resolved_at)) $req->resolved_at = $now;
                    break;

                case 'closed':
                    if (empty($req->closed_at)) $req->closed_at = $now;
                    if (empty($req->completed_date)) $req->completed_date = $now;
                    break;
            }

            $req->save();

            $statusChanged = $originalStatus !== $req->status;

            $newTechId = (int) ($req->technician_id ?? 0);
            $techChanged = ($originalTechId !== $newTechId);

            if (($techChanged || $statusChanged) && $newTechId > 0) {
                $this->syncAssignment($req, $newTechId, $actorId, true);
            }

            // log
            if (class_exists(\App\Models\MaintenanceLog::class)) {
                $defaultNote = $data['note']
                    ?? $this->defaultNoteForStatus($req->status, $actorId, $req);

                // ถ้ามีการเปลี่ยนช่าง ให้ใส่ชื่อช่างในโน้ต
                if ($techChanged && $req->technician) {
                    $defaultNote = trim(
                        ($defaultNote ? $defaultNote.' • ' : '') .
                        'ช่าง: '.$req->technician->name
                    );
                }

                \App\Models\MaintenanceLog::create([
                    'request_id'  => $req->id,
                    'action'      => \App\Models\MaintenanceLog::ACTION_TRANSITION,
                    'note'        => $defaultNote ?: null,
                    'user_id'     => $actorId,
                    'from_status' => $originalStatus,
                    'to_status'   => $req->status,
                ]);
            }
        });

        return $req->fresh(['technician:id,name']);
    }

    public function uploadAttachmentFromBlade(Request $request, MR $req)
    {
        \Gate::authorize('attach', $req);
        $maxKb = config('uploads.max_kb', 10240);
        $mimetypes = implode(',', config('uploads.mimetypes', ['image/*','application/pdf']));
        $fileRules = ['required','file','max:'.$maxKb,'mimetypes:'.$mimetypes];

        $validated = $request->validate([
            'file'       => $fileRules,
            'is_private' => ['nullable','boolean'],
            'caption'    => ['nullable','string','max:255'],
            'alt_text'   => ['nullable','string','max:255'],
        ]);

        $up = $validated['file'];
        $isPrivate = (bool) ($validated['is_private'] ?? false);
        $disk = $isPrivate ? 'local' : 'public';
        $storedPath = $up->store("maintenance/{$req->id}", $disk);

        $stream = fopen($up->getRealPath(), 'r');
        $ctx = hash_init('sha256');
        while (!feof($stream)) {
            $buf = fread($stream, 1024 * 1024);
            if ($buf === false) break;
            hash_update($ctx, $buf);
        }
        fclose($stream);
        $sha = hash_final($ctx);

        $file = File::firstOrCreate(
            ['checksum_sha256' => $sha],
            [
                'path'       => $storedPath,
                'disk'       => $disk,
                'mime'       => $up->getClientMimeType(),
                'size'       => $up->getSize(),
                'path_hash'  => hash('sha256', $storedPath),
                'meta'       => null,
            ]
        );

        $existing = $req->attachments()->withTrashed()->where('file_id', $file->id)->first();
        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->fill([
                'original_name' => $up->getClientOriginalName(),
                'extension'     => $up->getClientOriginalExtension() ?: $existing->extension,
                'uploaded_by'   => optional($request->user())->id,
                'is_private'    => $isPrivate,
                'caption'       => $validated['caption'] ?? $existing->caption,
                'alt_text'      => $validated['alt_text'] ?? $existing->alt_text,
            ])->save();

            return $this->respondWithToast(
                $request,
                \App\Support\Toast::info('ไฟล์นี้ถูกแนบไว้แล้ว (อัปเดตข้อมูลใหม่)', 1600),
                redirect()->back(),
                ['duplicate' => true, 'attachment_id' => $existing->id]
            );
        }

        $req->attachments()->create([
            'file_id'       => $file->id,
            'original_name' => $up->getClientOriginalName(),
            'extension'     => $up->getClientOriginalExtension() ?: null,
            'uploaded_by'   => optional($request->user())->id,
            'source'        => 'web',
            'is_private'    => $isPrivate,
            'caption'       => $validated['caption'] ?? null,
            'alt_text'      => $validated['alt_text'] ?? null,
            'order_column'  => 0,
        ]);

        return $this->respondWithToast(
            $request,
            \App\Support\Toast::success('อัปโหลดไฟล์แนบแล้ว', 1800),
            redirect()->back(),
            ['data' => $req->fresh('attachments.file')]
        );
    }

    public function destroyAttachment(MR $req, Attachment $attachment)
    {
        \Gate::authorize('deleteAttachment', $req);
        abort_unless(
            $attachment->attachable_type === MR::class &&
            (int) $attachment->attachable_id === (int) $req->id,
            404
        );

        $attachment->deleteAndCleanup(true);

        return $this->respondWithToast(
            request(),
            \App\Support\Toast::success('ลบไฟล์แนบแล้ว', 1600),
            redirect()->back(),
            ['deleted' => true]
        );
    }

    protected function respondWithToast(
        Request $request,
        array $toast,
        $webRedirect,
        array $jsonPayload = [],
        int $status = Response::HTTP_OK
    ) {
        if (!$request->expectsJson()) {
            return $webRedirect->with('toast', [
                'type'     => $toast['type']    ?? 'info',
                'message'  => $toast['message'] ?? '',
                'position' => $toast['position'] ?? 'tc',
                'timeout'  => $toast['timeout']  ?? 2000,
                'size'     => $toast['size']     ?? 'sm',
            ]);
        }

        $payload = array_merge($jsonPayload, [
            'toast' => [
                'type'     => $toast['type']    ?? 'info',
                'message'  => $toast['message'] ?? '',
                'position' => $toast['position'] ?? 'tc',
                'timeout'  => $toast['timeout']  ?? 2000,
                'size'     => $toast['size']     ?? 'sm',
            ],
        ]);

        return response()->json($payload, $status);
    }

    public function edit($id)
    {
        $mr = \App\Models\MaintenanceRequest::with([
                'asset',
                'reporter',
                'attachments.file',
                'operationLog',
            ])
            ->findOrFail($id);

        \Gate::authorize('update', $mr);

        $assets = \App\Models\Asset::orderBy('asset_code')->get(['id','asset_code','name']);
        $users  = \App\Models\User::orderBy('name')->get(['id','name']);
        $depts  = \App\Models\Department::orderBy('name_th')->get(['id','code','name_th','name_en']);

        $attachments = $mr->attachments()
            ->select(['id','file_id','original_name','is_private','order_column','attachable_id','attachable_type'])
            ->with(['file:id,path,disk,mime,size'])
            ->get();

        return view('maintenance.requests.edit', compact('mr','assets','users','attachments','depts'));
    }

    protected function defaultNoteForStatus(string $status, ?int $actorId, MR $req): string
    {
        $actorName = optional(\App\Models\User::find($actorId))->name;

        return match ($status) {
            'pending'     => 'ตั้งคิวงานใหม่',
            'accepted'    => $actorName ? ('รับงานโดย '.$actorName) : 'รับงานแล้ว',
            'in_progress' => 'เริ่มดำเนินการซ่อม',
            'on_hold'     => 'พักงานชั่วคราว',
            'resolved'    => 'แก้ไขเสร็จ รอตรวจรับ',
            'closed'      => 'ปิดงานเรียบร้อย',
            'cancelled'   => 'ยกเลิกคำขอ',
            default       => 'อัปเดตสถานะ',
        };
    }

    public function printWorkOrder(Request $request, MR $maintenanceRequest)
    {
        \Gate::authorize('view', $maintenanceRequest);

        $maintenanceRequest->loadMissing([
            'asset',
            'reporter:id,name,email',
            'technician:id,name',
            'attachments' => fn($qq) => $qq->with('file'),
            'logs.user:id,name',
            'rating',
            'rating.rater:id,name',
        ]);

        $hospital = [
            'name_th'  => 'โรงพยาบาลพระปกเกล้า',
            'name_en'  => 'PHRAPOKKLAO HOSPITAL',
            'subtitle' => 'Maintenance Work Order',
            'logo'     => public_path('images/logoppk1.png'),
        ];

        $fileName = sprintf(
            'maintenance-work-order-%s.pdf',
            $maintenanceRequest->request_no ?? $maintenanceRequest->id
        );

        $pdf = Pdf::loadView('maintenance.requests.print', [
                'req'      => $maintenanceRequest,
                'hospital' => $hospital,
            ])
            ->setPaper('A4', 'portrait');

        return $pdf->stream($fileName);
    }

    protected function resolveSort(Request $request): array
    {
        $user   = $request->user();
        $userId = $user?->id;

        $sessionSortByKey  = $userId ? "maintenance_sort_by_user_{$userId}"  : 'maintenance_sort_by_guest';
        $sessionSortDirKey = $userId ? "maintenance_sort_dir_user_{$userId}" : 'maintenance_sort_dir_guest';

        $allowedSorts = ['request_no', 'id', 'request_date'];

        $sortByReq  = $request->query('sort_by');
        $sortDirReq = strtolower((string) $request->query('sort_dir'));

        // sort_by
        if (in_array($sortByReq, $allowedSorts, true)) {
            $sortBy = $sortByReq;
            session([$sessionSortByKey => $sortBy]);
        } else {
            $sortBy = session($sessionSortByKey, 'request_no');
        }

        // sort_dir
        if (in_array($sortDirReq, ['asc','desc'], true)) {
            $sortDir = $sortDirReq;
            session([$sessionSortDirKey => $sortDir]);
        } else {
            $sortDir = session($sessionSortDirKey, 'desc');
        }

        return [$sortBy, $sortDir];
    }

    protected function syncAssignment(MR $req, int $userId, ?int $actorId = null, bool $isLead = true): void
    {
        $status = match ($req->status) {
            'resolved', 'closed' => MaintenanceAssignment::STATUS_DONE,
            'cancelled'          => MaintenanceAssignment::STATUS_CANCELLED,
            default              => MaintenanceAssignment::STATUS_IN_PROGRESS,
        };

        $workerRole = \App\Models\User::query()->whereKey($userId)->value('role') ?? 'technician';

        $as = MaintenanceAssignment::updateOrCreate(
            ['maintenance_request_id' => $req->id, 'user_id' => $userId],
            [
                'role'    => $workerRole,
                'is_lead' => $isLead,
                'status'  => $status,
            ]
        );

        // ไม่รีเซ็ต assigned_at ทุกครั้ง (ตั้งครั้งแรกเท่านั้น)
        if (empty($as->assigned_at)) {
            $as->assigned_at = now();
        }

        $as->save();
    }


}
