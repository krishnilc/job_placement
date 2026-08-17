<?php

namespace Tests\Unit;

use App\Models\JobApplication;
use Tests\TestCase;

class JobApplicationFileNamesTest extends TestCase
{
    public function test_it_uses_original_uploaded_file_names_for_single_files(): void
    {
        $application = new JobApplication([
            'application_file' => 'application_123.pdf',
            'application_file_name' => 'cover-letter.pdf',
            'resume_file' => 'resume_456.pdf',
            'resume_file_name' => 'my-resume.pdf',
        ]);

        $this->assertSame('cover-letter.pdf', $application->application_file_label);
        $this->assertSame('my-resume.pdf', $application->resume_file_label);
    }

    public function test_it_uses_original_uploaded_file_names_for_certificate_arrays(): void
    {
        $application = new JobApplication([
            'certificates_file' => json_encode([
                'certificates/certificate_1.pdf',
                'certificates/certificate_2.pdf',
            ]),
            'certificates_file_names' => json_encode([
                'degree.pdf',
                'transcript.pdf',
            ]),
        ]);

        $this->assertSame(['degree.pdf', 'transcript.pdf'], $application->certificate_file_labels);
    }
}
