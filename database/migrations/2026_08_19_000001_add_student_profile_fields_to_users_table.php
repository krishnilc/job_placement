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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country')->nullable()->after('city');
            }
            if (!Schema::hasColumn('users', 'university')) {
                $table->string('university')->nullable()->after('country');
            }
            if (!Schema::hasColumn('users', 'degree')) {
                $table->string('degree')->nullable()->after('university');
            }
            if (!Schema::hasColumn('users', 'major')) {
                $table->string('major')->nullable()->after('degree');
            }
            if (!Schema::hasColumn('users', 'graduation_year')) {
                $table->string('graduation_year')->nullable()->after('major');
            }
            if (!Schema::hasColumn('users', 'cgpa')) {
                $table->decimal('cgpa', 3, 2)->nullable()->after('graduation_year');
            }
            if (!Schema::hasColumn('users', 'skills')) {
                $table->text('skills')->nullable()->after('cgpa');
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('skills');
            }
            if (!Schema::hasColumn('users', 'linkedin_url')) {
                $table->string('linkedin_url')->nullable()->after('bio');
            }
            if (!Schema::hasColumn('users', 'github_url')) {
                $table->string('github_url')->nullable()->after('linkedin_url');
            }
            if (!Schema::hasColumn('users', 'portfolio_url')) {
                $table->string('portfolio_url')->nullable()->after('github_url');
            }
            if (!Schema::hasColumn('users', 'availability')) {
                $table->string('availability')->nullable()->after('portfolio_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'date_of_birth',
                'gender',
                'address',
                'city',
                'country',
                'university',
                'degree',
                'major',
                'graduation_year',
                'cgpa',
                'skills',
                'bio',
                'linkedin_url',
                'github_url',
                'portfolio_url',
                'availability',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
