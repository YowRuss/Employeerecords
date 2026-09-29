<?php

namespace App\Http\Controllers;

use App\Models\SalaryGrade;
use App\Models\StepIncrementLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StepIncrementController extends Controller
{
    /**
     * Process a salary step increment (NOSI) for the given employee.
     * Restricted to HR (role_id=2) and Admin (role_id=3).
     */
    public function processIncrement(Request $request, User $user)
    {
        // Authorization guard
        if (! in_array((int) session('role_id'), [2, 3], true)) {
            abort(403, 'Unauthorized. Only HR and Admin can process step increments.');
        }

        $request->validate([
            'effective_date' => 'nullable|date',
        ]);

        // Business rule guards
        if (! $user->position || ! $user->position->salary_grade) {
            return back()->with('error', 'Cannot process increment: employee has no assigned position or salary grade.');
        }

        if ($user->step_increment >= 8) {
            return back()->with('error', 'Cannot process increment: employee is already at the maximum step (Step 8).');
        }

        $effectiveDate = $request->input('effective_date', now()->toDateString());

        DB::transaction(function () use ($user, $effectiveDate) {
            $oldStep = $user->step_increment ?: 1;
            $oldRate = (float) SalaryGrade::where('grade', $user->position->salary_grade)
                ->where('step', $oldStep)
                ->value('amount') ?? 0.00;

            $newStep = $oldStep + 1;
            $newRate = (float) SalaryGrade::where('grade', $user->position->salary_grade)
                ->where('step', $newStep)
                ->value('amount') ?? 0.00;

            // Update the user's step and last increment date
            $user->step_increment = $newStep;
            $user->last_increment_date = $effectiveDate;
            $user->save();

            // Create audit log entry
            StepIncrementLog::create([
                'user_id' => $user->id,
                'old_step' => $oldStep,
                'new_step' => $newStep,
                'old_rate' => $oldRate,
                'new_rate' => $newRate,
                'approved_by' => session('user_id'),
                'effective_date' => $effectiveDate,
            ]);
        });

        return back()->with('success', "Step increment processed successfully for {$user->last_name}, {$user->first_name}. Now at Step {$user->step_increment}.");
    }
}
