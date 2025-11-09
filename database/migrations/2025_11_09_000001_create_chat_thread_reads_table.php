<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_thread_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chat_thread_id')->constrained('chat_threads')->cascadeOnDelete();
            $table->unsignedBigInteger('last_read_message_id')->nullable();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id','chat_thread_id'], 'utr_user_thread_unique');
            $table->index(['chat_thread_id','last_read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_thread_reads');
    }
};
