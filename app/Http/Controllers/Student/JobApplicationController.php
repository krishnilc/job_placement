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
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');

        if (! in_array($sort, ['title', 'created_at'], true)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $search = trim((string) $request->query('search', ''));

        $jobApplications = JobApplication::where('job_applications.user_id', Auth::user()->id)
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';
                $query->whereHas('job', function ($j) use ($like) {
                    $j->where('title', 'like', $like)
                        ->orWhere('company_name', 'like', $like)
                        ->orWhere('location', 'like', $like);
                });
            })
            ->with(['job', 'job.JobType', 'job.applications', 'applicationStatus', 'studentFeedback'])
            ->when($sort === 'title', fn ($query) => $query
                ->leftJoin('jobs', 'jobs.id', '=', 'job_applications.job_id')
                ->select('job_applications.*')
                ->orderBy('jobs.title', $direction))
            ->when($sort === 'created_at', fn ($query) => $query->orderBy('job_applications.created_at', $direction))
            ->paginate(10)
            ->withQueryString(); // Retrieve job applications submitted by the authenticated user

        return view('student.job-applications.index', [
            'jobApplications' => $jobApplications,
            'sort' => $sort,
            'direction' => $direction,
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

        $jobApplication->delete();

        session()->flash('success', 'Job application removed successfully!');

        return response()->json([
            'status' => true,
        ]);
    }
}
