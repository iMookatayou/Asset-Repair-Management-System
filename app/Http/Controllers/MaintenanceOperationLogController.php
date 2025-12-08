<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceOperationLogController extends Controller
{
    public function upsert(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $data = $request->validate([
            'operation_date'   => ['nullable', 'date'],
            'operation_method' => ['nullable', 'in:requisition,service_fee,other'],

            'property_code'    => ['nullable', 'string', 'max:100'],

            'require_precheck' => ['nullable', 'boolean'],
            'remark'           => ['nullable', 'string'],
            'issue_software'   => ['nullable', 'boolean'],
            'issue_hardware'   => ['nullable', 'boolean'],
        ]);

        // checkbox ที่ไม่ติ๊กจะไม่ส่งมา → ตั้ง default เป็น false
        $data['require_precheck'] = (bool) ($data['require_precheck'] ?? false);
        $data['issue_software']   = (bool) ($data['issue_software'] ?? false);
        $data['issue_hardware']   = (bool) ($data['issue_hardware'] ?? false);

        // คนที่บันทึก
        $data['user_id'] = Auth::id();

        $maintenanceRequest->operationLog()
            ->updateOrCreate(
                ['maintenance_request_id' => $maintenanceRequest->id],
                $data
            );

        return redirect()
            ->route('maintenance.requests.show', $maintenanceRequest)
            ->with('toast', [
                'type'    => 'success',
                'message' => 'บันทึกรายงานการปฏิบัติงานเรียบร้อยแล้ว',
            ]);
    }
}
