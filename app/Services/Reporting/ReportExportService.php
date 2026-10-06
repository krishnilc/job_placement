<?php

namespace App\Services\Reporting;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ReportExportService
{
    public function __construct(private readonly ReportPdfRenderer $pdfRenderer) {}

    public function download(Request $request, string $report, string $format, array $dashboard): Response
    {
        $format = strtolower($format);
        if (! in_array($format, ['pdf', 'excel'], true)) {
            abort(404);
        }

        if ($report === 'job-type-colleges' && ! $dashboard['selectedReportJobType']) {
            abort(422, 'Select a job type to export its college breakdown.');
        }
        $rows = $this->buildExportRows($report, $dashboard);
        $headers = array_keys($rows[0] ?? []);
        if (! $headers) {
            $headers = match ($report) {
                'job-types' => ['Job Type', 'Jobs', 'Applications', 'Placed', 'Rejected', 'Placement Rate'],
                'job-type-colleges' => ['Job Type', 'College', 'Jobs', 'Applications', 'Placed', 'Rejected', 'Placement Rate'],
                'jobs' => ['Job ID', 'Organization', 'Job Title', 'Job Type', 'Location', 'Vacancies', 'Status', 'Posted', 'Closing Date', 'Applications'],
                default => [],
            };
        }
        $title = ucfirst(str_replace('-', ' ', $report));
        if ($report === 'funnel') {
            if ($dashboard['selectedFunnelOrganization']) {
                $title .= ' - '.$dashboard['selectedFunnelOrganization']->name;
            }
            if ($dashboard['selectedFunnelJob']) {
                $title .= ' - '.$dashboard['selectedFunnelJob']->title.' (#'.$dashboard['selectedFunnelJob']->id.')';
            }
        }
        if ($report === 'job-types' && $dashboard['selectedReportCollege']) {
            $title .= ' - '.$dashboard['selectedReportCollege']->name;
            if ($rows === []) {
                array_unshift($headers, 'College/Center');
            }
        }

        if ($format === 'pdf') {
            $html = $this->pdfRenderer->render($title, $rows, $request, $headers);

            $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');
            $pdf->render();

            // Page numbers via dompdf canvas (CSS counter(pages) is unreliable)
            $canvas = $pdf->getDompdf()->getCanvas();
            $font = $pdf->getDompdf()->getFontMetrics()->getFont('Arial', 'normal');
            $canvas->page_text(
                $canvas->get_width() - 90,
                $canvas->get_height() - 28,
                'Page {PAGE_NUM} of {PAGE_COUNT}',
                $font,
                9,
                [0.33, 0.33, 0.33]
            );

            return $pdf->download(Str::slug($title).'-report.pdf');
        }

        $csv = fopen('php://temp', 'r+');
        if ($csv === false) {
            throw new \RuntimeException('Unable to open report export stream.');
        }

        try {
            if ($headers) {
                if (fputcsv($csv, $headers) === false) {
                    throw new \RuntimeException('Unable to write report export headers.');
                }
                foreach ($rows as $row) {
                    $values = array_map(fn ($value) => is_scalar($value) ? $value : json_encode($value, JSON_THROW_ON_ERROR), $row);
                    if (fputcsv($csv, $values) === false) {
                        throw new \RuntimeException('Unable to write report export row.');
                    }
                }
            }

            if (! rewind($csv)) {
                throw new \RuntimeException('Unable to rewind report export stream.');
            }
            $contents = stream_get_contents($csv);
            if ($contents === false) {
                throw new \RuntimeException('Unable to read report export stream.');
            }
        } finally {
            fclose($csv);
        }

        return response($contents, 200)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="'.Str::slug($title).'-report.csv"');
    }

