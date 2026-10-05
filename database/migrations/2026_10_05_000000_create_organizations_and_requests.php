<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_key', 64)->unique();
            $table->string('address', 1000)->nullable();
            $table->text('postal_address')->nullable();
            $table->string('website_url')->nullable();
            $table->text('description')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->timestamps();
        });
        Schema::table('employer_profiles', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->constrained()->restrictOnDelete();
        });
        Schema::table('jobs', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->constrained()->restrictOnDelete();
        });
        Schema::create('organization_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('address', 1000);
            $table->string('website_url')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('pending')->index();
            $table->foreignId('organization_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });

        // Preserve legacy profile values for auditing conflicts and rolling back.
        DB::table('employer_profiles')->orderBy('id')->chunkById(100, function ($profiles) {
            foreach ($profiles as $profile) {
                $name = trim(preg_replace('/\s+/u', ' ', $profile->company_name ?? ''));
                if ($name === '') {
                    continue;
                }
                $key = hash('sha256', mb_strtolower($name));
                $organization = DB::table('organizations')->where('name_key', $key)->first();
                $fields = [
                    'address' => $profile->company_address,
                    'postal_address' => $profile->postal_address,
                    'website_url' => $profile->website_url,
                    'description' => $profile->company_description,
                ];
                if (! $organization) {
                    $organizationId = DB::table('organizations')->insertGetId([
                        'name' => $name, 'name_key' => $key, ...$fields,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                } else {
                    $organizationId = $organization->id;
                    $missing = array_filter($fields, fn ($value, $field) => ! $organization->{$field} && $value, ARRAY_FILTER_USE_BOTH);
                    if ($missing !== []) {
                        DB::table('organizations')->where('id', $organizationId)->update($missing);
                    }
                }
                DB::table('employer_profiles')->where('id', $profile->id)->update(['organization_id' => $organizationId]);
                DB::table('jobs')->where('user_id', $profile->user_id)->update(['organization_id' => $organizationId]);
            }
        });
    }

    public function down(): void
    {
        DB::table('employer_profiles')->whereNotNull('organization_id')->orderBy('id')->chunkById(100, function ($profiles) {
            foreach ($profiles as $profile) {
                $organization = DB::table('organizations')->find($profile->organization_id);
                DB::table('employer_profiles')->where('id', $profile->id)->update([
                    'company_name' => $organization->name,
                    'company_address' => $organization->address,
                    'postal_address' => $organization->postal_address,
                    'website_url' => $organization->website_url,
                    'company_description' => $organization->description,
                ]);
            }
        });
        Schema::dropIfExists('organization_requests');
        Schema::table('jobs', fn (Blueprint $table) => $table->dropConstrainedForeignId('organization_id'));
        Schema::table('employer_profiles', fn (Blueprint $table) => $table->dropConstrainedForeignId('organization_id'));
        Schema::dropIfExists('organizations');
    }
};
