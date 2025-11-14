<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            DevAdminSeeder::class,
            MockUsersSeeder::class,
            DemoDataSeeder::class,
            MaintenanceRequestSeeder::class,
            MaintenanceLogSeeder::class,
            AssetCategorySeeder::class,
            AssetSeeder::class,
            ChatSeeder::class,
        ]);
    }
}
