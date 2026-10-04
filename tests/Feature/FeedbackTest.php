<?php

namespace Tests\Feature;

use App\Models\ApplicationStatus;
use App\Models\Category;
use App\Models\Feedback;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    private function makePlacedApplication(): JobApplication
    {
        $employer = User::factory()->create(['role' => 'employer', 'status' => 'active']);
        $student = User::factory()->create(['role' => 'student', 'status' => 'active']);

        $placedStatus = ApplicationStatus::create(['name' => 'Placed', 'category' => 'Successful', 'sort_order' => 7]);

        $job = Job::factory()->create([
            'user_id' => $employer->id,
            'job_type_id' => JobType::factory(),
            'category_id' => Category::factory(),
        ]);

        $application = JobApplication::create([
            'job_id' => $job->id,
            'user_id' => $student->id,
            'employer_id' => $employer->id,
            'applied_at' => now(),
            'application_status_id' => $placedStatus->id,
        ]);

        return $application->fresh(['job', 'applicationStatus']);
    }

    public function test_employer_can_submit_feedback_for_placed_student(): void
    {
        $application = $this->makePlacedApplication();
        $employer = $application->employer;

        $response = $this->actingAs($employer)->post(route('admin.jobApplications.feedback.store', $application), [
            'rating' => 5,
            'comments' => 'Excellent performance during placement.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('feedbacks', [
            'job_application_id' => $application->id,
            'given_by' => $employer->id,
            'given_to' => $application->user_id,
            'feedback_type' => Feedback::TYPE_EMPLOYER_TO_STUDENT,
            'rating' => 5,
        ]);
    }

    public function test_student_can_submit_feedback_about_company(): void
    {
        $application = $this->makePlacedApplication();
        $student = $application->user;

        $response = $this->actingAs($student)->post(route('account.feedback.store', $application), [
            'rating' => 4,
            'comments' => 'Great company culture.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('feedbacks', [
            'job_application_id' => $application->id,
            'given_by' => $student->id,
            'given_to' => $application->employer_id,
            'feedback_type' => Feedback::TYPE_STUDENT_TO_EMPLOYER,
            'rating' => 4,
        ]);
    }

    public function test_feedback_cannot_be_submitted_twice(): void
    {
        $application = $this->makePlacedApplication();
        $employer = $application->employer;

        $this->actingAs($employer)->post(route('admin.jobApplications.feedback.store', $application), [
            'rating' => 5,
            'comments' => 'Great.',
        ]);

        $this->actingAs($employer)->post(route('admin.jobApplications.feedback.store', $application), [
            'rating' => 3,
            'comments' => 'Second attempt.',
        ]);

        $this->assertSame(1, Feedback::where('job_application_id', $application->id)
            ->where('feedback_type', Feedback::TYPE_EMPLOYER_TO_STUDENT)
            ->count());
    }

    public function test_feedback_cannot_be_submitted_before_placement(): void
    {
        $employer = User::factory()->create(['role' => 'employer', 'status' => 'active']);
        $student = User::factory()->create(['role' => 'student', 'status' => 'active']);
        $submittedStatus = ApplicationStatus::create(['name' => 'Submitted', 'category' => 'Active', 'sort_order' => 1]);
        $job = Job::factory()->create([
            'user_id' => $employer->id,
            'job_type_id' => JobType::factory(),
            'category_id' => Category::factory(),
        ]);

        $application = JobApplication::create([
            'job_id' => $job->id,
            'user_id' => $student->id,
            'employer_id' => $employer->id,
            'applied_at' => now(),
            'application_status_id' => $submittedStatus->id,
        ]);

        $this->actingAs($employer)->post(route('admin.jobApplications.feedback.store', $application), [
            'rating' => 5,
        ]);

        $this->assertDatabaseCount('feedbacks', 0);
    }

    public function test_only_admin_and_super_admin_can_view_feedback_list(): void
    {
        $application = $this->makePlacedApplication();
        Feedback::create([
            'job_application_id' => $application->id,
            'given_by' => $application->employer_id,
            'given_to' => $application->user_id,
            'feedback_type' => Feedback::TYPE_EMPLOYER_TO_STUDENT,
            'rating' => 5,
            'comments' => 'Great work.',
        ]);

        // Employer and student are blocked from the admin-only feedback listing.
        $this->actingAs($application->employer)->get(route('admin.feedback'))->assertRedirect();
        $this->actingAs($application->user)->get(route('admin.feedback'))->assertRedirect(route('student.dashboard'));

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin)->get(route('admin.feedback'))->assertOk()->assertSee('Great work.');

        $superAdmin = User::factory()->create(['role' => 'super_admin', 'status' => 'active']);
        $this->actingAs($superAdmin)->get(route('admin.feedback'))->assertOk()->assertSee('Great work.');
    }
}
