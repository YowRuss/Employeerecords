<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\IncomeType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class IncomeTypeController extends Controller
{
    /**
     * Display the income types settings page.
     */
    public function index()
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $incomeTypes = IncomeType::orderBy('name')->get();

        return view('hr.settings.incomes.index', compact('incomeTypes'));
    }

    /**
     * Store a new income type.
     */
    public function store(Request $request)
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:income_types,name',
            'description' => 'nullable|string|max:1000',
            'default_amount' => 'required|numeric|min:0',
        ]);

        IncomeType::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'default_amount' => $validated['default_amount'],
            'is_active' => true,
        ]);

        return redirect()->route('hr.settings.incomes.index')
            ->with('success', 'Income type created successfully.');
    }

    /**
     * Update an existing income type.
     */
    public function update(Request $request, string $id)
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $incomeType = IncomeType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:income_types,name,'.$incomeType->id,
            'description' => 'nullable|string|max:1000',
            'default_amount' => 'required|numeric|min:0',
        ]);

        $incomeType->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'default_amount' => $validated['default_amount'],
        ]);

        return redirect()->route('hr.settings.incomes.index')
            ->with('success', 'Income type updated successfully.');
    }

    /**
     * Toggle the is_active flag on an income type.
     */
    public function toggle(string $id)
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $incomeType = IncomeType::findOrFail($id);
        $incomeType->is_active = ! $incomeType->is_active;
        $incomeType->save();

        $status = $incomeType->is_active ? 'activated' : 'deactivated';

        return redirect()->route('hr.settings.incomes.index')
            ->with('success', "Income type \"{$incomeType->name}\" {$status} successfully.");
    }

    /**
     * Delete an income type.
     */
    public function destroy(string $id)
    {
        if ($redirect = $this->authorizeHrAccess()) {
            return $redirect;
        }

        $incomeType = IncomeType::findOrFail($id);
        $incomeType->delete();

        return redirect()->route('hr.settings.incomes.index')
            ->with('success', 'Income type deleted successfully.');
    }

    /**
     * Return all active income types as JSON for dynamic dropdowns.
     */
    public function apiList()
    {
        return response()->json(
            IncomeType::active()->orderBy('name')->get(['id', 'name', 'default_amount'])
        );
    }

    /**
     * Enforce HR/Admin access (role_id 2 or 3).
     */
    private function authorizeHrAccess(): ?RedirectResponse
    {
        $userId = Session::get('user_id') ?? Auth::id();
        if (! $userId) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $roleId = Session::get('role_id') ?? Auth::user()?->role_id;
        if (! $roleId) {
            $roleId = User::where('id', $userId)->value('role_id');
            if ($roleId) {
                Session::put('role_id', (int) $roleId);
            }
        }

        if (! in_array((int) $roleId, [2, 3], true)) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access. HR privileges required.');
        }

        return null;
    }
}
