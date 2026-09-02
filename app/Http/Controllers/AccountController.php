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


    //This method will show user registration form
    public function registration()
    {
        return view('front.account.registration');
    }

    //This method will save user registration data to database
    public function processRegistration(Request $request)
    {
        $role = in_array($request->input('role'), ['student', 'employer'], true) ? $request->input('role') : 'student';

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|digits:7',
            'password' => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required|same:password',
            'role' => 'required|in:student,employer',
            'student_id' => $role === 'student' ? 'required|string|max:9|unique:users,student_id' : 'nullable|string|max:9',
            'designation' => $role === 'employer' ? 'required|string|max:100' : 'nullable',
            'company_name' => $role === 'employer' ? 'required|string|max:255' : 'nullable',
            'company_address' => $role === 'employer' ? 'required|string|max:1000' : 'nullable',
        ], [
            'student_id.unique' => 'The University Student ID has already been taken. Please enter a unique one.',
        ]);

        if ($validator->passes()) {
            $user = new User();

            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->password = Hash::make($request->password); // Hash the password before saving
            // Set the role based on the selected option in the radio button (student or employer)
            $user->role = $role;
            $user->status = 'pending';
            if ($role === 'student') {
                $user->student_id = $request->student_id;
            } else {
                $user->student_id = null;
                $user->designation = $request->designation;
                $user->company_name = $request->company_name;
                $user->company_address = $request->company_address;
            }
            $user->save();

            session()->flash('success', 'Registration successful! Your account is pending administrator approval.');

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
                if (in_array(Auth::user()->role, ['student', 'employer'], true) && Auth::user()->status !== 'active') {
                    $status = Auth::user()->status;
                    Auth::logout();

                    $message = $status === 'blocked'
                        ? 'Your account has been blocked. Please contact the administrator.'
                        : 'Your account is pending administrator approval.';

                    return redirect()->route('account.login')->with('error', $message);
                }

                // Authentication passed...
                // Check user role and redirect accordingly
                if (Auth::user()->role === 'admin') {
                    return redirect()->route('admin.dashboard')
                        ->with('success', 'Login successful! Welcome back.');
                } elseif (Auth::user()->role === 'employer') {
                    return redirect()->route('employer.dashboard')
                        ->with('success', 'Login successful! Welcome back.');
                } else {
                    return redirect()->route('student.dashboard')
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

        return view('front.account.edit-profile', [
            'user' => $user
        ]);
    }

    public function viewProfile()
    {
        return view('front.account.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function employerProfile()
    {
        return view('employer.account.edit-profile', [
            'user' => Auth::user(),
        ]);
    }

    public function employerViewProfile()
    {
        return view('employer.account.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function adminProfile()
    {
        return view('admin.account.edit-profile', [
            'user' => Auth::user(),
        ]);
    }

    public function adminViewProfile()
    {
        return view('admin.account.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $id = Auth::user()->id;

        // Validation rules for profile update
        $role = Auth::user()->role;

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:5|max:50',
            'email' => 'required|email|unique:users,email,' . $id . ',id',
            'mobile' => 'required|digits:7',
            'email_2' => 'nullable|email|max:255',
            'mobile_2' => 'nullable|digits:7',
            'designation' => in_array($role, ['admin', 'employer'], true)
                ? 'required|string|max:100'
                : 'required|in:Full-time Student,Part-time Student,Alumni',
            'company_name' => $role === 'employer' ? 'required|string|max:255' : 'nullable',
            'company_address' => $role === 'employer' ? 'required|string|max:1000' : 'nullable',
            'website_url' => $role === 'employer' ? 'nullable|url|max:255' : 'nullable',
            'company_description' => $role === 'employer' ? 'required|string|max:2000' : 'nullable',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'residential_address' => 'nullable|string|max:255',
            'postal_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'high_school' => 'nullable|string|max:255',
            'high_school_graduation_year' => 'nullable|string|max:10',
            'university' => 'nullable|string|max:255',
            'degree' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'graduation_year' => 'nullable|string|max:10',
            'skills' => 'nullable|string|max:1000',
            'bio' => 'nullable|string|max:1000',
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'availability' => 'nullable|string|max:255',
        ]);

        if ($validator->passes()) {
            $user = User::find($id);

            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->email_2 = $request->email_2;
            $user->mobile_2 = $request->mobile_2;
            $user->designation = $request->designation;
            if ($role === 'employer') {
                $user->company_name = $request->company_name;
                $user->company_address = $request->company_address;
                $user->website_url = $request->website_url;
                $user->company_description = $request->company_description;
            }
            $user->date_of_birth = $request->date_of_birth;
            $user->gender = $request->gender;
            $user->residential_address = $request->residential_address;
            $user->postal_address = $request->postal_address;
            $user->city = $request->city;
            $user->country = $request->country;
            $user->high_school = $request->high_school;
            $user->high_school_graduation_year = $request->high_school_graduation_year;
            $user->university = $request->university;
            $user->degree = $request->degree;
            $user->major = $request->major;
            $user->graduation_year = $request->graduation_year;
            $user->skills = $request->skills;
            $user->bio = $request->bio;
            $user->linkedin_url = $request->linkedin_url;
            $user->facebook_url = $request->facebook_url;
            $user->availability = $request->availability;

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
        if (!in_array(Auth::user()->role, ['admin', 'employer'], true)) {
            session()->flash('error', 'Only admins and employers can create jobs.');

            return redirect()->route('home');
        }

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

        $jobs = Job::where('user_id', Auth::id())
            ->with(['jobType', 'applications'])
            ->orderBy($sortableColumns[$sort], $direction)
            ->paginate(10)
            ->withQueryString();

        return view('front.account.job.my_jobs', [
            'jobs' => $jobs,
            'sort' => $sort,
            'direction' => $direction,
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

    public function myJobApplications(Request $request)
    {
        $jobApplications = JobApplication::where('user_id', Auth::user()->id)
            ->with(['job', 'job.JobType', 'job.applications', 'applicationStatus'])
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
        $sortableColumns = [
            'title' => 'jobs.title',
            'company_name' => 'jobs.company_name',
            'closing_date' => 'jobs.closing_date',
            'status' => 'jobs.status',
        ];
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'created_at';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $sortColumn = $sortableColumns[$sort] ?? 'saved_jobs.created_at';
        $savedJobs = SavedJob::select('saved_jobs.*')
            ->leftJoin('jobs', 'jobs.id', '=', 'saved_jobs.job_id')
            ->where('saved_jobs.user_id', Auth::id())
            ->with(['job', 'job.jobType', 'job.applications'])
            ->orderBy($sortColumn, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('front.account.job.saved-jobs', [
            'savedJobs' => $savedJobs,
            'sort' => $sort,
            'direction' => $direction,
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

    // This method will handle the password update request
    public function editPassword()
    {
        return view('front.account.edit-password');
    }

    public function employerEditPassword()
    {
        return view('employer.account.edit-password');
    }

    public function adminEditPassword()
    {
        return view('admin.account.edit-password');
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
