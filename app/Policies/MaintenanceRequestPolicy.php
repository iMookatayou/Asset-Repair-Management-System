<?php

namespace App\Policies;

use App\Models\MaintenanceRequest as MR;
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
        return $this->isTech($user) && (int) $req->technician_id === (int) $user->id;
    }

    protected function isOpenForTech(MR $req): bool
    {
        return empty($req->technician_id) && in_array($req->status, ['pending', 'accepted'], true);
    }

    public function view(User $user, MR $req): Response
    {
        if ($this->isAdminTeam($user)) return Response::allow();

        if ($this->isTech($user) && $this->isOpenForTech($req)) return Response::allow();

        if ($this->isAssignedTech($user, $req)) return Response::allow();

        if ((int) $req->reporter_id === (int) $user->id) return Response::allow();

        return Response::deny('อนุญาตให้ดูเฉพาะงานของตนเองหรือที่ได้รับมอบหมายเท่านั้น');
    }

    public function update(User $user, MR $req): Response
    {
        if ($this->isAdminTeam($user)) return Response::allow();

        if ($this->isAssignedTech($user, $req)) return Response::allow();

        if ((int) $req->reporter_id === (int) $user->id && empty($req->technician_id)) {
            return Response::allow();
        }

        return Response::deny('ไม่มีสิทธิ์แก้ไขข้อมูลใบงานนี้');
    }

    /**
     * เปลี่ยนสถานะ (ยกเว้นรับงาน) = ทีมงานหรือช่างที่รับผิดชอบเท่านั้น
     */
    public function transition(User $user, MR $req): Response
    {
        if ($this->isAdminTeam($user)) return Response::allow();

        if ($this->isAssignedTech($user, $req)) return Response::allow();

        return Response::deny('อนุญาตให้เปลี่ยนสถานะเฉพาะช่างที่รับผิดชอบหรือผู้ดูแลระบบเท่านั้น');
    }

    public function accept(User $user, MR $req): Response
    {
        // ให้ admin/sup รับแทนได้ ถ้าอยาก (ปล่อยไว้เป็น allow)
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

        return Response::deny('ไม่มีสิทธิ์ลบไฟล์แนบ');
    }

    public function assign(User $user, MR $req): Response
    {
        if ($this->isAdminTeam($user)) return Response::allow();

        if ($this->isAssignedTech($user, $req)) return Response::allow();

        return Response::deny('อนุญาตให้มอบหมายทีมช่างเฉพาะผู้ดูแล/ช่างผู้รับผิดชอบเท่านั้น');
    }
}
