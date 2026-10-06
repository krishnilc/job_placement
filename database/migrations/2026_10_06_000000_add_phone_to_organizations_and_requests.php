<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('phone', 50)->nullable()->after('address');
        });

        Schema::table('organization_requests', function (Blueprint $table) {
            $table->string('phone', 50)->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('organization_requests', function (Blueprint $table) {
            $table->dropColumn('phone');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
