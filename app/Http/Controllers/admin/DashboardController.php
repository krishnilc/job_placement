<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationStatus;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get statistics
        $totalUsers = User::count();
        $totalJobs = Job::count();
        $pendingJobs = Job::where('status', 0)->count();
        $blockedJobs = Job::where('status', 2)->count();
        $featuredJobs = Job::where('isFeatured', 1)->count();
        $applicationQuery = JobApplication::query();
        $applicationQuery
            ->when($request->filled('college'), fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->where('university', $request->college)))
            ->when($request->filled('programme'), fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->where('degree', $request->programme)))
            ->when($request->filled('employer'), fn ($query) => $query->whereHas('job', fn ($jobQuery) => $jobQuery->where('user_id', $request->employer)))
            ->when($request->filled('year'), fn ($query) => $query->whereYear('job_applications.created_at', $request->year))
            ->when($request->filled('opportunity_type'), fn ($query) => $query->whereHas('job', fn ($jobQuery) => $jobQuery->where('job_type_id', $request->opportunity_type)))
            ->when($request->filled('category'), fn ($query) => $query->whereHas('job', fn ($jobQuery) => $jobQuery->where('category_id', $request->category)));

        $placementTotalApplications = (clone $applicationQuery)->count();
        $placedApplications = (clone $applicationQuery)->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Placed'))->count();
        $placementRate = $placementTotalApplications > 0 ? ($placedApplications / $placementTotalApplications) * 100 : 0;
        $collegePlacementReports = (clone $applicationQuery)
            ->join('users', 'users.id', '=', 'job_applications.user_id')
            ->whereNotNull('users.university')
            ->select('users.university', DB::raw('COUNT(job_applications.id) as application_count'))
            ->groupBy('users.university')
            ->get();
        $collegePlacedCounts = (clone $applicationQuery)
            ->join('users', 'users.id', '=', 'job_applications.user_id')
            ->whereNotNull('users.university')
            ->whereHas('applicationStatus', fn ($query) => $query->where('name', 'Placed'))
            ->select('users.university', DB::raw('COUNT(job_applications.id) as placed_count'))
            ->groupBy('users.university')
            ->pluck('placed_count', 'university');
        $collegePlacementReports->each(function ($report) use ($collegePlacedCounts) {
            $report->placed_count = $collegePlacedCounts[$report->university] ?? 0;
            $report->placement_rate = $report->application_count > 0
                ? round(($report->placed_count / $report->application_count) * 100, 1)
                : 0;
        });
        $collegePlacementReports = $collegePlacementReports->sortByDesc('placement_rate')->values();
        $collegeOptions = User::whereNotNull('university')->where('university', '<>', '')->distinct()->orderBy('university')->pluck('university');
        $programmeOptions = User::whereNotNull('degree')->where('degree', '<>', '')->distinct()->orderBy('degree')->pluck('degree');
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
        
        // Get recent job applications
        $recentApplications = JobApplication::with(['job', 'user', 'employer', 'applicationStatus'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        // Get job statistics
        $jobsByCategory = Job::with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($jobs) {
                return count($jobs);
            });

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

        $applicationStatusCategoryReports = $applicationStatusReports
            ->groupBy('category')
            ->map(fn ($statuses, $category) => [
                'category' => $category,
                'application_count' => $statuses->sum('application_count'),
            ])
            ->values();
        
        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalJobs' => $totalJobs,
            'pendingJobs' => $pendingJobs,
            'blockedJobs' => $blockedJobs,
            'featuredJobs' => $featuredJobs,
            'totalApplications' => JobApplication::count(),
            'placementTotalApplications' => $placementTotalApplications,
            'placedApplications' => $placedApplications,
            'placementRate' => $placementRate,
            'collegePlacementReports' => $collegePlacementReports,
            'collegeOptions' => $collegeOptions,
            'programmeOptions' => $programmeOptions,
            'employerOptions' => $employerOptions,
            'yearOptions' => $yearOptions,
            'opportunityTypeOptions' => $opportunityTypeOptions,
            'categoryOptions' => $categoryOptions,
            'pendingApplications' => $pendingApplications,
            'recentApplications' => $recentApplications,
            'jobsByCategory' => $jobsByCategory,
            'applicationStatusReports' => $applicationStatusReports,
            'applicationStatusCategoryReports' => $applicationStatusCategoryReports,
        ]);
    }
}
