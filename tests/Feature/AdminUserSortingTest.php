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
}
