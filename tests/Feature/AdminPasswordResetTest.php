<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reset_student_and_employer_passwords(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $employer = User::factory()->create(['role' => 'employer']);

        foreach ([$student, $employer] as $target) {
            $this->actingAs($admin)
                ->postJson(route('admin.users.resetPassword', $target), [
                    'password' => 'new-secret',
                    'password_confirmation' => 'new-secret',
                ])
                ->assertOk()
                ->assertJson(['status' => true]);

            $this->assertTrue(Hash::check('new-secret', $target->fresh()->password));
        }
    }

    public function test_admin_cannot_reset_administrative_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)
            ->postJson(route('admin.users.resetPassword', $target), [
                'password' => 'new-secret',
                'password_confirmation' => 'new-secret',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_reset_any_user_password(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        foreach (['student', 'employer', 'admin', 'super_admin', 'management'] as $role) {
            $target = User::factory()->create(['role' => $role]);

            $this->actingAs($superAdmin)
                ->postJson(route('admin.users.resetPassword', $target), [
                    'password' => 'new-secret',
                    'password_confirmation' => 'new-secret',
                ])
                ->assertOk()
                ->assertJson(['status' => true]);

            $this->assertTrue(Hash::check('new-secret', $target->fresh()->password));
        }
    }

    public function test_password_reset_validates_confirmation_and_length(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($admin)
            ->postJson(route('admin.users.resetPassword', $student), [
                'password' => '1234',
                'password_confirmation' => 'different',
            ])
            ->assertOk()
            ->assertJsonValidationErrors(['password', 'password_confirmation']);
    }
}
