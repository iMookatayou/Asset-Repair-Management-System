<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * ถ้าตาราง attachments / maintenance_logs อ้างใบงานด้วยคีย์อื่น
     * เช่น 'maintenance_request_id' ให้เปลี่ยนตรงนี้จุดเดียว
     */
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

    // =========================
    // Relationships
    // =========================

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function categoryRef()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    /**
     * ใบงานซ่อมของทรัพย์สินนี้ (FK: maintenance_requests.asset_id)
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'asset_id');
    }

    /**
     * ไฟล์แนบของทรัพย์สิน “ผ่าน” ใบงานซ่อม (polymorphic)
     * attachments.attachable_id -> maintenance_requests.id AND attachments.attachable_type = MaintenanceRequest::class
     * NOTE: ตาราง attachments ไม่มีคอลัมน์ request_id อีกต่อไป (ใช้ morph: attachable_type/attachable_id)
     */
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

    /**
     * บันทึกเหตุการณ์/Log ของการซ่อม “ผ่าน” ใบงานซ่อม
     * maintenance_logs.{request_id|maintenance_request_id} -> maintenance_requests.id
     */
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

    // =========================
    // Query Scopes (ช่วยให้ controller/view สะอาด)
    // =========================

    /**
     * ค้นหาแบบรวมหลายฟิลด์ (code/name/serial)
     */
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
        // Treat 0 / null / falsy as no filter (Request::integer returns 0 when param missing)
        return ($departmentId && $departmentId > 0) ? $q->where('department_id', $departmentId) : $q;
    }

    /**
     * Filter by status if provided (supports '', null => no-op)
     */
    public function scopeStatus($q, ?string $status)
    {
        $status = trim((string) $status);
        return $status !== '' ? $q->where('status', $status) : $q;
    }

    public function scopeCategory($q, ?string $category)
    {
        // legacy: ก่อนใช้ category_id มีคอลัมน์ category (string) ใน model
        // ปัจจุบันตัดออกเพื่อหลีกเลี่ยงความสับสน หากต้องการ filter หมวดหมู่ให้ใช้ where('category_id', ..) หรือ join กับ asset_categories
        return $q; // no-op
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
}
