<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationStatus;
use App\Models\College;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.dashboard', $this->buildDashboardData($request));
    }

    public function exportReport(Request $request, string $report, string $format)
    {
        $format = strtolower($format);
        if (! in_array($format, ['pdf', 'excel'], true)) {
            abort(404);
        }

        $dashboard = $this->buildDashboardData($request);
        $rows = $this->buildExportRows($report, $dashboard);
        $title = ucfirst(str_replace('-', ' ', $report));

        if ($format === 'pdf') {
            $html = $this->renderPdfHtml($title, $rows, $request);

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

            return $pdf->download(Str::slug($title) . '-report.pdf');
        }

        $csv = fopen('php://temp', 'r+');
        $headers = array_keys($rows[0] ?? []);
        if ($headers) {
            fputcsv($csv, $headers);
            foreach ($rows as $row) {
                fputcsv($csv, array_map(fn ($value) => is_scalar($value) ? $value : json_encode($value), $row));
            }
        }

        rewind($csv);
        $contents = stream_get_contents($csv);
        fclose($csv);

        return response($contents, 200)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . Str::slug($title) . '-report.csv"');
    }

    private function buildDashboardData(Request $request): array
    {
        // Get statistics
        $totalUsers = User::count();
        $totalJobs = Job::count();
        $pendingJobs = Job::where('status', 0)->count();
        $activeJobs = Job::where('status', 1)
            ->where(function ($query) {
                $query->whereNull('closing_date')
                    ->orWhere('closing_date', '>=', now()->toDateString());
            })
            ->count();
        $blockedJobs = Job::where('status', 2)->count();
        $featuredJobs = Job::where('isFeatured', 1)->count();
        $totalEmployers = User::where('role', 'employer')->count();
        $activeEmployers = User::where('role', 'employer')->where('status', 'active')->count();
        $pendingEmployers = User::where('role', 'employer')->where('status', 'pending')->count();
        $blockedEmployers = User::where('role', 'employer')->where('status', 'blocked')->count();
        $totalStudents = User::where('role', 'student')->count();
        $activeStudents = User::where('role', 'student')->where('status', 'active')->count();
        $pendingApprovalStudents = User::where('role', 'student')->where('status', 'pending')->count();
        $blockedStudents = User::where('role', 'student')->where('status', 'blocked')->count();
        $totalAdmins = User::whereIn('role', ['admin', 'super_admin'])->count();
        $totalSuperAdmins = User::where('role', 'super_admin')->count();
        $totalRegularAdmins = User::where('role', 'admin')->count();
        $activeAdmins = User::whereIn('role', ['admin', 'super_admin'])->where('status', 'active')->count();
        $totalActiveUsers = User::where('status', 'active')->count();
        $activeSuperAdmins = User::where('role', 'super_admin')->where('status', 'active')->count();
        $activeRegularAdmins = User::where('role', 'admin')->where('status', 'active')->count();
        $totalStaffUsers = User::whereIn('role', ['admin', 'super_admin'])->count();
        $activeStaffUsers = User::whereIn('role', ['admin', 'super_admin'])->where('status', 'active')->count();
        $applicationQuery = JobApplication::query();
        $applicationQuery
            ->when($request->filled('college'), fn ($query) => $query->whereHas('user.studentProfile', fn ($p) => $p->where('college_id', $request->college)))
            ->when($request->filled('programme'), fn ($query) => $query->whereHas('user.studentProfile', fn ($p) => $p->where('degree', $request->programme)))
            ->when($request->filled('employer'), fn ($query) => $query->whereHas('job', fn ($jobQuery) => $jobQuery->where('user_id', $request->employer)))
            ->when($request->filled('year'), fn ($query) => $query->whereYear('job_applications.created_at', $request->year))
            ->when($request->filled('opportunity_type'), fn ($query) => $query->whereHas('job', fn ($jobQuery) => $jobQuery->where('job_type_id', $request->opportunity_type)))
            ->when($request->filled('category'), fn ($query) => $query->whereHas('job', fn ($jobQuery) => $jobQuery->where('category_id', $request->category)));

        $placementTotalApplications = (clone $applicationQuery)->count();
        $placedApplications = (clone $applicationQuery)->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Placed'))->count();
        $placementRate = $placementTotalApplications > 0 ? ($placedApplications / $placementTotalApplications) * 100 : 0;
        $collegePlacementReports = (clone $applicationQuery)
            ->join('users', 'users.id', '=', 'job_applications.user_id')
            ->join('student_profiles', 'student_profiles.user_id', '=', 'users.id')
            ->join('colleges', 'colleges.id', '=', 'student_profiles.college_id')
            ->select('colleges.name as college_name', DB::raw('COUNT(job_applications.id) as application_count'))
            ->groupBy('colleges.id', 'colleges.name')
            ->get();
        $collegePlacedCounts = (clone $applicationQuery)
            ->join('users', 'users.id', '=', 'job_applications.user_id')
            ->join('student_profiles', 'student_profiles.user_id', '=', 'users.id')
            ->join('colleges', 'colleges.id', '=', 'student_profiles.college_id')
            ->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Placed'))
            ->select('colleges.name as college_name', DB::raw('COUNT(job_applications.id) as placed_count'))
            ->groupBy('colleges.id', 'colleges.name')
            ->pluck('placed_count', 'college_name');
        $collegePlacementReports->each(function ($report) use ($collegePlacedCounts) {
            $report->placed_count = $collegePlacedCounts[$report->college_name] ?? 0;
            $report->placement_rate = $report->application_count > 0
                ? round(($report->placed_count / $report->application_count) * 100, 1)
                : 0;
        });
        $collegePlacementReports = $collegePlacementReports->sortByDesc('placement_rate')->values();

        $interviewThresholdOrder = ApplicationStatus::where('name', 'Interview Completed')->value('sort_order');
        $interviewStatusIds = $interviewThresholdOrder !== null
            ? ApplicationStatus::where('sort_order', '>=', $interviewThresholdOrder)->pluck('id')
            : collect();
        $interviewedApplicationIds = DB::table('application_status_history')
            ->whereIn('application_status_id', $interviewStatusIds)
            ->distinct()
            ->pluck('job_application_id');

        $interviewConversionQuery = (clone $applicationQuery)->whereIn('job_applications.id', $interviewedApplicationIds);
        $interviewedApplications = (clone $interviewConversionQuery)->count();
        $interviewPlacedApplications = (clone $interviewConversionQuery)->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Placed'))->count();
        $interviewConversionRate = $interviewedApplications > 0 ? ($interviewPlacedApplications / $interviewedApplications) * 100 : 0;

        $interviewConversionByProgramme = (clone $interviewConversionQuery)
            ->join('student_profiles', 'student_profiles.user_id', '=', 'job_applications.user_id')
            ->whereNotNull('student_profiles.degree')
            ->where('student_profiles.degree', '<>', '')
            ->select('student_profiles.degree', DB::raw('COUNT(job_applications.id) as interviewed_count'))
            ->groupBy('student_profiles.degree')
            ->get();
        $programmePlacedCounts = (clone $interviewConversionQuery)
            ->join('student_profiles', 'student_profiles.user_id', '=', 'job_applications.user_id')
            ->whereNotNull('student_profiles.degree')
            ->where('student_profiles.degree', '<>', '')
            ->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Placed'))
            ->select('student_profiles.degree', DB::raw('COUNT(job_applications.id) as placed_count'))
            ->groupBy('student_profiles.degree')
            ->pluck('placed_count', 'degree');
        $interviewConversionByProgramme->each(function ($report) use ($programmePlacedCounts) {
            $report->placed_count = $programmePlacedCounts[$report->degree] ?? 0;
            $report->conversion_rate = $report->interviewed_count > 0
                ? round(($report->placed_count / $report->interviewed_count) * 100, 1)
                : 0;
        });
        $interviewConversionByProgramme = $interviewConversionByProgramme->sortByDesc('conversion_rate')->values();

        $rejectedApplications = (clone $applicationQuery)->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Rejected'))->count();
        $activeApplications = (clone $applicationQuery)->whereHas('applicationStatus', fn ($query) => $query->where('category', 'Active'))->count();
        $unsuccessfulApplications = (clone $applicationQuery)->whereHas('applicationStatus', fn ($query) => $query->where('category', 'Unsuccessful'))->count();
        $rejectionRate = $placementTotalApplications > 0 ? ($rejectedApplications / $placementTotalApplications) * 100 : 0;

        $rejectionByYear = $this->rejectionBreakdown($applicationQuery, $this->getYearExpression('job_applications.created_at'), 'year');
        $rejectionByMonth = $this->rejectionBreakdown($applicationQuery, $this->getMonthExpression('job_applications.created_at'), 'month');
        $rejectionByCollege = $this->rejectionBreakdown(
            (clone $applicationQuery)->join('users', 'users.id', '=', 'job_applications.user_id')->join('student_profiles', 'student_profiles.user_id', '=', 'users.id')->join('colleges', 'colleges.id', '=', 'student_profiles.college_id'),
            'colleges.name',
            'college_name'
        );
        $rejectionByProgramme = $this->rejectionBreakdown(
            (clone $applicationQuery)->join('student_profiles', 'student_profiles.user_id', '=', 'job_applications.user_id')->whereNotNull('student_profiles.degree')->where('student_profiles.degree', '<>', ''),
            'student_profiles.degree',
            'degree'
        );
        $rejectionByCategory = $this->rejectionBreakdown(
            (clone $applicationQuery)->join('jobs', 'jobs.id', '=', 'job_applications.job_id')->join('categories', 'categories.id', '=', 'jobs.category_id'),
            'categories.name',
            'category'
        );
        $rejectionByEmployer = $this->rejectionBreakdown(
            (clone $applicationQuery)->join('jobs', 'jobs.id', '=', 'job_applications.job_id')->join('users as employer_users', 'employer_users.id', '=', 'jobs.user_id'),
            'jobs.company_name',
            'employer'
        );
        $rejectionByOpportunityType = $this->rejectionBreakdown(
            (clone $applicationQuery)->join('jobs', 'jobs.id', '=', 'job_applications.job_id')->join('job_types', 'job_types.id', '=', 'jobs.job_type_id'),
            'job_types.name',
            'opportunity_type'
        );

        $funnelBuckets = [
            'submitted' => ['Submitted', 'Under Review'],
            'shortlisted' => ['Shortlisted'],
            'interviewed' => ['Interview Scheduled', 'Interview Completed'],
            'placed' => ['Accepted', 'Placed'],
            'rejected' => ['Rejected'],
            'withdrawn' => ['Withdrawn'],
        ];
        $statusCountsByYear = (clone $applicationQuery)
            ->join('application_statuses', 'application_statuses.id', '=', 'job_applications.application_status_id')
            ->select(
                DB::raw($this->getYearExpression('job_applications.created_at') . ' as year'),
                'application_statuses.name as status_name',
                DB::raw('COUNT(job_applications.id) as count')
            )
            ->groupBy(DB::raw($this->getYearExpression('job_applications.created_at')), 'application_statuses.name')
            ->get();
        $yearlyFunnelReports = $statusCountsByYear
            ->groupBy('year')
            ->map(function ($rows, $year) use ($funnelBuckets) {
                $bucketTotals = ['year' => $year];
                foreach ($funnelBuckets as $bucket => $statusNames) {
                    $bucketTotals[$bucket] = $rows->whereIn('status_name', $statusNames)->sum('count');
                }
                $bucketTotals['total'] = $rows->sum('count');
                return $bucketTotals;
            })
            ->sortKeysDesc()
            ->values();

        $employerJobsQuery = (clone $applicationQuery)
            ->join('jobs', 'jobs.id', '=', 'job_applications.job_id')
            ->join('users as employer_users', 'employer_users.id', '=', 'jobs.user_id');
        $employerPerformanceReports = (clone $employerJobsQuery)
            ->select('employer_users.id as employer_id', 'jobs.company_name as employer_name', DB::raw('COUNT(job_applications.id) as application_count'))
            ->groupBy('employer_users.id', 'jobs.company_name')
            ->get();
        $employerInterviewedCounts = (clone $employerJobsQuery)
            ->whereIn('job_applications.id', $interviewedApplicationIds)
            ->select('employer_users.id as employer_id', DB::raw('COUNT(job_applications.id) as interviewed_count'))
            ->groupBy('employer_users.id')
            ->pluck('interviewed_count', 'employer_id');
        $employerPlacedCounts = (clone $employerJobsQuery)
            ->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Placed'))
            ->select('employer_users.id as employer_id', DB::raw('COUNT(job_applications.id) as placed_count'))
            ->groupBy('employer_users.id')
            ->pluck('placed_count', 'employer_id');
        $employerRejectedCounts = (clone $employerJobsQuery)
            ->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Rejected'))
            ->select('employer_users.id as employer_id', DB::raw('COUNT(job_applications.id) as rejected_count'))
            ->groupBy('employer_users.id')
            ->pluck('rejected_count', 'employer_id');
        $employerPerformanceReports->each(function ($report) use ($employerInterviewedCounts, $employerPlacedCounts, $employerRejectedCounts) {
            $report->interviewed_count = $employerInterviewedCounts[$report->employer_id] ?? 0;
            $report->placed_count = $employerPlacedCounts[$report->employer_id] ?? 0;
            $report->rejected_count = $employerRejectedCounts[$report->employer_id] ?? 0;
        });
        $employerPerformanceReports = $employerPerformanceReports->sortByDesc('application_count')->values();

        $funnelStatuses = ApplicationStatus::whereIn('category', ['Active', 'Successful'])->orderBy('sort_order')->get();
        $funnelStatusIds = $funnelStatuses->pluck('id');
        $funnelSortOrderById = $funnelStatuses->pluck('sort_order', 'id');
        $filteredApplicationIds = (clone $applicationQuery)->pluck('job_applications.id');
        $currentStageRows = (clone $applicationQuery)->whereIn('application_status_id', $funnelStatusIds)->pluck('application_status_id', 'id');
        $historyStageRows = DB::table('application_status_history')
            ->whereIn('application_status_id', $funnelStatusIds)
            ->whereIn('job_application_id', $filteredApplicationIds)
            ->select('job_application_id', 'application_status_id')
            ->get();

        $maxSortOrderByApplication = [];
        foreach ($currentStageRows as $applicationId => $statusId) {
            $maxSortOrderByApplication[$applicationId] = $funnelSortOrderById[$statusId] ?? 0;
        }
        foreach ($historyStageRows as $row) {
            $sortOrder = $funnelSortOrderById[$row->application_status_id] ?? 0;
            $maxSortOrderByApplication[$row->job_application_id] = max($maxSortOrderByApplication[$row->job_application_id] ?? 0, $sortOrder);
        }
        $maxSortOrders = collect($maxSortOrderByApplication);

        $previousStageCount = null;
        $firstStageCount = null;
        $funnelReports = $funnelStatuses->map(function ($status) use ($maxSortOrders, &$previousStageCount, &$firstStageCount) {
            $count = $maxSortOrders->filter(fn ($sortOrder) => $sortOrder >= $status->sort_order)->count();
            $firstStageCount ??= $count;
            $dropOff = $previousStageCount !== null ? $previousStageCount - $count : 0;
            $dropOffRate = $previousStageCount ? round(($dropOff / $previousStageCount) * 100, 1) : 0;
            $conversionFromStart = $firstStageCount > 0 ? round(($count / $firstStageCount) * 100, 1) : 0;
            $previousStageCount = $count;

            return [
                'name' => $status->name,
                'count' => $count,
                'drop_off' => $dropOff,
                'drop_off_rate' => $dropOffRate,
                'conversion_from_start' => $conversionFromStart,
            ];
        })->values();

        $shortlistedFunnelCount = $funnelReports->firstWhere('name', 'Shortlisted')['count'] ?? 0;
        $acceptedFunnelCount = $funnelReports->firstWhere('name', 'Accepted')['count'] ?? 0;
        $applicationMetrics = [
            'total_applications' => $placementTotalApplications,
            'shortlisting_rate' => $placementTotalApplications > 0 ? round(($shortlistedFunnelCount / $placementTotalApplications) * 100, 1) : 0,
            'interview_conversion_rate' => round($interviewConversionRate, 1),
            'rejection_rate' => round($rejectionRate, 1),
            'offer_conversion_rate' => $acceptedFunnelCount > 0 ? round(($placedApplications / $acceptedFunnelCount) * 100, 1) : 0,
        ];

        $studentsSeekingEmployment = (clone $applicationQuery)->pluck('job_applications.user_id')->unique()->count();
        $studentsSuccessfullyPlaced = (clone $applicationQuery)
            ->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Placed'))
            ->pluck('job_applications.user_id')->unique()->count();
        $studentMetrics = [
            'students_seeking_employment' => $studentsSeekingEmployment,
            'students_successfully_placed' => $studentsSuccessfullyPlaced,
            'graduate_employment_rate' => $studentsSeekingEmployment > 0 ? round(($studentsSuccessfullyPlaced / $studentsSeekingEmployment) * 100, 1) : 0,
        ];

        $collegeOptions = College::active()->orderBy('name')->get(['id', 'name']);
        $collegeCategoryCounts = College::withCount('categories')->orderBy('name')->get(['id', 'name', 'code']);
        $programmeOptions = \App\Models\StudentProfile::whereNotNull('degree')->where('degree', '<>', '')->distinct()->orderBy('degree')->pluck('degree');
        $employerOptions = User::where('role', 'employer')->orderBy('name')->get(['id', 'name']);
        $yearOptions = JobApplication::whereNotNull('created_at')->get(['created_at'])->pluck('created_at')->map(fn ($date) => $date->year)->unique()->sortDesc()->values();
        $opportunityTypeOptions = \App\Models\JobType::orderBy('name')->get(['id', 'name']);
        $categoryOptions = \App\Models\Category::orderBy('name')->get(['id', 'name']);
        $pendingStatusIds = ApplicationStatus::whereIn('name', ['Submitted', 'Under Review'])->pluck('id');
        $pendingApplications = JobApplication::where(function ($query) use ($pendingStatusIds) {
            $query->whereIn('application_status_id', $pendingStatusIds)
                ->orWhere(function ($legacyQuery) {
                    $legacyQuery->whereNull('application_status_id')
                        ->where(function ($statusQuery) {
                            $statusQuery->whereNull('status')->orWhere('status', 'pending');
                        });
                });
        })->count();

        $recentApplications = JobApplication::with(['job', 'user', 'employer', 'applicationStatus'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $jobsByCategory = Job::with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($jobs) {
                return count($jobs);
            });

        // Job Categories report: jobs, applications and placements per category
        $categoryJobCounts = Job::select('category_id', DB::raw('COUNT(*) as job_count'))
            ->groupBy('category_id')
            ->pluck('job_count', 'category_id');
        $categoryApplicationCounts = (clone $applicationQuery)
            ->join('jobs', 'jobs.id', '=', 'job_applications.job_id')
            ->select('jobs.category_id', DB::raw('COUNT(job_applications.id) as application_count'))
            ->groupBy('jobs.category_id')
            ->pluck('application_count', 'jobs.category_id');
        $categoryPlacedCounts = (clone $applicationQuery)
            ->join('jobs', 'jobs.id', '=', 'job_applications.job_id')
            ->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Placed'))
            ->select('jobs.category_id', DB::raw('COUNT(job_applications.id) as placed_count'))
            ->groupBy('jobs.category_id')
            ->pluck('placed_count', 'jobs.category_id');
        $categoryRejectedCounts = (clone $applicationQuery)
            ->join('jobs', 'jobs.id', '=', 'job_applications.job_id')
            ->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Rejected'))
            ->select('jobs.category_id', DB::raw('COUNT(job_applications.id) as rejected_count'))
            ->groupBy('jobs.category_id')
            ->pluck('rejected_count', 'jobs.category_id');

        $categoryReports = \App\Models\Category::with('college:id,name')
            ->when($request->filled('category_college'), fn ($query) => $query->where('college_id', $request->category_college))
            ->orderBy('name')
            ->get(['id', 'name', 'college_id'])
            ->map(function ($category) use ($categoryJobCounts, $categoryApplicationCounts, $categoryPlacedCounts, $categoryRejectedCounts) {
                $applicationCount = $categoryApplicationCounts[$category->id] ?? 0;
                $placedCount = $categoryPlacedCounts[$category->id] ?? 0;

                return (object) [
                    'name' => $category->name,
                    'college_name' => $category->college->name ?? '—',
                    'job_count' => $categoryJobCounts[$category->id] ?? 0,
                    'application_count' => $applicationCount,
                    'placed_count' => $placedCount,
                    'rejected_count' => $categoryRejectedCounts[$category->id] ?? 0,
                    'placement_rate' => $applicationCount > 0 ? round(($placedCount / $applicationCount) * 100, 1) : 0,
                ];
            })
            ->sortBy([['college_name', 'asc'], ['job_count', 'desc'], ['name', 'asc']])
            ->values();

        $applicationStatusReports = ApplicationStatus::query()
            ->leftJoin('job_applications', 'job_applications.application_status_id', '=', 'application_statuses.id')
            ->select(
                'application_statuses.id',
                'application_statuses.name',
                'application_statuses.category',
                'application_statuses.sort_order',
                DB::raw('COUNT(job_applications.id) as application_count')
            )
            ->groupBy(
                'application_statuses.id',
                'application_statuses.name',
                'application_statuses.category',
                'application_statuses.sort_order'
            )
            ->orderBy('application_statuses.sort_order')
            ->get();

        $applicationStatusReportTotal = $applicationStatusReports->sum('application_count');

        $applicationStatusCategoryReports = $applicationStatusReports
            ->groupBy('category')
            ->map(fn ($statuses, $category) => [
                'category' => $category,
                'application_count' => $statuses->sum('application_count'),
            ])
            ->values();

        return [
            'totalUsers' => $totalUsers,
            'totalAdmins' => $totalAdmins,
            'totalSuperAdmins' => $totalSuperAdmins,
            'totalRegularAdmins' => $totalRegularAdmins,
            'activeAdmins' => $activeAdmins,
            'totalActiveUsers' => $totalActiveUsers,
            'activeSuperAdmins' => $activeSuperAdmins,
            'activeRegularAdmins' => $activeRegularAdmins,
            'totalStaffUsers' => $totalStaffUsers,
            'activeStaffUsers' => $activeStaffUsers,
            'totalJobs' => $totalJobs,
            'pendingJobs' => $pendingJobs,
            'activeJobs' => $activeJobs,
            'blockedJobs' => $blockedJobs,
            'featuredJobs' => $featuredJobs,
            'totalEmployers' => $totalEmployers,
            'activeEmployers' => $activeEmployers,
            'pendingEmployers' => $pendingEmployers,
            'blockedEmployers' => $blockedEmployers,
            'totalStudents' => $totalStudents,
            'activeStudents' => $activeStudents,
            'pendingApprovalStudents' => $pendingApprovalStudents,
            'blockedStudents' => $blockedStudents,
            'totalApplications' => JobApplication::count(),
            'placementTotalApplications' => $placementTotalApplications,
            'placedApplications' => $placedApplications,
            'placementRate' => $placementRate,
            'collegePlacementReports' => $collegePlacementReports,
            'collegeCategoryCounts' => $collegeCategoryCounts,
            'interviewedApplications' => $interviewedApplications,
            'interviewPlacedApplications' => $interviewPlacedApplications,
            'interviewConversionRate' => $interviewConversionRate,
            'interviewConversionByProgramme' => $interviewConversionByProgramme,
            'collegeOptions' => $collegeOptions,
            'programmeOptions' => $programmeOptions,
            'employerOptions' => $employerOptions,
            'yearOptions' => $yearOptions,
            'opportunityTypeOptions' => $opportunityTypeOptions,
            'categoryOptions' => $categoryOptions,
            'pendingApplications' => $pendingApplications,
            'recentApplications' => $recentApplications,
            'jobsByCategory' => $jobsByCategory,
            'categoryReports' => $categoryReports,
            'applicationStatusReports' => $applicationStatusReports,
            'applicationStatusReportTotal' => $applicationStatusReportTotal,
            'applicationStatusCategoryReports' => $applicationStatusCategoryReports,
            'rejectedApplications' => $rejectedApplications,
            'activeApplications' => $activeApplications,
            'unsuccessfulApplications' => $unsuccessfulApplications,
            'rejectionRate' => $rejectionRate,
            'rejectionByYear' => $rejectionByYear,
            'rejectionByMonth' => $rejectionByMonth,
            'rejectionByCollege' => $rejectionByCollege,
            'rejectionByProgramme' => $rejectionByProgramme,
            'rejectionByCategory' => $rejectionByCategory,
            'rejectionByEmployer' => $rejectionByEmployer,
            'rejectionByOpportunityType' => $rejectionByOpportunityType,
            'yearlyFunnelReports' => $yearlyFunnelReports,
            'employerPerformanceReports' => $employerPerformanceReports,
            'funnelReports' => $funnelReports,
            'applicationMetrics' => $applicationMetrics,
            'studentMetrics' => $studentMetrics,
        ];
    }

    private function buildExportRows(string $report, array $dashboard): array
    {
        return match ($report) {
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
                ['--- SYSTEM INFORMATION ---', ''],
                ['Total Users (Super Admin/Admins/Employers/Students)', $dashboard['totalUsers']],
                ['Total Users (Super Admin/Admins)', $dashboard['totalStaffUsers']],
                ['Total Active Users (Super Admin/Admins)', $dashboard['activeStaffUsers']],
                ['Total Super Admin', $dashboard['totalSuperAdmins']],
                ['Total Active Super Admin', $dashboard['activeSuperAdmins']],
                ['Total Admin Users', $dashboard['totalRegularAdmins']],
                ['Total Active Admin Users', $dashboard['activeRegularAdmins']],
                ['Total Colleges/Centers', $dashboard['collegeCategoryCounts']->count()],
                ['Total Categories', $dashboard['collegeCategoryCounts']->sum('categories_count')],
            ],
            'placement' => [
                ['Metric', 'Value'],
                ['Total Applications', $dashboard['placementTotalApplications']],
                ['Students Placed', $dashboard['placedApplications']],
                ['Placement Rate', number_format($dashboard['placementRate'], 1) . '%'],
                ['Interviewed Applications', $dashboard['interviewedApplications']],
                ['Interview Conversion Rate', number_format($dashboard['interviewConversionRate'], 1) . '%'],
            ],
            'applications' => collect($dashboard['applicationStatusReports'])->map(function ($row) use ($dashboard) {
                $percentage = $dashboard['applicationStatusReportTotal'] > 0
                    ? round(($row->application_count / $dashboard['applicationStatusReportTotal']) * 100)
                    : 0;
                return [
                    'Status' => $row->name,
                    'Category' => $row->category,
                    'Applications' => $row->application_count,
                    'Share of Applications' => $percentage . '%',
                ];
            })
                ->push([
                    'Status' => 'Total',
                    'Category' => '',
                    'Applications' => $dashboard['applicationStatusReportTotal'],
                    'Share of Applications' => '100%',
                ])
                ->toArray(),
            'rejection' => [
                ['Metric', 'Value'],
                ['Rejected Applications', $dashboard['rejectedApplications']],
                ['Rejection Rate', number_format($dashboard['rejectionRate'], 1) . '%'],
                ['Active Applications', $dashboard['activeApplications']],
                ['Unsuccessful Applications', $dashboard['unsuccessfulApplications']],
            ],
            'employer' => collect($dashboard['employerPerformanceReports'])->map(fn ($row) => [
                'Employer' => $row->employer_name,
                'Applications' => $row->application_count,
                'Interviewed' => $row->interviewed_count,
                'Placed' => $row->placed_count,
                'Rejected' => $row->rejected_count,
            ])->toArray(),
            'funnel' => collect($dashboard['funnelReports'])->map(fn ($row) => [
                'Stage' => $row['name'],
                'Count' => $row['count'],
                'Drop Off' => $row['drop_off'],
                'Drop Off Rate' => $row['drop_off_rate'] . '%',
                'Progression from Start' => $row['conversion_from_start'] . '%',
            ])->toArray(),
            'categories' => collect($dashboard['categoryReports'])->map(fn ($row) => [
                'Category' => $row->name,
                'College' => $row->college_name,
                'Jobs' => $row->job_count,
                'Applications' => $row->application_count,
                'Placed' => $row->placed_count,
                'Rejected' => $row->rejected_count,
                'Placement Rate' => number_format($row->placement_rate, 1) . '%',
            ])->toArray(),
            default => [
                ['Report', 'Value'],
                ['Selected Report', $report],
                ['Status', 'No export data available'],
            ],
        };
    }

    private function renderPdfHtml(string $title, array $rows, ?Request $request = null): string
    {
        // If rows are associative (keyed), use the keys as table headers
        $firstRow = $rows[0] ?? null;
        $isAssoc = is_array($firstRow) && array_keys($firstRow) !== range(0, count($firstRow) - 1);

        $headerHtml = '';
        $bodyRows = $rows;

        if ($isAssoc) {
            $headerHtml = '<thead><tr>'
                . collect(array_keys($firstRow))->map(fn ($h) => '<th>' . e($h) . '</th>')->implode('')
                . '</tr></thead>';
        } else {
            // First numeric row is a header if it looks like one (e.g. ['Metric','Value'])
            $firstIsHeader = is_array($firstRow)
                && count($firstRow) === 2
                && $firstRow[0] === 'Metric' && $firstRow[1] === 'Value';
            if ($firstIsHeader) {
                $headerHtml = '<thead><tr>'
                    . collect($firstRow)->map(fn ($h) => '<th>' . e($h) . '</th>')->implode('')
                    . '</tr></thead>';
                $bodyRows = array_slice($rows, 1);
            }
        }

        $htmlRows = '';
        foreach ($bodyRows as $row) {
            $cells = collect($row)->map(fn ($value) => '<td>' . e($value) . '</td>')->implode('');
            $htmlRows .= '<tr>' . $cells . '</tr>';
        }

        $downloadedBy = $request && $request->user() ? $request->user()->name . ' (' . $request->user()->email . ')' : 'System';
        $downloadedAt = now()->format('F j, Y \a\t g:i A');

        return '<!DOCTYPE html><html><head><meta charset="utf-8"><style>'
            . '@page{size:A4 landscape;margin:40px 40px 60px 40px}'
            . 'body{font-family:Arial,sans-serif;padding:0;margin:0;font-size:12px;color:#000}'
            . '.brand{font-size:22px;font-weight:700;color:#174a68;margin:0 0 2px;text-align:left}'
            . '.report-name{font-size:15px;font-weight:600;color:#444;margin:0 0 6px;text-align:left}'
            . 'table{width:100%;border-collapse:collapse;margin-top:10px;page-break-inside:auto}'
            . 'tr{page-break-inside:avoid;page-break-after:auto}'
            . 'th,td{border:1px solid #ddd;padding:8px;text-align:left;font-size:12px}'
            . 'th{background:#f3f4f6;font-weight:700}'
            . '.report-footer{position:fixed;bottom:-40px;left:0;right:0;height:40px;border-top:1px solid #ddd;padding-top:6px;font-size:10px;color:#555;}'
            . '.report-footer .left{float:left}'
            . '.report-footer .right{float:right}'
            . '@media print{body{-webkit-print-color-adjust:exact;print-color-adjust:exact}}'
            . '</style></head><body>'
            . '<div class="brand">FNU Job Placement</div>'
            . '<div class="report-name">' . e($title) . ' Report</div>'
            . '<table>' . $headerHtml . '<tbody>' . $htmlRows . '</tbody></table>'
            . '<div class="report-footer">'
            . '<span class="left">Downloaded: ' . e($downloadedAt) . ' &nbsp;|&nbsp; Downloaded by: ' . e($downloadedBy) . '</span>'
            . '</div>'
            . '</body></html>';
    }

    private function getYearExpression(string $column): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', {$column}) AS INTEGER)"
            : "YEAR({$column})";
    }

    private function getMonthExpression(string $column): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }

    /**
     * Count applications and rejections grouped by the given SQL expression.
     */
    protected function rejectionBreakdown($baseQuery, string $selectExpression, string $alias)
    {
        $totals = (clone $baseQuery)
            ->select(DB::raw("{$selectExpression} as {$alias}"), DB::raw('COUNT(job_applications.id) as application_count'))
            ->groupBy(DB::raw($selectExpression))
            ->get();

        $rejectedCounts = (clone $baseQuery)
            ->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Rejected'))
            ->select(DB::raw("{$selectExpression} as {$alias}"), DB::raw('COUNT(job_applications.id) as rejected_count'))
            ->groupBy(DB::raw($selectExpression))
            ->pluck('rejected_count', $alias);

        return $totals->map(function ($row) use ($rejectedCounts, $alias) {
            $row->rejected_count = $rejectedCounts[$row->$alias] ?? 0;
            $row->rejection_rate = $row->application_count > 0
                ? round(($row->rejected_count / $row->application_count) * 100, 1)
                : 0;
            return $row;
        })->sortByDesc('rejection_rate')->values();
    }
}
