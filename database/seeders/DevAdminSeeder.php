<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevAdminSeeder extends Seeder
{
    public function run(): void
    {
        // กันไว้ไม่ให้ไปรันบน production
        if (app()->environment('production')) {
            $this->command?->warn('DevAdminSeeder: skipped on production environment.');
            return;
        }

        // ===== ข้อมูล Dev Admin จาก .env (ตั้งค่าได้เอง) =====
        $citizenId = env('DEV_ADMIN_CITIZEN_ID', '1234567890123');   // <— ใช้ citizen_id
        $email     = env('DEV_ADMIN_EMAIL', 'dev@example.com');       // optional
        $name      = env('DEV_ADMIN_NAME', 'ผู้ดูแลระบบ Admin');
        $password  = env('DEV_ADMIN_PASSWORD', 'Dev12345!');

        // ===== สร้างหรืออัปเดต Dev Admin =====
        User::updateOrCreate(
            ['citizen_id' => $citizenId],
            [
                'name'              => $name,
                'citizen_id'        => $citizenId,
                'email'             => $email,
                'email_verified_at' => now(),
                'password'          => Hash::make($password),
                'role'              => User::ROLE_ADMIN,
                'department'        => 'IT',
            ],
        );

        $this->command?->info("Dev admin user seeded: citizen_id={$citizenId}, email={$email}");
    }
}
