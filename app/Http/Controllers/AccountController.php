<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\SavedJob;
use App\Models\User;
use App\Models\Job;
use App\Models\Organization;
use App\Models\OrganizationRequest;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    //This method will show the student dashboard
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

        return view('student.dashboard', [
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
        $inputRole = $request->input('role');
        $role = in_array($inputRole, ['student', 'alumni', 'employer'], true) ? $inputRole : 'student';
        // Alumni are stored with the 'student' role; isAlumni distinguishes them.
        $isAlumni = $role === 'alumni';
        $isStudentType = in_array($role, ['student', 'alumni'], true);
        $dbRole = $isAlumni ? 'student' : $role;

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|digits:7',
            'password' => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required|same:password',
            'role' => 'required|in:student,alumni,employer',
            'student_id' => $role === 'student' ? 'required|string|max:9|unique:student_profiles,student_id' : 'nullable|string|max:9|unique:student_profiles,student_id',
            'date_of_birth' => $isStudentType ? 'required|date|before:today' : 'nullable|date',
            'graduation_year' => $isAlumni ? 'required|integer|min:1950|max:' . date('Y') : 'nullable|integer|min:1950|max:' . date('Y'),
            'designation' => $role === 'employer' ? 'required|string|max:100' : 'nullable',
            'organization_mode' => $role === 'employer' ? 'required|in:existing,request' : 'nullable',
            'organization_id' => $role === 'employer' && $request->input('organization_mode') === 'existing'
                ? 'required|integer|exists:organizations,id' : 'nullable',
            'company_name' => $role === 'employer' && $request->input('organization_mode') === 'request'
                ? ['required', 'string', 'max:255', 'regex:/\S/u'] : 'nullable',
            'company_address' => $role === 'employer' && $request->input('organization_mode') === 'request'
                ? 'required|string|max:1000' : 'nullable',
            'company_phone' => $role === 'employer' && $request->input('organization_mode') === 'request'
                ? 'required|string|max:50' : 'nullable',
        ], [
            'student_id.unique' => 'The University Student ID has already been taken. Please enter a unique one.',
        ]);
        $validator->after(function ($validator) use ($request, $role) {
            if ($role === 'employer' && $request->input('organization_mode') === 'request'
                && is_string($request->company_name)
                && Organization::where('name_key', Organization::nameKey($request->company_name))->exists()) {
                $validator->errors()->add('company_name', 'This organization already exists. Search for it and select the existing record.');
            }
        });

        if ($validator->passes()) {
            DB::transaction(function () use ($request, $isStudentType, $isAlumni, $dbRole) {
                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->mobile = $request->mobile;
                $user->password = Hash::make($request->password);
                $user->role = $dbRole;
                $user->status = 'pending';
                if ($isStudentType) {
                    $user->student_id = $request->student_id;
                    $user->date_of_birth = $request->date_of_birth;
                    if ($isAlumni) {
                        $user->designation = 'Alumni';
                        $user->graduation_year = $request->graduation_year;
                    }
                } else {
                    $user->designation = $request->designation;
                }
                $user->save();
                if (!$isStudentType) {
                    $user->employerProfile()->create([
                        'organization_id' => $request->input('organization_mode') === 'existing' ? $request->integer('organization_id') : null,
                    ]);
                    if ($request->input('organization_mode') === 'request') {
                        OrganizationRequest::create([
                            'user_id' => $user->id, 'name' => $request->company_name,
                            'address' => $request->company_address,
                            'phone' => $request->company_phone,
                        ]);
                    }
                }
            });

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

                // Authentication passed. Check user role and redirect accordingly
                if (in_array(Auth::user()->role, ['admin', 'super_admin'], true)) {
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

    public function logout()
    {
        Auth::logout();
        return redirect()->route('account.login');
    }

    public function forgotPassword()
    {
        return view('front.account.forgot-password');
    }
}
