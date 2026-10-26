<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEditSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_update_staff_using_post_with_method_override(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $staff = User::factory()->create(['role' => 'admin']);

        $this->actingAs($superAdmin)->get(route('admin.users.edit', $staff))
            ->assertOk()
            ->assertSee('action="'.route('admin.users.update', $staff).'"', false)
            ->assertSee('name="_method" value="PUT"', false);

        $this->post(route('admin.users.update', $staff), [
            '_method' => 'PUT',
            'name' => 'Updated Staff',
            'email' => $staff->email,
            'mobile' => '1234567',
            'designation' => 'Administrator',
            'role' => 'management',
            'status' => 'active',
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson(['status' => true, 'errors' => []]);

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'name' => 'Updated Staff',
            'mobile' => '1234567',
            'designation' => 'Administrator',
            'role' => 'management',
            'status' => 'active',
        ]);
    }

    public function test_method_override_preserves_validation_and_authorization(): void
    {
        $staff = User::factory()->create(['role' => 'admin']);
        $data = [
            '_method' => 'PUT',
            'name' => '',
            'email' => 'invalid',
            'mobile' => 'invalid',
            'role' => 'invalid',
            'status' => 'invalid',
        ];
        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->post(route('admin.users.update', $staff), $data, ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['status' => false])
            ->assertJsonStructure(['errors' => ['name', 'email', 'mobile', 'role', 'status']]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('admin.users.update', $staff), $data, ['Accept' => 'application/json'])
            ->assertForbidden()
            ->assertHeader('Content-Type', 'application/json');

        $this->assertSame($staff->name, $staff->fresh()->name);
    }
}
