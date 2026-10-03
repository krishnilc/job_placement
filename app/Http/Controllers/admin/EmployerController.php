<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationStatus;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployerController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['id', 'name', 'email', 'mobile', 'designation', 'company_name', 'status', 'created_at'];

        $sort = $request->query('sort', 'created_at');
        $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $search = trim((string) $request->query('search', ''));

        $users = User::where('role', 'employer')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('mobile', 'like', $like)
                        ->orWhere('designation', 'like', $like)
                        ->orWhereHas('employerProfile', fn ($p) => $p->where('company_name', 'like', $like));
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(10);
        $users->appends($request->query());

        return view('admin.employers.list', [
            'users' => $users,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,active,blocked']);

        $user = User::where('role', 'employer')->findOrFail($id);
        $user->status = $request->status;
        $user->save();

        return response()->json(['status' => true]);
    }

    public function create()
    {
        return view('admin.employers.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:5|max:50',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|digits:7',
            'designation' => 'required|string|max:100',
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string|max:1000',
            'password' => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required|min:5',
        ]);

        if ($validator->passes()) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->designation = $request->designation;
            $user->company_name = $request->company_name;
            $user->company_address = $request->company_address;
            $user->password = Hash::make($request->password);
            $user->role = 'employer';
            $user->status = 'active';
            $user->save();

            session()->flash('success', 'Employer created successfully!');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);
        }

        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ]);
    }

    //This method will show employer dashboard
    public function dashboard()
    {
        $userId = Auth::user()->id;

        // Total jobs posted by the employer
        $totalJobs = Job::where('user_id', $userId)->count();

        $studentUsers = User::where('role', 'student')->count();
        $blockedJobs = Job::where('user_id', $userId)->where('status', 2)->count();
        $featuredJobs = Job::where('user_id', $userId)->where('isFeatured', 1)->count();

        // Total job applications received
        $totalApplications = JobApplication::whereHas('job', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();

        // Pending applications
        $pendingStatusIds = ApplicationStatus::whereIn('name', ['Submitted', 'Under Review'])->pluck('id');
        $pendingApplications = JobApplication::whereHas('job', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where(function ($query) use ($pendingStatusIds) {
            $query->whereIn('application_status_id', $pendingStatusIds)
                ->orWhere(function ($legacyQuery) {
                    $legacyQuery->whereNull('application_status_id')
                        ->where(function ($statusQuery) {
                            $statusQuery->whereNull('status')->orWhere('status', 'pending');
                        });
                });
        })->count();

        // Recent job applications
        $recentApplications = JobApplication::whereHas('job', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with(['user', 'job', 'applicationStatus'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Recent jobs posted by employer
        $recentJobs = Job::where('user_id', $userId)
            ->with(['jobType'])
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        return view('employer.employer-dashboard', [
            'totalJobs' => $totalJobs,
            'studentUsers' => $studentUsers,
            'blockedJobs' => $blockedJobs,
            'featuredJobs' => $featuredJobs,
            'totalApplications' => $totalApplications,
            'pendingApplications' => $pendingApplications,
            'recentApplications' => $recentApplications,
            'recentJobs' => $recentJobs
        ]);
    }
}
