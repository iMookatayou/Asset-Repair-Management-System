<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceOperationLog extends Model
{
    protected $fillable = [
        'maintenance_request_id',
        'user_id',
        'operation_date',
        'operation_method',
        'hospital_name',
        'require_precheck',
        'remark',
        'issue_software',
        'issue_hardware',
    ];

    protected $casts = [
        'operation_date'   => 'date',
        'require_precheck' => 'boolean',
        'issue_software'   => 'boolean',
        'issue_hardware'   => 'boolean',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class, 'maintenance_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
