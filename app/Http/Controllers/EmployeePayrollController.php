<?php

namespace App\Http\Controllers;

use App\Models\DeductionType;
use App\Models\PayrollRecord;
use Illuminate\Support\Facades\Session;

class EmployeePayrollController extends Controller
{
    /**
     * Display the authenticated employee's payroll history.
     */
    public function index()
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        $records = PayrollRecord::with('payrollPeriod')
            ->where('user_id', Session::get('user_id'))
            ->get()
            ->sortByDesc(function (PayrollRecord $record) {
                $period = $record->payrollPeriod;

                return $period
                    ? ($period->period_year * 100) + $period->period_month
                    : 0;
            })
            ->values();

        return view('employee.payroll.index', compact('records'));
    }

    /**
     * Display a single digital payslip for the authenticated employee.
     */
    public function show(string $id)
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        $record = PayrollRecord::with(['payrollPeriod', 'payrollIncomes.incomeType'])
            ->where('id', $id)
            ->where('user_id', Session::get('user_id'))
            ->firstOrFail();

        $deductionDictionary = DeductionType::pluck('name', 'code')->toArray();

        return view('employee.payroll.show', compact('record', 'deductionDictionary'));
    }
}
