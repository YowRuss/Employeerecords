<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class EmployeeController extends Controller
{
    /**
     * Display the Employee Dashboard
     */
    public function dashboard()
    {
        // Make sure the user is actually logged in
        if (!Session::has('user_id')) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        $userId = Session::get('user_id');

        // You can fetch recent leaves or notifications here to display on their dashboard
        $recentLeaves = DB::table('leaves')
                        ->where('user_id', $userId)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();

        return view('employee.dashboard', compact('recentLeaves'));
    }

    /**
     * Display the Employee's own Service Record (Read-Only)
     */
    public function myServiceRecord()
    {
        // Make sure the user is actually logged in
        if (!Session::has('user_id')) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        $userId = Session::get('user_id');

        // Fetch user details and PDS (for the birth date/place in the header)
        $employee = DB::table('users')->where('id', $userId)->first();
        $pds = DB::table('pds')->where('user_id', $userId)->first();

        // Fetch only THEIR service records, sorted chronologically by start date
        $records = DB::table('service_records')
                    ->where('user_id', $userId)
                    ->orderBy('start_date', 'asc')
                    ->get();

        // Send all this data to the Blade view we created earlier
        return view('employee.service_record', compact('employee', 'pds', 'records'));
    }
}