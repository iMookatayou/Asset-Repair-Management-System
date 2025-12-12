<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_ratings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('maintenance_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('rater_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('technician_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // ใช้ ENUM แทน CHECK เพื่อรองรับ MySQL เก่า
            $table->enum('score', [1, 2, 3, 4, 5]);

            $table->text('comment')->nullable();

            $table->timestamps();

            // ลบแบบนิ่ม (ถ้าไม่ใช้ก็ลบบรรทัดนี้ออกได้)
            $table->softDeletes();

            // 1 คำขอซ่อม + 1 คน → ให้คะแนนได้ครั้งเดียว
            $table->unique(['maintenance_request_id', 'rater_id']);

            // index สำหรับ query รายงาน
            $table->index('technician_id');
            $table->index('rater_id');
            $table->index('maintenance_request_id');
            $table->index(['technician_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_ratings');
    }
};
