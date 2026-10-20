<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationAttributionTest extends TestCase
{
    use RefreshDatabase;

    public static function publicRegistrations(): array
    {
        return [
            'student' => ['student', 'existing'],
            'alumni' => ['alumni', 'existing'],
            'employer with organization' => ['employer', 'existing'],
            'employer requesting organization' => ['employer', 'request'],
        ];
    }

    #[DataProvider('publicRegistrations')]
    public function test_public_registration_records_self_registration(string $role, string $organizationMode): void
    {
        Notification::fake();
        $this->travelTo(now()->startOfSecond());
        $other = User::factory()->create();
        $organization = Organization::create([
            'name' => 'Existing Organization',
            'address' => 'Suva',
            'phone' => '1234567',
            'email' => 'office@example.com',
        ]);

        $this->postJson(route('account.processRegistration'), [
            'name' => 'New Registrant',
            'email' => 'registrant@example.com',
            'mobile' => '1234567',
            'password' => 'secret123',
            'confirm_password' => 'secret123',
            'role' => $role,
            'student_id' => 'ST123456',
            'date_of_birth' => '2000-01-01',
            'graduation_year' => '2020',
            'designation' => 'HR Manager',
            'organization_mode' => $organizationMode,
            'organization_id' => $organization->id,
            'company_name' => 'Requested Organization',
            'company_address' => 'Suva',
            'company_phone' => '7654321',
            'company_email' => 'requested@example.com',
            'registration_source' => 'staff',
            'registered_by_id' => $other->id,
            'registered_by_name' => 'Forged Creator',
        ])->assertOk()->assertJsonPath('status', true);

        $user = User::where('email', 'registrant@example.com')->firstOrFail();
        $this->assertSame('self', $user->registration_source);
        $this->assertNull($user->registered_by_id);
        $this->assertNull($user->registered_by_name);
        $this->assertTrue($user->created_at->equalTo(now()));
    }

    public static function staffRegistrations(): array
    {
        return [
            'student by admin' => ['admin', 'student', 'admin.users.students.store'],
            'employer by admin' => ['admin', 'employer', 'admin.users.employers.store'],
            'admin by super admin' => ['super_admin', 'admin', 'admin.users.admins.store'],
            'super admin by super admin' => ['super_admin', 'super_admin', 'admin.users.admins.store'],
            'management by super admin' => ['super_admin', 'management', 'admin.users.admins.store'],
        ];
    }

    #[DataProvider('staffRegistrations')]
    public function test_staff_registration_records_authenticated_creator(
        string $creatorRole,
        string $role,
        string $route
    ): void {
        Mail::fake();
        $creator = User::factory()->create(['role' => $creatorRole, 'name' => 'Original Creator']);
        $other = User::factory()->create(['name' => 'Forged Creator']);
        $organization = Organization::create([
            'name' => 'Existing Organization',
            'address' => 'Suva',
            'phone' => '1234567',
            'email' => 'office@example.com',
        ]);

        $this->actingAs($creator)->postJson(route($route), [
            'name' => 'New Account',
            'email' => 'new@example.com',
            'mobile' => '1234567',
            'password' => 'secret123',
            'confirm_password' => 'secret123',
            'designation' => 'Placement Officer',
            'student_id' => 'ST123456',
            'organization_id' => $organization->id,
            'role' => $role,
            'registration_source' => 'self',
            'registered_by_id' => $other->id,
            'registered_by_name' => 'Forged Creator',
        ])->assertOk()->assertJsonPath('status', true);

        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertSame('staff', $user->registration_source);
        $this->assertSame($creator->id, $user->registered_by_id);
        $this->assertSame('Original Creator', $user->registered_by_name);
        $this->assertTrue($user->registeredBy->is($creator));

        $this->get(route('admin.users.profile', $user->id))->assertOk()
            ->assertSee('Registered By')
            ->assertSee('Original Creator');
    }

    public function test_profile_shows_self_registration_and_legacy_unknown_in_reporting_timezone(): void
    {
        config(['reporting.timezone' => 'Pacific/Fiji']);
        $viewer = User::factory()->create(['role' => 'super_admin']);

        foreach ([[null, 'Unknown'], ['self', 'Self-registered']] as [$source, $label]) {
            $user = User::factory()->create([
                'registration_source' => $source,
                'created_at' => '2026-10-19 18:30:00',
            ]);

            $this->actingAs($viewer)->get(route('admin.users.profile', $user->id))
                ->assertOk()
                ->assertSee('Registered At')
                ->assertSee('Oct 20, 2026 6:30 AM Pacific/Fiji')
                ->assertSee('Registered By')
                ->assertSee($label);
        }
    }

    public function test_registration_attribution_survives_profile_edits_creator_renaming_and_deletion(): void
    {
        $creator = User::factory()->create(['role' => 'super_admin', 'name' => 'Original Creator']);
        $user = User::factory()->make(['role' => 'admin']);
        $user->setRegistrationCreator($creator);
        $user->save();
        $registeredAt = $user->created_at;
        $viewer = User::factory()->create(['role' => 'super_admin']);
        $this->travel(1)->day();

        $this->actingAs($viewer)->putJson(route('admin.users.update', $user->id), [
            'name' => 'Updated Admin',
            'email' => $user->email,
            'mobile' => '1234567',
            'designation' => 'Officer',
            'role' => 'admin',
            'status' => 'active',
            'registration_source' => 'self',
            'registered_by_id' => $viewer->id,
            'registered_by_name' => 'Changed Creator',
        ])->assertOk()->assertJsonPath('status', true);

        $this->assertSame($creator->id, $user->fresh()->registered_by_id);
        $this->assertSame('staff', $user->fresh()->registration_source);
        $this->assertEquals($registeredAt, $user->fresh()->created_at);
        $creator->update(['name' => 'Renamed Creator']);
        $creator->delete();

        $this->assertNull($user->fresh()->registered_by_id);
        $this->assertSame('Original Creator', $user->fresh()->registered_by_name);
        $this->get(route('admin.users.profile', $user->id))
            ->assertOk()->assertSee('Original Creator')->assertDontSee('Renamed Creator');
    }
}
