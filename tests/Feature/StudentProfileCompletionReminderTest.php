<?php

namespace Tests\Feature;

use App\Models\College;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentProfileCompletionReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_incomplete_student_sees_profile_completion_reminder(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'designation' => 'Full-time Student',
        ]);

        $this->actingAs($student)
            ->get(route('account.editProfile'))
            ->assertOk()
            ->assertSee('Your student profile is incomplete.')
            ->assertSee(route('account.editProfile'));
    }

    public function test_incomplete_alumni_sees_profile_completion_reminder(): void
    {
        $alumni = User::factory()->create([
            'role' => 'student',
            'designation' => 'Alumni',
        ]);

        $this->actingAs($alumni)
            ->get(route('account.editProfile'))
            ->assertOk()
            ->assertSee('Your alumni profile is incomplete.');
    }

    public function test_reminder_disappears_after_profile_is_complete(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'designation' => 'Full-time Student',
        ]);
        $college = College::create([
            'name' => 'College of Engineering and Science',
            'code' => 'CEST',
            'status' => 1,
        ]);

        StudentProfile::create([
            'user_id' => $student->id,
            'date_of_birth' => '2001-05-10',
            'gender' => 'Female',
            'marital_status' => 'Single',
            'residential_address' => '12 Queen Street, Suva',
            'city' => 'Suva',
            'country' => 'Fiji',
            'high_school' => 'Suva Grammar School',
            'high_school_graduation_year' => '2019',
            'college_id' => $college->id,
            'degree' => 'Bachelor of Information Technology',
            'major' => 'Software Engineering',
            'graduation_year' => '2027',
            'skills' => 'PHP, Laravel',
            'bio' => 'Motivated student developer.',
            'availability' => 'Available for internships',
        ]);

        $this->actingAs($student)
            ->get(route('account.editProfile'))
            ->assertOk()
            ->assertDontSee('profile is incomplete.');
    }

    public function test_student_without_profile_picture_sees_persistent_warning(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'designation' => 'Full-time Student',
            'image' => null,
        ]);

        $this->actingAs($student)
            ->get(route('account.editProfile'))
            ->assertOk()
            ->assertSee('Your profile picture has not been uploaded.')
            ->assertSee('data-bs-target="#exampleModal"', false);
    }

    public function test_profile_picture_warning_disappears_after_upload(): void
    {
        $alumni = User::factory()->create([
            'role' => 'student',
            'designation' => 'Alumni',
            'image' => 'alumni-profile.jpg',
        ]);

        $this->actingAs($alumni)
            ->get(route('account.editProfile'))
            ->assertOk()
            ->assertDontSee('Your profile picture has not been uploaded.');
    }
}
