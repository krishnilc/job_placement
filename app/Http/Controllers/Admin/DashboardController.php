<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardReportRequest;
use App\Services\Reporting\DashboardReportService;
use App\Services\Reporting\ReportExportService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    public function index(DashboardReportRequest $request, DashboardReportService $reports): View
    {
        return view('admin.dashboard', $reports->build($request));
    }

    public function exportReport(DashboardReportRequest $request, string $report, string $format, DashboardReportService $reports, ReportExportService $exports): Response
    {
        abort_unless(in_array(strtolower($format), ['pdf', 'excel'], true), 404);

        return $exports->download($request, $report, $format, $reports->build($request));
    }
}
