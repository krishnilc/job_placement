<?php

namespace App\Services\Reporting;

use App\Models\ApplicationStatus;
use App\Models\Category;
use App\Models\College;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\Organization;
use App\Models\OrganizationRequest;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardReportService
{
    public function __construct(private readonly JobReportService $jobReports) {}

    public function build(Request $request): array
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
        $totalOrganizations = Organization::count();
        $organizationsWithEmployees = Organization::has('employerProfiles')->count();
        $organizationsWithJobs = Organization::has('jobs')->count();
        $pendingOrganizationRequests = OrganizationRequest::where('status', 'pending')->count();
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
        $activeManagementUsers = User::where('role', 'management')->where('status', 'active')->count();
        $totalStaffUsers = User::whereIn('role', ['admin', 'super_admin', 'management'])->count();
        $activeStaffUsers = User::whereIn('role', ['admin', 'super_admin', 'management'])->where('status', 'active')->count();
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
                ? round(($report->placed_count / $report->application_count) * 100, 2)
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
                ? round(($report->placed_count / $report->interviewed_count) * 100, 2)
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
                DB::raw($this->getYearExpression('job_applications.created_at').' as year'),
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

        // Group by the authoritative organization record (jobs.organization_id) rather than the
        // free-text jobs.company_name, so jobs posted under slightly different spellings or by
        // different employer users for the same organization are consolidated into a single row.
        // Jobs with no linked organization (legacy data) still fall back to grouping by their raw
        // company_name so distinct, unlinked companies are not merged together.
        $employerJobsQuery = (clone $applicationQuery)
            ->join('jobs', 'jobs.id', '=', 'job_applications.job_id')
            ->leftJoin('organizations', 'organizations.id', '=', 'jobs.organization_id');
        $legacyCompanyNameExpr = 'CASE WHEN jobs.organization_id IS NULL THEN jobs.company_name ELSE NULL END';
        $employerGroupKey = fn ($row) => $row->organization_id !== null
            ? 'org:'.$row->organization_id
            : 'name:'.$row->organization_name;
        $employerPerformanceReports = (clone $employerJobsQuery)
            ->select(
                'jobs.organization_id as organization_id',
                DB::raw('MIN(COALESCE(organizations.name, jobs.company_name)) as organization_name'),
                DB::raw('COUNT(job_applications.id) as application_count')
            )
            ->groupBy('jobs.organization_id', DB::raw($legacyCompanyNameExpr))
            ->get();
        $employerInterviewedCounts = (clone $employerJobsQuery)
            ->whereIn('job_applications.id', $interviewedApplicationIds)
            ->select(
                'jobs.organization_id as organization_id',
                DB::raw('MIN(COALESCE(organizations.name, jobs.company_name)) as organization_name'),
                DB::raw('COUNT(job_applications.id) as interviewed_count')
            )
            ->groupBy('jobs.organization_id', DB::raw($legacyCompanyNameExpr))
            ->get()
            ->keyBy($employerGroupKey)
            ->map->interviewed_count;
        $employerPlacedCounts = (clone $employerJobsQuery)
            ->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Placed'))
            ->select(
                'jobs.organization_id as organization_id',
                DB::raw('MIN(COALESCE(organizations.name, jobs.company_name)) as organization_name'),
                DB::raw('COUNT(job_applications.id) as placed_count')
            )
            ->groupBy('jobs.organization_id', DB::raw($legacyCompanyNameExpr))
            ->get()
            ->keyBy($employerGroupKey)
            ->map->placed_count;
        $employerRejectedCounts = (clone $employerJobsQuery)
            ->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Rejected'))
            ->select(
                'jobs.organization_id as organization_id',
                DB::raw('MIN(COALESCE(organizations.name, jobs.company_name)) as organization_name'),
                DB::raw('COUNT(job_applications.id) as rejected_count')
            )
            ->groupBy('jobs.organization_id', DB::raw($legacyCompanyNameExpr))
            ->get()
            ->keyBy($employerGroupKey)
            ->map->rejected_count;
        $employerPerformanceReports->each(function ($report) use ($employerInterviewedCounts, $employerPlacedCounts, $employerRejectedCounts, $employerGroupKey) {
            $key = $employerGroupKey($report);
            $report->interviewed_count = $employerInterviewedCounts[$key] ?? 0;
            $report->placed_count = $employerPlacedCounts[$key] ?? 0;
            $report->rejected_count = $employerRejectedCounts[$key] ?? 0;
        });
        $employerPerformanceReports = $employerPerformanceReports->sortByDesc('application_count')->values();

        $funnelReports = $this->buildRecruitmentFunnel(clone $applicationQuery);

        $shortlistedFunnelCount = $funnelReports->firstWhere('name', 'Shortlisted')['count'] ?? 0;
        $acceptedFunnelCount = $funnelReports->firstWhere('name', 'Accepted')['count'] ?? 0;
        $applicationMetrics = [
            'total_applications' => $placementTotalApplications,
            'shortlisting_rate' => $placementTotalApplications > 0 ? round(($shortlistedFunnelCount / $placementTotalApplications) * 100, 2) : 0,
            'interview_conversion_rate' => round($interviewConversionRate, 2),
            'rejection_rate' => round($rejectionRate, 2),
            'offer_conversion_rate' => $acceptedFunnelCount > 0 ? round(($placedApplications / $acceptedFunnelCount) * 100, 2) : 0,
        ];

        $funnelApplicationQuery = (clone $applicationQuery)
            ->when($request->filled('report_organization'), fn ($query) => $query->whereHas('job', fn ($jobs) => $jobs->where('organization_id', $request->input('report_organization'))))
            ->when($request->filled('funnel_job'), fn ($query) => $query->where('job_id', $request->input('funnel_job')));
        if ($request->filled('report_organization') || $request->filled('funnel_job')) {
            $funnelReports = $this->buildRecruitmentFunnel($funnelApplicationQuery);
        }

        $studentsSeekingEmployment = (clone $applicationQuery)->pluck('job_applications.user_id')->unique()->count();
        $studentsSuccessfullyPlaced = (clone $applicationQuery)
            ->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Placed'))
            ->pluck('job_applications.user_id')->unique()->count();
        $studentMetrics = [
            'students_seeking_employment' => $studentsSeekingEmployment,
            'students_successfully_placed' => $studentsSuccessfullyPlaced,
            'graduate_employment_rate' => $studentsSeekingEmployment > 0 ? round(($studentsSuccessfullyPlaced / $studentsSeekingEmployment) * 100, 2) : 0,
        ];

        $collegeOptions = College::active()->orderBy('name')->get(['id', 'name']);
        $collegeCategoryCounts = College::withCount('categories')->orderBy('name')->get(['id', 'name', 'code']);
        $programmeOptions = StudentProfile::whereNotNull('degree')->where('degree', '<>', '')->distinct()->orderBy('degree')->pluck('degree');
        $employerOptions = User::where('role', 'employer')->orderBy('name')->get(['id', 'name']);
        $yearOptions = JobApplication::whereNotNull('created_at')->get(['created_at'])->pluck('created_at')->map(fn ($date) => $date->year)->unique()->sortDesc()->values();
        $opportunityTypeOptions = JobType::orderBy('name')->get(['id', 'name']);
        $categoryOptions = Category::orderBy('name')->get(['id', 'name']);
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

        $categoryReports = Category::with('college:id,name')
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
                    'placement_rate' => $applicationCount > 0 ? round(($placedCount / $applicationCount) * 100, 2) : 0,
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

        return array_merge($this->jobReports->buildJobTypes($request), $this->jobReports->buildJobs($request, $applicationQuery), [
            'totalUsers' => $totalUsers,
            'totalAdmins' => $totalAdmins,
            'totalSuperAdmins' => $totalSuperAdmins,
            'totalRegularAdmins' => $totalRegularAdmins,
            'activeAdmins' => $activeAdmins,
            'totalActiveUsers' => $totalActiveUsers,
            'activeSuperAdmins' => $activeSuperAdmins,
            'activeRegularAdmins' => $activeRegularAdmins,
            'activeManagementUsers' => $activeManagementUsers,
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
            'totalOrganizations' => $totalOrganizations,
            'organizationsWithEmployees' => $organizationsWithEmployees,
            'organizationsWithJobs' => $organizationsWithJobs,
            'pendingOrganizationRequests' => $pendingOrganizationRequests,
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
            'funnelJobOptions' => Job::with('organization')->when($request->filled('report_organization'),
                fn ($query) => $query->where('organization_id', $request->input('report_organization')))
                ->orderBy('title')->orderBy('id')->get(),
            'selectedFunnelJob' => $request->filled('funnel_job') ? Job::with('organization')->findOrFail($request->input('funnel_job')) : null,
            'selectedFunnelOrganization' => $request->filled('report_organization') ? Organization::findOrFail($request->input('report_organization')) : null,
            'applicationMetrics' => $applicationMetrics,
            'studentMetrics' => $studentMetrics,
        ]);
    }

    private function buildRecruitmentFunnel(Builder $applicationQuery): Collection
    {
        $funnelStatuses = ApplicationStatus::whereIn('category', ['Active', 'Successful'])->orderBy('sort_order')->get();
        $funnelStatusIds = $funnelStatuses->pluck('id');
        $funnelSortOrderById = $funnelStatuses->pluck('sort_order', 'id');
        $filteredApplicationIds = (clone $applicationQuery)->pluck('job_applications.id');
        $currentStageRows = (clone $applicationQuery)->whereIn('application_status_id', $funnelStatusIds)->pluck('application_status_id', 'id');
        $historyStageRows = DB::table('application_status_history')
            ->whereIn('application_status_id', $funnelStatusIds)
            ->whereIn('job_application_id', $filteredApplicationIds)
            ->select('job_application_id', 'application_status_id')->get();
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

        return $funnelStatuses->map(function ($status) use ($maxSortOrders, &$previousStageCount, &$firstStageCount) {
            $count = $maxSortOrders->filter(fn ($sortOrder) => $sortOrder >= $status->sort_order)->count();
            $firstStageCount ??= $count;
            $dropOff = $previousStageCount !== null ? $previousStageCount - $count : 0;
            $dropOffRate = $previousStageCount ? round(($dropOff / $previousStageCount) * 100, 2) : 0;
            $conversionFromStart = $firstStageCount > 0 ? round(($count / $firstStageCount) * 100, 2) : 0;
            $previousStageCount = $count;

            return [
                'name' => $status->name, 'count' => $count, 'drop_off' => $dropOff,
                'drop_off_rate' => $dropOffRate, 'conversion_from_start' => $conversionFromStart,
            ];
        })->values();
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
    private function rejectionBreakdown(Builder $baseQuery, string $selectExpression, string $alias): Collection
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
                ? round(($row->rejected_count / $row->application_count) * 100, 2)
                : 0;

            return $row;
        })->sortByDesc('rejection_rate')->values();
    }
}
