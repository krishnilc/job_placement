<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $applications = JobApplication::orderBy('job_id', 'asc')
                ->with('job', 'user', 'employer')
                ->paginate(10);
        } elseif ($user->role === 'employer') {
            $applications = JobApplication::whereHas('job', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->with('job', 'user', 'employer')
                ->orderBy('job_id', 'asc')
                ->paginate(10);
        } else {
            $applications = JobApplication::where('user_id', $user->id)
                ->with('job', 'user', 'employer')
                ->orderBy('job_id', 'asc')
                ->paginate(10);
        }

        return view('admin.job-applications.list', [
            'applications' => $applications
        ]);
    }

    public function destroy(Request $request)
    {
        $applicationId = $request->id;
        $application = JobApplication::findOrFail($applicationId);

        if ($application) {
            $application->delete();
            session()->flash('success', 'Application deleted successfully.');
            return response()->json(['success' => true, 'message' => 'Application deleted successfully.']);
        } else {
            session()->flash('error', 'Application not found.');
            return response()->json(['success' => false, 'message' => 'Application not found.'], 404);
        }
    }
}
