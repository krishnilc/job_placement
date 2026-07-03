<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, expand the enum to include both 'student' and 'user'
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'user', 'student', 'employer') NOT NULL DEFAULT 'student'");

        // Update any existing 'user' role to 'student'
        DB::statement("UPDATE users SET role = 'student' WHERE role = 'user'");

        // Then reduce the enum to only include 'student'
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'student', 'employer') NOT NULL DEFAULT 'student'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Expand the enum to include both 'student' and 'user'
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'user', 'student', 'employer') NOT NULL DEFAULT 'user'");

        // Update 'student' back to 'user'
        DB::statement("UPDATE users SET role = 'user' WHERE role = 'student'");

        // Reduce the enum back to original
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'user', 'employer') NOT NULL DEFAULT 'user'");
    }
};
