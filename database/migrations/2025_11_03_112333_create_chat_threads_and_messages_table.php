<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chat_threads', function (Blueprint $t) {
            $t->id();
            $t->string('title', 180);
            $t->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $t->boolean('is_locked')->default(false);
            $t->timestamps();

            $t->index('created_at', 'chat_threads_created_at_idx');
        });

        Schema::create('chat_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('chat_thread_id')->constrained('chat_threads')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->text('body');
            $t->timestamps();

            $t->index(['chat_thread_id', 'created_at'], 'chat_messages_thread_created_idx');
            $t->index('user_id', 'chat_messages_user_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_threads');
    }
};
