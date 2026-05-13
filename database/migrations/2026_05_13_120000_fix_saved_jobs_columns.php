<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('saved_jobs')) {
            return;
        }

        Schema::table('saved_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('saved_jobs', 'jobs_id')) {
                $table->dropForeign(['jobs_id']);
            }
            if (Schema::hasColumn('saved_jobs', 'users_id')) {
                $table->dropForeign(['users_id']);
            }
        });

        if (Schema::hasColumn('saved_jobs', 'jobs_id')) {
            DB::statement('ALTER TABLE `saved_jobs` CHANGE `jobs_id` `job_id` BIGINT UNSIGNED NOT NULL');
        }

        if (Schema::hasColumn('saved_jobs', 'users_id')) {
            DB::statement('ALTER TABLE `saved_jobs` CHANGE `users_id` `user_id` BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('saved_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('saved_jobs', 'job_id')) {
                $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
            }
            if (Schema::hasColumn('saved_jobs', 'user_id')) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('saved_jobs')) {
            return;
        }

        Schema::table('saved_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('saved_jobs', 'job_id')) {
                $table->dropForeign(['job_id']);
            }
            if (Schema::hasColumn('saved_jobs', 'user_id')) {
                $table->dropForeign(['user_id']);
            }
        });

        if (Schema::hasColumn('saved_jobs', 'job_id')) {
            DB::statement('ALTER TABLE `saved_jobs` CHANGE `job_id` `jobs_id` BIGINT UNSIGNED NOT NULL');
        }

        if (Schema::hasColumn('saved_jobs', 'user_id')) {
            DB::statement('ALTER TABLE `saved_jobs` CHANGE `user_id` `users_id` BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('saved_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('saved_jobs', 'jobs_id')) {
                $table->foreign('jobs_id')->references('id')->on('jobs')->onDelete('cascade');
            }
            if (Schema::hasColumn('saved_jobs', 'users_id')) {
                $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
            }
        });
    }
};
