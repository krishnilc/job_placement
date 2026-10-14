<?php

namespace Tests\Feature;

use App\Mail\AdminAccountCreated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminAccountCreatedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_admin_receives_account_details_by_email_without_the_password(): void
    {
        Mail::fake();

        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)->post(route('admin.users.admins.store'), [
            'name' => 'New Admin',
            'designation' => 'Placement Officer',
            'email' => 'new.admin@example.com',
            'mobile' => '1234567',
            'role' => 'admin',
            'password' => 'secret123',
            'confirm_password' => 'secret123',
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        Mail::assertSent(AdminAccountCreated::class, function (AdminAccountCreated $mail): bool {
            return $mail->hasTo('new.admin@example.com')
                && $mail->user->name === 'New Admin'
                && $mail->user->role === 'admin'
                && !str_contains($mail->render(), 'secret123');
        });
    }

    public function test_admin_creation_requires_a_designation(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)->postJson(route('admin.users.admins.store'), [
            'name' => 'New Admin',
            'email' => 'new.admin@example.com',
            'mobile' => '1234567',
            'role' => 'admin',
            'password' => 'secret123',
            'confirm_password' => 'secret123',
        ])->assertOk()
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors' => ['designation']]);

        $this->assertDatabaseMissing('users', ['email' => 'new.admin@example.com']);
    }

    public function test_management_account_email_displays_management_role(): void
    {
        Mail::fake();

        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)->post(route('admin.users.admins.store'), [
            'name' => 'Management Account',
            'designation' => 'Director',
            'email' => 'management@example.com',
            'mobile' => '1234567',
            'role' => 'management',
            'password' => 'secret123',
            'confirm_password' => 'secret123',
        ])->assertOk()->assertJson(['status' => true]);

        Mail::assertSent(AdminAccountCreated::class, function (AdminAccountCreated $mail): bool {
            return $mail->hasTo('management@example.com')
                && str_contains($mail->render(), '<strong>Role:</strong> Management (Read-only)');
        });
    }
}
