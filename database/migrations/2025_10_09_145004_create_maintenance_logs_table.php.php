<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')
                ->constrained('maintenance_requests')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('action', 100);
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['request_id', 'created_at']);
            $table->index(['action']);
            $table->index('user_id'); // แนะนำ
        });

    }
    public function down(): void {
        Schema::dropIfExists('maintenance_logs');
    }
};
