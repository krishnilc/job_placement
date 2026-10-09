<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('email')->nullable()->after('phone');
        });

        DB::table('organizations')
            ->whereNull('email')
            ->orWhere('email', '')
            ->pluck('id')
            ->each(function (int $organizationId): void {
                $email = DB::table('employer_profiles')
                    ->join('users', 'users.id', '=', 'employer_profiles.user_id')
                    ->where('employer_profiles.organization_id', $organizationId)
                    ->value('users.email');

                if ($email) {
                    DB::table('organizations')
                        ->where('id', $organizationId)
                        ->update(['email' => $email]);
                }
            });

        $missingOrganizationIds = DB::table('organizations')
            ->whereNull('email')
            ->orWhere('email', '')
            ->pluck('id')
            ->all();

        if ($missingOrganizationIds !== []) {
            throw new RuntimeException(
                'Cannot enforce organizations.email as required. Missing email for organization IDs: '
                .implode(', ', $missingOrganizationIds)
            );
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
