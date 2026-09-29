<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Session::has('user_id') || Auth::check()) {
            return redirect()->route('dashboard');
        }

        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function processLogin(Request $request)
    {
        $request->validate([
            'id_number' => 'required',
            'password' => 'required',
        ]);

        $key = 'login_attempts_'.$request->ip();
        if (cache()->get($key, 0) >= 5) {
            return back()->with('error', 'Too many failed attempts. Please try again in a minute.');
        }

        $id_number = $request->id_number;

        $user = DB::table('users')->where(function ($query) use ($id_number) {
            $query->where('username', $id_number)
                ->orWhere('username', $id_number.'@gmail.com');

            if (str_ends_with($id_number, '@gmail.com')) {
                $query->orWhere('username', str_replace('@gmail.com', '', $id_number));
            }
        })->first();

        $passwordMatches = false;

        if ($user && $user->status === 'Inactive') {
            return back()->with('error', 'Your account has been deactivated. Please contact HR.');
        }

        if ($user) {
            // Normal, secure login path
            if (Hash::check($request->password, $user->password)) {
                $passwordMatches = true;
            }

            // TESTING-ONLY shortcut: only active when APP_ENV=local
            // This block is skipped entirely on staging/production, regardless of code state.
            if (! $passwordMatches && app()->environment('local')) {
                if ($request->password === $user->password) {
                    $passwordMatches = true;
                }
            }
        }

        if ($user && $passwordMatches) {
            if ($user->must_change_password) {
                Session::put('password_change_user_id', $user->id);

                return redirect()->route('password.change');
            }

            // Sync with standard Laravel Auth guard
            $eloquentUser = User::find($user->id);
            if ($eloquentUser) {
                Auth::login($eloquentUser);
            }

            cache()->forget($key);
            Session::regenerate();

            Session::put('user_id', $user->id);
            Session::put('username', $user->username);
            Session::put('role_id', $user->role_id);

            $fullName = trim($user->first_name.' '.($user->middle_name ? $user->middle_name.' ' : '').$user->last_name);
            Session::put('full_name', $fullName);

            return redirect()->route('dashboard');
        }

        cache()->put($key, cache()->get($key, 0) + 1, now()->addMinutes(1));

        return back()->with('error', 'Invalid ID Number or password.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function showChangePassword()
    {
        if (! Session::has('password_change_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.change_password');
    }

    public function updatePassword(Request $request)
    {
        if (! Session::has('password_change_user_id')) {
            return redirect()->route('login');
        }

        $request->validate([
            'password' => 'required|string|min:4|confirmed',
        ]);

        $userId = Session::get('password_change_user_id');

        DB::table('users')->where('id', $userId)->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        Session::forget('password_change_user_id');

        return redirect()->route('login')->with('success', 'Password changed successfully. Please log in with your new password.');
    }
}
