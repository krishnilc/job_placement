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

    public function test_student_can_store_extended_profile_details(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
            'designation' => 'Full-time Student',
        ]);

        $response = $this->actingAs($user)->put('/account/update-profile', [
            'name' => 'Student One',
            'email' => $user->email,
            'mobile' => '1234567',
            'designation' => 'Full-time Student',
            'date_of_birth' => '2001-05-10',
            'gender' => 'Female',
            'address' => 'Suva, Fiji',
            'city' => 'Suva',
            'country' => 'Fiji',
            'university' => 'Fiji National University',
            'degree' => 'Bachelor of Information Technology',
            'major' => 'Software Engineering',
            'graduation_year' => '2027',
            'cgpa' => '3.8',
            'skills' => 'PHP, Laravel, JavaScript',
            'bio' => 'Motivated student developer.',
            'linkedin_url' => 'https://linkedin.com/in/student-one',
            'github_url' => 'https://github.com/student-one',
            'portfolio_url' => 'https://student-one.dev',
            'availability' => 'Available for internships',
        ]);

        $response->assertJson(['status' => true]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'gender' => 'Female',
            'city' => 'Suva',
            'country' => 'Fiji',
            'university' => 'Fiji National University',
            'degree' => 'Bachelor of Information Technology',
            'major' => 'Software Engineering',
            'graduation_year' => '2027',
            'skills' => 'PHP, Laravel, JavaScript',
        ]);
    }
}
