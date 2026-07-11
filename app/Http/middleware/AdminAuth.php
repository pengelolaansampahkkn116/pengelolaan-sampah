<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Jika tidak ada session admin_login, redirect ke halaman login
        if (!session()->has('admin_login')) {
            return redirect()->route('login.form');
        }

        return $next($request);
    }
}