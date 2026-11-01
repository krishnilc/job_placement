<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereIn('role', ['admin', 'super_admin', 'management'])
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')->from('status_histories')
                    ->where('subject_type', User::class)
                    ->whereColumn('subject_id', 'users.id');
            })
            ->orderBy('id')
            ->chunkById(100, function ($users) {
                $timestamp = now();
                $rows = [];
                foreach ($users as $user) {
                    $rows[] = [
                        'subject_type' => User::class,
                        'subject_id' => $user->id,
                        'subject_kind' => $user->role,
                        'subject_name' => $user->name,
                        'event' => 'baseline',
                        'previous_status' => null,
                        'current_status' => $user->status,
                        'changed_by' => null,
                        'changed_by_name' => null,
                        'created_at' => $timestamp,
                    ];
                }
                DB::table('status_histories')->insert($rows);
            });
    }

    public function down(): void
    {
        // Audit records are retained on rollback; they may have subsequent status changes.
    }
};
