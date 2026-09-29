<?php

namespace App\Http\Controllers;

use App\Models\ServiceCredit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ServiceCreditController extends Controller
{
    public function store(Request $request, $id)
    {
        $roleId = Session::get('role_id');
        if (! Session::has('user_id') || ! in_array($roleId, [2, 3], true)) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access. HR privileges required.');
        }

        $employee = User::query()->where('role_id', 1)->find($id);
        if (! $employee) {
            return redirect()->route('hr.staff_profiling')->with('error', 'Employee not found.');
        }

        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'days' => ['required', 'numeric', 'min:0.5', 'max:30'],
        ]);

        ServiceCredit::create([
            'user_id' => $employee->id,
            'transaction_date' => $validated['transaction_date'],
            'description' => $validated['description'],
            'type' => 'earned',
            'days' => $validated['days'],
        ]);

        return redirect()
            ->route('hr.view_profile', $employee->id)
            ->with('success', 'Service credits granted.')
            ->with('active_tab', 'credits');
    }
}
