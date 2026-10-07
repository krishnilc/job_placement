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
}
