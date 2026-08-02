<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_login_with_valid_credentials(): void
    {
        $this->withoutMiddleware();

        User::factory()->create([
            'email' => 'student@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'student',
        ]);

        $response = $this->post('/account/authenticate', [
            'email' => 'student@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/account/student-dashboard');
        $this->assertAuthenticatedAs(User::where('email', 'student@example.com')->first());
    }
}
