<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AccountController extends Controller
{
    // This method will show user profile page
    public function profile()
    {
        $id = Auth::user()->id;
        $user = User::find($id);

        return view('student.account.edit-profile', [
            'user' => $user,
            'colleges' => College::active()->orderBy('name')->get(),
        ]);
    }

    public function viewProfile()
    {
        return view('student.account.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function employerProfile()
    {
        return view('employer.account.edit-profile', [
            'user' => Auth::user(),
        ]);
    }

    public function employerViewProfile()
    {
        return view('employer.account.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function adminProfile()
    {
        return view('admin.account.edit-profile', [
            'user' => Auth::user(),
        ]);
    }

    public function adminViewProfile()
    {
        return view('admin.account.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $id = Auth::user()->id;

        // Validation rules for profile update
        $role = Auth::user()->role;

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:5|max:50',
            'email' => 'required|email|unique:users,email,'.$id.',id',
            'mobile' => 'required|digits:7',
            'email_2' => 'nullable|email|max:255',
            'mobile_2' => 'nullable|digits:7',
            'designation' => in_array($role, ['admin', 'super_admin', 'management', 'employer'], true)
                ? 'required|string|max:100'
                : 'required|in:Full-time Student,Part-time Student,Alumni',
            'organization_id' => $role === 'employer' ? 'prohibited' : 'nullable',
            'company_name' => $role === 'employer' ? 'prohibited' : 'nullable',
            'company_address' => $role === 'employer' ? 'prohibited' : 'nullable',
            'website_url' => $role === 'employer' ? 'prohibited' : 'nullable',
            'company_description' => $role === 'employer' ? 'prohibited' : 'nullable',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'residential_address' => 'nullable|string|max:255',
            'postal_address' => $role === 'employer' ? 'prohibited' : 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'high_school' => 'nullable|string|max:255',
            'high_school_graduation_year' => 'nullable|string|max:10',
            'college_id' => 'nullable|exists:colleges,id',
            'degree' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'graduation_year' => 'nullable|string|max:10',
            'skills' => 'nullable|string|max:1000',
            'bio' => 'nullable|string|max:1000',
            'linkedin_url' => $role === 'employer' ? 'prohibited' : ['nullable', 'url', 'max:255'],
            'facebook_url' => $role === 'employer' ? 'prohibited' : ['nullable', 'url', 'max:255'],
            'availability' => 'nullable|string|max:255',
        ]);

        if ($validator->passes()) {
            $user = User::find($id);

            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->email_2 = $request->email_2;
            $user->mobile_2 = $request->mobile_2;
            $user->designation = $request->designation;
            if ($role !== 'employer' && ! $user->isReadOnlyManagement()) {
                $user->date_of_birth = $request->date_of_birth;
                $user->gender = $request->gender;
                $user->residential_address = $request->residential_address;
                $user->postal_address = $request->postal_address;
                $user->city = $request->city;
                $user->country = $request->country;
                $user->high_school = $request->high_school;
                $user->high_school_graduation_year = $request->high_school_graduation_year;
                $user->college_id = $request->college_id;
                $user->degree = $request->degree;
                $user->major = $request->major;
                $user->graduation_year = $request->graduation_year;
                $user->skills = $request->skills;
                $user->bio = $request->bio;
                $user->linkedin_url = $request->linkedin_url;
                $user->facebook_url = $request->facebook_url;
                $user->availability = $request->availability;
            }

            $user->save();

            session()->flash('success', 'Profile updated successfully!');

            return response()->json([
                'status' => true,
                'errors' => [],
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function updateProfilePic(Request $request)
    {
        $id = Auth::user()->id;

        $validator = Validator::make($request->all(), [
            'profile_pic' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->passes()) {
            $image = $request->file('profile_pic'); // Get the uploaded file
            $extension = $image->getClientOriginalExtension(); // Get the file extension
            $imageName = $id.'_'.time().'.'.$extension; // Create a unique filename using the current timestamp
            $image->move(public_path('/profile_pic'), $imageName); // Move the file to the public/profile_pic directory

            // / Image processing using Intervention Image library - cropping and resizing the uploaded image
            $sourcePath = public_path('/profile_pic/'.$imageName); // Get the path of the uploaded image
            $manager = new ImageManager(Driver::class); // Create an instance of the Intervention Image Manager using the GD driver
            $image = $manager->read($sourcePath); // Read the uploaded image

            // crop the best fitting 150x150 and save the thumbnail
            $image->cover(150, 150);
            $image->toPng()->save(public_path('/profile_pic/thumb/'.$imageName)); // Save the cropped image as a PNG file

            // Delete old profile picture if exists
            File::delete(public_path('/profile_pic/'.Auth::user()->image)); // Delete the old profile picture
            File::delete(public_path('/profile_pic/thumb/'.Auth::user()->image)); // Delete the old thumbnail

            User::where('id', $id)->update(['image' => $imageName]); // Update the user's profile picture in the database

            session()->flash('success', 'Profile picture updated successfully!');

            return response()->json([
                'status' => true,
                'errors' => [],
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    // This method will show the password update form
    public function editPassword()
    {
        return view('student.account.edit-password');
    }

    public function employerEditPassword()
    {
        return view('employer.account.edit-password');
    }

    public function adminEditPassword()
    {
        return view('admin.account.edit-password');
    }

    // This method will handle the password update request
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required|same:new_password',
        ]);

        if ($validator->passes()) {
            $user = User::find(Auth::id());

            if (Hash::check($request->old_password, $user->password)) {
                $user->password = Hash::make($request->new_password);
                $user->save();

                session()->flash('success', 'Password changed successfully!');

                return response()->json([
                    'status' => true,
                    'errors' => [],
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'errors' => ['old_password' => ['Old password is incorrect.']],
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }
}
