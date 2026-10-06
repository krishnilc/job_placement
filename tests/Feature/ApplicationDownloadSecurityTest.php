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

class ApplicationDownloadSecurityTest extends TestCase
{
    use RefreshDatabase;

    private JobApplication $application;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('applications');
        Storage::fake('public');
        $student = User::factory()->create();
        $employer = User::factory()->create(['role' => 'employer']);
        $job = Job::factory()->create([
            'user_id' => $employer->id,
            'category_id' => Category::factory(),
            'job_type_id' => JobType::factory(),
        ]);
        $this->application = JobApplication::create([
            'job_id' => $job->id,
            'user_id' => $student->id,
            'employer_id' => $employer->id,
            'application_file' => 'letter.pdf',
            'resume_file' => 'legacy/resume.pdf',
            'certificates_file' => json_encode(['certificates/approved.pdf']),
        ]);
        Storage::disk('applications')->put('letter.pdf', 'Application fixture');
        Storage::disk('applications')->put('certificates/approved.pdf', 'Certificate fixture');
        Storage::disk('applications')->put('another-application.pdf', 'Unrelated fixture');
        Storage::disk('public')->put('legacy/resume.pdf', 'Legacy resume fixture');
        $this->actingAs($student);
    }

    private function downloadUrl(string $type, array $query = []): string
    {
        return route('application.download', [
            'application' => $this->application->id,
            'type' => $type,
            ...$query,
        ]);
    }

    public function test_application_participants_and_staff_can_download_saved_documents(): void
    {
        $readers = [
            $this->application->user,
            $this->application->employer,
            ...array_map(fn ($role) => User::factory()->create(['role' => $role, 'status' => 'active']), ['admin', 'super_admin', 'management']),
        ];
        foreach ($readers as $reader) {
            $this->actingAs($reader);
            $this->get($this->downloadUrl('application'))->assertOk()->assertDownload('letter.pdf');
            $this->get($this->downloadUrl('resume'))->assertOk()->assertDownload('resume.pdf');
            $this->get($this->downloadUrl('certificate', ['file' => base64_encode('certificates/approved.pdf')]))
                ->assertOk()->assertDownload('approved.pdf');
        }
    }

    public function test_unrelated_accounts_cannot_download_application_documents(): void
    {
        foreach (['student', 'employer'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get($this->downloadUrl('application'))->assertForbidden();
            $this->get($this->downloadUrl('certificate', ['file' => base64_encode('certificates/approved.pdf')]))
                ->assertForbidden();
        }
    }

    public function test_certificate_query_cannot_select_unrecorded_files_or_traverse_directories(): void
    {
        foreach ([
            'another-application.pdf', '../../../.env', '..\\..\\..\\.env',
            '/etc/passwd', 'C:\\Windows\\win.ini', "file\0.pdf",
        ] as $path) {
            $this->get($this->downloadUrl('certificate', ['file' => base64_encode($path)]))->assertNotFound();
        }
        foreach (['!invalid-base64!', '', ['not-a-string']] as $encoded) {
            $this->get($this->downloadUrl('certificate', ['file' => $encoded]))->assertNotFound();
        }
        $this->get($this->downloadUrl('certificate'))->assertNotFound();
    }

    public function test_unsafe_paths_are_rejected_even_if_saved_on_the_application(): void
    {
        foreach (['../public/legacy/resume.pdf', '..\\public\\legacy\\resume.pdf', '/absolute.pdf', 'C:\\absolute.pdf'] as $path) {
            $this->application->update([
                'application_file' => $path,
                'resume_file' => $path,
                'certificates_file' => json_encode([$path]),
            ]);
            foreach (['application', 'resume', 'certificate'] as $type) {
                $this->get($this->downloadUrl($type, ['file' => base64_encode($path)]))->assertNotFound();
            }
        }
    }

    public function test_missing_documents_and_invalid_certificate_lists_return_not_found(): void
    {
        $this->application->update(['resume_file' => 'missing.pdf', 'certificates_file' => '{invalid-json']);
        $this->get($this->downloadUrl('resume'))->assertNotFound();
        $this->get($this->downloadUrl('certificate', ['file' => base64_encode('certificates/approved.pdf')]))
            ->assertNotFound();
    }
}
