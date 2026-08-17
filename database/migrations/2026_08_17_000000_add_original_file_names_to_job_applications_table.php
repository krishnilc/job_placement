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
            $table->string('application_file_name')->nullable()->after('application_file');
            $table->string('resume_file_name')->nullable()->after('resume_file');
            $table->text('certificates_file_names')->nullable()->after('certificates_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['application_file_name', 'resume_file_name', 'certificates_file_names']);
        });
    }
};
