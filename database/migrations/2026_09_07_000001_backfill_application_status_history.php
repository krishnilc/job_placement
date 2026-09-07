<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $submittedStatusId = DB::table('application_statuses')
            ->where('name', 'Submitted')
            ->value('id');

        if (!$submittedStatusId) {
            return;
        }

        DB::table('job_applications')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('application_status_history')
                    ->whereColumn('application_status_history.job_application_id', 'job_applications.id');
            })
            ->orderBy('id')
            ->chunkById(100, function ($applications) use ($submittedStatusId) {
                foreach ($applications as $application) {
                    DB::table('application_status_history')->insert([
                        'job_application_id' => $application->id,
                        'application_status_id' => $application->application_status_id ?: $submittedStatusId,
                        'changed_by' => $application->user_id,
                        'created_at' => $application->created_at ?? now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Historical rows cannot be distinguished from rows created after this migration.
    }
};