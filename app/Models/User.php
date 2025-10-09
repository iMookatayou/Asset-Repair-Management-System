<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // ===== Roles (ตรงกับ enum ใน migration) =====
    public const ROLE_ADMIN      = 'admin';
    public const ROLE_TECHNICIAN = 'technician';
    public const ROLE_STAFF      = 'staff';

    protected $fillable = [
        'name',
        'email',
        'password',
        'department',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ====== Helpers ใช้เช็ค role แบบสั้น ๆ ======
    public function isAdmin(): bool      { return $this->role === self::ROLE_ADMIN; }
    public function isTechnician(): bool { return $this->role === self::ROLE_TECHNICIAN; }
    public function isStaff(): bool      { return $this->role === self::ROLE_STAFF; }

    // ====== Scopes สำหรับ query ======
    public function scopeRole($q, string $role)
    {
        return $q->where('role', $role);
    }

    public function scopeInRoles($q, array $roles)
    {
        return $q->whereIn('role', $roles);
    }

    // ====== ความสัมพันธ์กับระบบซ่อม (non-breaking) ======
    public function reportedRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'reporter_id');
    }

    public function assignedRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'technician_id');
    }

    public function logs()
    {
        return $this->hasMany(MaintenanceLog::class, 'user_id');
    }
}
