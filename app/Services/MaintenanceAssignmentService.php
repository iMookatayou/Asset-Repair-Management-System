<?php

namespace App\Services;

use App\Models\MaintenanceRequest;
use App\Models\MaintenanceAssignment;
use Illuminate\Support\Facades\DB;

class MaintenanceAssignmentService
{
    /**
     * ตั้ง / เปลี่ยน Lead Technician
     */
    public function assignLead(
        MaintenanceRequest $req,
        int $userId,
        ?int $actorId = null
    ): MaintenanceAssignment {
        return DB::transaction(function () use ($req, $userId) {

            // ปิด lead เดิม
            MaintenanceAssignment::where('maintenance_request_id', $req->id)
                ->where('is_lead', true)
                ->update(['is_lead' => false]);

            // สร้างหรืออัปเดต lead ใหม่
            return MaintenanceAssignment::updateOrCreate(
                [
                    'maintenance_request_id' => $req->id,
                    'user_id' => $userId,
                ],
                [
                    'role'        => 'technician',
                    'is_lead'     => true,
                    'assigned_at' => now(),
                    'status'      => $this->mapStatus($req->status),
                ]
            );
        });
    }

    /**
     * sync assignment ตามสถานะใบงาน
     */
    public function syncFromRequest(
        MaintenanceRequest $req,
        ?int $actorId = null
    ): void {
        if (empty($req->technician_id)) {
            return;
        }

        MaintenanceAssignment::updateOrCreate(
            [
                'maintenance_request_id' => $req->id,
                'user_id' => $req->technician_id,
            ],
            [
                'role'        => 'technician',
                'is_lead'     => true,
                'assigned_at' => now(),
                'status'      => $this->mapStatus($req->status),
            ]
        );
    }

    /**
     * map สถานะ MR -> Assignment
     */
    protected function mapStatus(string $requestStatus): string
    {
        return match ($requestStatus) {
            'resolved', 'closed' => MaintenanceAssignment::STATUS_DONE,
            'cancelled'          => MaintenanceAssignment::STATUS_CANCELLED,
            default              => MaintenanceAssignment::STATUS_IN_PROGRESS,
        };
    }
}
