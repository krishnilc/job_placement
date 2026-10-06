<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class JobTypeController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['id', 'name', 'status', 'created_at'];
        $sort = $request->query('sort', 'name');
        $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        $search = trim((string) $request->query('search', ''));
        $jobTypes = JobType::query()
            ->withCount('jobs')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->appends($request->query());

        return view('admin.job-types.index', [
            'jobTypes' => $jobTypes,
        ]);
    }

    public function create()
    {
        return view('admin.job-types.create');
    }

    public function store(Request $request)
    {
        $validator = $this->jobTypeValidator($request);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

        JobType::create($validator->validated());
        session()->flash('success', 'Job type created successfully!');

        return response()->json([
            'status' => true,
            'errors' => [],
        ]);
    }

    public function edit($id)
    {
        return view('admin.job-types.edit', [
            'jobType' => JobType::findOrFail($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $jobType = JobType::findOrFail($id);
        $validator = $this->jobTypeValidator($request, $jobType);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

        $jobType->update($validator->validated());
        session()->flash('success', 'Job type updated successfully!');

        return response()->json([
            'status' => true,
            'errors' => [],
        ]);
    }

    public function destroy(Request $request)
    {
        if ($request->user()->role !== 'super_admin') {
            session()->flash('error', 'Only super admins can delete job types.');

            return response()->json(['status' => false]);
        }

        $jobType = JobType::find($request->input('id'));
        if ($jobType === null) {
            session()->flash('error', 'Job type not found!');

            return response()->json(['status' => false]);
        }

        if ($jobType->jobs()->exists()) {
            session()->flash('error', 'Job type cannot be deleted because it is assigned to jobs.');

            return response()->json(['status' => false]);
        }

        $jobType->delete();
        session()->flash('success', 'Job type deleted successfully!');

        return response()->json(['status' => true]);
    }

    private function jobTypeValidator(Request $request, ?JobType $jobType = null)
    {
        return Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('job_types', 'name')->ignore($jobType?->id),
            ],
            'status' => ['required', 'in:0,1'],
        ]);
    }
}
