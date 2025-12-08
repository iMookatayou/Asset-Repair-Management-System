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

            // วันที่ปฏิบัติงาน / วันที่ลงรายงาน
            $table->date('operation_date')->nullable();

            // วิธีการปฏิบัติ : ตามใบเบิก / ค่าบริการ / อื่น ๆ
            $table->enum('operation_method', ['requisition', 'service_fee', 'other'])
                ->nullable();

            // รพจ. = รหัสครุภัณฑ์ (Property Code) เช่น 68101068718
            $table->string('property_code', 100)
                ->nullable()
                ->comment('รหัสครุภัณฑ์ (รพจ.)');

            // ยืนยันว่าได้แจ้ง/ขออนุญาตผู้ใช้งาน/หน่วยงานก่อนปฏิบัติงาน/ปิดเครื่อง
            $table->boolean('require_precheck')->default(false);

            // หมายเหตุ/รายละเอียดเพิ่มเติมในการปฏิบัติงาน
            $table->text('remark')->nullable();

            // ประเภทปัญหา
            $table->boolean('issue_software')->default(false);
            $table->boolean('issue_hardware')->default(false);

            $table->timestamps();

            // บังคับ 1 ใบงาน → มีได้ 1 รายงานการปฏิบัติงาน
            $table->unique('maintenance_request_id', 'uniq_operation_log_request');

            $table->index(['operation_date']);
            $table->index(['property_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_operation_logs');
    }
};
