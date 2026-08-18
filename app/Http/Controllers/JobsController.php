<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\JobNotificationEmail;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use to;

class JobsController extends Controller
{
   public function index(Request $request)
   {
      $categories = Category::where('status', 1)->get(); // Retrieve active categories to display in the jobs listing page
      $jobTypes = JobType::where('status', 1)->get(); // Retrieve active job types to display in the jobs listing page

      $jobs = Job::where('status', 1); // Only active jobs are public

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
      $job = Job::with(['jobType', 'category'])->find($id);

      if (!$job) {
         abort(404);
      }

      $isOwner = Auth::check() && Auth::id() === $job->user_id;
      $isAdmin = Auth::check() && Auth::user()->role === 'admin';

      if ($job->status !== 1 && !$isOwner && !$isAdmin) {
         abort(404);
      }

      $count = 0; // Initialize the count variable to 0

      if (Auth::user()){
         $count = SavedJob::where([
            'job_id' => $id, 
            'user_id' => Auth::id()
         ])->count(); // Check if the currently authenticated user has already saved the job by counting the number of saved job records that match the job ID and user ID

         $isOwner = Auth::id() === $job->user_id;
      }

      //fetch applications count for the job
      $applications = JobApplication::where('job_id', $id)->with('user')->get(); //
     
      return view('front.job_detail', ['job' => $job, 'count' => $count, 'applications' => $applications, 'isOwner' => $isOwner]); // Pass the job data and save count to the job detail view
   }

   public function applyJob(Request $request)
   {
      $id = $request->job_id; // Get the job ID from the request input

      $job = Job::where('id', $id)->where('status', 1)->first(); // Retrieve only active jobs

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

      if (in_array(Auth::user()->role, ['admin', 'employer'])) {
         session()->flash('error', 'Admins and employers cannot apply for jobs');

         return response()->json([
            'status' => false,
            'message' => 'Admins and employers cannot apply for jobs'
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
      // Validate uploaded files (optional)
      $validator = \Validator::make($request->all(), [
         'application' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
         'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
         'certificates.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
      ]);

      if ($validator->fails()) {
         return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
         ], 422);
      }

      // Create new application and attach file paths if provided
      $application = new JobApplication(); // Create a new instance of the Application model
      $application->job_id = $id; // Set the job ID on the application
      $application->user_id = Auth::user()->id; // Set the user ID on the application to the currently authenticated user's ID
      $application->employer_id = $employer_id; // Set the employer ID on the application to the employer ID associated with the job
      $application->applied_at = now(); // Set the application date to the current date and time

      // Store single application file
      if ($request->hasFile('application')) {
         $file = $request->file('application');
         $fileName = 'application_' . Auth::user()->id . '_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
         $path = $file->storeAs('', $fileName, 'applications');
         $application->application_file = $path;
         $application->application_file_name = $file->getClientOriginalName();
      }

      // Store resume
      if ($request->hasFile('resume')) {
         $file = $request->file('resume');
         $fileName = 'resume_' . Auth::user()->id . '_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
         $path = $file->storeAs('', $fileName, 'applications');
         $application->resume_file = $path;
         $application->resume_file_name = $file->getClientOriginalName();
      }

      // Store certificates (allow multiple)
      $certificatePaths = [];
      $certificateNames = [];
      if ($request->hasFile('certificates')) {
         foreach ($request->file('certificates') as $file) {
            $fileName = 'certificate_' . Auth::user()->id . '_' . $id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $certificatePaths[] = $file->storeAs('', $fileName, 'applications');
            $certificateNames[] = $file->getClientOriginalName();
         }
      }

      if (!empty($certificatePaths)) {
         $application->certificates_file = json_encode($certificatePaths);
         $application->certificates_file_names = json_encode($certificateNames);
      }

      $application->save(); // Save the application to the database

      // Send a notification email to the employer about the new job application
      $employer = User::where('id', $employer_id)->first(); // Retrieve the employer's user record based on the employer ID
      $mailData = [
        'employer' => $employer, // Pass the employer's user data to the email template
        'user' => Auth::user(), // Pass the authenticated user's data to the email template
        'job' => $job, // Pass the job data to the email template
      ];
     // Mail::to($employer->email)->send(new JobNotificationEmail($mailData)); // Send a notification email to the employer using the JobNotificationEmail Mailable class   


      session()->flash('success', 'You have successfully applied for the job'); // Flash a success message to the session if the application is successful
      return response()->json([
         'status' => true,
         'message' => 'You have successfully applied for the job'
      ]); // Return a JSON response indicating that the application was successful
   }

   public function downloadApplicationFile(Request $request, JobApplication $application, $type)
   {
      if (!Auth::check()) {
         abort(403);
      }

      $userId = Auth::id();
      $user = Auth::user();
      
      // Allow if user is the applicant, employer, or admin
      if ($user->role !== 'admin' && $userId !== $application->user_id && $userId !== $application->employer_id) {
         abort(403);
      }

      if (!in_array($type, ['application', 'resume', 'certificate'])) {
         abort(404);
      }

      if ($type === 'certificate') {
         $encodedFile = $request->query('file');
         if (empty($encodedFile)) {
            abort(404);
         }
         $path = base64_decode($encodedFile, true);
      } elseif ($type === 'resume') {
         $path = $application->resume_file;
      } else {
         $path = $application->application_file;
      }

      if (empty($path)) {
         abort(404);
      }

      // Get MIME type based on extension
      $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
      $mimeTypes = [
         'pdf' => 'application/pdf',
         'doc' => 'application/msword',
         'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
         'jpg' => 'image/jpeg',
         'jpeg' => 'image/jpeg',
         'png' => 'image/png'
      ];
      $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';

      // Try applications disk first (public/assets/applications)
      $applicationsPath = public_path('assets' . DIRECTORY_SEPARATOR . 'applications' . DIRECTORY_SEPARATOR . $path);
      if (is_file($applicationsPath) && is_readable($applicationsPath)) {
         return response()->download($applicationsPath, basename($path), [
            'Content-Type' => $mimeType
         ]);
      }

      // Try public disk (storage/app/public)
      $publicPath = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $path);
      if (is_file($publicPath) && is_readable($publicPath)) {
         return response()->download($publicPath, basename($path), [
            'Content-Type' => $mimeType
         ]);
      }

      abort(404);
   }

