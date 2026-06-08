<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $totalUsers = User::count();
        $totalJobs = Job::count();
        $totalApplications = JobApplication::count();
        $pendingApplications = JobApplication::whereNull('status')->count();
        
        // Get recent job applications
        $recentApplications = JobApplication::with(['job', 'user', 'employer'])
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
        
        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalJobs' => $totalJobs,
            'totalApplications' => $totalApplications,
            'pendingApplications' => $pendingApplications,
            'recentApplications' => $recentApplications,
            'jobsByCategory' => $jobsByCategory,
        ]);
    }
}
