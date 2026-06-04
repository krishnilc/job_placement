<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('application_file')->nullable()->after('applied_at');
            $table->string('resume_file')->nullable()->after('application_file');
            $table->text('certificates_file')->nullable()->after('resume_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['application_file', 'resume_file', 'certificates_file']);
        });
    }
};
