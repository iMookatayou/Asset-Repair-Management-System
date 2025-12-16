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
        $q        = $request->string('q')->toString();

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
            // จำกัดเฉพาะผู้ใช้ระดับ Member (computer_officer) ให้เห็นงานที่ตนแจ้งเท่านั้น
            // Admin / Supervisor / Technician roles เห็นทั้งหมด
            ->when(
                ($user && !$user->isAdmin() && !$user->isSupervisor() && !$user->isTechnician()),
                fn($qb) => $qb->where('reporter_id', $user->id)
            )
            ->when($status, fn ($qb) => $qb->where('status', $status))
            ->when($priority, fn ($qb) => $qb->where('priority', $priority))
            ->when($q, function ($w) use ($q) {
                $w->where(function ($ww) use ($q) {
                    $ww->where('title','like',"%{$q}%")
                    ->orWhere('description','like',"%{$q}%")
                    ->orWhere('request_no','like',"%{$q}%")              // ค้นด้วยเลขใบงาน 68xxxx
                    ->orWhere('reporter_name','like',"%{$q}%")           // ชื่อผู้แจ้ง
                    ->orWhere('reporter_position','like',"%{$q}%")       // ตำแหน่งผู้แจ้ง
                    ->orWhere('reporter_phone','like',"%{$q}%")          // เบอร์ผู้แจ้ง
                    ->orWhere('reporter_email','like',"%{$q}%")          // อีเมลที่เก็บในฟิลด์
                    ->orWhereHas('reporter', fn($qr) =>                  // user ภายใน
                            $qr->where('email','like',"%{$q}%")
                            ->orWhere('name','like',"%{$q}%")
                    );
                });
            });

        if ($sortBy === 'request_no') {
            $dir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

            // 1) เอา request_no ว่าง/NULL ไปท้ายเสมอ
            $query->orderByRaw("CASE WHEN request_no IS NULL OR request_no = '' THEN 1 ELSE 0 END ASC");

            // 2) เรียง request_no ตามทิศทางที่เลือก
            $query->orderBy('request_no', $dir);

            // 3) tie-breaker กันสลับแถว
            $query->orderBy('id', $dir);
        } else {
            $query->orderBy($sortBy, $sortDir);
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
                      ->orWhere('request_no','like',"%{$q}%")              // ค้นด้วยเลขใบงาน 68xxxx
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
            'completed'   => MR::query()->where('status','resolved')->orWhere('status','closed')->count(),
        ];

        return view('repair.queue', compact('list','stats','just'));
    }

    public function myJobsPage(Request $request)
    {
        \Gate::authorize('view-my-jobs');
        $user = Auth::user();
        $userId = $user->id;

        $filter = $request->string('filter')->toString() ?: 'all'; // my|available|all
        $status = $request->string('status')->toString();
        $q      = $request->string('q')->toString();
        $tech   = $request->integer('tech'); // optional technician filter

        // สถิติภาพรวม
        $stats = [
            'pending'     => MR::whereIn('status', ['pending'])->count(),
            'in_progress' => MR::whereIn('status', ['accepted','in_progress','on_hold'])->count(),
            'completed'   => MR::whereIn('status', ['resolved','closed'])->count(),
            'my_active'   => MR::where('technician_id', $userId)
                ->whereNotIn('status', ['resolved','closed','cancelled'])->count(),
        ];

        // ทีมงาน (หัวหน้า + ช่าง) + ผู้ที่ถูกระบุเป็น technician ในงานที่ยังไม่เสร็จ
        $activeTechIds = MR::query()
            ->whereNotIn('status', ['resolved','closed','cancelled'])
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
                'assignedRequests as active_count' => function ($q) {
                    $q->whereNotIn('maintenance_requests.status', ['resolved','closed','cancelled'])
                    ->whereNull('maintenance_requests.deleted_at');
                },
                'assignedRequests as total_count' => function ($q) {
                    $q->whereNull('maintenance_requests.deleted_at');
                },
            ])
            ->orderBy('name')
            ->get(['id','name','role']);

        // รายการงาน
        $query = MR::query()
            ->with(['asset','reporter:id,name,email','technician:id,name'])
            ->when($filter === 'my', fn($qb) => $qb->where('technician_id', $userId))
            ->when($filter === 'available', fn($qb) => $qb->whereNull('technician_id')->whereIn('status', ['pending','accepted']))
            ->when($tech, fn($qb) => $qb->where('technician_id', $tech))
            ->when($status, fn($qb) => $qb->where('status', $status))
            ->when($q, function ($qb) use ($q) {
                $qb->where(function ($w) use ($q) {
                    $w->where('title','like',"%{$q}%")
                      ->orWhere('description','like',"%{$q}%")
                      ->orWhere('request_no','like',"%{$q}%")              // ค้นด้วยเลขใบงาน 68xxxx
                      ->orWhereHas('asset', fn($qa) => $qa->where('name','like',"%{$q}%")->orWhere('asset_code','like',"%{$q}%"));
                });
            })
            ->whereNotIn('status', ['cancelled'])
            ->orderByRaw("FIELD(status,'pending','accepted','in_progress','on_hold','resolved','closed')")
            ->orderByRaw("FIELD(priority,'urgent','high','medium','low')")
            ->orderByDesc('updated_at');

        $list = $query->paginate(20)->withQueryString();

        return view('repair.my-jobs', compact('list','stats','team','filter','status','q','tech'));
    }

    public function acceptJobQuick(Request $request, MR $req)
    {
        \Gate::authorize('accept', $req);

        $userId = Auth::id();

        try {
            DB::transaction(function () use ($req, $userId) {

                $locked = MR::query()
                    ->whereKey($req->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // ถ้ามีคนรับไปแล้ว (และไม่ใช่เรา) ให้ชน 409
                if (!empty($locked->technician_id) && (int)$locked->technician_id !== (int)$userId) {
                    abort(409, 'งานนี้ถูกรับไปแล้วโดยคนอื่น');
                }

                // รับงาน -> accepted + auto-assign ใน applyTransition
                $this->applyTransition($locked, ['status' => 'accepted'], $userId);
            });
        } catch (\Throwable $e) {
            // ถ้าชน 409 หรือ error อื่น ๆ
            $msg = $e->getCode() === 409 ? 'งานนี้ถูกรับไปแล้วโดยคนอื่น' : 'เกิดข้อผิดพลาดในการรับงาน';
            return redirect()->back()->with('toast', \App\Support\Toast::warning($msg, 2200));
        }

        return redirect()->back()->with('toast', \App\Support\Toast::success('รับงาน #'.$req->id.' เรียบร้อย', 1800));
    }

    public function showPage(MR $req)
    {
        \Gate::authorize('view', $req);
        $req->loadMissing([
            'asset',
            'reporter:id,name,email',
            'technician:id,name',
            'attachments' => fn($qq) => $qq->with('file'),
            'logs.user:id,name',

            'rating',
            'rating.rater:id,name',

            'operationLog.user:id,name',
        ]);

        // รายชื่อทีมงาน (หัวหน้า + ช่าง) สำหรับ dropdown เลือกผู้รับผิดชอบ
        $techUsers = \App\Models\User::query()
            ->inRoles(\App\Models\User::teamRoles())
            ->orderBy('name')
            ->get(['id','name']);

        return view('maintenance.requests.show', compact('req','techUsers'));
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
        $q        = $request->string('q')->toString();

        $user = $request->user();

        // ---- ใช้ helper ดึงค่าการเรียง + จัดการ session ต่อ user ----
        [$sortBy, $sortDir] = $this->resolveSort($request);

        $query = MR::query()
            ->with(['asset','reporter:id,name,email','technician:id,name'])
            // API: บังคับ filter เช่นเดียวกับหน้าเว็บ สำหรับ Member เท่านั้น
            ->when(
                ($user && !$user->isAdmin() && !$user->isSupervisor() && !$user->isTechnician()),
                fn($qb) => $qb->where('reporter_id', $user->id)
            )
            ->when($status, fn ($qb) => $qb->where('status', $status))
            ->when($priority, fn ($qb) => $qb->where('priority', $priority))
            ->when($q, function ($w) use ($q) {
                $w->where(function ($ww) use ($q) {
                    $ww->where('title','like',"%{$q}%")
                    ->orWhere('description','like',"%{$q}%")
                    ->orWhere('request_no','like',"%{$q}%")        // ค้นเลขใบงาน 68xxxx
                    ->orWhere('reporter_name','like',"%{$q}%")     // ชื่อผู้แจ้ง
                    ->orWhere('reporter_position','like',"%{$q}%") // ตำแหน่งผู้แจ้ง
                    ->orWhere('reporter_phone','like',"%{$q}%")    // เบอร์ผู้แจ้ง
                    ->orWhere('reporter_email','like',"%{$q}%")    // อีเมลฟิลด์ตรง
                    ->orWhereHas('reporter', fn($qr) =>            // user ภายใน
                            $qr->where('email','like',"%{$q}%")
                            ->orWhere('name','like',"%{$q}%")
                    );
                });
            });

        if ($sortBy === 'request_no') {
            $dir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

            // 1) เอา request_no ว่าง/NULL ไปท้ายเสมอ
            $query->orderByRaw("CASE WHEN request_no IS NULL OR request_no = '' THEN 1 ELSE 0 END ASC");

            // 2) เรียง request_no ตามทิศทางที่เลือก
            $query->orderBy('request_no', $dir);

            // 3) tie-breaker กันสลับแถว
            $query->orderBy('id', $dir);
        } else {
            $query->orderBy($sortBy, $sortDir);
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
            'title'     => ['required','string','max:255'],
            'priority'  => ['required', Rule::in(['low','medium','high','urgent'])],
            // ไม่มี technician_id
        ];

        $data = Validator::make($request->all(), $rules)->validate();
        $actorId = optional($request->user())->id;

        $req = MR::create([
            'title'        => $data['title'],
            'priority'     => $data['priority'],
            'status'       => 'pending',
            'request_date' => now(),
            'technician_id'=> null,
            'reporter_id'  => $actorId,
        ]);

        return redirect()->route('maintenance.requests.show', $req);
    }

    public function update(Request $request, MR $req)
    {
        \Gate::authorize('update', $req);

        $user    = $request->user();
        $actorId = optional($user)->id;
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

            'reporter_name'     => ['nullable','string','max:255'],
            'reporter_phone'    => ['nullable','string','max:30'],
            'reporter_email'    => ['nullable','email','max:255'],
            'reporter_position' => ['nullable','string','max:255'],

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

            'operation_date'   => ['nullable','date'],
            'operation_method' => ['nullable', Rule::in(['requisition','service_fee','other'])],
            'property_code'    => ['nullable','string','max:100'],
            'require_precheck' => ['nullable','boolean'],
            'remark'           => ['nullable','string'],
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

            if (!$isTeam) {
            if (($data['status'] ?? null) === 'cancelled') {
                if (!in_array($req->status, ['pending','accepted'], true) || !empty($req->technician_id)) {
                    unset($data['status']); // หรือ throw validation error
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
                $data['issue_software'],
                $data['issue_hardware']
            );
        }

            // fill basic fields
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

            /* ---------- operation log ---------- */
            $hasOp =
                !empty($data['operation_date'] ?? null) ||
                !empty($data['operation_method'] ?? null) ||
                !empty($data['property_code'] ?? null) ||
                !empty($data['remark'] ?? null) ||
                !empty($data['require_precheck'] ?? null) ||
                !empty($data['issue_software'] ?? null) ||
                !empty($data['issue_hardware'] ?? null) ||
                $req->operationLog()->exists();

            if ($hasOp) {
                $req->operationLog()->updateOrCreate(
                    ['maintenance_request_id' => $req->id],
                    [
                        'operation_date'   => $data['operation_date'] ?? null,
                        'operation_method' => $data['operation_method'] ?? null,
                        'property_code'    => $data['property_code'] ?? null,
                        'require_precheck' => !empty($data['require_precheck']),
                        'remark'           => $data['remark'] ?? null,
                        'issue_software'   => !empty($data['issue_software']),
                        'issue_hardware'   => !empty($data['issue_hardware']),
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

            $technicianChanged = false;

            // เปลี่ยนช่างจาก payload (เฉพาะทีมงาน/ผ่าน policy แล้ว)
            if (!empty($data['technician_id']) && (int)$req->technician_id !== (int)$data['technician_id']) {
                $req->technician_id = (int) $data['technician_id'];
                $technicianChanged = true;
            }

            // รับงาน แต่ยังไม่มีช่าง -> ตั้งเป็นผู้กดรับ
            if ($req->status === 'accepted' && empty($req->technician_id) && $actorId) {
                $req->technician_id = (int) $actorId;
                $technicianChanged = true;
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

            // ---- log ----
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

    /**
     * สร้าง note เริ่มต้นเมื่อเปลี่ยนสถานะ หากผู้ใช้ไม่ได้ใส่ note เอง
     */
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

    public function printWorkOrder(Request $request, MR $req)
    {
        \Gate::authorize('view', $req);

        // โหลดความสัมพันธ์ที่ต้องใช้ในใบงาน
        $req->loadMissing([
            'asset',
            'reporter:id,name,email',
            'technician:id,name',
            'attachments' => fn($qq) => $qq->with('file'),
            'logs.user:id,name',
            'rating',
            'rating.rater:id,name',
        ]);

        // ข้อมูลหัวกระดาษสำหรับ Maintenance Work Order
        $hospital = [
            'name_th' => 'โรงพยาบาลพระปกเกล้า',
            'name_en' => 'PHRAPOKKLAO HOSPITAL',
            'subtitle' => 'Maintenance Work Order',
            // ใช้ public_path เพื่อให้ DomPDF หาไฟล์เจอแน่นอน
            'logo'     => public_path('images/logoppk1.png'),
        ];

        // ตั้งชื่อไฟล์
        $fileName = sprintf(
            'maintenance-work-order-%s.pdf',
            $req->request_no ?? $req->id
        );

        $pdf = Pdf::loadView('maintenance.requests.print', [
                'req'      => $req,
                'hospital' => $hospital,
            ])
            ->setPaper('A4', 'portrait');

        // เปิดในแท็บใหม่
        return $pdf->stream($fileName);
        // หรือถ้าอยากโหลดเลย: return $pdf->download($fileName);
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

        MaintenanceAssignment::updateOrCreate(
            ['maintenance_request_id' => $req->id, 'user_id' => $userId],
            [
                'role'    => $req->technician?->role ?? 'technician',
                'is_lead' => $isLead,
                'status'  => $status,
            ]
        )->fill([
            'assigned_at' => now(),
        ])->save();
    }
}
