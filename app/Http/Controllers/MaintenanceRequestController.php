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

    public function show(MaintenanceRequest $maintenanceRequest)
    {
        return response()->json(
            $maintenanceRequest->load(['asset','reporter','technician','attachments','logs'])
        );
    }

    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $data = $request->validate([
            'status'        => 'sometimes|in:pending,in_progress,completed,cancelled',
            'technician_id' => 'nullable|exists:users,id',
            'remark'        => 'nullable|string',
            'assigned_date' => 'nullable|date',
            'completed_date'=> 'nullable|date',
        ]);

        $maintenanceRequest->update($data);

        MaintenanceLog::create([
            'request_id' => $maintenanceRequest->id,
            'user_id'    => $request->user()->id ?? null,
            'action'     => 'update_request',
            'note'       => $maintenanceRequest->status,
            'created_at' => now(),
        ]);

        return response()->json(['message'=>'updated']);
    }

    // === Actions เฉพาะกิจ ===
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

    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->delete();
        return response()->json(['message'=>'deleted']);
    }
}
