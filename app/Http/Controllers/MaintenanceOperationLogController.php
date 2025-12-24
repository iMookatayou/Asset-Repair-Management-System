<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class MaintenanceOperationLogController extends Controller
{
    public function upsert(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        Gate::authorize('update', $maintenanceRequest);

        $data = $request->validate([
            'operation_date'   => ['nullable', 'date'],
            'operation_method' => ['nullable', Rule::in(['requisition','service_fee','other'])],
            'property_code'    => ['nullable', 'string', 'max:100'],
            'require_precheck' => ['nullable', 'boolean'],
            'remark'           => ['nullable', 'string', 'max:5000'],
            'issue_software'   => ['nullable', 'boolean'],
            'issue_hardware'   => ['nullable', 'boolean'],
        ]);

        // normalize date to Y-m-d (เพราะ column เป็น date)
        if (!empty($data['operation_date'])) {
            $data['operation_date'] = \Carbon\Carbon::parse($data['operation_date'])->toDateString();
        }

        // checkbox ที่ไม่ติ๊กจะไม่ส่งมา → default = false
        $data['require_precheck'] = (bool) ($data['require_precheck'] ?? false);
        $data['issue_software']   = (bool) ($data['issue_software'] ?? false);
        $data['issue_hardware']   = (bool) ($data['issue_hardware'] ?? false);

        $data['user_id'] = Auth::id();

        DB::transaction(function () use ($maintenanceRequest, $data) {
            $maintenanceRequest->operationLog()->updateOrCreate(
                ['maintenance_request_id' => $maintenanceRequest->id],
                $data
            );
        });

        return redirect()
            ->route('maintenance.requests.show', $maintenanceRequest)
            ->with('toast', [
                'type'    => 'success',
                'message' => 'บันทึกรายงานการปฏิบัติงานเรียบร้อยแล้ว',
            ]);
    }
}
