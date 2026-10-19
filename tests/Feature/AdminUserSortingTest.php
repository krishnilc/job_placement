<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_can_be_sorted_by_name_descending(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Alice Example',
            'email' => 'alice@example.com',
            'role' => 'student',
        ]);
        User::factory()->create([
            'name' => 'Bob Example',
            'email' => 'bob@example.com',
            'role' => 'student',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users/students?sort=name&direction=desc');

        $response->assertOk();

        $users = $response->viewData('users');
        $this->assertSame(['Bob Example', 'Alice Example'], $users->pluck('name')->all());
    }

    public function test_staff_listing_shows_account_status_and_can_be_sorted(): void
    {
        $viewer = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
        ]);
        $activeAdmin = User::factory()->create([
            'name' => 'Active Admin',
            'role' => 'admin',
            'status' => 'active',
        ]);
        $blockedAdmin = User::factory()->create([
            'name' => 'Blocked Admin',
            'role' => 'admin',
            'status' => 'blocked',
        ]);
        $pendingAdmin = User::factory()->create([
            'name' => 'Pending Admin',
            'role' => 'admin',
            'status' => 'pending',
        ]);

        foreach (['asc', 'desc'] as $direction) {
            $response = $this->actingAs($viewer)->get(route('admin.users.admins', [
                'sort' => 'status',
                'direction' => $direction,
            ]));

            $response->assertOk()
                ->assertSee('Account status')
                ->assertSee('>Active</span>', false)
                ->assertSee('>Blocked</span>', false)
                ->assertSee('>Pending</span>', false);

            $this->assertSame(
                $direction === 'asc'
                    ? [$activeAdmin->id, $blockedAdmin->id, $pendingAdmin->id]
                    : [$pendingAdmin->id, $blockedAdmin->id, $activeAdmin->id],
                $response->viewData('users')
                    ->whereIn('id', [$activeAdmin->id, $blockedAdmin->id, $pendingAdmin->id])
                    ->pluck('id')->all()
            );
        }
    }

    public function test_staff_listing_can_search_details_and_preserve_search_when_sorting(): void
    {
        $viewer = User::factory()->create(['role' => 'super_admin']);
        $matchingAdmin = User::factory()->create([
            'name' => 'Searchable Admin',
            'email' => 'searchable@example.com',
            'mobile' => '1234567',
            'role' => 'admin',
            'status' => 'active',
        ]);
        User::factory()->create([
            'name' => 'Another Admin',
            'email' => 'another@example.com',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($viewer)->get(route('admin.users.admins', [
            'search' => 'Searchable',
            'sort' => 'name',
            'direction' => 'desc',
        ]));

        $response->assertOk()
            ->assertSee('Searchable')
            ->assertSee('Search')
            ->assertSee('search=Searchable');
        $this->assertSame([$matchingAdmin->id], $response->viewData('users')->pluck('id')->all());
    }
}
