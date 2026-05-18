<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    public function index()
    {
        $jobs = Job::orderBy('created_at', 'desc')->with('user', 'applications')->paginate(10);

        return view('admin.jobs.list', [
            'jobs' => $jobs
        ]);
    }

    public function edit($id)
    {
        $job = Job::findOrFail($id); // Find the job by ID or throw a 404 error if not found
        $categories = Category::orderBy('name', 'ASC')->get(); // Fetch all categories to populate the dropdown in the edit form
        $jobTypes = JobType::orderBy('name', 'ASC')->get(); // Fetch all job types to populate the dropdown in the edit form

        return view('admin.jobs.edit', [
            'job' => $job,
            'categories' => $categories,
            'jobTypes' => $jobTypes
        ]);
    }

     public function update(Request $request, $id)
    {
        $rules = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'job_type' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required|min:3|max:75',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $job = job::find($id);

            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->job_type;    
            $job->vacancy = $request->vacancy;
            $job->salary = $request->salary;
            $job->location = $request->location;
            $job->description = $request->description;
            $job->benefits = $request->benefits;
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
}
