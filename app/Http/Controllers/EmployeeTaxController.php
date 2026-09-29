<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;

class EmployeeTaxController extends Controller
{
    /**
     * Placeholder dashboard for downloadable BIR Form 2316 PDFs.
     */
    public function index()
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        return view('employee.tax.index');
    }
}
