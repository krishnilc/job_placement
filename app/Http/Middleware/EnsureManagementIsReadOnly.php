<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureManagementIsReadOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->isReadOnlyManagement()) {
            return $next($request);
        }

        abort_unless($user->status === 'active' || $request->routeIs('account.logout'), 403, 'Your account is not active.');

        // These handlers always update the authenticated user, never a supplied user ID.
        if ($request->routeIs('account.updateProfile', 'account.updatePassword', 'account.updateProfilePic')) {
            return $next($request);
        }

        abort_unless($request->isMethodSafe(), 403, 'Management access is read-only. Only your own account settings can be changed.');

        if ($request->routeIs('admin.*')) {
            abort_unless($request->routeIs(
                'admin.dashboard', 'admin.reports.export',
                'admin.account.profile', 'admin.account.editProfile', 'admin.account.editPassword',
                'admin.users', 'admin.users.students', 'admin.users.employers', 'admin.users.profile',
                'admin.jobs', 'admin.jobApplications',
                'admin.organizations.index', 'admin.organizations.show', 'admin.organizations.review',
                'admin.feedback', 'admin.feedback.export',
                'admin.colleges', 'admin.categories', 'admin.jobTypes'
            ), 403, 'Management access is read-only.');
        }

        if ($request->routeIs('account.*', 'student.*', 'employer.*')) {
            abort_unless($request->routeIs(
                'account.profile', 'account.editProfile', 'account.editPassword', 'account.logout'
            ), 403, 'Management access is read-only.');

            $accountRoute = match ($request->route()->getName()) {
                'account.profile' => 'admin.account.profile',
                'account.editProfile' => 'admin.account.editProfile',
                'account.editPassword' => 'admin.account.editPassword',
                default => null,
            };
            if ($accountRoute !== null) {
                return redirect()->route($accountRoute);
            }
        }

        return $next($request);
    }
}
