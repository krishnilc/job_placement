<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\College;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['id', 'name', 'status', 'created_at'];

        $sort = $request->query('sort', 'name');
        $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        $categories = Category::query()
            ->with('college')
            ->withCount('jobs')
            ->orderBy($sort, $direction)
            ->paginate(10);
        $categories->appends($request->query());

        return view('admin.categories.list', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return view('admin.categories.create', [
            'colleges' => College::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->categoryValidator($request);

        if ($validator->passes()) {
            $category = new Category();
            $category->name = $request->name;
            $category->college_id = $request->college_id;
            $category->status = $request->status;
            $category->save();

            session()->flash('success', 'Category created successfully!');

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
        $category = Category::findOrFail($id);

        return view('admin.categories.edit', [
            'category' => $category,
            'colleges' => College::active()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validator = $this->categoryValidator($request);

        if ($validator->passes()) {
            $category->name = $request->name;
            $category->college_id = $request->college_id;
            $category->status = $request->status;
            $category->save();

            session()->flash('success', 'Category updated successfully!');

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

    //delete category
    public function destroy(Request $request)
    {
        $id = $request->id;
        $category = Category::find($id);

        if ($category == null) {
            session()->flash('error', 'Category not found!');

            return response()->json([
                'status' => false,
            ]);
        }

        if ($category->jobs()->exists()) {
            session()->flash('error', 'Category cannot be deleted because it is assigned to jobs.');

            return response()->json([
                'status' => false,
            ]);
        }

        $category->delete();
        session()->flash('success', 'Category deleted successfully!');

        return response()->json([
            'status' => true,
        ]);
    }

    private function categoryValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'college_id' => 'required|exists:colleges,id',
            'status' => 'required|in:0,1',
        ]);
    }
}
