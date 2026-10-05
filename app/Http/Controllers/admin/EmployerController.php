<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationStatus;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Organization;
use App\Models\EmployerProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            ->with(['employerProfile.organization', 'organizationRequest'])
            ->when($request->filled('organization_id'), fn ($query) => $query->whereHas('employerProfile', fn ($profile) => $profile->where('organization_id', $request->input('organization_id'))))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('mobile', 'like', $like)
                        ->orWhere('designation', 'like', $like)
                        ->orWhereHas('employerProfile.organization', fn ($organization) => $organization->where('name', 'like', $like))
                        ->orWhereHas('organizationRequest', fn ($pending) => $pending->where('name', 'like', $like));
                });
            })
            ->when($sort === 'company_name',
                fn ($query) => $query->orderBy(Organization::select('name')->where('id', EmployerProfile::select('organization_id')->whereColumn('user_id', 'users.id')->limit(1))->limit(1), $direction),
                fn ($query) => $query->orderBy($sort, $direction))
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
        if ($request->status === 'active' && (!$user->employerProfile?->organization_id || $user->organizationRequest?->status === 'pending')) {
            throw ValidationException::withMessages(['status' => 'Approve or link the organization request before activating this contact.']);
        }
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
            'organization_id' => 'required|integer|exists:organizations,id',
            'password' => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required|min:5',
        ]);

        if ($validator->passes()) {
            DB::transaction(function () use ($request) {
                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->mobile = $request->mobile;
                $user->designation = $request->designation;
                $user->password = Hash::make($request->password);
                $user->role = 'employer';
                $user->status = 'active';
                $user->save();
                $user->employerProfile()->create(['organization_id' => $request->integer('organization_id')]);
            });

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
