<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            // Default list sort + status filtering
            $table->index('applied_at', 'job_applications_applied_at_index');
            // Composite for common filter+sort pattern
            $table->index(['application_status_id', 'applied_at'], 'job_applications_status_applied_index');
        });

        Schema::table('application_status_history', function (Blueprint $table) {
            // Speeds up latestOfMany() and per-application history ordering
            $table->index(['job_application_id', 'created_at'], 'ash_app_created_index');
        });

        Schema::table('jobs', function (Blueprint $table) {
            // Search + sort on title / company_name
            $table->index('title', 'jobs_title_index');
            $table->index('company_name', 'jobs_company_name_index');
        });

        Schema::table('users', function (Blueprint $table) {
            // Applicant name search/sort
            $table->index('name', 'users_name_index');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropIndex('job_applications_applied_at_index');
            $table->dropIndex('job_applications_status_applied_index');
        });

        Schema::table('application_status_history', function (Blueprint $table) {
            $table->dropIndex('ash_app_created_index');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex('jobs_title_index');
            $table->dropIndex('jobs_company_name_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_name_index');
        });
    }
};
