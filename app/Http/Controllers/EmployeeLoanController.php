<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Support\Facades\Session;

class EmployeeLoanController extends Controller
{
    /**
     * Display the authenticated employee's active and paid loans.
     */
    public function index()
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        $loans = Loan::where('user_id', Session::get('user_id'))
            ->whereIn('status', ['Active', 'Paid'])
            ->orderByRaw("CASE WHEN status = 'Active' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->get();

        return view('employee.loans.index', compact('loans'));
    }
}
