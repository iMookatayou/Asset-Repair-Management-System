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

    public const ROLE_ADMIN      = 'admin';
    public const ROLE_TECHNICIAN = 'technician';
    public const ROLE_STAFF      = 'staff';

    protected $fillable = [
        'name',
        'email',
        'password',
        'department',            // เก็บรหัสแผนก (code) เป็น string
        'role',
        'profile_photo_path',
        'profile_photo_thumb',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['avatar_url', 'avatar_thumb_url', 'department_name'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ===== Role Helpers =====
    public function isAdmin(): bool      { return $this->role === self::ROLE_ADMIN; }
    public function isTechnician(): bool { return $this->role === self::ROLE_TECHNICIAN; }
    public function isStaff(): bool      { return $this->role === self::ROLE_STAFF; }

    public function scopeRole($q, string $role)    { return $q->where('role', $role); }
    public function scopeInRoles($q, array $roles) { return $q->whereIn('role', $roles); }

    // ===== Relations =====
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

    /**
     * อ้างอิงแผนกด้วยคีย์ภายนอกเป็น code
     * เปลี่ยนชื่อรีเลชันเป็น departmentRef เพื่อไม่ชนกับคอลัมน์ users.department
     */
    public function departmentRef()
    {
        // users.department (local key) -> departments.code (owner key)
        return $this->belongsTo(Department::class, 'department', 'code');
    }

    // ===== Accessors / Scopes =====
    public function getDepartmentNameAttribute(): ?string
    {
        return $this->departmentRef?->name;
    }

    public function scopeDepartment($q, ?string $code)
    {
        return $code ? $q->where('department', $code) : $q;
    }

    // ===== Avatar / Profile Photo =====
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

    // ===== Private helpers =====
    private function uiAvatarUrl(int $size = 256): string
    {
        $name = urlencode($this->name ?: 'User');
        $palette = ['0D8ABC','0E2B51','16A34A','7C3AED','EA580C','DB2777','374151'];
        $idx = crc32(strtolower($this->name ?? 'user')) % count($palette);
        $bg  = $palette[$idx];
        return "https://ui-avatars.com/api/?name={$name}&background={$bg}&color=fff&size={$size}&bold=true";
    }
}
