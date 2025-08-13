<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\ResponseCache\Facades\ResponseCache;

class DebuggerAuth
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
	    if (Auth::guard('admin')->check() && Auth::guard('admin')->user()->id === 1) {
	        \Debugbar::enable();
	    }
	    else {
	    	\Debugbar::disable();
	    }

	    return $next($request);
	}
}