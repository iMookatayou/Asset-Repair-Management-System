<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('maintenance_assignments', function (Blueprint $table) {
            $table->id();

            // งานอะไร
            $table->foreignId('maintenance_request_id')
                ->constrained('maintenance_requests')
                ->cascadeOnDelete();

            // คนที่ถูก assign (ทุก role ในระบบยกเว้น member)
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // เผื่อเก็บ role ตอน assign ไว้ใช้ filter ย้อนหลัง (snapshot)
            $table->string('role', 50)->nullable();

            // อันนี้เผื่อในอนาคต กรณีมีหัวหน้าทีม / คนหลักของงาน
            $table->boolean('is_lead')->default(false);

            $table->dateTime('assigned_at')->nullable();
            $table->string('status')->default('in_progress'); // in_progress / done / cancelled ...

            $table->timestamps();

            $table->unique(
                ['maintenance_request_id', 'user_id'],
                'uniq_request_user'
            ); // กันไม่ให้ assign คนเดิมซ้ำงานเดียวกัน
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_assignments');
    }
};
