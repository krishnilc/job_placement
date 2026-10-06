<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistrationEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->needsEmailVerification()
            || $request->routeIs('verification.*', 'account.logout')) {
            return $next($request);
        }

        $email = $user->email;
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('verification_email', $email);
        $request->session()->flash('error', 'Please verify your email address before continuing. Request a new verification link below if your email has changed.');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Please verify your email address before continuing.',
                'redirect' => route('verification.notice'),
            ], 403);
        }

        return redirect()->route('verification.notice');
    }
}
