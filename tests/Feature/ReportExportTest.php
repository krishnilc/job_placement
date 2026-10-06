<?php

namespace Tests\Feature;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_overview_shows_and_exports_only_active_internal_users_by_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        User::factory()->count(2)->create(['role' => 'management', 'status' => 'active']);
        User::factory()->count(3)->create(['role' => 'super_admin', 'status' => 'active']);
        foreach (['management', 'admin', 'super_admin'] as $role) {
            foreach (['pending', 'blocked'] as $status) {
                User::factory()->create(['role' => $role, 'status' => $status]);
            }
        }
        User::factory()->create(['role' => 'employer', 'status' => 'active']);
        User::factory()->create(['role' => 'student', 'status' => 'active']);
        $this->actingAs($admin);

        $response = $this->get(route('admin.dashboard'));
        $response->assertOk()
            ->assertViewHas('activeManagementUsers', 2)
            ->assertViewHas('activeRegularAdmins', 1)
            ->assertViewHas('activeSuperAdmins', 3)
            ->assertSeeInOrder(['Active Internal Users', 'Management (Read-only)', 'Administrators', 'Super Administrators'])
            ->assertDontSee('System Information')
            ->assertDontSee('Total Staff Users');
        $section = substr($response->getContent(), strpos($response->getContent(), '<h6 class="mb-0">Active Internal Users</h6>'));
        $section = substr($section, 0, strpos($section, '</table>'));
        foreach (['Management (Read-only)' => 2, 'Administrators' => 1, 'Super Administrators' => 3] as $label => $count) {
            $this->assertMatchesRegularExpression(
                '/<td>'.preg_quote($label, '/').'<\/td>\s*<td class="text-end fw-semibold">'.$count.'<\/td>/',
                $section
            );
        }
        $this->assertSame(3, substr_count($section, '<td class="text-end fw-semibold">'));

        $csv = $this->get(route('admin.reports.export', ['report' => 'overview', 'format' => 'excel']));
        $csv->assertOk()->assertDownload('overview-report.csv');
        $rows = array_map('str_getcsv', explode("\n", trim($csv->getContent())));
        $this->assertSame([
            ['--- ACTIVE INTERNAL USERS ---', ''],
            ['Management (Read-only)', '2'],
            ['Administrators', '1'],
            ['Super Administrators', '3'],
        ], array_slice($rows, -4));
        $csv->assertDontSee('SYSTEM INFORMATION');

        Pdf::shouldReceive('loadHTML')->once()->andReturnUsing(function ($html) {
            foreach (['Management (Read-only)' => 2, 'Administrators' => 1, 'Super Administrators' => 3] as $label => $count) {
                $this->assertMatchesRegularExpression(
                    '/<td[^>]*>'.preg_quote($label, '/').'<\/td>\s*<td[^>]*>'.$count.'<\/td>/',
                    $html
                );
            }
            $this->assertStringNotContainsString('SYSTEM INFORMATION', $html);
            $pdf = new \Barryvdh\DomPDF\PDF(app('dompdf'), app('config'), app('files'), app('view'));

            return $pdf->loadHTML($html);
        });
        $this->get(route('admin.reports.export', ['report' => 'overview', 'format' => 'pdf']))
            ->assertOk()->assertDownload('overview-report.pdf');
    }

    public function test_admin_can_download_placement_report_as_excel(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports/export/placement/excel');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="placement-report.csv"');
    }

    public function test_pdf_download_timestamp_uses_fiji_time_and_correct_am_pm(): void
    {
        config(['reporting.timezone' => 'Pacific/Fiji']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        foreach ([
            '2026-10-05 03:08:00' => 'October 5, 2026 at 3:08 PM Pacific/Fiji',
            '2026-10-05 15:08:00' => 'October 6, 2026 at 3:08 AM Pacific/Fiji',
        ] as $utc => $expected) {
            $this->travelTo(Carbon::parse($utc, 'UTC'));
            Pdf::shouldReceive('loadHTML')->once()->andReturnUsing(function ($html) use ($expected) {
                $this->assertStringContainsString($expected, $html);
                $pdf = new \Barryvdh\DomPDF\PDF(app('dompdf'), app('config'), app('files'), app('view'));

                return $pdf->loadHTML($html);
            });
            $this->get(route('admin.reports.export', ['report' => 'placement', 'format' => 'pdf']))
                ->assertOk()->assertDownload('placement-report.pdf');
        }
        $this->travelBack();
    }
}
