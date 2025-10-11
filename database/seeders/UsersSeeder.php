<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // แอดมินตัวอย่าง (ล็อกอินทดสอบ)
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'System Admin', 'password' => Hash::make('password')]
        );

        // ผู้ใช้ทั่วไป / ช่างซ่อม อย่างละ 5 คน
        User::factory()->count(5)->create(); // reporters
        User::factory()->count(5)->create(); // technicians
    }
}
