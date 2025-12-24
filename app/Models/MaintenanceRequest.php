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
        // ===== อ้างอิง / พื้นฐาน =====
        'request_no',
        'asset_id',
        'department_id',
        'reporter_id',
        'title',
        'description',
        'priority',
        'status',
        'technician_id',

        // ===== ผู้แจ้ง =====
        'reporter_name',
        'reporter_phone',
        'reporter_email',
        'reporter_position',

        'legacy_payload',

        // ===== สถานที่ =====
        'location_text',

        // ===== timeline =====
        'request_date',
        'assigned_date',
        'completed_date',
        'accepted_at',
        'started_at',
        'on_hold_at',
        'resolved_at',
        'closed_at',

        // ===== อื่น ๆ =====
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

        // รองรับระบบเก่า / เก็บข้อมูลเพิ่ม
        'legacy_payload' => 'array',
        'extra'          => 'array',

        'deleted_at'     => 'datetime',
    ];

    /* ================= STATUS ================= */

    public const STATUS_PENDING     = 'pending';
    public const STATUS_ACCEPTED    = 'accepted';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_ON_HOLD     = 'on_hold';
    public const STATUS_RESOLVED    = 'resolved';
    public const STATUS_CLOSED      = 'closed';
    public const STATUS_CANCELLED   = 'cancelled';
    public const STATUS_REJECTED    = 'rejected';

    // legacy (เผื่อยังมีข้อมูลเก่าใน DB)
    public const STATUS_COMPLETED   = 'completed';

    public const PRIORITY_LOW    = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH   = 'high';
    public const PRIORITY_URGENT = 'urgent';

    // ให้สอดคล้องกับหน้าคิว/งานของฉัน (Controller)
    public const GROUP_PENDING    = ['pending'];
    public const GROUP_INPROGRESS = ['accepted','in_progress','on_hold'];
    public const GROUP_COMPLETED  = ['resolved','closed','completed'];// completed เป็น legacy

    /* ================= RELATION ================= */

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function operationLog()
    {
        return $this->hasOne(MaintenanceOperationLog::class, 'maintenance_request_id');
    }

    public function assignments()
    {
        return $this->hasMany(MaintenanceAssignment::class, 'maintenance_request_id');
    }

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
        return $this->attachments()
            ->whereHas('file', fn ($q) => $q->where('mime', 'like', 'image/%'));
    }

    public function latestAttachment()
    {
        return $this->morphOne(\App\Models\Attachment::class, 'attachable')
            ->latestOfMany('id');
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

    /* ================= ACCESSOR ================= */

    public function getNormalizedStatusAttribute(): string
    {
        // normalize legacy completed -> resolved (ถ้ามี timestamp resolved_at)
        if ($this->status === self::STATUS_COMPLETED && $this->resolved_at) {
            return self::STATUS_RESOLVED;
        }
        return (string) $this->status;
    }

    /* ================= REQUEST NO ================= */

    /**
     * Legacy format: YY + TYPE + RUNNING(5)
     * example: 68 + 10 + 00001 = 681000001
     */
    public static function generateLegacyRequestNo(): string
    {
        $thaiYear = now()->year + 543;
        $yy = substr((string) $thaiYear, -2);

        $type = '10'; // legacy fixed type

        $count = static::query()
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        $run = str_pad((string) $count, 5, '0', STR_PAD_LEFT);

        return $yy . $type . $run;
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->request_no)) {
                $model->request_no = static::generateLegacyRequestNo();
            }
            if (empty($model->source)) {
                $model->source = 'web';
            }
        });
    }

    /* ================= SCOPE ================= */

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

    /**
     * ให้ผลค้นหาสอดคล้องกับ Controller (title/description/request_no/reporter fields + reporter relation + asset)
     */
    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        $isNumeric = ctype_digit($term);
        $len = strlen($term);

        // ไม่ต้องไป match request_no (เลขใบงานยาว) เพราะมันจะปนเหมือนในรูป
        if ($isNumeric && $len <= 5) {
            $hash = '#'.$term;

            return $q->where(function ($w) use ($term, $hash) {
                    $w->where('id', (int) $term)
                    ->orWhere('title', 'like', "%{$hash}%")
                    ->orWhere('title', 'like', "%{$term}%"); // เผื่อบางคนพิมพ์ไม่มี #
                })
                ->orderByRaw(
                    "CASE
                        WHEN id = ? THEN 0
                        WHEN title LIKE ? THEN 1
                        WHEN title LIKE ? THEN 2
                        ELSE 9
                    END ASC",
                    [(int)$term, "%{$hash}%", "%{$term}%"]
                )
                ->orderByDesc('id');
        }

        return $q->where(function ($w) use ($term) {
            $w->where('title', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%")
            ->orWhere('request_no', 'like', "%{$term}%")
            ->orWhere('reporter_name', 'like', "%{$term}%")
            ->orWhere('reporter_phone', 'like', "%{$term}%")
            ->orWhere('reporter_email', 'like', "%{$term}%")
            ->orWhereHas('reporter', fn ($qr) =>
                    $qr->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
            )
            ->orWhereHas('asset', fn ($qa) =>
                    $qa->where('name', 'like', "%{$term}%")
                    ->orWhere('asset_code', 'like', "%{$term}%")
            );
        })
        ->orderByDesc('id');
    }

    public function scopePendingGroup($q)
    {
        return $q->whereIn('status', self::GROUP_PENDING);
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