    private function buildExportRows(string $report, array $dashboard): array
    {
        return match ($report) {
            'job-types' => $dashboard['jobTypeReports']->map(fn ($row) => [
                ...($dashboard['selectedReportCollege'] ? ['College/Center' => $dashboard['selectedReportCollege']->name] : []),
                'Job Type' => $row->name,
                'Jobs' => $row->job_count,
                'Applications' => $row->application_count,
                'Placed' => $row->placed_count,
                'Rejected' => $row->rejected_count,
                'Placement Rate' => number_format($row->placement_rate, 2).'%',
            ])->toArray(),
            'job-type-colleges' => $dashboard['jobTypeCollegeReports']->map(fn ($row) => [
                'Job Type' => $dashboard['selectedReportJobType']->name,
                'College' => $row->name,
                'Jobs' => $row->job_count,
                'Applications' => $row->application_count,
                'Placed' => $row->placed_count,
                'Rejected' => $row->rejected_count,
                'Placement Rate' => number_format($row->placement_rate, 2).'%',
            ])->toArray(),
            'overview' => [
                ['Metric', 'Value'],
                ['--- JOB REPORT ---', ''],
                ['Total Jobs', $dashboard['totalJobs']],
                ['Open Jobs', $dashboard['activeJobs']],
                ['Approval Pending Jobs', $dashboard['pendingJobs']],
                ['Blocked Jobs', $dashboard['blockedJobs']],
                ['Featured Jobs', $dashboard['featuredJobs']],
                ['--- EMPLOYER REPORT ---', ''],
                ['Total Employers', $dashboard['totalEmployers']],
                ['Active Employers', $dashboard['activeEmployers']],
                ['Approval Pending Employers', $dashboard['pendingEmployers']],
                ['Blocked Employers', $dashboard['blockedEmployers']],
                ['--- ORGANIZATION REPORT ---', ''],
                ['Total Organizations', $dashboard['totalOrganizations']],
                ['Organizations With Linked Employees', $dashboard['organizationsWithEmployees']],
                ['Organizations With Job Postings', $dashboard['organizationsWithJobs']],
                ['Pending Organization Requests', $dashboard['pendingOrganizationRequests']],
                ['--- STUDENT REPORT ---', ''],
                ['Total Students', $dashboard['totalStudents']],
                ['Active Students', $dashboard['activeStudents']],
                ['Approval Pending Students', $dashboard['pendingApprovalStudents']],
                ['Blocked Students', $dashboard['blockedStudents']],
                ['--- APPLICATION REPORT ---', ''],
                ['Total Applications', $dashboard['totalApplications']],
                ['Active Applications', $dashboard['activeApplications']],
                ['Placed Applications', $dashboard['placedApplications']],
                ['Unsuccessful Applications', $dashboard['unsuccessfulApplications']],
                ['--- ACTIVE INTERNAL USERS ---', ''],
                ['Management (Read-only)', $dashboard['activeManagementUsers']],
                ['Administrators', $dashboard['activeRegularAdmins']],
                ['Super Administrators', $dashboard['activeSuperAdmins']],
            ],
            'placement' => [
                ['Metric', 'Value'],
                ['Total Applications', $dashboard['placementTotalApplications']],
                ['Students Placed', $dashboard['placedApplications']],
                ['Placement Rate', number_format($dashboard['placementRate'], 2).'%'],
                ['Interviewed Applications', $dashboard['interviewedApplications']],
                ['Interview Conversion Rate', number_format($dashboard['interviewConversionRate'], 2).'%'],
            ],
            'applications' => collect($dashboard['applicationStatusReports'])->map(function ($row) use ($dashboard) {
                $percentage = $dashboard['applicationStatusReportTotal'] > 0
                    ? ($row->application_count / $dashboard['applicationStatusReportTotal']) * 100
                    : 0;

                return [
                    'Status' => $row->name,
                    'Category' => $row->category,
                    'Applications' => $row->application_count,
                    'Share of Applications' => number_format($percentage, 2).'%',
                ];
            })
                ->push([
                    'Status' => 'Total',
                    'Category' => '',
                    'Applications' => $dashboard['applicationStatusReportTotal'],
                    'Share of Applications' => '100.00%',
                ])
                ->toArray(),
            'rejection' => [
                ['Metric', 'Value'],
                ['Rejected Applications', $dashboard['rejectedApplications']],
                ['Rejection Rate', number_format($dashboard['rejectionRate'], 2).'%'],
                ['Active Applications', $dashboard['activeApplications']],
                ['Unsuccessful Applications', $dashboard['unsuccessfulApplications']],
            ],
            'jobs' => $dashboard['jobLevelReports']->toArray(),
            'employer' => collect($dashboard['employerPerformanceReports'])->map(fn ($row) => [
                'Organization' => $row->organization_name,
                'Applications' => $row->application_count,
                'Interviewed' => $row->interviewed_count,
                'Placed' => $row->placed_count,
                'Rejected' => $row->rejected_count,
            ])->toArray(),
            'funnel' => collect($dashboard['funnelReports'])->map(fn ($row) => [
                'Stage' => $row['name'],
                'Count' => $row['count'],
                'Drop Off' => $row['drop_off'],
                'Drop Off Rate' => number_format($row['drop_off_rate'], 2).'%',
                'Progression from Start' => number_format($row['conversion_from_start'], 2).'%',
            ])->toArray(),
            'categories' => collect($dashboard['categoryReports'])->map(fn ($row) => [
                'Category' => $row->name,
                'College' => $row->college_name,
                'Jobs' => $row->job_count,
                'Applications' => $row->application_count,
                'Placed' => $row->placed_count,
                'Rejected' => $row->rejected_count,
                'Placement Rate' => number_format($row->placement_rate, 2).'%',
            ])->toArray(),
            default => [
                ['Report', 'Value'],
                ['Selected Report', $report],
                ['Status', 'No export data available'],
            ],
        };
    }
}
