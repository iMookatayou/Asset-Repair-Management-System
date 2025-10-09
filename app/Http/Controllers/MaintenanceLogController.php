<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceLog;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;

class MaintenanceLogController extends Controller
{
    /**
     * GET /repair-requests/{req}/logs
     * คืน logs ของคำขอซ่อม (ไม่ใช่ทุกอัน)
     */
    public function index(MaintenanceRequest $req)
    {
        return response()->json(
            $req->logs()
                ->with('user')
                ->latest('created_at')
                ->paginate(50)
        );
    }

    /**
     * (มีไว้เผื่อใช้งาน) POST /logs หรือ /repair-requests/{req}/logs
     * ถ้าจะใช้แบบอ้างอิง {req} ให้เพิ่ม route เองภายหลังได้
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'request_id' => 'required|exists:maintenance_requests,id',
            'action'     => 'required|string|max:100',
            'note'       => 'nullable|string',
        ]);

        $log = MaintenanceLog::create($data + [
            'user_id'    => $request->user()->id ?? null,
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'created', 'data' => $log], 201);
    }

    /** GET /logs/{maintenanceLog} (ถ้ามี route แยก) */
    public function show(MaintenanceLog $maintenanceLog)
    {
        return response()->json($maintenanceLog->load('user'));
    }
}
