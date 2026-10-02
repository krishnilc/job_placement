<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('college_id')->nullable()->after('university')->constrained('colleges')->nullOnDelete();
        });

        // Map existing "College Name (CODE)" values to college IDs via the code
        $collegeIdsByCode = DB::table('colleges')->pluck('id', 'code');

        $users = DB::table('users')
            ->whereNotNull('university')
            ->where('university', '<>', '')
            ->get(['id', 'university']);

        foreach ($users as $user) {
            if (preg_match('/\(([^)]+)\)\s*$/', $user->university, $matches) && isset($collegeIdsByCode[$matches[1]])) {
                DB::table('users')->where('id', $user->id)->update(['college_id' => $collegeIdsByCode[$matches[1]]]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('university');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('university')->nullable()->after('college_id');
        });

        $colleges = DB::table('colleges')->get(['id', 'name', 'code']);
        foreach ($colleges as $college) {
            DB::table('users')
                ->where('college_id', $college->id)
                ->update(['university' => $college->name . ' (' . $college->code . ')']);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('college_id');
        });
    }
};
