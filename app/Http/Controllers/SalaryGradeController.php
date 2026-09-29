<?php

namespace App\Http\Controllers;

use App\Models\SalaryGrade;
use Illuminate\Http\Request;

class SalaryGradeController extends Controller
{
    public function index()
    {
        $salaryGrades = SalaryGrade::orderBy('grade', 'asc')
            ->orderBy('step', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('payroll.salary_settings.index', compact('salaryGrades'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $salaryGrade = SalaryGrade::findOrFail($id);

        $salaryGrade->update([
            'amount' => $request->amount,
        ]);

        return back()->with('success', 'Salary Grade amount updated successfully!');
    }
}
