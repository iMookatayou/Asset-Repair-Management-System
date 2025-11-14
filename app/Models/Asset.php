<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    private const REQ_FK = 'request_id';

    protected $fillable = [
        'asset_code',
        'name',
        'type',
        'brand',
        'model',
        'serial_number',
        'location',
        'purchase_date',
        'warranty_expire',
        'status',
        'department_id',
        'category_id',
    ];

    protected $casts = [
        'purchase_date'   => 'date',
        'warranty_expire' => 'date',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    protected $appends = [
        'display_name',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function categoryRef()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'asset_id');
    }

    public function requestAttachments()
    {
        return $this->hasManyThrough(
            Attachment::class,          // ปลายทาง (ไฟล์แนบ)
            MaintenanceRequest::class,  // ผ่าน (ใบงาน)
            'asset_id',                 // maintenance_requests.asset_id -> assets.id
            'attachable_id',            // attachments.attachable_id -> maintenance_requests.id
            'id',                       // assets.id
            'id'                        // maintenance_requests.id
        )->where('attachments.attachable_type', (new MaintenanceRequest())->getMorphClass());
    }

    public function requestLogs()
    {
        return $this->hasManyThrough(
            MaintenanceLog::class,
            MaintenanceRequest::class,
            'asset_id',                 // maintenance_requests.asset_id -> assets.id
            self::REQ_FK,               // maintenance_logs.FK -> maintenance_requests.id
            'id',
            'id'
        );
    }
    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        return $q->where(function ($w) use ($term) {
            $w->where('asset_code', 'like', "%{$term}%")
              ->orWhere('name', 'like', "%{$term}%")
              ->orWhere('serial_number', 'like', "%{$term}%");
        });
    }

    public function scopeDepartmentId($q, ?int $departmentId)
    {
        return ($departmentId && $departmentId > 0) ? $q->where('department_id', $departmentId) : $q;
    }

    public function scopeStatus($q, ?string $status)
    {
        $status = trim((string) $status);
        return $status !== '' ? $q->where('status', $status) : $q;
    }

    public function scopeCategory($q, ?string $category)
    {
        return $q;
    }

    public function scopeLocation($q, ?string $location)
    {
        return $location ? $q->where('location', $location) : $q;
    }

    public function scopeType($q, ?string $type)
    {
        return $type ? $q->where('type', $type) : $q;
    }

    public function scopeSortBySafe($q, ?string $by, string $dir = 'desc')
    {
        $map = [
            'id'              => 'id',
            'asset_code'      => 'asset_code',
            'name'            => 'name',
            'status'          => 'status',
            'purchase_date'   => 'purchase_date',
            'warranty_expire' => 'warranty_expire',
            'created_at'      => 'created_at',
        ];
        $col = $map[$by ?? 'id'] ?? 'id';
        $dir = strtolower($dir) === 'asc' ? 'asc' : 'desc';
        return $q->orderBy($col, $dir);
    }

    public function getDisplayNameAttribute(): string
    {
        $code = trim((string) $this->asset_code);
        $name = trim((string) $this->name);
        if ($code && $name) return $code.' - '.$name;
        return $code ?: $name;
    }
}
