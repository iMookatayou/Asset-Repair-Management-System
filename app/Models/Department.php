<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_th',
        'name_en',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

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

    public function getNameAttribute(): string
    {
        return $this->name_th ?: ($this->name_en ?: '');
    }

    public function getDisplayNameAttribute(): string
    {
        $th = trim((string) $this->name_th);
        $en = trim((string) $this->name_en);
        if ($th && $en) {
            return "{$th} ({$en})";
        }
        return $th ?: $en;
    }

    protected $appends = [
        'display_name',
    ];

    public function scopeCode($q, ?string $code)
    {
        return $code ? $q->where('code', $code) : $q;
    }

    public function scopeNameLike($q, ?string $name)
    {
        if (!$name) return $q;

        return $q->where(function ($qq) use ($name) {
            $qq->where('name_th', 'like', "%{$name}%")
               ->orWhere('name_en', 'like', "%{$name}%");
        });
    }

    public function scopeSearch($q, ?string $term)
    {
        if (!$term) return $q;

        return $q->where(function ($qq) use ($term) {
            $qq->where('code', 'like', "%{$term}%")
               ->orWhere('name_th', 'like', "%{$term}%")
               ->orWhere('name_en', 'like', "%{$term}%");
        });
    }
}
