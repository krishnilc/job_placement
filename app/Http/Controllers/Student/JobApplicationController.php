<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobApplicationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $jobApplications = JobApplication::where('user_id', Auth::user()->id)
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';
                $query->whereHas('job', function ($j) use ($like) {
                    $j->where('title', 'like', $like)
                        ->orWhere('company_name', 'like', $like)
                        ->orWhere('location', 'like', $like);
                });
            })
            ->with(['job', 'job.JobType', 'job.applications', 'applicationStatus', 'studentFeedback'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString(); // Retrieve job applications submitted by the authenticated user

        return view('student.job-applications.index', [
            'jobApplications' => $jobApplications,
        ]);
    }

    public function destroy(Request $request)
    {
        $jobApplication = JobApplication::where([
            'id' => $request->id, // Find the job application by its ID
            'user_id' => Auth::user()->id, // Ensure that the job application belongs to the authenticated user
        ])->first();

        if ($jobApplication == null) {
            session()->flash('error', 'Job application not found or you do not have permission to remove this application!');

            return response()->json([
                'status' => false,
                'errors' => ['Job application not found or you do not have permission to remove this application!'],
            ]);
        }

        JobApplication::find($request->id)->delete(); // Permanently delete the job application from the database

        session()->flash('success', 'Job application removed successfully!');

        return response()->json([
            'status' => true,
        ]);
    }
}
