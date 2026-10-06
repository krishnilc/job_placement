<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CollegeController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['id', 'name', 'code', 'status', 'created_at'];

        $sort = $request->query('sort', 'name');
        $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        $colleges = College::query()
            ->withCount(['categories'])
            ->orderBy($sort, $direction)
            ->paginate(10);
        $colleges->appends($request->query());

        return view('admin.colleges.index', [
            'colleges' => $colleges,
        ]);
    }

    public function create()
    {
        return view('admin.colleges.create');
    }

    public function store(Request $request)
    {
        $validator = $this->collegeValidator($request);

        if ($validator->passes()) {
            $college = new College();
            $college->name = $request->name;
            $college->code = strtoupper($request->code);
            $college->status = $request->status;
            $college->save();

            session()->flash('success', 'College created successfully!');

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

    public function edit($id)
    {
        $college = College::findOrFail($id);

        return view('admin.colleges.edit', [
            'college' => $college,
        ]);
    }

    public function update(Request $request, $id)
    {
        $college = College::findOrFail($id);

        $validator = $this->collegeValidator($request, $college->id);

        if ($validator->passes()) {
            $college->name = $request->name;
            $college->code = strtoupper($request->code);
            $college->status = $request->status;
            $college->save();

            session()->flash('success', 'College updated successfully!');

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

    //delete college
    public function destroy(Request $request)
    {
        if ($request->user()->role !== 'super_admin') {
            session()->flash('error', 'Only super admins can delete colleges.');

            return response()->json([
                'status' => false,
            ]);
        }

        $id = $request->id;
        $college = College::find($id);

        if ($college == null) {
            session()->flash('error', 'College not found!');

            return response()->json([
                'status' => false,
            ]);
        }

        $isAssignedToUsers = \App\Models\StudentProfile::where('college_id', $college->id)->exists();

        if ($isAssignedToUsers || $college->categories()->exists()) {
            session()->flash('error', 'College cannot be deleted because it is assigned to users or categories.');

            return response()->json([
                'status' => false,
            ]);
        }

        $college->delete();
        session()->flash('success', 'College deleted successfully!');

        return response()->json([
            'status' => true,
        ]);
    }

    private function collegeValidator(Request $request, ?int $ignoreId = null)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:20',
                'alpha_num',
                Rule::unique('colleges', 'code')->ignore($ignoreId),
            ],
            'status' => 'required|in:0,1',
        ]);
    }
}
