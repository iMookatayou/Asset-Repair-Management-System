<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // ⬅️ เพิ่ม

class MaintenanceRequest extends Model
{
    use HasFactory; // ⬅️ เพิ่ม

    protected $fillable = [
        'asset_id','reporter_id','title','description',
        'priority','status','technician_id',
        'request_date','assigned_date','completed_date','remark',
        'cost', // ⬅️ อย่าลืมถ้ามีคอลัมน์นี้
    ];

    protected $casts = [
        'request_date'   => 'datetime',
        'assigned_date'  => 'datetime',
        'completed_date' => 'datetime',
        'cost'           => 'decimal:2',
    ];

    // ===== Constants =====
    public const STATUS_PENDING     = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_CANCELLED   = 'cancelled';

    public const PRIORITY_LOW     = 'low';
    public const PRIORITY_MEDIUM  = 'medium';
    public const PRIORITY_HIGH    = 'high';
    public const PRIORITY_URGENT  = 'urgent';

    // ===== Relationships =====
    public function asset()       { return $this->belongsTo(Asset::class); }
    public function reporter()    { return $this->belongsTo(User::class, 'reporter_id'); }
    public function technician()  { return $this->belongsTo(User::class, 'technician_id'); }
    public function attachments() { return $this->hasMany(Attachment::class, 'request_id'); }
    public function logs()        { return $this->hasMany(MaintenanceLog::class, 'request_id'); }

    // ===== Scopes =====
    public function scopeStatus($q, ?string $status)    { return $status ? $q->where('status', $status) : $q; }
    public function scopePriority($q, ?string $priority){ return $priority ? $q->where('priority', $priority) : $q; }
    public function scopeRequestedBetween($q, ?string $from, ?string $to)
    {
        if ($from) $q->where('request_date', '>=', $from);
        if ($to)   $q->where('request_date', '<=', $to);
        return $q;
    }
}
