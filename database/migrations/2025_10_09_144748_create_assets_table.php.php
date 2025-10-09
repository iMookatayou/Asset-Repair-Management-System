<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 100)->unique();
            $table->string('name');
            $table->string('category', 100)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 100)->nullable()->unique();
            $table->string('location')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expire')->nullable();
            $table->enum('status', ['active','in_repair','disposed'])->default('active');
            $table->timestamps();

            $table->index(['category']);
            $table->index(['location']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('assets');
    }
};
