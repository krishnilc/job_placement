<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('jobs')->where('is_approved', 0)->update(['status' => 0]);
        DB::table('jobs')->where('is_approved', 1)->where('status', 0)->update(['status' => 2]);

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->boolean('is_approved')->default(1)->after('status');
        });

        DB::table('jobs')->where('status', 0)->update(['is_approved' => 0, 'status' => 1]);
        DB::table('jobs')->where('status', 2)->update(['is_approved' => 1, 'status' => 0]);
    }
};