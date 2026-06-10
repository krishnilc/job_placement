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
       
        if ($request->user()->role !== 'admin') {
            session()->flash('error', 'You do not have permission to access the admin page.');
            return redirect()->route('account.dashboard');
        }
        
        return $next($request);
    }
}
