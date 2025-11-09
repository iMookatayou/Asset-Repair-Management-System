<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            $table->string('asset_code', 100)->unique();
            $table->string('name');
            $table->string('type', 100)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 100)->nullable()->unique();
            $table->string('location')->nullable();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('asset_categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->date('purchase_date')->nullable();
            $table->date('warranty_expire')->nullable();
            $table->enum('status', ['active','in_repair','disposed'])->default('active');

            $table->timestamps();
            $table->softDeletes(); // allow safe logical deletion while keeping historical links

            $table->index(['type', 'location', 'department_id', 'category_id']);
            // Speed up filters for dashboard/list by status + department
            $table->index(['status', 'department_id'], 'assets_status_department_idx');
            // ไม่สร้าง composite index asset_code+serial_number (ซ้ำกับ unique รายคอลัมน์)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
