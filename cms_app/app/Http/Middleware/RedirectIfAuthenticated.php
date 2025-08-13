<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard('admin')->check()) {
            if (Auth::guard('admin')->user()->is_first_login) {
                return redirect('/admin/setup');
            }
            return redirect('/admin/home');
        }

        if (Auth::guard('web')->check()) {
            return redirect('/user/home');
        }

        if (Auth::guard('client')->check()) {
            return redirect('/client/home');
        }

        return $next($request);
    }
}
