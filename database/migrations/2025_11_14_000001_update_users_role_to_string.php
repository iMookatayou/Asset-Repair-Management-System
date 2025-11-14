<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Map legacy enum values to new roles before altering type
        DB::table('users')->where('role', 'admin')->update(['role' => 'supervisor']);
        DB::table('users')->where('role', 'technician')->update(['role' => 'it_support']);
    DB::table('users')->where('role', 'staff')->update(['role' => 'computer_officer']); // legacy staff -> computer_officer

        // Change enum to varchar(50) with default
        // Use raw statement to avoid doctrine/dbal dependency
        if (Schema::hasColumn('users', 'role')) {
            DB::statement("ALTER TABLE `users` MODIFY `role` VARCHAR(50) NOT NULL DEFAULT 'computer_officer'");
        }
    }

    public function down(): void
    {
        // Revert varchar back to enum with legacy values and map back
        if (Schema::hasColumn('users', 'role')) {
            // Map new roles back into closest legacy set
            DB::table('users')->where('role', 'supervisor')->update(['role' => 'admin']);
            DB::table('users')->whereIn('role', ['it_support','network','developer','computer_officer'])->update(['role' => 'technician']);

            // Restore legacy enum (only for backward rollback scenarios). 'staff' maps to 'computer_officer' in new system.
            DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','technician','staff') NOT NULL DEFAULT 'staff'");
        }
    }
};
