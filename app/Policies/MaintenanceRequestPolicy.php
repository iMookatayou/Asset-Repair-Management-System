<?php

namespace App\Policies;

use App\Models\MaintenanceRequest as MR;
use App\Models\MaintenanceAssignment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MaintenanceRequestPolicy
{
    use HandlesAuthorization;

    protected function isAdminTeam(User $user): bool
    {
        return $user->isAdmin() || $user->isSupervisor();
    }

    protected function isTech(User $user): bool
    {
        return $user->isTechnician();
    }

    protected function isAssignedTech(User $user, MR $req): bool
    {
        if (!$this->isTech($user)) return false;

        // lead / technician_id
        if ((int) $req->technician_id === (int) $user->id) return true;

        // อยู่ในทีมช่างจาก maintenance_assignments
        return $req->assignments()
            ->where('user_id', $user->id)
            ->where('status', '!=', MaintenanceAssignment::STATUS_CANCELLED)
            ->exists();
    }

    protected function isOpenForTech(MR $req): bool
    {
        return empty($req->technician_id) && $req->status === 'pending';
    }

    public function view(User $user, MR $req): Response
    {
        if ($this->isAdminTeam($user)) return Response::allow();

        // ช่างเห็นงานว่างเพื่อเข้า queue
        if ($this->isTech($user) && $this->isOpenForTech($req)) return Response::allow();

        // ช่างในทีมงานนี้
        if ($this->isAssignedTech($user, $req)) return Response::allow();

        // ผู้แจ้งดูงานตัวเอง
        if ((int) $req->reporter_id === (int) $user->id) return Response::allow();

        return Response::deny('อนุญาตให้ดูเฉพาะงานของตนเองหรือที่ได้รับมอบหมายเท่านั้น');
    }

    public function update(User $user, MR $req): Response
    {
        if ($this->isAdminTeam($user)) return Response::allow();

        if ($this->isAssignedTech($user, $req)) return Response::allow();

        // ผู้แจ้งแก้ได้เฉพาะช่วงต้น และต้องยังไม่มีการ assign ทีมช่างจริง ๆ
        if (
            (int) $req->reporter_id === (int) $user->id &&
            empty($req->technician_id) &&
            in_array($req->status, ['pending'], true)
        ) {
            return Response::allow();
        }

        return Response::deny('ไม่มีสิทธิ์แก้ไขข้อมูลใบงานนี้');
    }

    public function transition(User $user, MR $req): Response
    {
        if ($this->isAdminTeam($user)) return Response::allow();

        if ($this->isAssignedTech($user, $req)) return Response::allow();

        return Response::deny('อนุญาตให้เปลี่ยนสถานะเฉพาะช่างที่รับผิดชอบหรือผู้ดูแลระบบเท่านั้น');
    }

    public function accept(User $user, MR $req): Response
    {
        if ($this->isAdminTeam($user)) return Response::allow();

        if (!$this->isTech($user)) return Response::deny('เฉพาะช่างเท่านั้น');

        if ($this->isOpenForTech($req)) return Response::allow();

        return Response::deny('งานนี้ถูกมอบหมายแล้วหรือไม่อยู่ในสถานะที่รับได้');
    }

    public function attach(User $user, MR $req): Response
    {
        if ($this->isAdminTeam($user)) return Response::allow();

        if ($this->isAssignedTech($user, $req)) return Response::allow();

        if ((int) $req->reporter_id === (int) $user->id) return Response::allow();

        return Response::deny('ไม่มีสิทธิ์แนบไฟล์ในงานนี้');
    }

    public function deleteAttachment(User $user, MR $req): Response
    {
        if ($this->isAdminTeam($user)) return Response::allow();

        if ($this->isAssignedTech($user, $req)) return Response::allow();

        if (
            (int) $req->reporter_id === (int) $user->id &&
            !in_array($req->status, ['resolved','closed'], true)
        ) {
            return Response::allow();
        }

        return Response::deny('ไม่มีสิทธิ์ลบไฟล์แนบ');
    }

    public function assign(User $user, MR $req): Response
    {
        if ($this->isAdminTeam($user)) return Response::allow();

        if ($this->isAssignedTech($user, $req)) return Response::allow();

        return Response::deny('อนุญาตให้มอบหมายทีมช่างเฉพาะผู้ดูแล/ช่างในทีมงานนี้เท่านั้น');
    }

        public function reject(User $user, MR $req): Response
    {
        if ($this->isAdminTeam($user)) return Response::allow();

        if (!$this->isTech($user)) return Response::deny('เฉพาะช่างเท่านั้น');

        // ไม่รับเรื่องได้เฉพาะงานว่างจริง
        if ($this->isOpenForTech($req)) return Response::allow();

        return Response::deny('งานนี้ไม่อยู่ในสถานะที่ไม่รับเรื่องได้');
    }

    public function cancelByReporter(User $user, MR $req): Response
    {
        if ($this->isAdminTeam($user)) return Response::allow();

        // ผู้แจ้งยกเลิกได้ช่วงต้น
        if ((int) $req->reporter_id === (int) $user->id) {
            if (in_array($req->status, ['pending','accepted'], true)) {
                return Response::allow();
            }
        }

        return Response::deny('ไม่มีสิทธิ์ยกเลิกคำขอนี้');
    }

    public function cancelByTech(User $user, MR $req): Response
    {
        if ($this->isAdminTeam($user)) return Response::allow();
        if (!$this->isTech($user)) return Response::deny('เฉพาะช่างเท่านั้น');

        if ($this->isOpenForTech($req)) {
            return Response::allow();
        }

        if (!$this->isAssignedTech($user, $req)) {
            return Response::deny('อนุญาตให้ยกเลิกเฉพาะงานที่ได้รับมอบหมายเท่านั้น');
        }

        if (!in_array($req->status, ['resolved','closed','cancelled'], true)) {
            return Response::allow();
        }

        return Response::deny('งานนี้ไม่อยู่ในสถานะที่ยกเลิกได้');
    }
}
