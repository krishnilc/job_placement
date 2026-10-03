<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columns moved from users to student_profiles.
     * (student_id, residential & postal address are student-only.)
     */
    private array $studentColumns = [
        'student_id',
        'date_of_birth',
        'gender',
        'residential_address',
        'postal_address',
        'city',
        'country',
        'high_school',
        'high_school_graduation_year',
        'college_id',
        'degree',
        'major',
        'graduation_year',
        'skills',
        'bio',
        'linkedin_url',
        'facebook_url',
        'availability',
    ];

    /**
     * Columns moved from users to employer_profiles.
     * (company_name, residential & postal address are employer-only.)
     */
    private array $employerColumns = [
        'company_name',
        'company_address',
        'website_url',
        'company_description',
        'residential_address',
        'postal_address',
    ];

    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('student_id', 9)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->text('residential_address')->nullable();
            $table->text('postal_address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('high_school')->nullable();
            $table->string('high_school_graduation_year')->nullable();
            $table->foreignId('college_id')->nullable()->constrained('colleges')->nullOnDelete();
            $table->string('degree')->nullable();
            $table->string('major')->nullable();
            $table->string('graduation_year')->nullable();
            $table->text('skills')->nullable();
            $table->text('bio')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('availability')->nullable();
            $table->timestamps();
        });

        Schema::create('employer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->string('company_address', 1000)->nullable();
            $table->string('website_url')->nullable();
            $table->text('company_description')->nullable();
            $table->text('residential_address')->nullable();
            $table->text('postal_address')->nullable();
            $table->timestamps();
        });

        // Migrate existing student data
        DB::table('users')->where('role', 'student')->orderBy('id')->chunk(100, function ($users) {
            foreach ($users as $user) {
                $data = ['user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()];
                foreach ($this->studentColumns as $column) {
                    $data[$column] = $user->$column ?? null;
                }
                DB::table('student_profiles')->insert($data);
            }
        });

        // Migrate existing employer data
        DB::table('users')->where('role', 'employer')->orderBy('id')->chunk(100, function ($users) {
            foreach ($users as $user) {
                $data = ['user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()];
                foreach ($this->employerColumns as $column) {
                    $data[$column] = $user->$column ?? null;
                }
                DB::table('employer_profiles')->insert($data);
            }
        });

        // Drop moved columns from users
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'college_id')) {
                $table->dropConstrainedForeignId('college_id');
            }
            $allMoved = array_unique(array_merge($this->studentColumns, $this->employerColumns));
            $drop = array_filter(
                $allMoved,
                fn ($col) => $col !== 'college_id' && Schema::hasColumn('users', $col)
            );
            if (!empty($drop)) {
                $table->dropColumn(array_values($drop));
            }
        });
    }

    public function down(): void
    {
        // Re-add columns to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('student_id', 9)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->text('residential_address')->nullable();
            $table->text('postal_address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('high_school')->nullable();
            $table->string('high_school_graduation_year')->nullable();
            $table->foreignId('college_id')->nullable()->constrained('colleges')->nullOnDelete();
            $table->string('degree')->nullable();
            $table->string('major')->nullable();
            $table->string('graduation_year')->nullable();
            $table->text('skills')->nullable();
            $table->text('bio')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('availability')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_address', 1000)->nullable();
            $table->string('website_url')->nullable();
            $table->text('company_description')->nullable();
        });

        // Restore data
        $restore = function (string $table, array $columns) {
            DB::table($table)->orderBy('id')->chunk(100, function ($profiles) use ($columns) {
                foreach ($profiles as $profile) {
                    $data = [];
                    foreach ($columns as $column) {
                        $data[$column] = $profile->$column ?? null;
                    }
                    DB::table('users')->where('id', $profile->user_id)->update($data);
                }
            });
        };
        $restore('student_profiles', $this->studentColumns);
        $restore('employer_profiles', $this->employerColumns);

        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('employer_profiles');
    }
};
