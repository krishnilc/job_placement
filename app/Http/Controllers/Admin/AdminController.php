<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['id', 'name', 'email', 'mobile', 'role', 'created_at'];

        $sort = $request->query('sort', 'created_at');
        $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $users = User::whereIn('role', ['admin', 'super_admin', 'management'])
            ->orderBy($sort, $direction)
            ->paginate(10);
        $users->appends($request->query());

        return view('admin.admins.index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return view('admin.admins.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:5|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required|min:5',
            'mobile' => 'required|digits:7',
            'role' => 'required|in:admin,super_admin,management',
        ]);

        if ($validator->passes()) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->mobile = $request->mobile;
            $user->role = $request->role;
            $user->status = 'active';
            $user->save();

            session()->flash('success', 'Admin user created successfully!');

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
