<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('organizations')
            ->whereNull('phone')
            ->orWhere('phone', '')
            ->pluck('id')
            ->each(function (int $organizationId): void {
                $phone = DB::table('employer_profiles')
                    ->join('users', 'users.id', '=', 'employer_profiles.user_id')
                    ->where('employer_profiles.organization_id', $organizationId)
                    ->value('users.mobile');

                if ($phone) {
                    DB::table('organizations')
                        ->where('id', $organizationId)
                        ->update(['phone' => $phone]);
                }
            });

        $missingOrganizationIds = DB::table('organizations')
            ->whereNull('phone')
            ->orWhere('phone', '')
            ->pluck('id')
            ->all();

        if ($missingOrganizationIds !== []) {
            throw new RuntimeException(
                'Cannot enforce organizations.phone as required. Missing phone for organization IDs: '
                .implode(', ', $missingOrganizationIds)
            );
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->string('phone', 50)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('phone', 50)->nullable()->change();
        });
    }
};
