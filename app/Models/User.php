<?php

namespace App\Models;

use App\Models\Department;
use App\Models\MaintenanceLog;
use App\Models\MaintenanceRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // บทบาทตามที่ตกลง (เก็บเป็นโค้ดสั้นสำหรับระบบ)
    public const ROLE_ADMIN             = 'admin';           // ผู้ดูแลระบบ Admin
    public const ROLE_SUPERVISOR        = 'supervisor';      // หัวหน้า Supervisor
    public const ROLE_IT_SUPPORT        = 'it_support';      // ไอทีซัพพอร์ต IT Support
    public const ROLE_NETWORK           = 'network';         // เน็ตเวิร์ค Network
    public const ROLE_DEVELOPER         = 'developer';       // นักพัฒนา Developer
    public const ROLE_COMPUTER_OFFICER  = 'computer_officer';// บุคลากร Member

    protected $fillable = [
        'name',
        'email',
        'password',
        'department',
        'role',
        'profile_photo_path',
        'profile_photo_thumb',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['avatar_url', 'avatar_thumb_url', 'department_name', 'role_label'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // เพื่อให้โค้ดเดิมทำงานต่อได้: map isAdmin/isTechnician ไปยังบทบาทใหม่
    public function isAdmin(): bool      { return $this->role === self::ROLE_ADMIN; }
    public function isTechnician(): bool { return in_array($this->role, [
        self::ROLE_IT_SUPPORT,
        self::ROLE_NETWORK,
        self::ROLE_DEVELOPER,
    ], true); }
    // Legacy compatibility method (staff removed); always false
    public function isStaff(): bool      { return false; }

    // กลุ่มสิทธิ์แบบอ่านง่าย
    public function isSupervisor(): bool { return $this->role === self::ROLE_SUPERVISOR; }
    public function isWorker(): bool     { return $this->isTechnician(); }

    // รายชื่อบทบาทที่ระบบรองรับ (ใช้ในฟอร์มหรือฟิลเตอร์)
    public static function availableRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_SUPERVISOR,
            self::ROLE_IT_SUPPORT,
            self::ROLE_NETWORK,
            self::ROLE_DEVELOPER,
            self::ROLE_COMPUTER_OFFICER,
        ];
    }

    // บทบาทที่ถือว่าเป็นทีมปฏิบัติการ (worker)
    public static function workerRoles(): array
    {
        return [
            self::ROLE_IT_SUPPORT,
            self::ROLE_NETWORK,
            self::ROLE_DEVELOPER,
        ];
    }

    // บทบาทที่แสดงใน Team Drawer (หัวหน้า + ทีมปฏิบัติการ)
    public static function teamRoles(): array
    {
        return array_merge([self::ROLE_SUPERVISOR], self::workerRoles());
    }

    // แผนที่บทบาท -> ป้ายกำกับ ไทย-อังกฤษ
    public static function roleLabels(): array
    {
        return [
            self::ROLE_ADMIN             => 'ผู้ดูแลระบบ Admin',
            self::ROLE_SUPERVISOR        => 'หัวหน้า Supervisor',
            self::ROLE_IT_SUPPORT        => 'ไอทีซัพพอร์ต IT Support',
            self::ROLE_NETWORK           => 'เน็ตเวิร์ค Network',
            self::ROLE_DEVELOPER         => 'นักพัฒนา Developer',
            self::ROLE_COMPUTER_OFFICER  => 'บุคลากร Member',
        ];
    }

    public function getRoleLabelAttribute(): string
    {
        $labels = self::roleLabels();
        return $labels[$this->role] ?? (ucfirst((string) $this->role) ?: '-');
    }

    public function scopeRole($q, string $role)    { return $q->where('role', $role); }
    public function scopeInRoles($q, array $roles) { return $q->whereIn('role', $roles); }

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
    public function departmentRef()
    {
        return $this->belongsTo(Department::class, 'department', 'code');
    }

    public function getDepartmentNameAttribute(): ?string
    {
        return $this->departmentRef?->name;
    }

    public function scopeDepartment($q, ?string $code)
    {
        return $code ? $q->where('department', $code) : $q;
    }

    public function getAvatarUrlAttribute(): string
    {
        $path = $this->profile_photo_path;

        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }
        return $this->uiAvatarUrl(256);
    }

    public function getAvatarThumbUrlAttribute(): string
    {
        $thumb = $this->profile_photo_thumb;
        $main  = $this->profile_photo_path;

        if ($thumb && Storage::disk('public')->exists($thumb)) {
            return Storage::url($thumb);
        }
        if ($main && Storage::disk('public')->exists($main)) {
            return Storage::url($main);
        }
        return $this->uiAvatarUrl(128);
    }

    public function scopeHasAvatar($q)
    {
        return $q->whereNotNull('profile_photo_path')->where('profile_photo_path', '!=', '');
    }

    private function uiAvatarUrl(int $size = 256): string
    {
        $name = urlencode($this->name ?: 'User');
        $palette = ['0D8ABC','0E2B51','16A34A','7C3AED','EA580C','DB2777','374151'];
        $idx = crc32(strtolower($this->name ?? 'user')) % count($palette);
        $bg  = $palette[$idx];
        return "https://ui-avatars.com/api/?name={$name}&background={$bg}&color=fff&size={$size}&bold=true";
    }
}
