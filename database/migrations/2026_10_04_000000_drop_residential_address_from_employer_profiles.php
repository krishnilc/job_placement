<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('employer_profiles', 'residential_address')) {
                $table->dropColumn('residential_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employer_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('employer_profiles', 'residential_address')) {
                $table->text('residential_address')->nullable()->after('company_description');
            }
        });
    }
};
