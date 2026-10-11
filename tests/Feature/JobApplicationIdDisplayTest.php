<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationIdDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_and_admins_can_see_the_application_id(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $employer = User::factory()->create(['role' => 'employer']);
        $job = Job::factory()->create([
            'user_id' => $employer->id,
            'category_id' => Category::factory(),
            'job_type_id' => JobType::factory(),
        ]);
        $application = JobApplication::create([
            'job_id' => $job->id,
            'user_id' => $student->id,
            'employer_id' => $employer->id,
        ]);

        $this->actingAs($student)
            ->get(route('account.myJobApplications'))
            ->assertOk()
            ->assertSee('Application ID')
            ->assertSee((string) $application->id);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.jobApplications'))
            ->assertOk()
            ->assertSee('Application ID')
            ->assertSee((string) $application->id);
    }

    public function test_student_applications_can_be_sorted_by_job_title(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $employer = User::factory()->create(['role' => 'employer']);
        foreach (['Zulu Opening', 'Alpha Opening'] as $title) {
            $job = Job::factory()->create([
                'user_id' => $employer->id,
                'title' => $title,
                'category_id' => Category::factory(),
                'job_type_id' => JobType::factory(),
            ]);
            JobApplication::create([
                'job_id' => $job->id,
                'user_id' => $student->id,
                'employer_id' => $employer->id,
            ]);
        }

        $this->actingAs($student)
            ->get(route('account.myJobApplications', ['sort' => 'title', 'direction' => 'asc']))
            ->assertOk()
            ->assertSeeInOrder(['Alpha Opening', 'Zulu Opening'])
            ->assertSee('aria-label="Sort by title"', false);

        $this->get(route('account.myJobApplications', ['sort' => 'title', 'direction' => 'desc']))
            ->assertOk()
            ->assertSeeInOrder(['Zulu Opening', 'Alpha Opening']);
    }
}
