<?php

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_histories', function (Blueprint $table) {
            $table->id();
            $table->morphs('subject');
            $table->string('subject_kind');
            $table->string('subject_name');
            $table->string('event');
            $table->string('previous_status')->nullable();
            $table->string('current_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('changed_by_name')->nullable();
            $table->timestamp('created_at');
        });

        // Existing records have a known current status, not a reconstructable creation history.
        foreach (['users' => User::class, 'jobs' => Job::class] as $table => $type) {
            DB::table($table)
                ->when($table === 'users', fn ($query) => $query->whereIn('role', ['student', 'employer']))
                ->orderBy('id')
                ->chunkById(100, function ($subjects) use ($table, $type) {
                    $timestamp = now();
                    $rows = [];
                    foreach ($subjects as $subject) {
                        $rows[] = [
                            'subject_type' => $type,
                            'subject_id' => $subject->id,
                            'subject_kind' => $table === 'users' ? $subject->role : 'job',
                            'subject_name' => $table === 'users' ? $subject->name : $subject->title,
                            'event' => 'baseline',
                            'previous_status' => null,
                            'current_status' => (string) $subject->status,
                            'changed_by' => null,
                            'changed_by_name' => null,
                            'created_at' => $timestamp,
                        ];
                    }
                    DB::table('status_histories')->insert($rows);
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('status_histories');
    }
};
