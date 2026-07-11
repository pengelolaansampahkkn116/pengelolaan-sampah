<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('admin_login')) {
            return redirect()->route('login.form');
        }
        return $next($request);
    }
}