<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SavedJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedJobController extends Controller
{
    public function index(Request $request)
    {
        $sortableColumns = [
            'title' => 'jobs.title',
            'company_name' => 'jobs.company_name',
            'closing_date' => 'jobs.closing_date',
            'status' => 'jobs.status',
        ];
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        if (! array_key_exists($sort, $sortableColumns)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $sortColumn = $sortableColumns[$sort] ?? 'saved_jobs.created_at';
        $search = trim((string) $request->query('search', ''));

        $savedJobs = SavedJob::select('saved_jobs.*')
            ->leftJoin('jobs', 'jobs.id', '=', 'saved_jobs.job_id')
            ->where('saved_jobs.user_id', Auth::id())
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';
                $query->where(function ($q) use ($like) {
                    $q->where('jobs.title', 'like', $like)
                        ->orWhere('jobs.company_name', 'like', $like)
                        ->orWhere('jobs.location', 'like', $like);
                });
            })
            ->with(['job', 'job.jobType', 'job.applications'])
            ->orderBy($sortColumn, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('student.saved-jobs.index', [
            'savedJobs' => $savedJobs,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function destroy(Request $request)
    {
        $savedJob = SavedJob::where([
            'id' => $request->id,
            'user_id' => Auth::user()->id,
        ])->first();

        if ($savedJob) {
            $savedJob->delete();
            session()->flash('success', 'Saved job removed successfully');
        } else {
            session()->flash('error', 'Saved job not found');
        }

        return response()->json([
            'status' => true,
        ]);
    }
}
