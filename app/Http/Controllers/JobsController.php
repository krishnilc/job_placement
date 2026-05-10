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
      if ($request->has('keywords') && !empty($request->keywords)) {
         $keywords = $request->input('keywords');
         $jobs = $jobs->where(function ($query) use ($keywords) {
            $query->where('title', 'like', '%' . $keywords . '%')
               ->orWhere('description', 'like', '%' . $keywords . '%');
         });
      }

      //Search using location
      if ($request->has('location') && !empty($request->location)) {
         $location = $request->input('location');
         $jobs = $jobs->where('location', 'like', '%' . $location . '%');
      }

      //Search using category
      if ($request->has('category') && !empty($request->category)) {
         $category = $request->input('category');
         $jobs = $jobs->where('category_id', $category);
      }

      //Search using job type
      if ($request->has('job_type') && !empty($request->job_type)) {
        $jobTypeArray =  explode('Job Type: ' . $request->input('job_type')); // Debugging line to check the value of job_type input
         $jobType = $request->input('job_type');
         $jobs = $jobs->where('job_type_id', $jobTypeArray);
      }

//Search using experience
      if ($request->has('experience') && !empty($request->experience)) {
         $experience = $request->input('experience');
         $jobs = $jobs->where('experience', $experience);
      }

      $jobs = $jobs->with('jobType')->orderBy('created_at', 'desc')->paginate(9); // Retrieve active jobs with their associated job types, ordered by creation date, and paginate the results

      // Logic to retrieve and display all jobs will go here
      return view('front.jobs', [
         'categories' => $categories,
         'jobTypes' => $jobTypes,
         'jobs' => $jobs
      ]);
   }
}
