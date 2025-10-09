<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\MaintenanceLog;
use Illuminate\Http\Request;

class MaintenanceRequestController extends Controller
{
    public function index()
    {
        $status = request('status');
        $priority = request('priority');

        $list = MaintenanceRequest::with(['asset','reporter','technician'])
            ->when($status, fn($q)=>$q->where('status',$status))
            ->when($priority, fn($q)=>$q->where('priority',$priority))
            ->latest('request_date')
            ->paginate(20);

        return response()->json($list);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'asset_id'     => 'required|exists:assets,id',
            'reporter_id'  => 'required|exists:users,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'priority'     => 'required|in:low,medium,high,urgent',
        ]);

        $req = MaintenanceRequest::create($data + [
            'status'       => 'pending',
            'request_date' => now(),
        ]);

        MaintenanceLog::create([
            'request_id' => $req->id,
            'user_id'    => $req->reporter_id,
            'action'     => 'create_request',
            'note'       => $req->title,
            'created_at' => now(),
        ]);

        return response()->json(['message'=>'created','data'=>$req], 201);
    }

    // IMPORTANT: ชื่อพารามิเตอร์ต้องตรงกับ {req} ใน routes -> ใช้ Model Binding
    public function show(MaintenanceRequest $req)
    {
        return response()->json(
            $req->load(['asset','reporter','technician','attachments','logs'])
        );
    }

    public function update(Request $request, MaintenanceRequest $req)
    {
        $data = $request->validate([
            'status'         => 'sometimes|in:pending,in_progress,completed,cancelled',
            'technician_id'  => 'nullable|exists:users,id',
            'remark'         => 'nullable|string',
            'assigned_date'  => 'nullable|date',
            'completed_date' => 'nullable|date',
        ]);

        $req->update($data);

        MaintenanceLog::create([
            'request_id' => $req->id,
            'user_id'    => $request->user()->id ?? null,
            'action'     => 'update_request',
            'note'       => $req->status,
            'created_at' => now(),
        ]);

        return response()->json(['message'=>'updated']);
    }

    // === Actions แบบเดิมของพี่ (คงไว้ให้ใช้ได้) ===
    public function assign(Request $request, $id)
    {
        $data = $request->validate([
            'technician_id' => 'required|exists:users,id',
        ]);

        $req = MaintenanceRequest::findOrFail($id);
        $req->update([
            'technician_id' => $data['technician_id'],
            'status'        => 'in_progress',
            'assigned_date' => now(),
        ]);

        MaintenanceLog::create([
            'request_id' => $req->id,
            'user_id'    => $request->user()->id ?? null,
            'action'     => 'assign_technician',
            'note'       => (string)$data['technician_id'],
            'created_at' => now(),
        ]);

        return response()->json(['message'=>'assigned']);
    }

    public function complete(Request $request, $id)
    {
        $req = MaintenanceRequest::findOrFail($id);
        $req->update([
            'status'         => 'completed',
            'completed_date' => now(),
        ]);

        MaintenanceLog::create([
            'request_id' => $req->id,
            'user_id'    => $request->user()->id ?? null,
            'action'     => 'complete_request',
            'note'       => $req->remark,
            'created_at' => now(),
        ]);

        return response()->json(['message'=>'completed']);
    }

    public function destroy(MaintenanceRequest $req)
    {
        $req->delete();
        return response()->json(['message'=>'deleted']);
    }

    /**
     * === รองรับเส้นทาง /repair-requests/{req}/transition ===
     * body:
     *   - action: assign | start | complete | cancel
     *   - technician_id (required when action=assign)
     *   - remark (optional)
     */
    public function transition(Request $request, MaintenanceRequest $req)
    {
        $data = $request->validate([
            'action'        => 'required|in:assign,start,complete,cancel',
            'technician_id' => 'nullable|exists:users,id',
            'remark'        => 'nullable|string',
        ]);

        $action = $data['action'];
        $note   = $data['remark'] ?? null;

        switch ($action) {
            case 'assign':
                if (empty($data['technician_id'])) {
                    return response()->json(['message'=>'technician_id is required for assign'], 422);
                }
                $req->update([
                    'technician_id' => $data['technician_id'],
                    'status'        => 'in_progress',   // ตามโค้ด assign เดิมของพี่
                    'assigned_date' => now(),
                    'remark'        => $note ?: $req->remark,
                ]);
                $this->log($req->id, $request->user()->id ?? null, 'assign_technician', (string)$data['technician_id']);
                break;

            case 'start':
                if (!in_array($req->status, ['pending','in_progress'])) {
                    return response()->json(['message'=>'Invalid state to start'], 422);
                }
                $req->update([
                    'status' => 'in_progress',
                    'remark' => $note ?: $req->remark,
                ]);
                $this->log($req->id, $request->user()->id ?? null, 'start_request', $note);
                break;

            case 'complete':
                if (!in_array($req->status, ['in_progress'])) {
                    return response()->json(['message'=>'Invalid state to complete'], 422);
                }
                $req->update([
                    'status'         => 'completed',
                    'completed_date' => now(),
                    'remark'         => $note ?: $req->remark,
                ]);
                $this->log($req->id, $request->user()->id ?? null, 'complete_request', $note);
                break;

            case 'cancel':
                $req->update([
                    'status' => 'cancelled',
                    'remark' => $note ?: $req->remark,
                ]);
                $this->log($req->id, $request->user()->id ?? null, 'cancel_request', $note);
                break;
        }

        return response()->json(['message'=>'transition_ok','data'=>$req->fresh(['asset','reporter','technician'])]);
    }

    private function log(int $requestId, ?int $userId, string $action, ?string $note = null): void
    {
        MaintenanceLog::create([
            'request_id' => $requestId,
            'user_id'    => $userId,
            'action'     => $action,
            'note'       => $note,
            'created_at' => now(),
        ]);
    }
}
