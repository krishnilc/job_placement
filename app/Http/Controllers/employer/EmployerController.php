<?php

namespace App\Http\Controllers\employer;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployerController extends Controller
{
     //This method will show employer dashboard
    public function index()
    {
        $userId = Auth::user()->id;

        // Total jobs posted by the employer
        $totalJobs = Job::where('user_id', $userId)->count();

        $studentUsers = User::where('role', 'student')->count();
        $blockedJobs = Job::where('user_id', $userId)->where('status', 2)->count();
        $featuredJobs = Job::where('user_id', $userId)->where('isFeatured', 1)->count();

        // Total job applications received
        $totalApplications = JobApplication::whereHas('job', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();

        // Pending applications
        $pendingApplications = JobApplication::whereHas('job', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('status', 'pending')->count();

        // Recent job applications
        $recentApplications = JobApplication::whereHas('job', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with(['user', 'job'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Recent jobs posted by employer
        $recentJobs = Job::where('user_id', $userId)
            ->with(['jobType'])
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        return view('employer.employer-dashboard', [
            'totalJobs' => $totalJobs,
            'studentUsers' => $studentUsers,
            'blockedJobs' => $blockedJobs,
            'featuredJobs' => $featuredJobs,
            'totalApplications' => $totalApplications,
            'pendingApplications' => $pendingApplications,
            'recentApplications' => $recentApplications,
            'recentJobs' => $recentJobs
        ]);
    }

}
