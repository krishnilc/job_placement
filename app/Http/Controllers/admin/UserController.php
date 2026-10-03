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
        return redirect()->route('admin.users.students');
    }

    public function edit(Request $request, $id)
    {
        $user = User::findOrfail($id);

        $view = match (true) {
            in_array($user->role, ['user', 'student'], true) => 'admin.students.edit',
            $user->role === 'employer' => 'admin.employers.edit',
            default => 'admin.admins.edit',
        };

        return view($view, [
            'user' => $user,
        ]);
    }

    public function profile($id)
    {
        $user = User::whereIn('role', ['admin', 'super_admin', 'student', 'employer'])->findOrFail($id);

        return view('admin.users.profile', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        // $id = Auth::user()->id;
        $user = User::findOrFail($id);
        $isStudent = in_array($user->role, ['user', 'student'], true);
        $willBeEmployer = $user->role === 'employer' || $request->input('role') === 'employer';

        // Validation rules for profile update
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:5|max:20',
            'email' => 'required|email|unique:users,email,' . $id . ',id', // Ensure email is unique except for the current user
            'mobile' => $isStudent ? 'nullable' : 'required|digits:7',
            'role' => $isStudent ? 'nullable' : 'required|in:admin,super_admin,student,employer,user',
            'student_id' => $isStudent ? 'required|string|max:9|unique:student_profiles,student_id,' . $id . ',user_id' : 'nullable',
            'designation' => $isStudent ? 'nullable' : 'nullable|string|max:100',
            'company_name' => $willBeEmployer ? 'required|string|max:255' : 'nullable',
            'status' => 'nullable|in:pending,active,blocked',
            // 'password' => 'nullable|min:5|same:confirm_password',
            // 'confirm_password' => 'nullable|same:password',
        ]);

        if ($validator->passes()) {
            $normalizedRole = $isStudent ? 'student' : ($request->role === 'user' ? 'student' : $request->role);

            $user->name = $request->name;
            $user->email = $request->email;
            if (!$isStudent) {
                $user->mobile = $request->mobile;
            }
            $user->role = $normalizedRole;
            if ($normalizedRole === 'student') {
                $user->student_id = $request->student_id;
            }
            if (!$isStudent) {
                $user->designation = $request->designation;
            }
            if ($normalizedRole === 'employer') {
                $user->company_name = $request->company_name;
            }
            if (in_array($normalizedRole, ['student', 'employer'], true)) {
                $user->status = $request->status ?? $user->status ?? 'pending';
            } else {
                $user->status = $request->status ?? $user->status ?? 'active';
            }

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

        if (in_array($user->role, ['student', 'user', 'employer'], true) && $request->user()->role !== 'super_admin') {
            session()->flash('error', 'Only super admins can delete students and employers.');

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
