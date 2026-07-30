<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot_password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'id_number' => 'required',
        ]);

        $id_number = $request->id_number;

        $user = DB::table('users')->where(function ($query) use ($id_number) {
            $query->where('username', $id_number)
                ->orWhere('recovery_email', $id_number);
        })->first();

        if (! $user) {
            return back()->with('error', 'No account found matching that username or recovery email.');
        }

        if (empty($user->recovery_email)) {
            return back()->with('error', 'This account does not have a recovery email setup. Please contact HR.');
        }

        // Generate token
        $token = Str::random(60);

        // Store token
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->recovery_email],
            [
                'email' => $user->recovery_email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        try {
            // Send email
            $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->recovery_email]);
            Mail::to($user->recovery_email)->send(new PasswordResetMail($resetUrl, $user->first_name));

            return back()->with('success', 'A recovery link has been sent to your registered recovery email.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send recovery email. Please check the server connection.');
        }
    }

    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset_password', ['token' => $token, 'email' => $request->email]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:4|confirmed',
            'token' => 'required',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return back()->with('error', 'This password reset token is invalid or has expired.');
        }

        $user = DB::table('users')->where('recovery_email', $request->email)->first();
        if (! $user) {
            return back()->with('error', 'We cannot find a user with that email address.');
        }

        DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'updated_at' => now(),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Your password has been reset successfully. Please log in.');
    }
}
