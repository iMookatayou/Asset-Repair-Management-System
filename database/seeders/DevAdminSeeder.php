<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DevAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'dev@example.com'],
            [
                'name' => 'ผู้ดูแลระบบ Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('Dev12345!'),
                'role' => \App\Models\User::ROLE_ADMIN,
                'department' => 'IT',
            ]
        );
    }
}
