<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();

            // รหัสอ้างอิงงาน (มนุษย์อ่านง่าย)
            $table->string('request_no', 32)->nullable()->unique();

            // ครุภัณฑ์ (optional เผื่อแจ้งงานทั่วไป)
            $table->foreignId('asset_id')
                ->nullable()
                ->constrained('assets')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // ผู้แจ้ง: รองรับทั้ง user ภายในและคนนอก
            $table->foreignId('reporter_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // ข้อมูลผู้แจ้ง (กรณีคนนอก)
            $table->string('reporter_name')->nullable();
            $table->string('reporter_phone', 30)->nullable();
            $table->string('reporter_email')->nullable();
            $table->string('reporter_position')->nullable();   // <- เพิ่ม
            $table->string('reporter_ip', 45)->nullable();     // <- เพิ่ม

            // หน่วยงาน/ตำแหน่ง/สถานที่ซ่อม
            $table->foreignId('department_id')->nullable()
                ->constrained('departments')->nullOnDelete();
            $table->string('location_text')->nullable();

            // ข้อมูลทั่วไป
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low','medium','high','urgent'])->default('medium');

            // ใช้ string แทน enum เพื่อรองรับสถานะใหม่ในอนาคต
            $table->string('status', 32)->default('pending');

            // ผู้รับผิดชอบ
            $table->foreignId('technician_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // ไทม์ไลน์หลัก
            $table->timestamp('request_date')->useCurrent();
            $table->timestamp('assigned_date')->nullable();
            $table->timestamp('completed_date')->nullable(); // legacy/back-compat

            // เวิร์กโฟลว์ละเอียด
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('on_hold_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            // หมายเหตุ/ผลการซ่อม/ค่าใช้จ่าย
            $table->text('remark')->nullable();
            $table->text('resolution_note')->nullable();
            $table->decimal('cost', 10, 2)->nullable();

            // ช่องทาง + ข้อมูลเพิ่มเติม
            $table->string('source', 32)->default('web');
            $table->json('extra')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ดัชนี
            $table->index(['asset_id', 'request_date']);
            $table->index(['status', 'priority']);
            $table->index(['technician_id', 'status']);
            $table->index(['resolved_at', 'closed_at']);
            $table->index(['department_id', 'status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('maintenance_requests');
    }
};
