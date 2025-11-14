<?php

namespace App\Policies;

use App\Models\MaintenanceRequest as MR;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MaintenanceRequestPolicy
{
    use HandlesAuthorization;

    // ไม่ให้สิทธิ์พิเศษทั่วระบบ (ของใครของมัน)
    // - Admin/หัวหน้า: ทำได้ทุกอย่าง

    public function view(User $user, MR $req): Response
    {
        // Admin และ Supervisor ดูได้ทุกงาน
        if ($user->isAdmin() || $user->isSupervisor()) {
            return Response::allow();
        }
        // ผู้รับผิดชอบดูได้
        if ($user->isTechnician() && (int)$req->technician_id === (int)$user->id) {
            return Response::allow();
        }
        // ผู้แจ้งดูงานตนเองได้
        if ((int)$req->reporter_id === (int)$user->id) {
            return Response::allow();
        }
        return Response::deny('อนุญาตให้ดูเฉพาะงานของตนเองหรือที่ได้รับมอบหมายเท่านั้น');
    }

    public function update(User $user, MR $req): Response
    {
        // Admin และ Supervisor แก้ไขได้ทุกงาน
        if ($user->isAdmin() || $user->isSupervisor()) {
            return Response::allow();
        }
        // ผู้รับผิดชอบแก้ไขได้
        if ($user->isTechnician() && (int)$req->technician_id === (int)$user->id) {
            return Response::allow();
        }
        // ผู้แจ้งแก้ไขได้เฉพาะช่วงยังไม่มีผู้รับผิดชอบ
        if ((int)$req->reporter_id === (int)$user->id && empty($req->technician_id)) {
            return Response::allow();
        }
        return Response::deny('ไม่มีสิทธิ์แก้ไขงานนี้');
    }

    public function transition(User $user, MR $req): Response
    {
        // Admin และ Supervisor เปลี่ยนสถานะได้ทุกงาน
        if ($user->isAdmin() || $user->isSupervisor()) {
            return Response::allow();
        }
        // ผู้รับผิดชอบเปลี่ยนสถานะได้
        if ($user->isTechnician() && (int)$req->technician_id === (int)$user->id) {
            return Response::allow();
        }
        return Response::deny('อนุญาตให้เปลี่ยนสถานะเฉพาะช่างที่รับผิดชอบหรือผู้ดูแลระบบเท่านั้น');
    }

    public function attach(User $user, MR $req): Response
    {
        // Admin และ Supervisor แนบไฟล์ได้ทุกงาน
        if ($user->isAdmin() || $user->isSupervisor()) {
            return Response::allow();
        }
        return $this->update($user, $req);
    }

    public function deleteAttachment(User $user, MR $req): Response
    {
        // Admin และ Supervisor ลบไฟล์ได้ทุกงาน
        if ($user->isAdmin() || $user->isSupervisor()) {
            return Response::allow();
        }
        return $this->update($user, $req);
    }
}
