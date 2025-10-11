<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_code',
        'name',
        'type',            // ← เพิ่มให้ตรงกับคอลัมน์ใหม่
        'category',
        'brand',
        'model',
        'serial_number',
        'location',
        'purchase_date',
        'warranty_expire',
        'status',
        'department_id',
    ];

    protected $casts = [
        'purchase_date'   => 'date',
        'warranty_expire' => 'date',
    ];

    // ค่าเริ่มต้น (กันกรณี factory/seed ไม่กำหนด)
    protected $attributes = [
        'status' => 'active',
    ];

    // ===== Relationships =====
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // ตั้งชื่อให้สื่อความหมายว่าคือ Maintenance Requests
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    // เผื่อในอนาคตจะผูกไฟล์แนบของทรัพย์สิน (optional)
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'asset_id');
    }

    // ===== Scopes =====
    public function scopeDepartmentId($q, ?int $departmentId)
    {
        return $departmentId ? $q->where('department_id', $departmentId) : $q;
    }

    public function scopeCategory($q, ?string $category)
    {
        return $category ? $q->where('category', $category) : $q;
    }

    public function scopeLocation($q, ?string $location)
    {
        return $location ? $q->where('location', $location) : $q;
    }

    public function scopeType($q, ?string $type)
    {
        return $type ? $q->where('type', $type) : $q;
    }
}
