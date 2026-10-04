<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationStatus;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $sortableColumns = [
            'title' => 'jobs.title',
            'applicant' => 'users.name',
            'company_name' => 'jobs.company_name',
            'applied_at' => 'job_applications.applied_at',
        ];
        $sort = $request->input('sort', 'applied_at');
        $direction = $request->input('direction', 'desc');

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'applied_at';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $applicationsQuery = JobApplication::select('job_applications.*')
            ->leftJoin('jobs', 'jobs.id', '=', 'job_applications.job_id')
            ->leftJoin('users', 'users.id', '=', 'job_applications.user_id');

        if (in_array($user->role, ['admin', 'super_admin'], true)) {
        } elseif ($user->role === 'employer') {
            $applicationsQuery->where('jobs.user_id', $user->id);
        } else {
            $applicationsQuery->where('job_applications.user_id', $user->id);
        }

        $search = trim((string) $request->query('search', ''));
        $statusFilter = $request->query('status');

        $applications = $applicationsQuery
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('jobs.title', 'like', $like)
                        ->orWhere('jobs.company_name', 'like', $like)
                        ->orWhere('users.name', 'like', $like)
                        ->orWhere('users.email', 'like', $like);
                });
            })
            ->when($statusFilter, fn ($query) => $query->where('job_applications.application_status_id', $statusFilter))
            // Only eager-load what's actually rendered; full statusHistories are lazy-loaded per-modal on demand.
            ->with(['job:id,title,company_name,user_id', 'user:id,name', 'applicationStatus:id,name,sort_order', 'latestStatusHistory.changedBy:id,name'])
            ->orderBy($sortableColumns[$sort], $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.job-applications.list', [
            'applications' => $applications,
            'applicationStatuses' => ApplicationStatus::orderBy('sort_order')->get(['id', 'name']),
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function updateStatus(Request $request, JobApplication $application)
    {
        $request->validate([
            'application_status_id' => ['required', 'integer', Rule::exists('application_statuses', 'id')],
        ]);

        $user = $request->user();
        if ($user->role === 'employer' && !$application->job()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $statusId = (int) $request->input('application_status_id');
        if ((int) $application->application_status_id !== $statusId) {
            DB::transaction(function () use ($application, $statusId, $user) {
                $application->update(['application_status_id' => $statusId]);

                DB::table('application_status_history')->insert([
                    'job_application_id' => $application->id,
                    'application_status_id' => $statusId,
                    'changed_by' => $user->id,
                    'created_at' => now(),
                ]);
            });
        }

        return back()->with('success', 'Application status updated successfully.');
    }

    public function destroy(Request $request)
    {
        if ($request->user()->role !== 'super_admin') {
            session()->flash('error', 'Only super admins can delete applications.');
            return response()->json(['success' => false, 'message' => 'Only super admins can delete applications.'], 403);
        }

        $applicationId = $request->id;
        $application = JobApplication::findOrFail($applicationId);

        if ($application) {
            $application->delete();
            session()->flash('success', 'Application deleted successfully.');
            return response()->json(['success' => true, 'message' => 'Application deleted successfully.']);
        } else {
            session()->flash('error', 'Application not found.');
            return response()->json(['success' => false, 'message' => 'Application not found.'], 404);
        }
    }
}
