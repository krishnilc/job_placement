<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountStatusNotifier;
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
        if ($user->role === 'employer') {
            abort_unless(in_array($request->user()->role, ['admin', 'super_admin'], true), 403);
        }

        $view = match (true) {
            in_array($user->role, ['user', 'student'], true) => 'admin.students.edit',
            $user->role === 'employer' => 'admin.employers.edit',
            default => 'admin.admins.edit',
        };

        return view($view, [
            'user' => $user,
            'colleges' => \App\Models\College::orderBy('name')->get(),
        ]);
    }

    public function profile($id)
    {
        $user = User::whereIn('role', ['admin', 'super_admin', 'management', 'student', 'employer'])->findOrFail($id);

        return view('admin.users.profile', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, $id, AccountStatusNotifier $notifier)
    {
        // $id = Auth::user()->id;
        $user = User::findOrFail($id);
        $isStudent = in_array($user->role, ['user', 'student'], true);
        if ($isStudent) {
            abort_unless(in_array($request->user()->role, ['admin', 'super_admin'], true), 403);
        }
        $previousStatus = $user->status;
        $isEmployer = $user->role === 'employer';
        $willBeEmployer = $isEmployer || $request->input('role') === 'employer';
        if ($willBeEmployer) {
            abort_unless(in_array($request->user()->role, ['admin', 'super_admin'], true), 403);
        }

        // Validation rules for profile update
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:5|max:20',
            'email' => 'required|email|unique:users,email,' . $id . ',id', // Ensure email is unique except for the current user
            'mobile' => $isStudent ? 'nullable|digits:7' : 'required|digits:7',
            'email_2' => 'nullable|email|max:255',
            'mobile_2' => 'nullable|digits:7',
            'date_of_birth' => $isStudent ? 'nullable|date|before:today' : 'nullable',
            'gender' => 'nullable|string|max:20',
            'residential_address' => 'nullable|string|max:255',
            'postal_address' => 'nullable|string|max:255',
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
            'linkedin_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'availability' => 'nullable|string|max:255',
            'role' => ($isStudent || $isEmployer) ? 'nullable' : 'required|in:admin,super_admin,management,student,employer,user',
            'student_id' => $isStudent ? 'required|string|max:9|unique:student_profiles,student_id,' . $id . ',user_id' : 'nullable',
            'designation' => $isStudent ? 'nullable' : ($willBeEmployer ? 'required|string|max:100' : 'nullable|string|max:100'),
            'organization_id' => $willBeEmployer ? 'nullable|integer|exists:organizations,id' : 'nullable',
            'website_url' => 'nullable|url|max:255',
            'company_description' => 'nullable',
            'status' => 'nullable|in:pending,active,blocked',
            // 'password' => 'nullable|min:5|same:confirm_password',
            // 'confirm_password' => 'nullable|same:password',
        ]);
        $validator->after(function ($validator) use ($request, $user, $willBeEmployer) {
            if ($willBeEmployer && $request->input('status', $user->status) === 'active') {
                if (!$request->filled('organization_id')) {
                    $validator->errors()->add('organization_id', 'Select an approved organization before activating this contact.');
                }
                if ($user->organizationRequest?->status === 'pending') {
                    $validator->errors()->add('status', 'Review the organization request before activating this contact.');
                }
            }
        });

        if ($validator->passes()) {
            $normalizedRole = $isStudent ? 'student' : ($isEmployer ? 'employer' : ($request->role === 'user' ? 'student' : $request->role));

            $user->name = $request->name;
            $user->email = $request->email;
            if (!$isStudent) {
                $user->mobile = $request->mobile;
            }
            $user->role = $normalizedRole;
            if ($normalizedRole === 'student') {
                $user->student_id = $request->student_id;
            }
            if ($isStudent) {
                $user->mobile = $request->mobile;
                $user->email_2 = $request->email_2;
                $user->mobile_2 = $request->mobile_2;
                $user->designation = $request->designation;
                foreach ([
                    'date_of_birth', 'gender', 'residential_address', 'postal_address', 'city', 'country',
                    'high_school', 'high_school_graduation_year', 'college_id', 'degree', 'major',
                    'graduation_year', 'skills', 'bio', 'linkedin_url', 'facebook_url', 'availability',
                ] as $field) {
                    $user->{$field} = $request->input($field);
                }
            }
            if (!$isStudent) {
                $user->designation = $request->designation;
                $user->email_2 = $request->email_2;
                $user->mobile_2 = $request->mobile_2;
            }
            if ($normalizedRole === 'employer') {
                $user->employerProfile()->updateOrCreate(['user_id' => $user->id], [
                    'organization_id' => $request->filled('organization_id') ? $request->integer('organization_id') : null,
                ]);
            }
            if (in_array($normalizedRole, ['student', 'employer'], true)) {
                $user->status = $request->status ?? $user->status ?? 'pending';
            } else {
                $user->status = $request->status ?? $user->status ?? 'active';
            }

            $user->save();
            $notificationSent = $notifier->sendIfChanged($user, $previousStatus);
            $recipientLabel = $user->role === 'employer' ? 'employer' : 'student';

            if ($notificationSent === false) {
                session()->flash('error', 'User information and status were saved, but the notification email could not be sent. Please contact the '.$recipientLabel.' directly.');
            } else {
                session()->flash('success', 'User information updated successfully!'
                    .($notificationSent === true ? ' The '.$recipientLabel.' has been notified by email.' : ''));
            }

            return response()->json([
                'status' => true,
                'errors' => [],
                'notification_sent' => $notificationSent,
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
