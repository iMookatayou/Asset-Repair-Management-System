<?php
// app/Models/ChatThread.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatThread extends Model
{
    protected $fillable = ['title', 'author_id', 'is_locked'];

    // เจ้าของกระทู้
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // ข้อความทั้งหมดในกระทู้ (อย่าใส่ ->latest() ตายตัว)
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'chat_thread_id');
    }

    // ข้อความล่าสุดของกระทู้ (ไว้ทำ preview)
    public function latestMessage(): HasOne
    {
        // ใช้ created_at ในการคัด "ล่าสุด"
        return $this->hasOne(ChatMessage::class, 'chat_thread_id')->latestOfMany('created_at');
    }
}
