<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class ProfileController extends Controller
{
    // Show the Profile Page
    public function editProfile()
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        $user = User::with(['position', 'learningArea'])->find(Session::get('user_id'));

        return view('profile', compact('user'));
    }

    // Process the Profile Update
    public function updateProfile(Request $request)
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        // 1. Validate the incoming request
        $request->validate([
            'email' => 'nullable|email',
            'recovery_email' => 'nullable|email|max:255',
            'password' => 'nullable|min:4|confirmed',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // FIXED: Added missing comma here
            'emergency_contact_person' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:50',
        ]);

        $user_id = Session::get('user_id');
        $data = [];

        // Update Email if provided
        if ($request->filled('email')) {
            $data['email'] = $request->email;
        }

        // Update Recovery Email
        if ($request->has('recovery_email')) {
            $data['recovery_email'] = $request->recovery_email;
        }

        // Update Password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Update Emergency Contacts
        if ($request->has('emergency_contact_person')) {
            $data['emergency_contact_person'] = strtoupper($request->emergency_contact_person);
        }

        if ($request->has('emergency_contact_number')) {
            $data['emergency_contact_number'] = $request->emergency_contact_number;
        }

        // Handle Image Upload (Storing as LONGBLOB)
        if ($request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {

            $file = $request->file('profile_image');

            // Get the binary data and mime type
            $data['profile_image'] = file_get_contents($file->getRealPath());
            $data['image_type'] = $file->getMimeType();
        }

        // Only update the database if there is data to change
        if (! empty($data)) {
            $data['updated_at'] = now();
            DB::table('users')->where('id', $user_id)->update($data);
        }

        return back()->with('success', 'Account Profile updated successfully!');
    }
}
