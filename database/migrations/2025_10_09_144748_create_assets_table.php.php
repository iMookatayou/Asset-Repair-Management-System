<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            // ====== ข้อมูลหลักของทรัพย์สิน ======
            $table->string('asset_code', 100)->unique();  // รหัสทรัพย์สิน (เช่น AS-IT-001)
            $table->string('name');                       // ชื่อทรัพย์สิน เช่น “เครื่องปรับอากาศ 18000 BTU”
            $table->string('type', 100)->nullable();      // ประเภทหลัก เช่น “เครื่องใช้ไฟฟ้า”, “IT”, “ครุภัณฑ์สำนักงาน”
            $table->string('category', 100)->nullable();  // หมวดย่อย เช่น “คอมพิวเตอร์”, “เครื่องพิมพ์”
            $table->string('brand', 100)->nullable();     // ยี่ห้อ
            $table->string('model', 100)->nullable();     // รุ่น
            $table->string('serial_number', 100)->nullable()->unique(); // หมายเลขซีเรียล
            $table->string('location')->nullable();       // ตำแหน่ง / ห้อง / แผนกที่อยู่

            // ====== ความสัมพันธ์กับแผนก ======
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // ====== ข้อมูลเพิ่มเติม ======
            $table->date('purchase_date')->nullable();    // วันที่ซื้อ
            $table->date('warranty_expire')->nullable();  // วันหมดประกัน
            $table->enum('status', ['active','in_repair','disposed'])
                ->default('active');                     // สถานะของทรัพย์สิน

            $table->timestamps();

            // ====== Indexes ======
            $table->index(['type']);
            $table->index(['category']);
            $table->index(['location']);
            $table->index(['department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
