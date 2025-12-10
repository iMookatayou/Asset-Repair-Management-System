<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // เลขบัตรประชาชน 13 หลัก (ใช้เป็นหลักในการ Login)
            $table->string('citizen_id', 13)
                  ->unique()
                  ->comment('เลขประจำตัวประชาชน 13 หลัก สำหรับใช้ล็อกอิน');

            $table->string('name');

            // email ไม่บังคับแล้ว แต่ถ้ามีก็ยังต้องไม่ซ้ำกัน
            $table->string('email')
                  ->nullable()
                  ->unique();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');

            // เก็บรหัสหน่วยงาน (code จากตาราง departments) เผื่อ filter
            $table->string('department', 100)
                  ->nullable()
                  ->index();

            // เก็บ role เป็น code (ไปแมปกับ roles.code)
            $table->string('role', 50)
                  ->default('member')
                  ->comment('Role code roles.code เช่น member, admin, supervisor, it_support, network, developer, technician')
                  ->index();

            // รูปโปรไฟล์
            $table->string('profile_photo_path', 2048)->nullable();
            $table->string('profile_photo_thumb', 2048)->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
