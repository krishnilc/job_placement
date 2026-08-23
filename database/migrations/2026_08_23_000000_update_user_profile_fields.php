<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'email_2')) {
                $table->string('email_2')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'mobile_2')) {
                $table->string('mobile_2')->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('users', 'residential_address')) {
                $table->text('residential_address')->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'postal_address')) {
                $table->text('postal_address')->nullable()->after('residential_address');
            }
            if (!Schema::hasColumn('users', 'high_school')) {
                $table->string('high_school')->nullable()->after('country');
            }
            if (!Schema::hasColumn('users', 'high_school_graduation_year')) {
                $table->string('high_school_graduation_year')->nullable()->after('high_school');
            }
            if (!Schema::hasColumn('users', 'facebook_url')) {
                $table->string('facebook_url')->nullable()->after('linkedin_url');
            }
            foreach (['cgpa', 'github_url', 'portfolio_url'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach ([
                'email_2',
                'mobile_2',
                'residential_address',
                'postal_address',
                'high_school',
                'high_school_graduation_year',
                'facebook_url',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};