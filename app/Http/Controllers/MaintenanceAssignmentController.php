<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceAssignment;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MaintenanceAssignmentController extends Controller
{
    /**
     * บันทึกการมอบหมายงาน (หลายคนต่อ 1 งาน)
     *
     * Expect:
     *  - user_ids[]    : array ของ user id ที่ถูก assign
     *  - lead_user_id  : (optional) user id ของหัวหน้าทีมงานนี้
     */
    public function store(Request $request, MaintenanceRequest $req)
    {
        Gate::authorize('assign', $req);

        $data = $request->validate([
            'user_ids'     => ['nullable', 'array'],
            'user_ids.*'   => ['integer', 'exists:users,id'],
            'lead_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $userIds = collect($data['user_ids'] ?? [])
            ->filter()
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values();

        $leadId = ($data['lead_user_id'] ?? null);
        $leadId = ($leadId === null || $leadId === '') ? null : (int) $leadId;

        // ถ้ามี lead แต่ไม่อยู่ใน user_ids ให้ใส่เข้าไป
        if ($leadId && !$userIds->contains($leadId)) {
            $userIds = $userIds->push($leadId)->unique()->values();
        }

        // ใช้มาตรฐานเดียวกับระบบหลัก (ทีมช่าง/หัวหน้า/IT ฯลฯ)
        $workerRoles = User::teamRoles();

        $workers = User::query()
            ->whereIn('id', $userIds->all())
            ->whereIn('role', $workerRoles)
            ->get(['id', 'name', 'role'])
            ->keyBy('id');

        if ($workers->isEmpty()) {
            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'ไม่พบผู้ปฏิบัติงานที่สามารถรับงานได้',
            ]);
        }

        DB::transaction(function () use ($req, $workers, $userIds, &$leadId) {
            $now = now();

            // lock ใบงานกัน race (assign พร้อมกัน)
            $lockedReq = MaintenanceRequest::query()
                ->whereKey($req->id)
                ->lockForUpdate()
                ->firstOrFail();

            $existing = $lockedReq->assignments()
                ->get()
                ->keyBy('user_id');

            // 1) ถ้า leadId ไม่ใช่ worker -> ทิ้ง
            if ($leadId && !$workers->has($leadId)) {
                $leadId = null;
            }

            // 2) ถ้าในใบงานมี technician_id และยังอยู่ใน workers -> เป็น lead
            if (!$leadId && $lockedReq->technician_id && $workers->has((int) $lockedReq->technician_id)) {
                $leadId = (int) $lockedReq->technician_id;
            }

            // 3) ไม่งั้นเลือกคนแรกจาก "ลิสต์ที่ user ส่งมา" ที่เป็น worker จริง
            if (!$leadId) {
                $leadId = (int) $userIds->first(fn($id) => $workers->has((int)$id));
            }

            // 1) upsert assignments เฉพาะ worker
            foreach ($userIds as $uid) {
                $uid = (int) $uid;

                $worker = $workers->get($uid);
                if (!$worker) continue;

                $isLead = ($leadId === $uid);

                $assignment = $existing->get($uid);
                if ($assignment) {
                    $assignment->fill([
                        'role'    => $worker->role,
                        'is_lead' => $isLead,
                    ]);

                    // ถ้าเคย cancelled แล้วถูก assign กลับมา
                    if ($assignment->status === MaintenanceAssignment::STATUS_CANCELLED) {
                        $assignment->status = MaintenanceAssignment::STATUS_IN_PROGRESS;

                        $assignment->response_status = MaintenanceAssignment::RESP_PENDING;
                        $assignment->responded_at = null;
                    }

                    // ให้มี assigned_at เสมอเมื่ออยู่ในทีม
                    $assignment->assigned_at = $assignment->assigned_at ?? $now;

                    // ถ้าไม่เคยมี response_status (ข้อมูลเก่า) ให้ตั้งค่าเริ่มต้น
                    if (empty($assignment->response_status)) {
                        $assignment->response_status = MaintenanceAssignment::RESP_PENDING;
                        $assignment->responded_at = null;
                    }

                    $assignment->save();
                } else {
                    MaintenanceAssignment::create([
                        'maintenance_request_id' => $lockedReq->id,
                        'user_id'                => $worker->id,
                        'role'                   => $worker->role,
                        'is_lead'                => $isLead,
                        'assigned_at'            => $now,

                        // ใหม่ (MyJob ใช้ตรงนี้)
                        'response_status'        => MaintenanceAssignment::RESP_PENDING,
                        'responded_at'           => null,

                        // ของเดิม
                        'status'                 => MaintenanceAssignment::STATUS_IN_PROGRESS,
                    ]);
                }
            }

            // 2) ยกเลิกคนที่ "เคยอยู่" แต่ตอนนี้ไม่อยู่ใน workers แล้ว
            $keepIds = $workers->keys()->map(fn($v) => (int) $v)->all();

            $toCancel = $existing->keys()
                ->map(fn($v) => (int) $v)
                ->filter(fn($uid) => !in_array($uid, $keepIds, true))
                ->all();

            if (!empty($toCancel)) {
                $lockedReq->assignments()
                    ->whereIn('user_id', $toCancel)
                    ->update([
                        'status'          => MaintenanceAssignment::STATUS_CANCELLED,
                        'is_lead'         => false,

                        'response_status' => MaintenanceAssignment::RESP_PENDING,
                        'responded_at'    => null,

                        'updated_at'      => $now,
                    ]);
            }

            // 3) ถ้าหลังปรับทีมแล้ว "ไม่มี lead" (เคส lead ถูก cancel) -> ตั้ง lead ใหม่ให้ 1 คน
            $stillHasLead = $lockedReq->assignments()
                ->whereIn('user_id', $keepIds)
                ->where('is_lead', true)
                ->where('status', '!=', MaintenanceAssignment::STATUS_CANCELLED)
                ->exists();

            if (!$stillHasLead) {
                $newLeadId = (int) ($leadId ?: ($keepIds[0] ?? 0));
                if ($newLeadId) {
                    $lockedReq->assignments()
                        ->where('user_id', $newLeadId)
                        ->update(['is_lead' => true, 'updated_at' => $now]);
                    $leadId = $newLeadId;
                }
            }

            // 4) อัปเดต assigned_date ถ้ายังว่าง
            if ($lockedReq->assigned_date === null) {
                $lockedReq->assigned_date = $now;
            }

            // 5) sync technician_id ตาม lead (ถ้า technician เดิมหลุดทีม)
            $leadStillInTeam = $leadId && in_array((int)$leadId, $keepIds, true);
            $techStillInTeam = $lockedReq->technician_id
                ? in_array((int)$lockedReq->technician_id, $keepIds, true)
                : false;

            if ($leadStillInTeam && (!$lockedReq->technician_id || !$techStillInTeam)) {
                $lockedReq->technician_id = (int) $leadId;
            }

            $lockedReq->save();
        });

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'มอบหมายงานให้ทีมช่างเรียบร้อยแล้ว',
        ]);
    }

    /**
     * ยกเลิก assignment ของช่าง 1 คนออกจากงาน (เก็บประวัติด้วย cancelled)
     */
    public function destroy(MaintenanceAssignment $assignment)
    {
        Gate::authorize('assign', $assignment->maintenanceRequest);

        // ถ้า done แล้วและคุณไม่อยากให้ยกเลิกย้อนหลัง ให้กันไว้ (เลือกได้)
        if ($assignment->status === MaintenanceAssignment::STATUS_DONE) {
            return back()->with('toast', [
                'type'    => 'warning',
                'message' => 'งานนี้ถูกทำเสร็จแล้ว ไม่สามารถยกเลิกการมอบหมายย้อนหลังได้',
            ]);
        }

        $assignment->update([
            'status'          => MaintenanceAssignment::STATUS_CANCELLED,
            'is_lead'         => false,

            'response_status' => MaintenanceAssignment::RESP_PENDING,
            'responded_at'    => null,
        ]);

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'ยกเลิกการมอบหมายช่างเรียบร้อยแล้ว',
        ]);
    }
}
