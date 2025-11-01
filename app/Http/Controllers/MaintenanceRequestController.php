<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\MaintenanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MaintenanceRequestController extends Controller
{
    /**
     * API: GET /api/repair-requests
     * Filters: ?status=&priority=&q=
     */
    public function index()
    {
        $status   = request('status');
        $priority = request('priority');
        $q        = request('q');

        $list = MaintenanceRequest::with(['asset','reporter','technician'])
            ->when($status, fn($qb)=>$qb->where('status',$status))
            ->when($priority, fn($qb)=>$qb->where('priority',$priority))
            ->when($q, fn($qb)=>$qb->where(function($w) use ($q){
                $w->where('title','like',"%$q%")
                  ->orWhere('description','like',"%$q%");
            }))
            ->latest('request_date')
            ->paginate(20);

        return response()->json($list);
    }

    /**
     * API: POST /api/repair-requests
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'asset_id'     => 'required|exists:assets,id',
            'reporter_id'  => 'required|exists:users,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'priority'     => ['required', Rule::in([
                MaintenanceRequest::PRIORITY_LOW,
                MaintenanceRequest::PRIORITY_MEDIUM,
                MaintenanceRequest::PRIORITY_HIGH,
                MaintenanceRequest::PRIORITY_URGENT,
            ])],
        ]);

        $req = DB::transaction(function () use ($data) {
            $req = MaintenanceRequest::create($data + [
                'status'       => MaintenanceRequest::STATUS_PENDING,
                'request_date' => now(),
            ]);

            MaintenanceLog::create([
                'request_id' => $req->id,
                'user_id'    => $req->reporter_id,
                'action'     => 'create_request',
                'note'       => $req->title,
            ]);

            return $req;
        });

        return response()->json(['message'=>'created','data'=>$req], 201);
    }

    /**
     * API: GET /api/repair-requests/{req}
     * ใช้ Route Model Binding: {req}
     */
    public function show(MaintenanceRequest $req)
    {
        return response()->json(
            $req->load(['asset','reporter','technician','attachments','logs' => fn($q)=>$q->latest()])
        );
    }

    /**
     * API: PUT/PATCH /api/repair-requests/{req}
     */
    public function update(Request $request, MaintenanceRequest $req)
    {
        $data = $request->validate([
            'status'         => ['sometimes', Rule::in([
                MaintenanceRequest::STATUS_PENDING,
                MaintenanceRequest::STATUS_IN_PROGRESS,
                MaintenanceRequest::STATUS_COMPLETED,
                MaintenanceRequest::STATUS_CANCELLED,
            ])],
            'technician_id'  => 'nullable|exists:users,id',
            'remark'         => 'nullable|string',
            'assigned_date'  => 'nullable|date',
            'completed_date' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request, $req, $data) {
            // set assigned_date และสถานะอัตโนมัติเมื่อมี technician_id แต่ยังไม่เคย assign
            if (array_key_exists('technician_id', $data) && $data['technician_id'] && !$req->assigned_date) {
                $data['assigned_date'] = $data['assigned_date'] ?? now();
                $data['status'] = $data['status'] ?? MaintenanceRequest::STATUS_IN_PROGRESS;
            }

            // ถ้าปรับสถานะเป็น completed แต่ไม่กำหนด completed_date
            if (($data['status'] ?? null) === MaintenanceRequest::STATUS_COMPLETED && empty($data['completed_date'])) {
                $data['completed_date'] = now();
            }

            $req->update($data);

            MaintenanceLog::create([
                'request_id' => $req->id,
                'user_id'    => $request->user()->id ?? null,
                'action'     => 'update_request',
                'note'       => $req->status,
            ]);
        });

        return response()->json(['message'=>'updated']);
    }

    /**
     * API: POST /api/repair-requests/{id}/assign
     */
    public function assign(Request $request, $id)
    {
        $data = $request->validate([
            'technician_id' => 'required|exists:users,id',
        ]);

        DB::transaction(function () use ($request, $id, $data) {
            $req = MaintenanceRequest::lockForUpdate()->findOrFail($id);

            if (in_array($req->status, [MaintenanceRequest::STATUS_COMPLETED, MaintenanceRequest::STATUS_CANCELLED], true)) {
                abort(422, 'Request already closed.');
            }

            $req->update([
                'technician_id' => $data['technician_id'],
                'status'        => MaintenanceRequest::STATUS_IN_PROGRESS,
                'assigned_date' => $req->assigned_date ?? now(),
            ]);

            MaintenanceLog::create([
                'request_id' => $req->id,
                'user_id'    => $request->user()->id ?? null,
                'action'     => 'assign_technician',
                'note'       => (string)$data['technician_id'],
            ]);
        });

        return response()->json(['message'=>'assigned']);
    }

    /**
     * API: POST /api/repair-requests/{id}/complete
     */
    public function complete(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $req = MaintenanceRequest::lockForUpdate()->findOrFail($id);

            if ($req->status !== MaintenanceRequest::STATUS_IN_PROGRESS) {
                abort(422, 'Invalid state to complete');
            }

            $req->update([
                'status'         => MaintenanceRequest::STATUS_COMPLETED,
                'completed_date' => $req->completed_date ?? now(),
            ]);

            MaintenanceLog::create([
                'request_id' => $req->id,
                'user_id'    => $request->user()->id ?? null,
                'action'     => 'complete_request',
                'note'       => $req->remark,
            ]);
        });

        return response()->json(['message'=>'completed']);
    }

    /**
     * API: DELETE /api/repair-requests/{req}
     */
    public function destroy(MaintenanceRequest $req)
    {
        $req->delete();
        return response()->json(['message'=>'deleted']);
    }

    /**
     * API: POST /api/repair-requests/{req}/transition
     * body: action(assign|start|complete|cancel), technician_id?, remark?
     */
    public function transition(Request $request, MaintenanceRequest $req)
    {
        $data = $request->validate([
            'action'        => 'required|in:assign,start,complete,cancel',
            'technician_id' => 'nullable|exists:users,id',
            'remark'        => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $req, $data) {
            $action = $data['action'];
            $note   = $data['remark'] ?? null;

            if (in_array($req->status, [MaintenanceRequest::STATUS_COMPLETED, MaintenanceRequest::STATUS_CANCELLED], true)
                && $action !== 'cancel') {
                abort(422, 'Request already closed.');
            }

            switch ($action) {
                case 'assign':
                    if (empty($data['technician_id'])) {
                        abort(422, 'technician_id is required for assign');
                    }
                    $req->update([
                        'technician_id' => $data['technician_id'],
                        'status'        => MaintenanceRequest::STATUS_IN_PROGRESS,
                        'assigned_date' => $req->assigned_date ?? now(),
                        'remark'        => $note ?: $req->remark,
                    ]);
                    $this->log($req->id, $request->user()->id ?? null, 'assign_technician', (string)$data['technician_id']);
                    break;

                case 'start':
                    if (!in_array($req->status, [MaintenanceRequest::STATUS_PENDING, MaintenanceRequest::STATUS_IN_PROGRESS], true)) {
                        abort(422, 'Invalid state to start');
                    }
                    $req->update([
                        'status' => MaintenanceRequest::STATUS_IN_PROGRESS,
                        'remark' => $note ?: $req->remark,
                    ]);
                    $this->log($req->id, $request->user()->id ?? null, 'start_request', $note);
                    break;

                case 'complete':
                    if ($req->status !== MaintenanceRequest::STATUS_IN_PROGRESS) {
                        abort(422, 'Invalid state to complete');
                    }
                    $req->update([
                        'status'         => MaintenanceRequest::STATUS_COMPLETED,
                        'completed_date' => $req->completed_date ?? now(),
                        'remark'         => $note ?: $req->remark,
                    ]);
                    $this->log($req->id, $request->user()->id ?? null, 'complete_request', $note);
                    break;

                case 'cancel':
                    $req->update([
                        'status' => MaintenanceRequest::STATUS_CANCELLED,
                        'remark' => $note ?: $req->remark,
                    ]);
                    $this->log($req->id, $request->user()->id ?? null, 'cancel_request', $note);
                    break;
            }
        });

        return response()->json(['message'=>'transition_ok','data'=>$req->fresh(['asset','reporter','technician'])]);
    }

    // ====== เพิ่ม: สำหรับหน้า List (Blade) ======
    public function indexPage(Request $request)
    {
        $status   = $request->query('status');
        $priority = $request->query('priority');
        $q        = $request->query('q');

        $list = MaintenanceRequest::with(['asset','reporter','technician'])
            ->when($status, fn($qb)=>$qb->where('status',$status))
            ->when($priority, fn($qb)=>$qb->where('priority',$priority))
            ->when($q, fn($qb)=>$qb->where(function($w) use ($q){
                $w->where('title','like',"%$q%")
                ->orWhere('description','like',"%$q%");
            }))
            ->orderByRaw('COALESCE(request_date, created_at) DESC')
            ->paginate(20);

        return view('maintenance.requests.index', compact('list'));
    }

    // ====== เพิ่ม: สำหรับหน้า Show (Blade) ======
    public function showPage(MaintenanceRequest $req)
    {
        $req->load(['asset','reporter','technician','attachments','logs'=>fn($q)=>$q->latest()]);
        return view('maintenance.requests.show', compact('req'));
    }

        // ====== เพิ่ม: transition จากหน้า Blade (redirect back) ======
    public function transitionFromBlade(Request $request, MaintenanceRequest $req)
    {
        // reuse logic เดิม
        $this->transition($request, $req);

        // transition() คืน JSON; เราต้องคืน redirect สำหรับ Blade:
        return back()->with('ok','บันทึกแล้ว');
    }

    // ====== เพิ่ม: upload จากหน้า Blade (redirect back) ======
    public function uploadAttachmentFromBlade(Request $request, MaintenanceRequest $req)
    {
        $validated = $request->validate([
            'type' => ['nullable', Rule::in(['before','after','other'])],
            'file' => ['required','file','max:10240'],
        ]);

        $file   = $request->file('file');
        $folder = "maintenance/{$req->id}";
        $path   = $file->storePublicly($folder, ['disk' => 'public']);

        // ต้องมี relation: $req->attachments()->create([...])
        $req->attachments()->create([
            'type'          => $validated['type'] ?? 'other',
            'path'          => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime'          => $file->getMimeType(),
            'size'          => $file->getSize(),
        ]);

        return back()->with('ok','อัปโหลดไฟล์แล้ว');
    }

    private function log(int $requestId, ?int $userId, string $action, ?string $note = null): void
    {
        MaintenanceLog::create([
            'request_id' => $requestId,
            'user_id'    => $userId,
            'action'     => $action,
            'note'       => $note,
        ]);
    }

    public function createPage()
    {
        // โหลดรายการ asset และ user (ช่าง)
        $assets = \App\Models\Asset::orderBy('name')->get();
        $users  = \App\Models\User::where('role','technician')->orderBy('name')->get();

        return view('maintenance.requests.create', compact('assets','users'));
    }
}