   public function saveJob(Request $request)
   {
      $id = $request->id; // Get the job ID from the request input
      $job = Job::where(['id' => $id, 'status' => 1])->first(); // Only public jobs can be saved

      if ($job == null) {
         session()->flash('error', 'Job not found'); // Flash an error message to the session if the job is not found

         return response()->json([
            'status' => false,
            'message' => 'Job not found' 
         ]); // Return a JSON response with a 404 status code if the job is not found   .
      }

     // Check if the user has already saved the job
      $savedJobCount = SavedJob::where([
         'user_id' => Auth::user()->id,
         'job_id' => $id
      ])->count();

      if($savedJobCount > 0) {
         session()->flash('error', 'You have already saved this job'); // Flash an error message to the session if the user has already saved the job 

         return response()->json([
            'status' => false,
            'message' => 'You have already saved this job'
         ]); // Return a JSON response indicating that the user has already saved the job
      }

      if (in_array(Auth::user()->role, ['admin', 'employer'])) {
         session()->flash('error', 'Admins and employers cannot save jobs');

         return response()->json([
            'status' => false,
            'message' => 'Admins and employers cannot save jobs'
         ]);
      }

      $savedJob = new SavedJob();
      $savedJob->user_id = Auth::user()->id;
      $savedJob->job_id = $id;
      $savedJob->save();

      session()->flash('success', 'Job saved successfully'); // Flash a success message to the session if the job is saved successfully
      return response()->json([
         'status' => true,
         'message' => 'Job saved successfully'
      ]); // Return a JSON response indicating that the job was saved successfully
   }
}
