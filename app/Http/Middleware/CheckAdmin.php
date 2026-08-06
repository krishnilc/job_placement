<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
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
       
        if ($request->user()->role !== 'admin' && $request->user()->role !== 'employer') {
            session()->flash('error', 'You do not have permission to access the admin page.');
            return redirect()->route('student.dashboard');
        }
        
        return $next($request);
    }
}
