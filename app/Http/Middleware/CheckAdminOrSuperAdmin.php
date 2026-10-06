<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminOrSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() == null) {
            return redirect()->route('home');
        }

        if (!$request->user()->hasAdminAccess()) {
            session()->flash('error', 'You do not have permission to manage colleges.');
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
