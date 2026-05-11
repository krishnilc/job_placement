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
        // Schema::table('jobs', function (Blueprint $table) {
        //     $table->integer('status')->default(1)->after('company_website'); // 1 for active, 0 for inactive         
        //     $table->integer('isFeatured')->default(0)->after('status'); // 1 for active, 0 for inactive        
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('jobs', function (Blueprint $table) {
        //     $table->dropColumn('status');
        //     $table->dropColumn('isFeatured');
        // });
    }
};
