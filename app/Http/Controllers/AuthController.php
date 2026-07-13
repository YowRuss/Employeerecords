<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    // Show login page
    public function showLogin()
    {
        return view('auth.login');
    }

    // Process login (No Hashing)
    public function processLogin(Request $request)
    {
        // 1. Validate using the new 'id_number' field from the HTML form
        $request->validate([
            'id_number' => 'required',
            'password' => 'required'
        ]);

        // 2. Retrieve user from the database
        // We look inside the 'username' column for the 'id_number' they typed in
        $user = DB::table('users')->where('username', $request->id_number)->first();

        // 3. Check if user exists and plain text password matches
        if ($user && $user->password === $request->password) {
            
            // Store user details in session manually since we bypassed Laravel's Auth guard
        Session::put('user_id', $user->id);
        Session::put('username', $user->username); 
        Session::put('role_id', $user->role_id);
        
        // NEW: Stitch the split names together for the session so your dashboard doesn't break
        $fullName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
        Session::put('full_name', $fullName);

            // Redirect to dashboard
            return redirect()->route('dashboard'); 
        }

        // 4. Update the error message to say "ID Number"
        return back()->with('error', 'Invalid ID Number or password.');
    }

    // Process logout
    public function logout()
    {
        Session::flush();
        return redirect()->route('login');
    }
}