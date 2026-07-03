<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use App\Models\Job;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\File;

class AccountController extends Controller
{
    public function index()
    {
        // Total available jobs
        $totalJobs = Job::where('status', 1)->count();

        // Total saved jobs by logged-in user
        $savedJobsCount = SavedJob::where('user_id', Auth::user()->id)->count();

        // Total applications submitted by logged-in user
        $appliedJobsCount = JobApplication::where('user_id', Auth::user()->id)->count(); 

        // Available jobs count (exclude already applied jobs)
        $availableJobs = max(0, $totalJobs - $appliedJobsCount);

        // Latest jobs excluding those already applied to by the current user
        $latestJobs = Job::where('status', 1)
            ->whereDoesntHave('applications', function ($query) {
                $query->where('user_id', Auth::user()->id);
            }) 
            ->with(['jobType'])
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        return view('front.account.student-dashboard', [
            'totalJobs' => $totalJobs,
            'savedJobsCount' => $savedJobsCount,
            'appliedJobsCount' => $appliedJobsCount,
            'availableJobs' => $availableJobs,
            'latestJobs' => $latestJobs
        ]);
    }

    //This method will show employer dashboard
    public function employerDashboard()
    {
        $userId = Auth::user()->id;

        // Total jobs posted by the employer
        $totalJobs = Job::where('user_id', $userId)->count();

        // Total job applications received
        $totalApplications = JobApplication::whereHas('job', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();

        // Pending applications
        $pendingApplications = JobApplication::whereHas('job', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('status', 'pending')->count();

        // Recent job applications
        $recentApplications = JobApplication::whereHas('job', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with(['user', 'job'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Recent jobs posted by employer
        $recentJobs = Job::where('user_id', $userId)
            ->with(['jobType'])
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        return view('front.account.employer-dashboard', [
            'totalJobs' => $totalJobs,
            'totalApplications' => $totalApplications,
            'pendingApplications' => $pendingApplications,
            'recentApplications' => $recentApplications,
            'recentJobs' => $recentJobs
        ]);
    }

    //This method will show user registration form
    public function registration()
    {
        return view('front.account.registration');
    }

    //This method will save user registration data to database
    public function processRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required|same:password',
            'role' => 'required|in:student,employer',
        ]);

        if ($validator->passes()) {
            $user = new User();

            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password); // Hash the password before saving
            // Set the role based on the selected option in the radio button (student or employer)
            $user->role = $request->role;
            $user->save();

            session()->flash('success', 'Registration successful! ');

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

    //This method will show user login form
    public function login()
    {
        return view('front.account.login');
    }

    //This method will authenticate user login credentials
    public function authenticate(Request $request)
    {
        // Authentication logic will go here
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->passes()) {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                // Authentication passed...
                // Check user role and redirect accordingly
                if (Auth::user()->role === 'admin') {
                    return redirect()->route('admin.dashboard')
                        ->with('success', 'Login successful! Welcome back.');
                } elseif (Auth::user()->role === 'employer') {
                    return redirect()->route('account.employer-dashboard')
                        ->with('success', 'Login successful! Welcome back.');
                } else {
                    return redirect()->route('account.dashboard')
                        ->with('success', 'Login successful! Welcome back.');
                }
            } else {
                return redirect()->route('account.login')
                    ->with('error', 'Invalid credentials. Please try again.');
            }
        } else {
            return redirect()->route('account.login')
                ->withErrors($validator)
                ->withInput($request->only('email')); // Redirect back with validation errors and old input
        }
    }

    //This method will show user profile page
    public function profile()
    {
        $id = Auth::user()->id;
        // dd($id); // Debugging statement to check if the user ID is being retrieved correctly

        // $user = User::where('id', $id)->first();
        $user = User::find($id);
        //dd($user); // Debugging statement to check if the user data is being retrieved correctly

        return view('front.account.profile', [
            'user' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        $id = Auth::user()->id;

        // Validation rules for profile update
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:5|max:20',
            'email' => 'required|email|unique:users,email,' . $id . ',id', // Ensure email is unique except for the current user
            'mobile' => 'required|digits:7',
            // 'password' => 'nullable|min:5|same:confirm_password',
            // 'confirm_password' => 'nullable|same:password',
        ]);

        if ($validator->passes()) {
            $user = User::find($id);

            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->designation = $request->designation;

            $user->save();

            session()->flash('success', 'Profile updated successfully!');

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
    public function logout()
    {
        Auth::logout();
        return redirect()->route('account.login');
    }

    public function updateProfilePic(Request $request)
    {
        $id = Auth::user()->id;

        $validator = Validator::make($request->all(), [
            'profile_pic' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->passes()) {
            $image = $request->file('profile_pic'); // Get the uploaded file
            $extension = $image->getClientOriginalExtension(); // Get the file extension
            $imageName = $id . '_' . time() . '.' . $extension; // Create a unique filename using the current timestamp
            $image->move(public_path('/profile_pic'), $imageName); // Move the file to the public/profile_pic directory

            /// Image processing using Intervention Image library - cropping and resizing the uploaded image
            $sourcePath = public_path('/profile_pic/' . $imageName); // Get the path of the uploaded image
            $manager = new ImageManager(Driver::class); // Create an instance of the Intervention Image Manager using the GD driver
            $image = $manager->read($sourcePath); // Read the uploaded image


            // crop the best fitting 5:3 (600x360) ratio and resize to 600x360 pixel
            $image->cover(150, 150); // Crop the image to a 5:3 ratio (600x360) while maintaining the center of the image
            $image->toPng()->save(public_path('/profile_pic/thumb/' . $imageName)); // Save the cropped image as a PNG file in the public/profile_pic directory with a "thumb" prefix

            //Delete old profile picture if exists
            File::delete(public_path('/profile_pic/' . Auth::user()->image)); // Delete the old profile picture from the public/profile_pic directory
            File::delete(public_path('/profile_pic/thumb/' . Auth::user()->image)); // Delete the old thumbnail profile picture from the public/profile_pic/thumb directory

            User::where('id', $id)->update(['image' => $imageName]); // Update the user's profile picture in the database

            session()->flash('success', 'Profile picture updated successfully!'); // Flash a success message to the session

            return response()->json([
                'status' => true,
                'errors' => []
            ]);

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

    public function createJob()
    {
        $categories = Category::orderBy('name', 'ASC')->where('status', '1')->get();
        $jobTypes = JobType::orderBy('name', 'ASC')->where('status', '1')->get();


        return view('front.account.job.create_job', [
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
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $job = new Job();

            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->job_type;
            $job->user_id = Auth::id();
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

            session()->flash('success', 'Job created successfully!');
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
        $jobs = Job::where('user_id', Auth::user()->id)->with('jobType')->orderBy('created_at', 'desc')->paginate(10); // Retrieve jobs posted by the authenticated user

        // Logic to retrieve and display the jobs posted by the authenticated user will go here
        return view('front.account.job.my_jobs', [
            'jobs' => $jobs
        ]);
    }

    public function editJob(Request $request, $id)
    {

        $categories = Category::orderBy('name', 'ASC')->where('status', '1')->get();
        $jobTypes = JobType::orderBy('name', 'ASC')->where('status', '1')->get();

        $job = Job::where([
            'user_id' => Auth::user()->id,
            'id' => $id
        ])->first();

        if (!$job) {
            abort(404); // Job not found or does not belong to the authenticated user
        }

        return view('front.account.job.edit_job', [
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
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $job = job::find($id);

            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->job_type;
            $job->user_id = Auth::id();
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

    public function deleteJob(Request $request)
    {
        $job = Job::where([
            'user_id' => Auth::user()->id, // Ensure that the job belongs to the authenticated user
            'id' => $request->jobId // Find the job by its ID
        ])->first();

        if (!$job) {
            session()->flash('error', 'Job not found or you do not have permission to delete this job!');
            return response()->json([
                'status' => false,
                'errors' => ['Job not found or you do not have permission to delete this job!']
            ]);
            // abort(404); // Job not found or does not belong to the authenticated user
        }

        //$job->delete(); 
        Job::where('id', $request->jobId)->delete(); // Permanently delete the job from the database
        session()->flash('success', 'Job deleted successfully!');

        return response()->json([
            'status' => true
        ]);
    }

    public function myJobApplications(Request $request)
    {
        $jobApplications = JobApplication::where('user_id', Auth::user()->id)
            ->with(['job', 'job.JobType', 'job.applications'])
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Retrieve job applications submitted by the authenticated user

        // dd($jobApplications); // Debugging statement to check if the job applications are being retrieved correctly
        return view('front.account.job.my-job-applications', [
            'jobApplications' => $jobApplications
        ]);
    }

    public function removeJobApplication(Request $request)
    {
        $jobApplication = JobApplication::where([
            'id' => $request->id, // Find the job application by its ID
            'user_id' => Auth::user()->id, // Ensure that the job application belongs to the authenticated user            
        ])->first();

        if ($jobApplication == null) {
            session()->flash('error', 'Job application not found or you do not have permission to remove this application!');
            return response()->json([
                'status' => false,
                'errors' => ['Job application not found or you do not have permission to remove this application!']
            ]);
        }

        JobApplication::find($request->id)->delete(); // Permanently delete the job application from the database

        session()->flash('success', 'Job application removed successfully!');
        return response()->json([
            'status' => true
        ]);
    }

    public function savedJobs(Request $request)
    {
        $savedJobs = SavedJob::where([
            'user_id' => Auth::user()->id
        ])->with(['job', 'job.jobType', 'job.applications'])->orderBy('created_at', 'desc')->paginate(10); // Retrieve saved jobs by the authenticated user

        // dd($savedJobs); // Debugging statement to check if the saved jobs are being retrieved correctly
        return view('front.account.job.saved-jobs', [
            'savedJobs' => $savedJobs
        ]);
    }

    public function removeSavedJob(Request $request)
    {
        $savedJob = SavedJob::where([
            'id' => $request->id,
            'user_id' => Auth::user()->id
        ])->first();

        if ($savedJob) {
            $savedJob->delete();
            session()->flash('success', 'Saved job removed successfully');
        } else {
            session()->flash('error', 'Saved job not found');
        }

        return response()->json([
            'status' => true
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required|same:new_password',
        ]);

        if ($validator->passes()) {
            $user = User::find(Auth::id());

            if (Hash::check($request->old_password, $user->password)) {
                $user->password = Hash::make($request->new_password);
                $user->save();

                session()->flash('success', 'Password changed successfully!');

                return response()->json([
                    'status' => true,
                    'errors' => []
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'errors' => ['old_password' => ['Old password is incorrect.']]
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function forgotPassword()
    {
        return view('front.account.forgot-password');
    }
}
