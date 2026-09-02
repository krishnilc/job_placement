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
    public function index()
    {
        // Get statistics
        $totalUsers = User::count();
        $totalJobs = Job::count();
        $pendingJobs = Job::where('status', 0)->count();
        $blockedJobs = Job::where('status', 2)->count();
        $featuredJobs = Job::where('isFeatured', 1)->count();
        $totalApplications = JobApplication::count();
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
            'totalApplications' => $totalApplications,
            'pendingApplications' => $pendingApplications,
            'recentApplications' => $recentApplications,
            'jobsByCategory' => $jobsByCategory,
            'applicationStatusReports' => $applicationStatusReports,
            'applicationStatusCategoryReports' => $applicationStatusCategoryReports,
        ]);
    }
}
