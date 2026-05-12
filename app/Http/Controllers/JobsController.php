<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

   public function applyJob(Request $request)
   {
      $id = $request->job_id; // Get the job ID from the request input

      $job = Job::where('id', $id)->where('status', 1)->first(); // Retrieve the job with the specified ID and ensure it is active

      if ($job == null) {
         session()->flash('error', 'Job not found'); // Flash an error message to the session if the job is not found

         return response()->json([
            'status' => false,
            'message' => 'Job not found'
         ]); // Return a JSON response with a 404 status code if the job is not found   .
      }

      //you cannot aply on your own job
      $employer_id = $job->user_id; // Get the employer ID associated with the job

      if ($employer_id == Auth::user()->id) {
         session()->flash('error', 'You cannot apply for your own job'); // Flash an error message to the session if the user is trying to apply for their own job   

         return response()->json([
            'status' => false,
            'message' => 'You cannot apply for your own job'
         ]);
      }

      // Check if the user has already applied for the job
      $jobApplicationCount = JobApplication::where([
         'user_id' => Auth::user()->id,
         'job_id' => $id
      ])->count();

      if ($jobApplicationCount > 0) {
         session()->flash('error', 'You have already applied for this job'); // Flash an error message to the session if the user has already applied for the job 

         return response()->json([
            'status' => false,
            'message' => 'You have already applied for this job'
         ]); // Return a JSON response indicating that the user has already applied for the job
      }
      // Logic to handle job application will go here
      $application = new JobApplication(); // Create a new instance of the Application model
      $application->job_id = $id; // Set the job ID on the application
      $application->user_id = Auth::user()->id; // Set the user ID on the application to the currently authenticated user's ID
      $application->employer_id = $employer_id; // Set the employer ID on the application to the employer ID associated with the job
      $application->applied_at = now(); // Set the application date to the current date and time
      $application->save(); // Save the application to the database

      session()->flash('success', 'You have successfully applied for the job'); // Flash a success message to the session if the application is successful
      return response()->json([
         'status' => true,
         'message' => 'You have successfully applied for the job'
      ]); // Return a JSON response indicating that the application was successful
   }
}
