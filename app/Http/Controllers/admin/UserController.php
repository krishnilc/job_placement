<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = $this->buildUserQuery($request, 'all')->paginate(10);
        $users->appends($request->query());

        return view('admin.users.list', [
            'users' => $users,
            'list_type' => 'all',
        ]);
    }

    public function students(Request $request)
    {
        $users = $this->buildUserQuery($request, 'students')->paginate(10);
        $users->appends($request->query());

        return view('admin.users.list', [
            'users' => $users,
            'list_type' => 'students'
        ]);
    }

    public function employers(Request $request)
    {
        $users = $this->buildUserQuery($request, 'employers')->paginate(10);
        $users->appends($request->query());

        return view('admin.users.list', [
            'users' => $users,
            'list_type' => 'employers'
        ]);
    }

    private function buildUserQuery(Request $request, string $listType)
    {
        $query = User::query();

        if ($listType === 'students') {
            $query->where('role', 'student');
        } elseif ($listType === 'employers') {
            $query->where('role', 'employer');
        }

        $allowedSorts = ['id', 'name', 'email', 'mobile', 'created_at'];

        if ($listType === 'students') {
            $allowedSorts[] = 'student_id';
        } elseif ($listType === 'employers') {
            $allowedSorts[] = 'designation';
        }

        $sort = $request->query('sort', 'created_at');
        $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        return $query->orderBy($sort, $direction);
    }

    public function edit(Request $request, $id)
    {
        $user = User::findOrfail($id);
        return view('admin.users.edit', [
            'user' => $user,
            'list_type' => $request->query('list_type', 'all'),
        ]);
    }

    public function update(Request $request, $id)
    {
        // $id = Auth::user()->id;

        // Validation rules for profile update
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:5|max:20',
            'email' => 'required|email|unique:users,email,' . $id . ',id', // Ensure email is unique except for the current user
            'mobile' => 'required|digits:7',
            'role' => 'required|in:admin,user,employer',
            // 'password' => 'nullable|min:5|same:confirm_password',
            // 'confirm_password' => 'nullable|same:password',
        ]);

        if ($validator->passes()) {
            $user = User::find($id);

            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->designation = $request->designation;
            $user->role = $request->role;

            $user->save();

            session()->flash('success', 'User information updated successfully!');

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

    //delete user
    public function destroy(Request $request)
    {
        $id = $request->id;
        $user = User::find($id);

        if ($user == null) {
            session()->flash('error', 'User not found!');

            return response()->json([
                'status' => false,
            ]);
        }

        $user->delete();
        session()->flash('success', 'User deleted successfully!');

        return response()->json([
            'status' => true,
        ]);       
    }
}
