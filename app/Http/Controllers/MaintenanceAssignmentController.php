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
        // ✅ ล็อกสิทธิ์ตาม Policy: assign()
        Gate::authorize('assign', $req);

        $data = $request->validate([
            'user_ids'     => ['nullable', 'array'],
            'user_ids.*'   => ['integer', 'exists:users,id'],
            'lead_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $userIds = collect($data['user_ids'] ?? [])
            ->filter()
            ->map(fn($v) => (int)$v)
            ->unique()
            ->values();

        $leadId = isset($data['lead_user_id']) ? (int)$data['lead_user_id'] : null;

        // กัน lead ไม่อยู่ใน list → ใส่เข้า list ให้
        if ($leadId && !$userIds->contains($leadId)) {
            $userIds = $userIds->push($leadId)->unique()->values();
        }

        // ✅ กรองเฉพาะ "ทีมช่าง/ทีมงาน" (ปรับ roles ให้ตรงโปรเจกต์คุณ)
        $workerRoles = ['supervisor', 'it_support', 'network', 'developer', 'technician'];

        $workers = User::query()
            ->whereIn('id', $userIds->all())
            ->whereIn('role', $workerRoles)
            ->get()
            ->keyBy('id');

        // ถ้าเลือกมาแต่ไม่มีช่างเลย
        if ($workers->isEmpty()) {
            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'ไม่พบผู้ปฏิบัติงานที่สามารถรับงานได้',
            ]);
        }

        DB::transaction(function () use ($req, $workers, $userIds, &$leadId) {
            $now = now();

            // ดึง assignment เดิม
            $existing = $req->assignments()
                ->get()
                ->keyBy('user_id');

            // ✅ เลือก lead ให้เสถียร:
            // 1) ถ้า leadId ไม่ใช่ worker → ทิ้ง
            if ($leadId && !$workers->has($leadId)) {
                $leadId = null;
            }
            // 2) ถ้า req มี technician_id และอยู่ในทีม → ให้เป็น lead
            if (!$leadId && $req->technician_id && $workers->has((int)$req->technician_id)) {
                $leadId = (int)$req->technician_id;
            }
            // 3) ไม่งั้นให้คนแรกในลิสต์ที่เป็น worker
            if (!$leadId) {
                $leadId = (int)$workers->keys()->first();
            }

            // 1) upsert assignments ตาม userIds (แต่ต้องเป็น worker จริง)
            foreach ($userIds as $uid) {
                /** @var \App\Models\User|null $worker */
                $worker = $workers->get((int)$uid);
                if (!$worker) {
                    continue; // ไม่ใช่ worker
                }

                $isLead = ((int)$leadId === (int)$uid);

                /** @var \App\Models\MaintenanceAssignment|null $assignment */
                $assignment = $existing->get((int)$uid);

                if ($assignment) {
                    $assignment->fill([
                        'role'    => $worker->role,
                        'is_lead' => $isLead,
                    ]);

                    // ถ้าเคย cancelled แล้วถูก assign กลับมา
                    if ($assignment->status === MaintenanceAssignment::STATUS_CANCELLED) {
                        $assignment->status      = MaintenanceAssignment::STATUS_IN_PROGRESS;
                        $assignment->assigned_at = $assignment->assigned_at ?? $now;
                    }

                    $assignment->save();
                } else {
                    MaintenanceAssignment::create([
                        'maintenance_request_id' => $req->id,
                        'user_id'                => $worker->id,
                        'role'                   => $worker->role,
                        'is_lead'                => $isLead,
                        'assigned_at'            => $now,
                        'status'                 => MaintenanceAssignment::STATUS_IN_PROGRESS,
                    ]);
                }
            }

            // 2) ยกเลิกคนที่ไม่อยู่ในทีมแล้ว
            // - ผมแนะนำ "cancelled" แทน delete เพื่อเก็บประวัติ
            $keepIds = $workers->keys()->map(fn($v)=>(int)$v)->all();

            $toCancel = $existing->keys()
                ->map(fn($v)=>(int)$v)
                ->filter(fn($uid) => !in_array($uid, $keepIds, true))
                ->all();

            if (!empty($toCancel)) {
                $req->assignments()
                    ->whereIn('user_id', $toCancel)
                    ->update([
                        'status'     => MaintenanceAssignment::STATUS_CANCELLED,
                        'is_lead'    => false,
                        'updated_at' => $now,
                    ]);
            }

            // 3) อัปเดต assigned_date ถ้ายังว่าง
            if ($req->assigned_date === null) {
                $req->assigned_date = $now;
            }

            // 4) อัปเดต technician_id ให้สอดคล้องกับ lead
            // ถ้าใบงานยังไม่มี technician_id หรือ technician_id ไม่อยู่ในทีมแล้ว → set = lead
            $leadStillInTeam = $workers->has((int)$leadId);
            $techStillInTeam = $req->technician_id ? $workers->has((int)$req->technician_id) : false;

            if ($leadStillInTeam && (!$req->technician_id || !$techStillInTeam)) {
                $req->technician_id = (int)$leadId;
            }

            $req->save();
        });

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'มอบหมายงานให้ทีมช่างเรียบร้อยแล้ว',
        ]);
    }

    /**
     * ยกเลิก assignment ของช่าง 1 คนออกจากงาน (unassign)
     * (แนะนำให้ยกเลิกเป็น cancelled เพื่อเก็บประวัติ)
     */
    public function destroy(MaintenanceAssignment $assignment)
    {
        Gate::authorize('assign', $assignment->maintenanceRequest);

        $assignment->update([
            'status'  => MaintenanceAssignment::STATUS_CANCELLED,
            'is_lead' => false,
        ]);

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'ยกเลิกการมอบหมายช่างเรียบร้อยแล้ว',
        ]);
    }
}
