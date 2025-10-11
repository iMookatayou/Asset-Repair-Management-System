<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name'];

    // ===== Relationships =====
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    // รวมใบซ่อมทั้งหมดของแผนก (ผ่านทรัพย์สิน)
    public function maintenanceRequests()
    {
        return $this->hasManyThrough(
            MaintenanceRequest::class, // ปลายทาง
            Asset::class,              // กลาง
            'department_id',           // FK บน assets → departments.id
            'asset_id',                // FK บน maintenance_requests → assets.id
            'id',                      // PK departments
            'id'                       // PK assets
        );
    }

    // ===== Scopes =====
    public function scopeCode($q, ?string $code)
    {
        return $code ? $q->where('code', $code) : $q;
    }

    public function scopeNameLike($q, ?string $name)
    {
        return $name ? $q->where('name', 'like', "%{$name}%") : $q;
    }
}
