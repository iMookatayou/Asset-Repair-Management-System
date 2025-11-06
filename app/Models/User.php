<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceLog;
use App\Models\Department;

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

    public function isAdmin(): bool      { return $this->role === self::ROLE_ADMIN; }
    public function isTechnician(): bool { return $this->role === self::ROLE_TECHNICIAN; }
    public function isStaff(): bool      { return $this->role === self::ROLE_STAFF; }

    public function scopeRole($q, string $role)
    {
        return $q->where('role', $role);
    }

    public function scopeInRoles($q, array $roles)
    {
        return $q->whereIn('role', $roles);
    }

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
}
