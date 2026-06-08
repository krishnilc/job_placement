<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $user = User::orderBy('created_at', 'asc')->paginate(10);
        return view('admin.users.list', [
            'users' => $user
        ]);
    }

    public function edit($id)
    {
        $user =  User::findOrfail($id);
        return view('admin.users.edit', [
            'user' => $user
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
