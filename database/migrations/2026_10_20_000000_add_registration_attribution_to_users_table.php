<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_source')->nullable();
            $table->foreignId('registered_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('registered_by_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['registered_by_id']);
            $table->dropColumn(['registration_source', 'registered_by_id', 'registered_by_name']);
        });
    }
};
