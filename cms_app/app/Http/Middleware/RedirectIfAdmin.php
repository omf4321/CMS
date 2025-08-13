<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAdmin
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string|null  $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = 'admin')
	{
	    if (Auth::guard($guard)->check()) {
			if (Auth::guard('admin')->user()->is_first_login) {
                return redirect('/admin/setup');
            }
	        return redirect('admin/home');
	    }

	    return $next($request);
	}
}