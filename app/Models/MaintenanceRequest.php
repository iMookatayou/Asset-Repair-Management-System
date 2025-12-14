<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\MaintenanceOperationLog;
use App\Models\MaintenanceAssignment;
use App\Models\MaintenanceLog;
use App\Models\MaintenanceRating;

class MaintenanceRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        // อ้างอิง/พื้นฐาน
        'request_no',
        'asset_id',
        'department_id',
        'reporter_id',
        'title',
        'description',
        'priority',
        'status',
        'technician_id',

        // ผู้แจ้ง (กรณีคนนอก)
        'reporter_name',
        'reporter_phone',
        'reporter_email',
        'reporter_position',
        'reporter_ip',

        // ที่ตั้ง/หน่วยงาน
        'location_text',

        // ไทม์ไลน์
        'request_date',
        'assigned_date',
        'completed_date',
        'accepted_at',
        'started_at',
        'on_hold_at',
        'resolved_at',
        'closed_at',

        // หมายเหตุ/ผลการซ่อม/ต้นทาง/ข้อมูลเสริม
        'remark',
        'resolution_note',
        'cost',
        'source',
        'extra',
    ];

    protected $casts = [
        'request_date'   => 'datetime',
        'assigned_date'  => 'datetime',
        'completed_date' => 'datetime',
        'accepted_at'    => 'datetime',
        'started_at'     => 'datetime',
        'on_hold_at'     => 'datetime',
        'resolved_at'    => 'datetime',
        'closed_at'      => 'datetime',
        'cost'           => 'decimal:2',
        'extra'          => 'array',
        'deleted_at'     => 'datetime',
    ];

    public const STATUS_PENDING     = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_CANCELLED   = 'cancelled';

    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_ON_HOLD  = 'on_hold';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED   = 'closed';

    public const PRIORITY_LOW    = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH   = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const GROUP_PENDING    = ['pending', 'accepted', null];
    public const GROUP_INPROGRESS = ['in_progress', 'on_hold'];
    public const GROUP_COMPLETED  = ['resolved', 'completed', 'closed'];

    public function asset()      { return $this->belongsTo(Asset::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function reporter()   { return $this->belongsTo(User::class, 'reporter_id'); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id'); }

    public function operationLog()
    {
        return $this->hasOne(\App\Models\MaintenanceOperationLog::class, 'maintenance_request_id');
    }

    // งานนี้ถูก assign ให้ใครบ้าง (ทุกคน)
    public function assignments()
    {
        return $this->hasMany(MaintenanceAssignment::class, 'maintenance_request_id');
    }

    // ดึงรายชื่อ "คนทำงาน" ตรง ๆ แบบ belongsToMany
    public function workers()
    {
        return $this->belongsToMany(User::class, 'maintenance_assignments')
                    ->withPivot(['role', 'is_lead', 'assigned_at', 'status'])
                    ->withTimestamps();
    }

    public function logs()
    {
        return $this->hasMany(MaintenanceLog::class, 'request_id');
    }

    public function attachments()
    {
        return $this->morphMany(\App\Models\Attachment::class, 'attachable')->ordered();
    }

    public function imageAttachments()
    {
        return $this->attachments()->whereHas('file', fn($q) => $q->where('mime', 'like', 'image/%'));
    }

    public function latestAttachment()
    {
        return $this->morphOne(\App\Models\Attachment::class, 'attachable')->latestOfMany('id');
    }

    public function ratings()
    {
        return $this->hasMany(MaintenanceRating::class, 'maintenance_request_id');
    }

    public function rating()
    {
        return $this->hasOne(MaintenanceRating::class, 'maintenance_request_id')
            ->where('rater_id', auth()->id());
    }

    public function ratingBy(int $userId)
    {
        return $this->hasOne(MaintenanceRating::class, 'maintenance_request_id')
            ->where('rater_id', $userId);
    }

    public function getNormalizedStatusAttribute(): string
    {
        if ($this->status === self::STATUS_COMPLETED && $this->resolved_at) {
            return self::STATUS_RESOLVED;
        }
        return (string) $this->status;
    }

    /**
     * สร้างเลขใบงานแบบ legacy: YY + TYPE + RUNNING(5 หลัก)
     * เช่น ปี พ.ศ. 2568 → 68, type 10 → 681000001
     */
    public static function generateLegacyRequestNo(): string
    {
        // ปี พ.ศ. → เอา 2 หลักท้าย เช่น 2568 -> "68"
        $thaiYear = now()->year + 543;
        $yy = substr((string) $thaiYear, -2);

        // TODO: ภายหลังอาจ map จาก department หรือประเภทงาน
        $type = '10'; // ตอนนี้ fix 10 ไปก่อนแบบระบบเก่า

        // นับจำนวนงานในปี ค.ศ. ปัจจุบัน แล้ว +1 ให้เป็น running
        $count = static::query()
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        // เติม 5 หลัก เช่น 1 -> 00001
        $run = str_pad((string) $count, 5, '0', STR_PAD_LEFT);

        return $yy.$type.$run; // ถ้าจะใส่ขีดก็เปลี่ยนเป็น "{$yy}-{$type}-{$run}"
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            // ถ้ายังไม่มี request_no ให้ใช้เลขแบบ legacy 68xxxxxxx ไปก่อน
            if (empty($model->request_no)) {
                $model->request_no = static::generateLegacyRequestNo();
            }

            if (empty($model->source)) {
                $model->source = 'web';
            }
        });
    }

    public function scopeStatus($q, ?string $s)
    {
        return $s ? $q->where('status', $s) : $q;
    }

    public function scopePriority($q, ?string $p)
    {
        return $p ? $q->where('priority', $p) : $q;
    }

    public function scopeRequestedBetween($q, ?string $from, ?string $to)
    {
        if ($from) $q->where('request_date', '>=', $from);
        if ($to)   $q->where('request_date', '<=', $to);
        return $q;
    }

    public function scopeSearch($q, ?string $term)
    {
        if (!$term) return $q;
        return $q->where(function ($w) use ($term) {
            $w->where('title', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopePendingGroup($q)
    {
        return $q->where(function ($w) {
            $w->whereIn('status', ['pending', 'accepted'])
              ->orWhereNull('status');
        });
    }

    public function scopeInProgressGroup($q)
    {
        return $q->whereIn('status', self::GROUP_INPROGRESS);
    }

    public function scopeCompletedGroup($q)
    {
        return $q->whereIn('status', self::GROUP_COMPLETED);
    }
}
