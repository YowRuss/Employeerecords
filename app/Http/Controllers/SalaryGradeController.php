<?php

namespace App\Http\Controllers;

use App\Models\SalaryGrade;
use Illuminate\Http\Request;

class SalaryGradeController extends Controller
{
    public function index(Request $request)
    {
        $step = (int) $request->input('step', 1);
        if ($step < 1 || $step > 8) {
            $step = 1;
        }

        $salaryGrades = SalaryGrade::where('step', $step)
            ->orderBy('grade', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('payroll.salary_settings.index', compact('salaryGrades', 'step'));
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
