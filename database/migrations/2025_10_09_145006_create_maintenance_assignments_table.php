<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('maintenance_request_id')
                ->constrained('maintenance_requests')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('role', 50)->nullable();
            $table->boolean('is_lead')->default(false);

            $table->dateTime('assigned_at')->nullable();

            // การตอบรับงานของช่าง (MyJob ใช้อันนี้เป็นหลัก)
            $table->enum('response_status', [
                'pending',
                'accepted',
                'rejected',
                'acknowledged',
            ])->default('pending');

            $table->dateTime('responded_at')->nullable();

            // สถานะความคืบหน้างาน
            $table->enum('status', [
                'in_progress',
                'done',
                'cancelled',
            ])->default('in_progress');

            $table->timestamps();

            // unique
            $table->unique(
                ['maintenance_request_id', 'user_id'],
                'ma_req_user_uniq'
            );

            // index สำหรับงาน
            $table->index(
                ['user_id', 'status'],
                'ma_user_status_idx'
            );

            $table->index(
                ['maintenance_request_id', 'status'],
                'ma_req_status_idx'
            );

            // index สำหรับ MyJob (response)
            $table->index(
                ['user_id', 'response_status'],
                'ma_user_resp_idx'
            );

            $table->index(
                ['maintenance_request_id', 'response_status'],
                'ma_req_resp_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_assignments');
    }
};
