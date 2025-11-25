<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_operation_logs', function (Blueprint $table) {
            $table->id();

            // ผูกกับใบงาน (ใช้ชื่อให้เข้ากับ maintenance_assignments)
            $table->foreignId('maintenance_request_id')
                ->constrained('maintenance_requests')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // คนที่บันทึก (ช่าง / แอดมิน)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // รายการซ่อมสำหรับ "วันที่"
            $table->date('operation_date')->nullable();

            // วิธีการปฏิบัติ : ตามใบเบิก / ค่าบริการ / อื่น ๆ
            $table->enum('operation_method', ['requisition', 'service_fee', 'other'])
                ->nullable();

            // ระบุ รพ. / หน่วยงาน (ตามฟอร์ม)
            $table->string('hospital_name')->nullable();

            // ต้องระบุขอปิดงาน/ปิดเครื่องก่อนเสมอ (checkbox)
            $table->boolean('require_precheck')->default(false);

            // หมายเหตุ/รายละเอียดเพิ่มเติม
            $table->text('remark')->nullable();

            // ประเภทงาน
            $table->boolean('issue_software')->default(false);
            $table->boolean('issue_hardware')->default(false);

            $table->timestamps();

            // บังคับ 1 ใบงาน → 1 รายงานการปฏิบัติงาน
            $table->unique('maintenance_request_id', 'uniq_operation_log_request');

            $table->index(['operation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_operation_logs');
    }
};
