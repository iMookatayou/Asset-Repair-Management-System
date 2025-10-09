<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'asset_code','name','category','brand','model',
        'serial_number','location','purchase_date','warranty_expire','status'
    ];

    protected $casts = [
        'purchase_date'   => 'date',
        'warranty_expire' => 'date',
    ];

    // ความสัมพันธ์ไปใบแจ้งซ่อม
    public function requests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    // เผื่อในอนาคตจะผูกไฟล์แนบของทรัพย์สิน (optional)
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'asset_id');
    }
}
