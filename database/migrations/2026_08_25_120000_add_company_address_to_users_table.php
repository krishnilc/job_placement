<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'company_address')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('company_address')->nullable()->after('company_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'company_address')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('company_address');
            });
        }
    }
};