<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminProfileViewFieldsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, array{0: string}>
     */
    public static function adminAccountRoleProvider(): array
    {
        return [['admin'], ['super_admin'], ['management']];
    }

    #[DataProvider('adminAccountRoleProvider')]
    public function test_super_admin_viewing_an_admin_sees_only_admin_fields(string $role): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $staff = User::factory()->create([
            'role' => $role,
            'status' => 'active',
            'designation' => 'Placement Officer',
        ]);

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.users.profile', $staff->id))
            ->assertOk();

        $response->assertSee('Administrator profile')
            ->assertSee('Account information')
            ->assertSee('Designation')
            ->assertSee('Account Status');

        foreach ([
            'Student profile',
            'Student ID:',
            'Student Status',
            'Personal details',
            'Date of Birth',
            'Marital Status',
            'Education',
            'College/Center',
            'Availability',
        ] as $studentOnlyText) {
            $response->assertDontSee($studentOnlyText);
        }
    }

    public function test_super_admin_viewing_a_student_still_sees_student_fields(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'designation' => 'Full-time Student',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('admin.users.profile', $student->id))
            ->assertOk()
            ->assertSee('Student profile')
            ->assertSee('Student Status')
            ->assertSee('Personal details')
            ->assertSee('Marital Status')
            ->assertSee('Education')
            ->assertSee('Availability')
            ->assertDontSee('Administrator profile');
    }

    public function test_super_admin_profile_routes_handle_null_account_status_without_500(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->nullable()->change();
        });

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => null,
        ]);
        $employer = User::factory()->create([
            'role' => 'employer',
            'status' => null,
        ]);

        foreach ([$admin, $employer] as $user) {
            $this->actingAs($superAdmin)
                ->get(route('admin.users.profile', $user->id))
                ->assertOk()
                ->assertSee('Account Status')
                ->assertSee('Not provided');
        }
    }

    public function test_super_admin_viewing_an_employer_still_sees_employer_fields(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $employer = User::factory()->create(['role' => 'employer']);

        $this->actingAs($superAdmin)
            ->get(route('admin.users.profile', $employer->id))
            ->assertOk()
            ->assertSee('Employer profile')
            ->assertSee('Company information')
            ->assertDontSee('Administrator profile')
            ->assertDontSee('Personal details');
    }
}
