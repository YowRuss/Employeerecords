<?php

namespace App\Http\Controllers;

use App\Models\LateDeduction;
use Illuminate\Support\Facades\Session;

class EmployeeAttendanceController extends Controller
{
    /**
     * Display the authenticated employee's late and absence deductions
     * by payroll period, newest first.
     */
    public function index()
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        $records = LateDeduction::where('user_id', Session::get('user_id'))
            ->orderByDesc('payroll_period')
            ->get();

        return view('employee.attendance.index', compact('records'));
    }
}
