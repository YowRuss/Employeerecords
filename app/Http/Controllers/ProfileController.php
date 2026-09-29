<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // Show the Profile Page
    public function editProfile()
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        $user = User::with(['position', 'learningArea'])->find(Session::get('user_id'));
        $positions = $this->isManagement()
            ? Position::orderBy('position_name')->get(['id', 'position_name'])
            : collect();

        return view('profile', compact('user', 'positions'));
    }

    public function update(Request $request)
    {
        return $this->updateProfile($request);
    }

    // Process the Profile Update
    public function updateProfile(Request $request)
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        $userId = (int) Session::get('user_id');
        $isManagement = $this->isManagement();

        $rules = [
            'email' => 'nullable|email',
            'recovery_email' => 'nullable|email|max:255',
            'password' => 'nullable|min:4|confirmed',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'emergency_contact_person' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:50',
        ];

        if ($isManagement) {
            $rules['first_name'] = 'required|string|max:100';
            $rules['middle_name'] = 'nullable|string|max:100';
            $rules['last_name'] = 'required|string|max:100';
            $rules['suffix'] = 'nullable|string|max:50';
            $rules['username'] = ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)];
            $rules['position_id'] = 'nullable|exists:positions,id';
        }

        $request->validate($rules);

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

        if ($isManagement) {
            $data['first_name'] = strtoupper($request->first_name);
            $data['middle_name'] = $request->filled('middle_name') ? strtoupper($request->middle_name) : null;
            $data['last_name'] = strtoupper($request->last_name);
            $data['suffix'] = $request->filled('suffix') ? strtoupper($request->suffix) : null;
            $data['username'] = $request->username;
            $data['position_id'] = $request->filled('position_id') ? $request->position_id : null;
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
            DB::table('users')->where('id', $userId)->update($data);
        }

        if ($isManagement) {
            DB::table('pds_personal_info')->where('user_id', $userId)->update([
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'last_name' => $data['last_name'],
                'name_extension' => $data['suffix'],
                'updated_at' => now(),
            ]);

            $account = User::find($userId);
            $account?->syncEmployeeType();

            Session::put('username', $data['username']);
            Session::put('full_name', trim($data['first_name'].' '.($data['middle_name'] ? $data['middle_name'].' ' : '').$data['last_name']));
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    private function isManagement(): bool
    {
        return in_array((int) Session::get('role_id'), [2, 3], true);
    }
}
