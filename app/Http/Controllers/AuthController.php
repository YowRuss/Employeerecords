<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function processLogin(Request $request)
    {
        $request->validate([
            'id_number' => 'required',
            'password'  => 'required',
        ]);

        $key = 'login_attempts_' . $request->ip();
        if (cache()->get($key, 0) >= 5) {
            return back()->with('error', 'Too many failed attempts. Please try again in a minute.');
        }

        $user = DB::table('users')->where('username', $request->id_number)->first();

        $passwordMatches = false;

        if ($user) {
            // Normal, secure login path
            if (Hash::check($request->password, $user->password)) {
                $passwordMatches = true;
            }

            // TESTING-ONLY shortcut: only active when APP_ENV=local
            // This block is skipped entirely on staging/production, regardless of code state.
            if (!$passwordMatches && app()->environment('local')) {
                if ($request->password === $user->password) {
                    $passwordMatches = true;
                }
            }
        }

        if ($user && $passwordMatches) {
            cache()->forget($key);
            Session::regenerate();

            Session::put('user_id', $user->id);
            Session::put('username', $user->username);
            Session::put('role_id', $user->role_id);

            $fullName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
            Session::put('full_name', $fullName);

            return redirect()->route('dashboard');
        }

        cache()->put($key, cache()->get($key, 0) + 1, now()->addMinutes(1));

        return back()->with('error', 'Invalid ID Number or password.');
    }

    public function logout()
    {
        Session::flush();
        return redirect()->route('login');
    }
}
