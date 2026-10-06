<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employer_can_register(): void
    {
        $this->withoutMiddleware();
        $organization = Organization::create(['name' => 'Example Company', 'address' => 'Suva']);

        $response = $this->post('/account/process-registration', [
            'name' => 'Employer One',
            'email' => 'employer@example.com',
            'mobile' => '1234567',
            'password' => 'secret123',
            'confirm_password' => 'secret123',
            'role' => 'employer',
            'designation' => 'HR Manager',
            'organization_mode' => 'existing',
            'organization_id' => $organization->id,
        ]);

        $response->assertJson(['status' => true]);
        $this->assertDatabaseHas('users', [
            'email' => 'employer@example.com',
            'role' => 'employer',
        ]);
        $this->assertDatabaseHas('employer_profiles', [
            'user_id' => User::where('email', 'employer@example.com')->firstOrFail()->id,
            'organization_id' => $organization->id,
        ]);
    }
}
