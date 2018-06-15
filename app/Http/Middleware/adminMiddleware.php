<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class adminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->isDisabled()) {
            Auth::logout();
            return redirect('/login')->with('warning', 'Your session has expired because your account has been disabled.');
        }
        else if (Auth::check() && Auth::user()->isAdmin()) {
            return $next($request);
        }
        return abort(404);
    }
}
