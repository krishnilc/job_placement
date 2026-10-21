<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobApplicationDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('applications');
        Storage::fake('public');
    }

    public function test_student_deletion_removes_all_application_files(): void
    {
        $student = User::factory()->create();
        $application = $this->makeJobApplication($student);
        $this->createFiles();

        $this->actingAs($student)
            ->postJson(route('account.removeJobApplication'), ['id' => $application->id])
            ->assertOk()
            ->assertJson(['status' => true]);

        $this->assertDatabaseMissing('job_applications', ['id' => $application->id]);
        $this->assertFilesMissing();
    }

    public function test_admin_deletion_removes_all_application_files(): void
    {
        $application = $this->makeJobApplication(User::factory()->create());
        $this->createFiles();

        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->deleteJson(route('admin.jobApplications.destroy'), ['id' => $application->id])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('job_applications', ['id' => $application->id]);
        $this->assertFilesMissing();
    }

    public function test_student_cannot_delete_another_students_application_or_files(): void
    {
        $application = $this->makeJobApplication(User::factory()->create());
        $this->createFiles();

        $this->actingAs(User::factory()->create())
            ->postJson(route('account.removeJobApplication'), ['id' => $application->id])
            ->assertOk()
            ->assertJson(['status' => false]);

        $this->assertDatabaseHas('job_applications', ['id' => $application->id]);
        Storage::disk('applications')->assertExists('application.pdf');
        Storage::disk('applications')->assertExists('certificates/certificate.pdf');
        Storage::disk('public')->assertExists('legacy-resume.pdf');
    }

    private function makeJobApplication(User $student): JobApplication
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $job = Job::factory()->create([
            'user_id' => $employer->id,
            'category_id' => Category::factory(),
            'job_type_id' => JobType::factory(),
        ]);

        return JobApplication::create([
            'job_id' => $job->id,
            'user_id' => $student->id,
            'employer_id' => $employer->id,
            'application_file' => 'application.pdf',
            'resume_file' => 'legacy-resume.pdf',
            'certificates_file' => json_encode(['certificates/certificate.pdf']),
        ]);
    }

    private function createFiles(): void
    {
        Storage::disk('applications')->put('application.pdf', 'Application');
        Storage::disk('applications')->put('certificates/certificate.pdf', 'Certificate');
        Storage::disk('public')->put('legacy-resume.pdf', 'Legacy resume');
        Storage::disk('applications')->put('unrelated.pdf', 'Unrelated');
    }

    private function assertFilesMissing(): void
    {
        Storage::disk('applications')->assertMissing('application.pdf');
        Storage::disk('applications')->assertMissing('certificates/certificate.pdf');
        Storage::disk('public')->assertMissing('legacy-resume.pdf');
        Storage::disk('applications')->assertExists('unrelated.pdf');
    }
}
