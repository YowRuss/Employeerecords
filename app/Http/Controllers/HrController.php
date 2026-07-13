<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class HrController extends Controller
{
    public function viewPds($id)
    {
        // 1. Security Check: Only allow HR (Role 2) or Admin (Role 3)
        $role_id = Session::get('role_id');
        if (!Session::has('user_id') || !in_array($role_id, [2, 3])) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access. HR privileges required.');
        }

        // 2. Fetch the Employee's Account Details
        $employee = DB::table('users')->where('id', $id)->first();
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        // 3. Fetch all their submitted PDS Data
        $personal_info = DB::table('pds_personal_info')->where('user_id', $id)->first();
        $children      = DB::table('pds_children')->where('user_id', $id)->get();
        $education     = DB::table('pds_education')->where('user_id', $id)->get();

        // 4. Load the HR Read-Only View
        return view('hr.view_pds', compact('employee', 'personal_info', 'children', 'education'));
    }
    public function staffProfiling()
    {
        // Fetch all employees (role_id 1)
        $employees = DB::table('users')
            ->where('users.role_id', 1)
            ->get();

        return view('hr.staff_profiling', compact('employees'));
    }
}
