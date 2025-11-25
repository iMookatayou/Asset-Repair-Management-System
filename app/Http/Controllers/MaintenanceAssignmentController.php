<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceAssignment;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaintenanceAssignmentController extends Controller
{
    /**
     * บันทึกการมอบหมายงาน (หลายคนต่อ 1 งาน)
     *
     * Expect:
     *  - user_ids[]    : array ของ user id ที่ถูก assign
     *  - lead_user_id  : (optional) user id ของหัวหน้าทีมงานนี้
     */
    public function store(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $authUser = Auth::user();

        // ถ้าจะล็อกสิทธิ์จริง ๆ ก็เพิ่ม logic ตรงนี้ได้
        // เช่น อนุญาตเฉพาะ admin / supervisor เท่านั้น
        // if (!$authUser->isAdmin() && !$authUser->isSupervisor()) {
        //     abort(403, 'คุณไม่มีสิทธิ์มอบหมายงานซ่อม');
        // }

        $data = $request->validate([
            'user_ids'     => ['required', 'array'],
            'user_ids.*'   => ['integer', 'exists:users,id'],
            'lead_user_id' => ['nullable', 'integer'],
        ]);

        $userIds = array_values(array_unique($data['user_ids'] ?? []));
        $leadId  = $data['lead_user_id'] ?? null;

        // กันเคส lead ไม่อยู่ในรายชื่อ user_ids
        if ($leadId && !in_array($leadId, $userIds, true)) {
            $userIds[] = $leadId;
        }

        // ดึง user ที่เลือกมา และ filter เฉพาะ worker (ไม่เอา member)
        $workers = User::query()
            ->whereIn('id', $userIds)
            ->technicians() // scopeTechnicians() = workerRoles()
            ->get()
            ->keyBy('id');

        if ($workers->isEmpty()) {
            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'ไม่พบผู้ปฏิบัติงานที่สามารถรับงานได้',
            ]);
        }

        DB::transaction(function () use ($maintenanceRequest, $workers, $userIds, $leadId) {
            $now = now();

            // ดึง assignment เดิมของงานนี้ มา keyBy user_id
            $existing = $maintenanceRequest->assignments()
                ->get()
                ->keyBy('user_id');

            // 1) สร้าง/อัปเดต assignment ตาม user_ids
            foreach ($userIds as $uid) {
                /** @var \App\Models\User|null $worker */
                $worker = $workers->get($uid);
                if (!$worker) {
                    // ถ้า user คนนี้ไม่ใช่ worker หรือไม่เจอในระบบ ให้ข้าม
                    continue;
                }

                $isLead = $leadId && ((int) $leadId === (int) $uid);

                /** @var \App\Models\MaintenanceAssignment|null $assignment */
                $assignment = $existing->get($uid);

                if ($assignment) {
                    // ถ้ามีอยู่แล้ว → อัปเดตข้อมูลบางส่วน
                    $assignment->fill([
                        'role'    => $worker->role,
                        'is_lead' => $isLead,
                    ]);

                    // ถ้า status เดิมเป็น cancelled แต่อาจจะ assign กลับมาใหม่
                    if ($assignment->status === MaintenanceAssignment::STATUS_CANCELLED) {
                        $assignment->status      = MaintenanceAssignment::STATUS_IN_PROGRESS;
                        $assignment->assigned_at = $assignment->assigned_at ?? $now;
                    }

                    $assignment->save();
                } else {
                    // ยังไม่เคยมี assignment → create ใหม่
                    MaintenanceAssignment::create([
                        'maintenance_request_id' => $maintenanceRequest->id,
                        'user_id'                => $worker->id,
                        'role'                   => $worker->role,
                        'is_lead'                => $isLead,
                        'assigned_at'            => $now,
                        'status'                 => MaintenanceAssignment::STATUS_IN_PROGRESS,
                    ]);
                }
            }

            // 2) ลบ assignment ของคนที่ไม่ได้อยู่ใน user_ids แล้ว (unassign)
            $idsToKeep   = $workers->keys()->all();
            $idsToDelete = $existing
                ->keys()
                ->filter(fn ($uid) => !in_array($uid, $idsToKeep, true))
                ->all();

            if (!empty($idsToDelete)) {
                $maintenanceRequest->assignments()
                    ->whereIn('user_id', $idsToDelete)
                    ->delete();
            }

            // 3) อัปเดต assigned_date ใน maintenance_requests ถ้ายังว่างอยู่
            if ($maintenanceRequest->assigned_date === null && $workers->isNotEmpty()) {
                $maintenanceRequest->forceFill([
                    'assigned_date' => $now,
                ])->save();
            }
        });

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'มอบหมายงานให้ทีมช่างเรียบร้อยแล้ว',
        ]);
    }

    /**
     * ยกเลิก assignment ของช่าง 1 คนออกจากงาน (unassign)
     */
    public function destroy(MaintenanceAssignment $assignment)
    {
        $authUser = Auth::user();

        // ถ้าต้องล็อกสิทธิ์จริง ๆ สามารถเช็กเพิ่มว่าคนนี้มีสิทธิลบหรือไม่
        // เช่น admin, supervisor หรือเจ้าของงานเอง
        // if (!$authUser->isAdmin() && !$authUser->isSupervisor()) {
        //     abort(403, 'คุณไม่มีสิทธิ์ยกเลิกการมอบหมายงานนี้');
        // }

        $assignment->delete();

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'ยกเลิกการมอบหมายช่างเรียบร้อยแล้ว',
        ]);
    }
}
