<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\SavedJob;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // This method will show the student dashboard
    public function index()
    {
        // Total available jobs
        $totalJobs = Job::where('status', 1)->count();

        // Total saved jobs by logged-in user
        $savedJobsCount = SavedJob::where('user_id', Auth::user()->id)->count();

        // Total applications submitted by logged-in user
        $appliedJobsCount = JobApplication::where('user_id', Auth::user()->id)->count();

        // Available jobs count (exclude already applied jobs)
        $availableJobs = max(0, $totalJobs - $appliedJobsCount);

        // Latest jobs excluding those already applied to by the current user
        $latestJobs = Job::where('status', 1)
            ->whereDoesntHave('applications', function ($query) {
                $query->where('user_id', Auth::user()->id);
            })
            ->with(['jobType'])
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        return view('student.dashboard', [
            'totalJobs' => $totalJobs,
            'savedJobsCount' => $savedJobsCount,
            'appliedJobsCount' => $appliedJobsCount,
            'availableJobs' => $availableJobs,
            'latestJobs' => $latestJobs,
        ]);
    }
}
