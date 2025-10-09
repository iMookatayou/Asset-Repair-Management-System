<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    public $timestamps = false;

    protected $fillable = ['request_id','file_path','file_type','uploaded_at'];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    // เพิ่ม: ให้เรียก $attachment->file_url ได้เลย
    protected $appends = ['file_url'];

    public function request()
    {
        return $this->belongsTo(MaintenanceRequest::class, 'request_id');
    }

    /** Scope: ใช้กรองตาม request_id ง่ายขึ้น -> Attachment::forRequest($id)->latest('uploaded_at')->paginate() */
    public function scopeForRequest($query, int $requestId)
    {
        return $query->where('request_id', $requestId);
    }

    /** Accessor: คืน URL สำหรับเปิดไฟล์บนเว็บ (รองรับทั้ง path ธรรมดาและ path ใน storage/public) */
    public function getFileUrlAttribute(): ?string
    {
        $path = $this->file_path;
        if (!$path) return null;

        // ถ้าเก็บเป็น URL อยู่แล้ว (เช่น S3 หรือ http(s) ตรง)
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        // กรณีใช้ storage:link (public/storage -> storage/app/public)
        // เปลี่ยนตามโครงโปรเจ็กต์ของพี่ได้
        return url('storage/'.$path);
    }
}
