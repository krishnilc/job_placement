<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use Illuminate\Http\Request;

class JobsController extends Controller
{
   public function index()
   {
    $categories = Category::where('status', 1)->get(); // Retrieve active categories to display in the jobs listing page
    $jobTypes = JobType::where('status', 1)->get(); // Retrieve active job types to display in the jobs listing page

    $jobs = Job::where('status', 1)->with('jobType')->orderBy('created_at', 'desc')->paginate(9); // Retrieve active jobs with their associated category and job type, ordered by creation date, and paginate the results

       // Logic to retrieve and display all jobs will go here
       return view('front.jobs', [
        'categories' => $categories,
        'jobTypes' => $jobTypes,
        'jobs' => $jobs
       ]);
   }
}
