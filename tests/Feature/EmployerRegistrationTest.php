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

    public function test_student_registration_rejects_duplicate_student_id(): void
    {
        $this->withoutMiddleware();

        User::factory()->create([
            'role' => 'student',
            'student_id' => 'ABC123',
        ]);

        $response = $this->post('/account/process-registration', [
            'name' => 'Student Two',
            'email' => 'student2@example.com',
            'password' => 'secret123',
            'confirm_password' => 'secret123',
            'role' => 'student',
            'student_id' => 'ABC123',
        ]);

        $response->assertJson(['status' => false]);
        $response->assertJsonPath('errors.student_id.0', 'The University Student ID has already been taken. Please enter a unique one.');
    }
}
