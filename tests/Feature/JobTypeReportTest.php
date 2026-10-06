<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\College;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\StudentProfile;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\ApplicationStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobTypeReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private JobType $ia;

    private College $college;

    private College $otherCollege;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationStatusSeeder::class);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->ia = JobType::create(['name' => 'Industrial Attachment', 'status' => 1]);
        $this->college = College::create(['name' => 'Engineering', 'code' => 'ENG', 'status' => 1]);
        $this->otherCollege = College::create(['name' => 'Business', 'code' => 'BUS', 'status' => 0]);
        $category = Category::factory()->create(['college_id' => $this->college->id]);
        $otherCategory = Category::factory()->create(['college_id' => $this->otherCollege->id]);
        $student = User::factory()->create(['role' => 'student']);
        StudentProfile::create(['user_id' => $student->id, 'college_id' => $this->otherCollege->id]);

        $firstJob = Job::factory()->create([
            'user_id' => $this->admin->id, 'job_type_id' => $this->ia->id,
            'category_id' => $category->id, 'status' => 0,
        ]);
        foreach ([7, 8, 1] as $status) {
            JobApplication::create([
                'job_id' => $firstJob->id, 'user_id' => $student->id,
                'employer_id' => $this->admin->id, 'application_status_id' => $status,
            ]);
        }
        Job::factory()->create([
            'user_id' => $this->admin->id, 'job_type_id' => $this->ia->id,
            'category_id' => $otherCategory->id, 'status' => 2,
        ]);
        $unassignedCategory = Category::factory()->create(['college_id' => null]);
        Job::factory()->create([
            'user_id' => $this->admin->id, 'job_type_id' => $this->ia->id,
            'category_id' => $unassignedCategory->id,
        ]);
        JobType::create(['name' => 'Inactive Type', 'status' => 0]);
    }

    public function test_dashboard_reports_job_types_and_ia_by_job_category_college(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard', ['tab' => 'job-types']));
        $response->assertOk()->assertSee('Job Type Reports')->assertSee('Summary by Job Type')
            ->assertDontSee('Industrial Attachment by College/Center')
            ->assertDontSee('By College')
            ->assertDontSee('id="job-type-colleges"', false)
            ->assertDontSee('<th>Drill-down</th>', false);
        $response->assertViewHas('selectedReportJobType', fn ($type) => $type->id === $this->ia->id);
        $response->assertViewHas('jobTypeReports', function ($rows) {
            $ia = $rows->firstWhere('id', $this->ia->id);
            $this->assertSame(3, $ia->job_count);
            $this->assertSame(3, $ia->application_count);
            $this->assertSame(1, $ia->placed_count);
            $this->assertSame(1, $ia->rejected_count);
            $this->assertSame(33.33, $ia->placement_rate);
            $this->assertSame(0, $rows->firstWhere('name', 'Inactive Type')->job_count);

            return true;
        });
        $response->assertViewHas('jobTypeCollegeReports', function ($rows) {
            $engineering = $rows->firstWhere('id', $this->college->id);
            $this->assertSame(1, $engineering->job_count);
            $this->assertSame(3, $engineering->application_count);
            $this->assertSame(1, $engineering->placed_count);
            $business = $rows->firstWhere('id', $this->otherCollege->id);
            $this->assertSame(1, $business->job_count);
            $this->assertSame(0, $business->application_count);
            $this->assertSame(0, $business->placement_rate);
            $this->assertSame(1, $rows->firstWhere('name', 'Unassigned college')->job_count);

            return true;
        });
    }

    public function test_college_filter_applies_to_summary_drill_down_and_exports(): void
    {
        $filters = ['report_college' => $this->college->id, 'report_job_type' => $this->ia->id];
        $this->actingAs($this->admin)->get(route('admin.dashboard', $filters))
            ->assertOk()
            ->assertSee('Summary by Job Type - Engineering')
            ->assertViewHas('jobTypeReports', fn ($rows) => $rows->count() === 1 && $rows->first()->id === $this->ia->id && $rows->first()->job_count === 1)
            ->assertViewHas('jobTypeCollegeReports', fn ($rows) => $rows->count() === 1 && $rows->first()->id === $this->college->id);

        $summary = $this->get(route('admin.reports.export', array_merge($filters, ['report' => 'job-types', 'format' => 'excel'])));
        $summary->assertOk()->assertSee('Engineering,"Industrial Attachment",1,3,1,1,33.33%', false)
            ->assertSee('College/Center,"Job Type",Jobs,Applications,Placed,Rejected,"Placement Rate"', false)
            ->assertDontSee('Inactive Type');
        $drillDown = $this->get(route('admin.reports.export', array_merge($filters, ['report' => 'job-type-colleges', 'format' => 'excel'])));
        $drillDown->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="job-type-colleges-report.csv"')
            ->assertSee('"Job Type",College,Jobs,Applications,Placed,Rejected,"Placement Rate"', false)
            ->assertSee('"Industrial Attachment",Engineering,1,3,1,1,33.33%', false)
            ->assertDontSee('Business')->assertDontSee('Unassigned college');
    }

    public function test_college_name_identifies_summary_and_pdf_and_reset_clears_it(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard', [
            'tab' => 'job-types', 'report_college' => $this->otherCollege->id,
        ]))->assertOk()->assertSee('Summary by Job Type - Business');

        Pdf::shouldReceive('loadHTML')->once()->andReturnUsing(function ($html) {
            $this->assertStringContainsString('Job types - Business Report', $html);
            $this->assertStringContainsString('<th>College/Center</th>', $html);
            $this->assertStringContainsString('<td>Business</td>', $html);

            $pdf = new \Barryvdh\DomPDF\PDF(app('dompdf'), app('config'), app('files'), app('view'));

            return $pdf->loadHTML($html);
        });
        $this->get(route('admin.reports.export', [
            'report' => 'job-types', 'format' => 'pdf', 'report_college' => $this->otherCollege->id,
        ]))->assertOk()->assertHeader('Content-Type', 'application/pdf');

        $this->get(route('admin.dashboard', ['tab' => 'job-types']))
            ->assertOk()->assertSee('Summary by Job Type - All colleges/centers');
    }

    public function test_selected_job_type_filters_summary_and_exports(): void
    {
        $type = JobType::create(['name' => 'Graduate Job', 'status' => 1]);
        $category = Category::factory()->create(['college_id' => $this->college->id]);
        Job::factory()->create([
            'user_id' => $this->admin->id, 'job_type_id' => $type->id,
            'category_id' => $category->id,
        ]);
        $filters = ['tab' => 'job-types', 'report_job_type' => $type->id];

        $this->actingAs($this->admin)->get(route('admin.dashboard', $filters))
            ->assertOk()
            ->assertViewHas('jobTypeReports', function ($rows) use ($type) {
                $this->assertSame([$type->id], $rows->pluck('id')->all());
                $this->assertSame(1, $rows->first()->job_count);
                $this->assertSame(0, $rows->first()->application_count);

                return true;
            })
            ->assertViewHas('jobTypeCollegeReports', fn ($rows) => $rows->sum('job_count') === 1);

        $csv = $this->get(route('admin.reports.export', array_merge($filters, ['report' => 'job-types', 'format' => 'excel'])))
            ->assertOk()
            ->assertSee('"Graduate Job",1,0,0,0,0.00%', false)
            ->assertDontSee('Industrial Attachment')
            ->assertDontSee('Inactive Type');
        $this->assertCount(2, explode("\n", trim($csv->getContent())));

        Pdf::shouldReceive('loadHTML')->once()->andReturnUsing(function ($html) {
            $this->assertStringContainsString('Graduate Job', $html);
            $this->assertStringNotContainsString('Industrial Attachment', $html);
            $this->assertStringNotContainsString('Inactive Type', $html);

            $pdf = new \Barryvdh\DomPDF\PDF(app('dompdf'), app('config'), app('files'), app('view'));

            return $pdf->loadHTML($html);
        });
        $this->get(route('admin.reports.export', array_merge($filters, ['report' => 'job-types', 'format' => 'pdf'])))
            ->assertOk()->assertHeader('Content-Type', 'application/pdf');

        $this->get(route('admin.dashboard', ['tab' => 'job-types']))
            ->assertOk()
            ->assertViewHas('jobTypeReports', fn ($rows) => $rows->count() === 3);
    }

    public function test_drill_down_can_select_another_type_and_zero_counts(): void
    {
        $type = JobType::where('name', 'Inactive Type')->firstOrFail();
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard', ['report_job_type' => $type->id]))
            ->assertOk()
            ->assertViewHas('selectedReportJobType', fn ($selected) => $selected->id === $type->id)
            ->assertViewHas('jobTypeReports', fn ($rows) => $rows->count() === 1 && $rows->first()->id === $type->id && $rows->first()->job_count === 0)
            ->assertViewHas('jobTypeCollegeReports', fn ($rows) => $rows->count() === 2 && $rows->sum('job_count') === 0);
    }

    public function test_report_form_only_has_college_dropdown_and_summary_exports_match(): void
    {
        $filters = ['tab' => 'job-types', 'report_college' => $this->college->id];
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard', $filters))->assertOk();
        $response->assertViewHas('jobTypeReports', fn ($rows) => $rows->contains('name', 'Inactive Type'));
        preg_match('/<form[^>]*>\\s*<div class="card-body row g-3">.*?<\/form>/s', $response->getContent(), $matches);
        $this->assertNotEmpty($matches);
        $this->assertSame(1, substr_count($matches[0], '<select'));
        $this->assertStringContainsString('name="report_college"', $matches[0]);
        $this->assertStringNotContainsString('name="report_job_type"', $matches[0]);
        foreach (['pdf', 'excel'] as $format) {
            $response->assertSee(route('admin.reports.export', [
                'report_college' => $this->college->id, 'report' => 'job-types', 'format' => $format,
            ]));
        }
        $this->get(route('admin.reports.export', [
            'report_college' => $this->college->id, 'report' => 'job-types', 'format' => 'excel',
        ]))->assertOk()->assertSee('Industrial Attachment')->assertSee('Inactive Type');

        $response = $this->get(route('admin.dashboard', array_merge($filters, ['report_job_type' => $this->ia->id])))
            ->assertOk();
        $response->assertSee('<input type="hidden" name="report_job_type" value="'.$this->ia->id.'">', false);
        $response->assertDontSee('<select name="report_job_type"', false);
    }

    public function test_placement_filters_do_not_change_job_category_college_reporting(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard', ['college' => $this->otherCollege->id, 'year' => 2000]))
            ->assertOk()
            ->assertViewHas('jobTypeReports', fn ($rows) => $rows->firstWhere('id', $this->ia->id)->application_count === 3)
            ->assertViewHas('placementTotalApplications', 0);
    }

    public function test_both_reports_can_be_exported_as_pdf(): void
    {
        foreach (['job-types', 'job-type-colleges'] as $report) {
            $response = $this->actingAs($this->admin)->get(route('admin.reports.export', [
                'report' => $report, 'format' => 'pdf', 'report_job_type' => $this->ia->id,
            ]));
            $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent());
        }
    }

    public function test_invalid_report_filters_are_rejected(): void
    {
        $this->actingAs($this->admin)
            ->getJson(route('admin.dashboard', ['report_job_type' => 99999, 'report_college' => 'bad']))
            ->assertUnprocessable()->assertJsonValidationErrors(['report_job_type', 'report_college']);
    }

    public function test_all_dashboard_percentages_have_two_decimal_places(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();
        $text = strip_tags($response->getContent());
        $this->assertStringContainsString('33.33%', $text);
        $this->assertStringContainsString('0.00%', $text);
        $this->assertStringContainsString('100.00%', $text);
        $this->assertPercentagePrecision($text);
    }

    public function test_dashboard_tabs_use_a_wrapping_responsive_grid(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();
        preg_match('/<ul[^>]*id="dashboardMainTabs".*?<\/ul>/s', $response->getContent(), $matches);
        $this->assertNotEmpty($matches);
        $this->assertSame(8, substr_count($matches[0], 'nav-item col-6 col-md-3'));
        $this->assertSame(8, substr_count($matches[0], 'w-100 h-100 text-center'));
        $this->assertSame(8, substr_count($matches[0], 'aria-hidden="true"'));
        $response->assertSee('assets/css/dashboard-tabs.css', false);
        $response->assertSee('assets/js/dashboard-tabs.js', false);
        $response->assertSee('Dashboard Reports');
        $this->assertStringNotContainsString('overflow-auto', $matches[0]);
        $this->assertStringNotContainsString('flex-nowrap', $matches[0]);
        $this->assertStringNotContainsString('text-nowrap', $matches[0]);
    }

    public function test_overview_groups_metrics_without_losing_reports_or_actions(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();
        $this->assertSame(5, substr_count($response->getContent(), 'shadow-sm overview-report-group'));
        foreach (['Overview Report', 'Job Report', 'Employer Report', 'Organization Report', 'Student Report', 'Application Report', 'Active Internal Users'] as $heading) {
            $response->assertSee($heading);
        }
        foreach (['admin.jobs', 'admin.users.employers', 'admin.organizations.index', 'admin.users.students', 'admin.jobApplications'] as $route) {
            $response->assertSee(route($route));
        }
        foreach (['pdf', 'excel'] as $format) {
            $response->assertSee(route('admin.reports.export', ['report' => 'overview', 'format' => $format]));
        }
    }

    public function test_all_report_partials_render_once_with_filters_and_export_links(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard', [
            'tab' => 'placement', 'college' => $this->college->id,
            'category_college' => $this->college->id, 'report_college' => $this->college->id,
        ]))->assertOk();

        foreach (['overview', 'applications', 'placement', 'job-types', 'categories', 'rejection', 'employers', 'metrics'] as $tab) {
            $this->assertTrue(view()->exists('admin.reports.'.$tab));
            $this->assertSame(1, substr_count($response->getContent(), 'id="tab-'.$tab.'"'));
            $response->assertSee('data-bs-target="#tab-'.$tab.'"', false);
        }

        foreach (['overview', 'applications', 'placement', 'job-types', 'categories', 'rejection', 'employer', 'funnel'] as $report) {
            foreach (['pdf', 'excel'] as $format) {
                $response->assertSee(route('admin.reports.export', ['report' => $report, 'format' => $format]));
            }
        }

        $response->assertSee('Summary by Job Type - Engineering')
            ->assertSee('Organization-Level Reporting')
            ->assertSee('Recruitment Funnel')
            ->assertSee('Application-Level vs Student-Level Metrics')
            ->assertSee('Application Status Reports')
            ->assertSee('id="rejectionTrendsTabs"', false)
            ->assertSee('name="college"', false)
            ->assertSee('name="category_college"', false)
            ->assertSee('name="report_college"', false);
    }

    public function test_percentage_precision_is_consistent_in_excel_and_pdf_reports(): void
    {
        $this->actingAs($this->admin);
        foreach (['placement', 'applications', 'rejection', 'funnel', 'categories', 'job-types', 'job-type-colleges'] as $report) {
            $response = $this->get(route('admin.reports.export', ['report' => $report, 'format' => 'excel']))->assertOk();
            $this->assertPercentagePrecision($response->getContent());
            if (in_array($report, ['placement', 'applications', 'rejection', 'categories', 'job-types', 'job-type-colleges'], true)) {
                $response->assertSee('33.33%', false);
            }

            Pdf::shouldReceive('loadHTML')->once()->andReturnUsing(function ($html) {
                $this->assertPercentagePrecision(strip_tags(preg_replace('/<style>.*?<\/style>/s', '', $html)));

                $pdf = new \Barryvdh\DomPDF\PDF(app('dompdf'), app('config'), app('files'), app('view'));

                return $pdf->loadHTML($html);
            });
            $this->get(route('admin.reports.export', ['report' => $report, 'format' => 'pdf']))
                ->assertOk()->assertHeader('Content-Type', 'application/pdf');
        }
    }

    private function assertPercentagePrecision(string $text): void
    {
        preg_match_all('/\d+(?:\.\d+)?%/', $text, $matches);
        $this->assertNotEmpty($matches[0]);
        foreach ($matches[0] as $percentage) {
            $this->assertMatchesRegularExpression('/^\d+\.\d{2}%$/', $percentage);
        }
    }

    public function test_empty_summary_and_exports_keep_headers(): void
    {
        Job::query()->delete();
        JobType::query()->delete();
        $this->actingAs($this->admin)->get(route('admin.dashboard', ['tab' => 'job-types']))
            ->assertOk()->assertSee('No job types configured.')
            ->assertDontSee('By College')
            ->assertViewHas('jobTypeCollegeReports', fn ($rows) => $rows->isEmpty());
        $response = $this->get(route('admin.reports.export', ['report' => 'job-types', 'format' => 'excel']));
        $response->assertOk();
        $this->assertSame(['Job Type', 'Jobs', 'Applications', 'Placed', 'Rejected', 'Placement Rate'], str_getcsv(trim($response->getContent())));
        $this->get(route('admin.reports.export', ['report' => 'job-type-colleges', 'format' => 'excel']))
            ->assertUnprocessable();
    }
}
