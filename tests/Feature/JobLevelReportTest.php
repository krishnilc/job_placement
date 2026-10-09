<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobLevelReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_select_a_job_within_an_organization_for_its_funnel(): void
    {
        $this->seed(\Database\Seeders\ApplicationStatusSeeder::class);
        $owner = User::factory()->create(['role' => 'employer']);
        $organization = Organization::create(['name' => 'Funnel Organization', 'phone' => '1111111', 'email' => 'funnel@example.com']);
        $other = Organization::create(['name' => 'Other Organization', 'phone' => '2222222', 'email' => 'other@example.com']);
        $base = [
            'user_id' => $owner->id, 'category_id' => Category::factory()->create()->id,
            'job_type_id' => JobType::create(['name' => 'Attachment', 'status' => 1])->id,
        ];
        $first = Job::factory()->create([...$base, 'organization_id' => $organization->id, 'title' => 'Shared Title']);
        $second = Job::factory()->create([...$base, 'organization_id' => $organization->id, 'title' => 'Shared Title']);
        $empty = Job::factory()->create([...$base, 'organization_id' => $organization->id]);
        $outside = Job::factory()->create([...$base, 'organization_id' => $other->id]);
        foreach ([[$first, 7], [$second, 3], [$outside, 7]] as [$job, $status]) {
            JobApplication::create([
                'job_id' => $job->id, 'user_id' => User::factory()->create()->id,
                'employer_id' => $owner->id, 'application_status_id' => $status,
            ]);
        }
        $filters = ['report_organization' => $organization->id];
        foreach (['admin', 'super_admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get(route('admin.dashboard', $filters))->assertOk()
                ->assertViewHas('funnelJobOptions', fn ($jobs) => $jobs->count() === 3 && ! $jobs->contains('id', $outside->id))
                ->assertViewHas('funnelReports', fn ($rows) => $rows->firstWhere('name', 'Submitted')['count'] === 2)
                ->assertSee('Job title for recruitment funnel');
            $this->get(route('admin.dashboard', [...$filters, 'funnel_job' => $second->id]))->assertOk()
                ->assertViewHas('funnelReports', fn ($rows) => $rows->firstWhere('name', 'Submitted')['count'] === 1
                    && $rows->firstWhere('name', 'Placed')['count'] === 0)
                ->assertViewHas('applicationMetrics', fn ($metrics) => $metrics['total_applications'] === 3);
            $this->get(route('admin.dashboard', [...$filters, 'funnel_job' => $first->id]))->assertOk()
                ->assertViewHas('funnelReports', fn ($rows) => $rows->firstWhere('name', 'Placed')['count'] === 1);
            $this->get(route('admin.dashboard', [...$filters, 'funnel_job' => $empty->id]))->assertOk()
                ->assertViewHas('funnelReports', fn ($rows) => $rows->sum('count') === 0);
            $this->get(route('admin.dashboard', [...$filters, 'funnel_job' => $first->id, 'year' => 1900]))->assertOk()
                ->assertViewHas('funnelReports', fn ($rows) => $rows->sum('count') === 0);
            $this->getJson(route('admin.dashboard', [...$filters, 'funnel_job' => $outside->id]))
                ->assertUnprocessable()->assertJsonValidationErrors('funnel_job');
        }
        $export = [...$filters, 'funnel_job' => $second->id, 'report' => 'funnel'];
        $response = $this->get(route('admin.reports.export', [...$export, 'format' => 'excel']))->assertOk();
        $rows = array_map('str_getcsv', explode("\n", trim($response->getContent())));
        $placed = collect(array_slice($rows, 1))->first(fn ($row) => $row[0] === 'Placed');
        $this->assertSame('0', $placed[1]);
        $this->get(route('admin.reports.export', [...$export, 'format' => 'pdf']))->assertOk();
        $this->getJson(route('admin.dashboard', ['funnel_job' => 99999]))
            ->assertUnprocessable()->assertJsonValidationErrors('funnel_job');
    }

    public function test_job_report_separates_postings_includes_zero_applications_and_filters_exports(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherContact = User::factory()->create(['role' => 'employer']);
        $organization = Organization::create(['name' => 'Shared Organization', 'phone' => '1111111', 'email' => 'shared@example.com']);
        $empty = Organization::create(['name' => 'Empty Organization', 'phone' => '2222222', 'email' => 'empty@example.com']);
        $category = Category::factory()->create();
        $type = JobType::create(['name' => 'Attachment', 'status' => 1]);
        $base = ['user_id' => $admin->id, 'category_id' => $category->id, 'job_type_id' => $type->id];
        $first = Job::factory()->create([...$base, 'organization_id' => $organization->id, 'title' => 'First Posting']);
        $second = Job::factory()->create([...$base, 'user_id' => $otherContact->id, 'organization_id' => $organization->id, 'title' => 'Second Posting', 'status' => 2]);
        $legacy = Job::factory()->create([...$base, 'company_name' => 'Legacy Company', 'title' => 'Legacy Posting', 'status' => 0]);
        foreach (range(1, 2) as $index) {
            JobApplication::create(['job_id' => $first->id, 'user_id' => User::factory()->create()->id, 'employer_id' => $admin->id]);
        }
        foreach (['admin', 'super_admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->get(route('admin.dashboard', ['tab' => 'employers']))
                ->assertOk()->assertSee('Job-Level Reporting')
                ->assertViewHas('jobLevelReports', function ($rows) use ($first, $second, $legacy) {
                    $this->assertCount(3, $rows);
                    $this->assertSame(2, $rows->firstWhere('Job ID', $first->id)['Applications']);
                    $this->assertSame(0, $rows->firstWhere('Job ID', $second->id)['Applications']);
                    $this->assertSame('Blocked', $rows->firstWhere('Job ID', $second->id)['Status']);
                    $this->assertSame('Legacy Company', $rows->firstWhere('Job ID', $legacy->id)['Organization']);

                    return true;
                });
        }
        $filters = ['report_organization' => $organization->id];
        $this->get(route('admin.dashboard', $filters))->assertOk()
            ->assertViewHas('jobLevelReports', fn ($rows) => $rows->count() === 2);
        $this->get(route('admin.dashboard', [...$filters, 'year' => 1900]))->assertOk()
            ->assertViewHas('jobLevelReports', fn ($rows) => $rows->count() === 2 && $rows->sum('Applications') === 0);
        $this->get(route('admin.dashboard', [...$filters, 'employer' => $otherContact->id]))->assertOk()
            ->assertViewHas('jobLevelReports', fn ($rows) => $rows->count() === 1 && $rows->first()['Job ID'] === $second->id);
        $this->get(route('admin.reports.export', [...$filters, 'report' => 'jobs', 'format' => 'excel']))
            ->assertOk()->assertDownload('jobs-report.csv')
            ->assertSee('First Posting')->assertSee('Second Posting')->assertDontSee('Legacy Posting');
        $this->get(route('admin.reports.export', [...$filters, 'report' => 'jobs', 'format' => 'pdf']))
            ->assertOk()->assertDownload('jobs-report.pdf');
        $this->get(route('admin.dashboard', ['report_organization' => $empty->id]))->assertOk()
            ->assertSee('No job postings for the selected filters.');
        $this->get(route('admin.reports.export', ['report_organization' => $empty->id, 'report' => 'jobs', 'format' => 'excel']))
            ->assertOk()->assertSee('Applications')->assertDontSee('First Posting');
        $this->get(route('admin.reports.export', ['report_organization' => $empty->id, 'report' => 'jobs', 'format' => 'pdf']))
            ->assertOk()->assertDownload('jobs-report.pdf');
        $this->getJson(route('admin.dashboard', ['report_organization' => 99999]))
            ->assertUnprocessable()->assertJsonValidationErrors('report_organization');
    }
}
