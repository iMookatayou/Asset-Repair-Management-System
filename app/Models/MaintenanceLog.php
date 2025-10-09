<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['request_id','user_id','action','note','created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // === คอนสแตนต์ action (อ้างอิงในโค้ดได้, ลดพิมพ์ผิด) ===
    public const ACTION_CREATE   = 'create_request';
    public const ACTION_UPDATE   = 'update_request';
    public const ACTION_ASSIGN   = 'assign_technician';
    public const ACTION_START    = 'start_request';
    public const ACTION_COMPLETE = 'complete_request';
    public const ACTION_CANCEL   = 'cancel_request';

    // === ความสัมพันธ์ ===
    public function request()
    {
        return $this->belongsTo(MaintenanceRequest::class, 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // === Scopes ช่วยเขียน query ให้สั้น ===
    public function scopeForRequest($query, int $requestId)
    {
        return $query->where('request_id', $requestId);
    }

    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }
}
