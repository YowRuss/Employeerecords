<?php

namespace App\Http\Controllers;

use App\Models\IncomeType;
use App\Models\User;
use App\Models\UserAllowance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AllowanceController extends Controller
{
    /**
     * Display the allowance management interface listing all active employees.
     */
    public function index(Request $request)
    {
        $query = User::where('role_id', 1)->where('status', 'active');

        // Filter by Teaching / Non-Teaching Category
        if ($request->filled('category') && $request->category !== 'all') {
            if ($request->category === 'teaching') {
                $query->where(function ($q) {
                    $q->where('employee_type', 1)
                      ->orWhereHas('position', fn ($pos) => $pos->where('category', 'Teaching'));
                });
            } elseif ($request->category === 'non-teaching') {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('employee_type', 0)
                            ->whereDoesntHave('position', fn ($pos) => $pos->where('category', 'Teaching'));
                    })->orWhereHas('position', fn ($pos) => $pos->where('category', 'Non-Teaching'));
                });
            }
        }

        // Filter by Sex
        if ($request->filled('sex') && $request->sex !== 'all') {
            $sexCode = strtolower($request->sex) === 'male' ? 1 : 0;
            $query->whereHas('pdsPersonalInfo', function ($q) use ($sexCode) {
                $q->where('sex', $sexCode);
            });
        }

        // Handle the Search Bar
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $employees = $query->with(['position', 'allowances'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(5)
            ->appends($request->query());

        $totalEmployees = User::where('role_id', 1)->where('status', 'active')->count();

        $incomeTypes = IncomeType::active()->orderBy('name')->get();

        return view('payroll.allowances.index', [
            'employees' => $employees,
            'totalEmployees' => $totalEmployees,
            'incomeTypes' => $incomeTypes,
        ]);
    }

    /**
     * Update all allowances for a specific employee.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'income_types' => 'nullable|array',
            'income_types.*' => 'nullable|exists:income_types,id',
            'income_amounts' => 'nullable|array',
            'income_amounts.*' => 'nullable|numeric|min:0',
        ]);

        $keptIds = [];
        $incomeTypes = $request->input('income_types', []);
        $incomeAmounts = $request->input('income_amounts', []);

        foreach ($incomeTypes as $index => $typeId) {
            if (empty($typeId)) {
                continue;
            }

            $incomeType = IncomeType::find($typeId);
            if (! $incomeType) {
                continue;
            }

            $incomeAmount = (float) ($incomeAmounts[$index] ?? 0);
            if ($incomeAmount <= 0) {
                $incomeAmount = (float) $incomeType->default_amount;
            }
            if ($incomeAmount <= 0) {
                continue;
            }

            $record = UserAllowance::query()
                ->where('user_id', $user->id)
                ->where(function ($query) use ($typeId, $incomeType) {
                    $query->where('income_type_id', $typeId)
                        ->orWhere('allowance_name', $incomeType->name);
                })
                ->first();

            $attributes = [
                'income_type_id' => $typeId,
                'allowance_name' => $incomeType->name,
                'amount' => round($incomeAmount, 2),
                'is_active' => true,
            ];

            if ($record) {
                $record->update($attributes);
            } else {
                $record = UserAllowance::create([
                    'user_id' => $user->id,
                    ...$attributes,
                ]);
            }

            $keptIds[] = $record->id;
        }

        UserAllowance::where('user_id', $user->id)
            ->when($keptIds !== [], fn ($query) => $query->whereNotIn('id', $keptIds))
            ->delete();

        return redirect()->route('payroll.allowances.index')
            ->with('success', 'Allowances updated successfully for ' . ($user->first_name ?? 'employee') . '.');
    }
}
