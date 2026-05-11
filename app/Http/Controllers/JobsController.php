<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use Illuminate\Http\Request;

class JobsController extends Controller
{
   public function index(Request $request)
   {
      $categories = Category::where('status', 1)->get(); // Retrieve active categories to display in the jobs listing page
      $jobTypes = JobType::where('status', 1)->get(); // Retrieve active job types to display in the jobs listing page

      $jobs = Job::where('status', 1); // Start building the query to retrieve active jobs

      //Search using keywords
      if (!empty($request->keywords)) {
         $jobs = $jobs->where(function ($query) use ($request) {
            $query->orWhere('title', 'like', '%' . $request->keywords . '%');
            $query->orWhere('keywords', 'like', '%' . $request->keywords . '%');
         });
      }

      //Search using location
      if (!empty($request->location)) {
         $jobs = $jobs->where('location', $request->location);
      }

      //Search using category
      if (!empty($request->category)) {
         $jobs = $jobs->where('category_id', $request->category);
      }

      //Search using job type
      $jobTypeArray = [];
      if (!empty($request->job_type)) {
         $jobTypeArray = explode(',', $request->job_type); // Convert the comma-separated string of job types into an array
         $jobs = $jobs->whereIn('job_type_id', $jobTypeArray);
      }

      //Search using experience
      if (!empty($request->experience)) {
         $jobs = $jobs->where('experience', $request->experience);
      }

      $jobs = $jobs->with('jobType', 'category');
      // $jobs = $jobs->orderBy('created_at', 'desc');

      if ($request->sort == '0') {
         $jobs = $jobs->orderBy('created_at', 'asc'); // If sort is 0, order by creation date in ascending order
      } else {
         $jobs = $jobs->orderBy('created_at', 'desc'); // Default order by creation date in descending order
      }

      $jobs = $jobs->paginate(9); // Retrieve active jobs with their associated job types, ordered by creation date, and paginate the results

      // Logic to retrieve and display all jobs will go here
      return view('front.jobs', [
         'categories' => $categories,
         'jobTypes' => $jobTypes,
         'jobs' => $jobs,
         'jobTypeArray' => $jobTypeArray
      ]);
   }

   //this method will show job detail page
   public function detail($id)
   {
      $job = Job::where(['id' => $id, 'status' => 1])->with(['jobType', 'category'])->first(); // Retrieve the job with the specified ID and ensure it is active, along with its associated job type and category

      if (!$job) {
         abort(404);
      }
      return view('front.job_detail', ['job' => $job]); // Pass the job data to the job detail view
   }
}
