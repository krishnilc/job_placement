<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employer_can_register(): void
    {
        $this->withoutMiddleware();

        $response = $this->post('/account/process-registration', [
            'name' => 'Employer One',
            'email' => 'employer@example.com',
            'password' => 'secret123',
            'confirm_password' => 'secret123',
            'role' => 'employer',
        ]);

        $response->assertJson(['status' => true]);
        $this->assertDatabaseHas('users', [
            'email' => 'employer@example.com',
            'role' => 'employer',
        ]);
    }
}
