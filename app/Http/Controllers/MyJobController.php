<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\College;
use App\Models\JobType;
use App\Models\Job;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MyJobController extends Controller
{
    public function createJob()
    {
        if (!in_array(Auth::user()->role, ['admin', 'super_admin', 'employer'], true)) {
            session()->flash('error', 'Only admins and employers can create jobs.');

            return redirect()->route('home');
        }

        $colleges = College::active()->orderBy('name')->get();
        $categories = Category::orderBy('name', 'ASC')->where('status', '1')->get();
        $jobTypes = JobType::orderBy('name', 'ASC')->where('status', '1')->get();

        return view('employer.job.create_job', [
            'colleges' => $colleges,
            'categories' => $categories,
            'jobTypes' => $jobTypes
        ]);
    }

    public function saveJob(Request $request)
    {
        $rules = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'job_type' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required|min:3|max:75',
            'closing_date' => 'nullable|date',
            'experience' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $job = new Job();

            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->job_type;
            $job->user_id = Auth::id();
            $job->vacancy = $request->vacancy;
            $job->closing_date = $request->closing_date;
            $job->salary = $request->salary;
            $job->location = $request->location;
            $job->description = $request->description;
            $job->responsibilities = $request->responsibilities;
            $job->qualifications = $request->qualifications;
            $job->keywords = $request->keywords;
            $job->experience = $request->experience;
            $job->company_name = $request->company_name;
            $job->company_location = $request->company_location;
            $job->company_website = $request->company_website;
            $job->status = Auth::user()->role === 'employer' ? 0 : 1;

            $job->save();

            $message = Auth::user()->role === 'employer'
                ? 'Job submitted successfully and is awaiting admin approval.'
                : 'Job created successfully!';

            session()->flash('success', $message);
            return response()->json([
                'status' => true,
                'errors' => []
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function myJobs(Request $request)
    {
        $sortableColumns = [
            'title' => 'title',
            'company_name' => 'company_name',
            'created_at' => 'created_at',
            'closing_date' => 'closing_date',
            'status' => 'status',
            'featured' => 'isFeatured',
        ];
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'created_at';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $search = trim((string) $request->query('search', ''));

        $jobs = Job::where('user_id', Auth::id())
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('title', 'like', $like)
                        ->orWhere('company_name', 'like', $like)
                        ->orWhere('location', 'like', $like);
                });
            })
            ->with(['jobType', 'applications'])
            ->orderBy($sortableColumns[$sort], $direction)
            ->paginate(10)
            ->withQueryString();

        return view('employer.job.my_jobs', [
            'jobs' => $jobs,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function editJob(Request $request, $id)
    {

        $colleges = College::active()->orderBy('name')->get();
        $categories = Category::orderBy('name', 'ASC')->where('status', '1')->get();
        $jobTypes = JobType::orderBy('name', 'ASC')->where('status', '1')->get();

        $job = Job::where([
            'user_id' => Auth::user()->id,
            'id' => $id
        ])->first();

        if (!$job) {
            abort(404); // Job not found or does not belong to the authenticated user
        }

        return view('employer.job.edit_job', [
            'colleges' => $colleges,
            'categories' => $categories,
            'jobTypes' => $jobTypes,
            'job' => $job
        ]);
    }

    public function updateJob(Request $request, $id)
    {
        $rules = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'job_type' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required|min:3|max:75',
            'closing_date' => 'nullable|date',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $job = job::find($id);

            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->job_type;
            $job->user_id = Auth::id();
            $job->vacancy = $request->vacancy;
            $job->closing_date = $request->closing_date;
            $job->salary = $request->salary;
            $job->location = $request->location;
            $job->description = $request->description;
            $job->responsibilities = $request->responsibilities;
            $job->qualifications = $request->qualifications;
            $job->keywords = $request->keywords;
            $job->experience = $request->experience;
            $job->company_name = $request->company_name;
            $job->company_location = $request->company_location;
            $job->company_website = $request->company_website;

            $job->save();

            session()->flash('success', 'Job updated successfully!');
            return response()->json([
                'status' => true,
                'errors' => []
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function deleteJob(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            session()->flash('error', 'Only admins can delete jobs.');
            return response()->json([
                'status' => false,
                'errors' => ['Only admins can delete jobs.']
            ]);
        }

        $job = Job::where([
            'id' => $request->jobId
        ])->first();

        if (!$job) {
            session()->flash('error', 'Job not found or you do not have permission to delete this job!');
            return response()->json([
                'status' => false,
                'errors' => ['Job not found or you do not have permission to delete this job!']
            ]);
        }

        Job::where('id', $request->jobId)->delete();
        session()->flash('success', 'Job deleted successfully!');

        return response()->json([
            'status' => true
        ]);
    }

    public function blockJob(Request $request)
    {
        if (Auth::user()->role !== 'employer') {
            session()->flash('error', 'Only employers can block jobs.');
            return response()->json([
                'status' => false,
                'errors' => ['Only employers can block jobs.']
            ]);
        }

        $job = Job::where([
            'id' => $request->jobId,
            'user_id' => Auth::id()
        ])->first();

        if (!$job) {
            session()->flash('error', 'Job not found or you do not have permission to block this job!');
            return response()->json([
                'status' => false,
                'errors' => ['Job not found or you do not have permission to block this job!']
            ]);
        }

        $job->status = 2;
        $job->save();

        session()->flash('success', 'Job blocked successfully!');

        return response()->json([
            'status' => true
        ]);
    }

    public function unblockJob(Request $request)
    {
        if (Auth::user()->role !== 'employer') {
            session()->flash('error', 'Only employers can unblock jobs.');
            return response()->json([
                'status' => false,
                'errors' => ['Only employers can unblock jobs.']
            ]);
        }

        $job = Job::where([
            'id' => $request->jobId,
            'user_id' => Auth::id()
        ])->first();

        if (!$job) {
            session()->flash('error', 'Job not found or you do not have permission to unblock this job!');
            return response()->json([
                'status' => false,
                'errors' => ['Job not found or you do not have permission to unblock this job!']
            ]);
        }

        $job->status = 1;
        $job->save();

        session()->flash('success', 'Job unblocked successfully!');

        return response()->json([
            'status' => true
        ]);
    }
}
