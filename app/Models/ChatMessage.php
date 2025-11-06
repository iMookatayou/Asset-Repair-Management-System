<?php
// app/Models/ChatMessage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    /**
     * ถ้าชื่อตารางเป็น "chat_messages" อยู่แล้ว ไม่ต้องกำหนด $table ก็ได้
     * protected $table = 'chat_messages';
     */

    /**
     * ฟิลด์ที่กรอกได้
     */
    protected $fillable = [
        'chat_thread_id',
        'user_id',
        'body',
    ];

    /**
     * อัปเดต updated_at ของ thread ทุกครั้งที่ข้อความเปลี่ยน
     */
    protected $touches = ['thread'];

    /**
     * แคสต์/ดีฟอลต์เพิ่มเติม (ถ้าต้องการ)
     */
    protected $casts = [
        'chat_thread_id' => 'integer',
        'user_id'        => 'integer',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    /**
     * ความสัมพันธ์: ข้อความนี้อยู่ในกระทู้ไหน
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(ChatThread::class, 'chat_thread_id');
    }

    /**
     * ความสัมพันธ์: ผู้ใช้ที่โพสต์ข้อความนี้
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* =======================
     *      Query Scopes
     * ======================= */

    /**
     * scope: จำกัดให้อยู่ในกระทู้ที่กำหนด
     */
    public function scopeInThread(Builder $q, int $threadId): Builder
    {
        return $q->where('chat_thread_id', $threadId);
    }

    /**
     * scope: เรียงล่าสุดก่อน (ตาม created_at)
     */
    public function scopeLatestFirst(Builder $q): Builder
    {
        return $q->orderByDesc('created_at');
    }

    /**
     * scope: โหลดเฉพาะ id ที่ใหม่กว่า (ใช้กับ polling)
     */
    public function scopeAfterId(Builder $q, int $afterId): Builder
    {
        return $q->where('id', '>', $afterId);
    }
}
