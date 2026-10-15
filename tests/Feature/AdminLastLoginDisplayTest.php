<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminLastLoginDisplayTest extends TestCase
{
    use RefreshDatabase;

    public static function accessibleListings(): array
    {
        return [
            'super admin viewing students' => ['super_admin', 'student', 'admin.users.students'],
            'super admin viewing employers' => ['super_admin', 'employer', 'admin.users.employers'],
            'super admin viewing staff' => ['super_admin', 'admin', 'admin.users.admins'],
        ];
    }

    #[DataProvider('accessibleListings')]
    public function test_lists_display_last_sign_in_and_sort_in_both_directions(
        string $viewerRole,
        string $listedRole,
        string $route
    ): void {
        config(['reporting.timezone' => 'Pacific/Fiji']);
        $viewer = User::factory()->create(['role' => $viewerRole, 'status' => 'active']);
        $older = User::factory()->create([
            'role' => $listedRole,
            'last_login_at' => '2026-10-13 18:30:00',
        ]);
        $newer = User::factory()->create([
            'role' => $listedRole,
            'last_login_at' => '2026-10-14 18:30:00',
        ]);
        $neverRecorded = User::factory()->create(['role' => $listedRole]);
        $this->actingAs($viewer);

        foreach (['asc', 'desc'] as $direction) {
            $response = $this->get(route($route, [
                'sort' => 'last_login_at',
                'direction' => $direction,
            ]));

            $response->assertOk()
                ->assertSee('Last sign-in')
                ->assertSee('Oct 14, 2026 6:30 AM Pacific/Fiji')
                ->assertSee('Oct 15, 2026 6:30 AM Pacific/Fiji')
                ->assertSee('No sign-in recorded')
                ->assertSee('sort=last_login_at')
                ->assertSee('direction='.($direction === 'asc' ? 'desc' : 'asc'));
            $datedUsers = $response->viewData('users')->filter(fn (User $user) => $user->last_login_at !== null);
            $this->assertSame(
                $direction === 'asc' ? [$older->id, $newer->id] : [$newer->id, $older->id],
                $datedUsers->pluck('id')->values()->all()
            );
            $this->assertContains($neverRecorded->id, $response->viewData('users')->pluck('id')->all());
        }

        $this->assertSame('2026-10-14 18:30:00', $newer->fresh()->last_login_at->format('Y-m-d H:i:s'));
    }

    public function test_display_uses_the_configured_timezone(): void
    {
        config(['reporting.timezone' => 'UTC']);
        $viewer = User::factory()->create(['role' => 'super_admin']);
        User::factory()->create([
            'role' => 'student',
            'last_login_at' => '2026-10-14 18:30:00',
        ]);

        $this->actingAs($viewer)->get(route('admin.users.students'))
            ->assertOk()
            ->assertSee('Oct 14, 2026 6:30 PM UTC');
    }

    public function test_admin_and_management_still_cannot_view_the_staff_listing(): void
    {
        foreach (['admin', 'management'] as $role) {
            $viewer = User::factory()->create(['role' => $role, 'status' => 'active']);
            $response = $this->actingAs($viewer)->get(route('admin.users.admins'));
            if ($role === 'management') {
                $response->assertForbidden();
            } else {
                $response->assertRedirect(route('admin.dashboard'))
                    ->assertSessionHas('error', 'You do not have permission to access this page.');
            }
        }
    }

    public function test_empty_student_and_employer_lists_span_all_columns(): void
    {
        foreach (['admin', 'super_admin', 'management'] as $role) {
            $viewer = User::factory()->create(['role' => $role, 'status' => 'active']);
            $this->actingAs($viewer);

            $this->get(route('admin.users.students'))
                ->assertOk()
                ->assertSee('colspan="'.($role === 'super_admin' ? 10 : 9).'"', false)
                ->assertSee('No students found.');
            $this->get(route('admin.users.employers'))
                ->assertOk()
                ->assertSee('colspan="'.($role === 'super_admin' ? 9 : 8).'"', false)
                ->assertSee('No employers found.');
        }
    }

    public function test_admin_and_management_cannot_see_or_sort_by_last_sign_in(): void
    {
        config(['reporting.timezone' => 'UTC']);
        foreach (['student', 'employer'] as $listedRole) {
            $older = User::factory()->create([
                'role' => $listedRole,
                'created_at' => '2026-10-01 00:00:00',
                'last_login_at' => '2026-10-14 18:30:00',
            ]);
            $newer = User::factory()->create([
                'role' => $listedRole,
                'created_at' => '2026-10-02 00:00:00',
                'last_login_at' => '2026-10-13 18:30:00',
            ]);
            User::factory()->create(['role' => $listedRole]);

            foreach (['admin', 'management'] as $viewerRole) {
                $viewer = User::factory()->create(['role' => $viewerRole, 'status' => 'active']);
                $this->actingAs($viewer);
                foreach (['asc', 'desc'] as $direction) {
                    $response = $this->get(route('admin.users.'.$listedRole.'s', [
                        'sort' => 'last_login_at',
                        'direction' => $direction,
                    ]));
                    $response->assertOk()
                        ->assertDontSee('Last sign-in')
                        ->assertDontSee('Oct 14, 2026 6:30 PM UTC')
                        ->assertDontSee('Oct 13, 2026 6:30 PM UTC')
                        ->assertDontSee('No sign-in recorded');
                    $ids = $response->viewData('users')->pluck('id')->all();
                    $this->assertTrue($direction === 'asc'
                        ? array_search($older->id, $ids) < array_search($newer->id, $ids)
                        : array_search($older->id, $ids) > array_search($newer->id, $ids));
                }
            }
        }
    }
}
