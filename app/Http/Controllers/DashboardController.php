<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        if (!Session::has('user_id')) {
            return redirect()->route('login');
        }

        $role_id = Session::get('role_id');
        $data = [];

        // Admin (3) and HR (2) need to see the employee list
        if ($role_id == 3 || $role_id == 2) {
            $data['employees'] = DB::table('users')
                ->leftJoin('positions', 'users.id', '=', 'positions.id')
                ->select(
                    'users.*', 
                    'positions.position_name',
                    DB::raw("CONCAT(users.first_name, ' ', COALESCE(users.middle_name, ''), ' ', users.last_name) as full_name")
                )
                ->where('role_id', 1)
                ->get();
            
            // ADD THIS: HR and Admin both need the positions list for the new module
            $data['positions'] = DB::table('positions')->orderBy('position_name', 'asc')->get();
        }

        return view('dashboard', $data);
    }

    // Function to save the new position
    public function storePosition(Request $request)
    {
        // Make sure it's an admin trying to do this
        if (Session::get('role_id') != 3) {
            return back()->with('error', 'Unauthorized access.');
        }

        // Validate the input
        $request->validate([
            'position_name' => 'required|string|max:100|unique:positions,position_name'
        ]);

        // Insert into the database
        DB::table('positions')->insert([
            'position_name' => strtoupper($request->position_name),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('dashboard')->with('success', 'Position created successfully!');
    }

    // Show the form to create a new employee
    public function createEmployee()
    {
        $role_id = Session::get('role_id');
        
        // Only HR (2) and Admin (3) can access this page
        if ($role_id != 2 && $role_id != 3) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        return view('hr.create_employee');
    }

    // Process and save the new employee to the database
    public function storeEmployee(Request $request)
    {
        $role_id = Session::get('role_id');
        if ($role_id != 2 && $role_id != 3) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        // 1. Validate the NEW split name inputs
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:4'
        ]);

        // 2. Format names perfectly (All Caps)
        $firstName = strtoupper($request->first_name);
        $mi = strtoupper($request->middle_initial);
        $lastName = strtoupper($request->last_name);

        // 3. Insert into the users table using insertGetId
        $user_id = DB::table('users')->insertGetId([
            'first_name'  => $firstName,
            'middle_name' => $mi,
            'last_name'   => $lastName,
            'suffix'      => strtoupper($request->suffix), // Saves to 'suffix' in users table
            'username'    => strtolower($request->username),
            'password'    => Hash::make($request->password), // Secure encryption
            'role_id'     => 1, // Automatically assign Role 1 (Employee)
            'created_at'  => now(),
            'updated_at'  => now()
        ]);

        // 4. AUTO-INITIALIZE THE PDS!
        DB::table('pds_personal_info')->insert([
            'user_id'        => $user_id,
            'first_name'     => $firstName,
            'middle_name'    => $mi, 
            'last_name'      => $lastName,
            'name_extension' => strtoupper($request->suffix), // FIXED: Maps to 'name_extension' in PDS
            // Add temporary placeholder dates to bypass SQL strict mode until employee fills it out
            'date_of_birth'  => '2000-01-01', 
            'place_of_birth' => '',
            'sex'            => '',
            'civil_status'   => '',
            'status'         => 'Draft',
            'created_at'     => now()
        ]);

        return redirect()->route('dashboard')->with('success', 'Employee created and PDS initialized successfully!');
    }
}