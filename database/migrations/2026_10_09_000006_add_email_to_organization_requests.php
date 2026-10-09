<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_requests', function (Blueprint $table) {
            $table->string('email')->nullable()->after('phone');
        });

        DB::table('organization_requests')
            ->whereNull('email')
            ->pluck('id')
            ->each(function (int $requestId): void {
                $email = DB::table('organization_requests')
                    ->join('users', 'users.id', '=', 'organization_requests.user_id')
                    ->where('organization_requests.id', $requestId)
                    ->value('users.email');

                if ($email) {
                    DB::table('organization_requests')
                        ->where('id', $requestId)
                        ->update(['email' => $email]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('organization_requests', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
