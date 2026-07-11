<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        if (
            $request->username == 'kelurahanlabukkang' &&
            $request->password == 'labukkangadmin'
        ) {

            session([
                'admin_login' => true
            ]);

            return redirect()->route('admin.dashboard');
        }

        return back()->with('error', 'Username atau Password salah.');
    }

    public function logout()
    {
        session()->forget('admin_login');

        return redirect('/');
    }
}