<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'super_admin', 'management', 'student', 'employer') NOT NULL DEFAULT 'student'");
    }

    public function down(): void
    {
        if (DB::table('users')->where('role', 'management')->exists()) {
            throw new RuntimeException('Reassign or remove management accounts before rolling back the management role.');
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'super_admin', 'student', 'employer') NOT NULL DEFAULT 'student'");
    }
};
