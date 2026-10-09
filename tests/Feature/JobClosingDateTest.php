<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class JobClosingDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_creation_persists_closing_date(): void
    {
        Mail::fake();
        $organization = Organization::create(['name' => 'Acme Tech', 'phone' => '1111111', 'email' => 'acme@example.com']);
        $user = User::factory()->create(['role' => 'employer']);
        $user->employerProfile()->create(['organization_id' => $organization->id]);
        $category = Category::factory()->create();
        $jobType = JobType::factory()->create();

        $response = $this->actingAs($user)->postJson(route('account.saveJob'), [
            'title' => 'Senior Laravel Developer',
            'category' => $category->id,
            'job_type' => $jobType->id,
            'vacancy' => 2,
            'location' => 'Remote',
            'description' => 'Build great products.',
            'company_name' => 'Acme Tech',
            'closing_date' => '2026-09-15',
            'experience' => '5',
        ])->assertOk()->assertJson(['status' => true, 'notification_sent' => true]);

        $job = Job::latest()->first();

        $this->assertNotNull($job);
        $this->assertSame('2026-09-15', $job->closing_date);
        Mail::assertSentCount(1);
    }

    public function test_employer_job_requires_admin_approval_before_public_visibility(): void
    {
        Mail::fake();
        $organization = Organization::create(['name' => 'BrightWork Ltd', 'phone' => '2222222', 'email' => 'brightwork@example.com']);
        $user = User::factory()->create(['role' => 'employer']);
        $user->employerProfile()->create(['organization_id' => $organization->id]);
        $category = Category::factory()->create();
        $jobType = JobType::factory()->create();

        $response = $this->actingAs($user)->postJson(route('account.saveJob'), [
            'title' => 'Junior PHP Developer',
            'category' => $category->id,
            'job_type' => $jobType->id,
            'vacancy' => 1,
            'location' => 'Remote',
            'description' => 'Build APIs and features.',
            'experience' => '2',
        ])->assertOk()->assertJson(['status' => true, 'notification_sent' => true]);

        $job = Job::latest()->first();

        $this->assertNotNull($job);
        $this->assertSame(0, $job->status);
        $this->assertSame(0, Job::where('status', 1)->count());
        Mail::assertSentCount(1);
    }
}
