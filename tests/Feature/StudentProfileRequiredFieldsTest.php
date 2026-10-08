<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentProfileRequiredFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_profile_requires_all_fields_marked_mandatory(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->putJson(route('account.updateProfile'), [
            'name' => $student->name,
            'email' => $student->email,
            'mobile' => $student->mobile,
            'designation' => '',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', false)
            ->assertJsonPath('errors', fn (array $errors): bool => collect([
            'designation',
            'date_of_birth',
            'gender',
            'residential_address',
            'city',
            'country',
            'high_school',
            'high_school_graduation_year',
            'college_id',
            'degree',
            'major',
            'graduation_year',
            'skills',
            'bio',
            'availability',
            ])->every(fn (string $field): bool => isset($errors[$field])));
    }
}
