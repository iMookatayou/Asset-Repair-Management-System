<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class MockUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [ 'name' => 'ผู้ดูแล ระบบ', 'email' => 'admin.simple@example.com', 'role' => User::ROLE_ADMIN, 'password' => '12345678' ],
            [ 'name' => 'หัวหน้า ทดสอบ', 'email' => 'supervisor@example.com', 'role' => User::ROLE_SUPERVISOR, 'password' => '12345678' ],
            [ 'name' => 'ช่าง ไอที 1', 'email' => 'it1@example.com', 'role' => User::ROLE_IT_SUPPORT, 'password' => '12345678' ],
            [ 'name' => 'ช่าง ไอที 2', 'email' => 'it2@example.com', 'role' => User::ROLE_IT_SUPPORT, 'password' => '12345678' ],
            [ 'name' => 'ช่าง เน็ตเวิร์ค 1', 'email' => 'net1@example.com', 'role' => User::ROLE_NETWORK, 'password' => '12345678' ],
            [ 'name' => 'ช่าง นักพัฒนา 1', 'email' => 'dev1@example.com', 'role' => User::ROLE_DEVELOPER, 'password' => '12345678' ],
            [ 'name' => 'บุคลากร ตัวอย่าง', 'email' => 'member1@example.com', 'role' => User::ROLE_COMPUTER_OFFICER, 'password' => '12345678' ],
        ];

        foreach ($users as $u) {
            if (!User::where('email', $u['email'])->exists()) {
                User::create([
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'role' => $u['role'],
                    'password' => Hash::make($u['password']),
                    'email_verified_at' => now(),
                ]);
            }
        }
    }
}
