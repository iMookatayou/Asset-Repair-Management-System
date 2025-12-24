<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceLog;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class MaintenanceLogController extends Controller
{
    /**
     * ดู log ของใบงาน (ต้องมีสิทธิ์ view ใบงานนั้น)
     */
    public function index(MaintenanceRequest $req)
    {
        Gate::authorize('view', $req);

        $logs = $req->logs()
            ->select(['id','request_id','user_id','action','note','from_status','to_status','created_at'])
            ->with(['user:id,name'])
            ->latest('created_at')
            ->paginate(50);

        return response()->json($logs);
    }

    /**
     * เพิ่ม log แบบ manual (เช่น "เพิ่มโน้ต")
     * - ต้องมีสิทธิ์ update ใบงาน
     * - จำกัด action ที่อนุญาต (กันคนยิง action มั่ว ๆ)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'request_id' => ['required','integer','exists:maintenance_requests,id'],
            'action'     => ['required','string','max:100', Rule::in([
                MaintenanceLog::ACTION_UPDATE,
                MaintenanceLog::ACTION_TRANSITION,
                // ถ้าคุณอยากอนุญาตเฉพาะ "note" จริง ๆ แนะนำสร้าง action ใหม่ เช่น 'note'
            ])],
            'note'       => ['nullable','string','max:2000'],
            'from_status'=> ['nullable','string','max:50'],
            'to_status'  => ['nullable','string','max:50'],
        ]);

        $req = MaintenanceRequest::findOrFail($data['request_id']);
        Gate::authorize('update', $req);

        $log = MaintenanceLog::create([
            'request_id'  => $req->id,
            'user_id'     => optional($request->user())->id,
            'action'      => $data['action'],
            'note'        => $data['note'] ?? null,
            'from_status' => $data['from_status'] ?? null,
            'to_status'   => $data['to_status'] ?? null,
            // created_at ไม่ต้องส่งก็ได้ เพราะ model booted() set ให้แล้ว
            // แต่ถ้าจะใส่ชัด ๆ ก็ได้:
            'created_at'  => now(),
        ]);

        return response()->json([
            'message' => 'created',
            'data'    => $log->load('user:id,name'),
        ], 201);
    }

    /**
     * ดู log รายการเดียว
     * - ต้องมีสิทธิ์ view ใบงานที่ log นั้นสังกัด
     */
    public function show(MaintenanceLog $maintenanceLog)
    {
        $maintenanceLog->loadMissing('request');

        Gate::authorize('view', $maintenanceLog->request);

        return response()->json(
            $maintenanceLog->load(['user:id,name'])
        );
    }
}
