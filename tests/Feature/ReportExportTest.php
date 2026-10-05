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
