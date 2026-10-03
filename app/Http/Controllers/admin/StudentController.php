<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['id', 'name', 'email', 'mobile', 'student_id', 'status', 'created_at'];

        $sort = $request->query('sort', 'created_at');
        $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $search = trim((string) $request->query('search', ''));

        $users = User::where('role', 'student')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('mobile', 'like', $like)
                        ->orWhere('designation', 'like', $like)
                        ->orWhereHas('studentProfile', fn ($p) => $p->where('student_id', 'like', $like));
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(10);
        $users->appends($request->query());

        return view('admin.students.list', [
            'users' => $users,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,active,blocked']);

        $user = User::where('role', 'student')->findOrFail($id);
        $user->status = $request->status;
        $user->save();

        return response()->json(['status' => true]);
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:5|max:50',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|digits:7',
            'student_id' => 'required|string|max:9|unique:student_profiles,student_id',
            'password' => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required|min:5',
        ], [
            'student_id.unique' => 'The University Student ID has already been taken. Please enter a unique one.',
        ]);

        if ($validator->passes()) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->student_id = $request->student_id;
            $user->password = Hash::make($request->password);
            $user->role = 'student';
            $user->status = 'active';
            $user->save();

            session()->flash('success', 'Student created successfully!');

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
}
