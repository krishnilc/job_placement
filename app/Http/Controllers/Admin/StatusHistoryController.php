<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusHistoryController extends Controller
{
    public function user(Request $request, User $user): View
    {
        $this->authorizeViewer($request);
        abort_unless(in_array($user->role, ['student', 'employer'], true), 404);

        return view('admin.status-history', [
            'subjectName' => $user->name,
            'listLabel' => $user->role === 'student' ? 'Students' : 'Employers',
            'listUrl' => route('admin.users.'.$user->role.'s'),
            'histories' => $user->statusHistories()->paginate(20),
        ]);
    }

    public function job(Request $request, Job $job): View
    {
        $this->authorizeViewer($request);

        return view('admin.status-history', [
            'subjectName' => $job->title,
            'listLabel' => 'Manage Jobs',
            'listUrl' => route('admin.jobs'),
            'histories' => $job->statusHistories()->paginate(20),
        ]);
    }

    private function authorizeViewer(Request $request): void
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'super_admin'], true), 403);
    }
}
