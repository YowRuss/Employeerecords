<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class LeaveCreditController extends Controller
{
    private function requireAuth()
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        return null;
    }

    private function isManagement(): bool
    {
        $roleId = Session::get('role_id');

        return $roleId !== null && (int) $roleId !== 1;
    }

    public function adjust(Request $request, $userId)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }
        if (! $this->isManagement()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'adjustment_type' => 'required|in:add,deduct',
            'amount' => 'required|numeric|min:0.5',
            'leave_bucket' => 'required|in:VL,SL,SERVICE_CREDIT',
            'remarks' => 'required|string|max:255',
        ]);

        $amount = (float) $request->amount;
        if ($request->adjustment_type === 'deduct') {
            $amount = -$amount;
        }

        DB::transaction(function () use ($userId, $amount, $request) {
            $balanceRow = DB::table('leave_credit_balances')
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            $column = 'vl_balance';
            if ($request->leave_bucket === 'SL') {
                $column = 'sl_balance';
            }
            if ($request->leave_bucket === 'SERVICE_CREDIT') {
                $column = 'service_credits';
            }

            $currentBalance = $balanceRow ? (float) $balanceRow->{$column} : 0.0;
            $newBalance = $currentBalance + $amount;

            if ($balanceRow) {
                DB::table('leave_credit_balances')
                    ->where('id', $balanceRow->id)
                    ->update([
                        $column => $newBalance,
                        'last_updated_at' => now(),
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('leave_credit_balances')->insert([
                    'user_id' => $userId,
                    $column => $newBalance,
                    'last_updated_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('leave_credit_logs')->insert([
                'user_id' => $userId,
                'source' => 'manual_adjustment',
                'leave_bucket' => $request->leave_bucket,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => null,
                'reference_id' => null,
                'remarks' => $request->remarks,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('success', 'Leave credits adjusted successfully.');
    }

    public function updateSettings(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }
        if (! $this->isManagement()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'settings' => 'required|array',
            'settings.TEACHING' => 'required|array',
            'settings.NON_TEACHING' => 'required|array',
            'settings.GLOBAL' => 'nullable|array',
            'settings.GLOBAL.certifying_officer_name' => 'nullable|string',
            'settings.GLOBAL.certifying_officer_position' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->settings as $type => $keys) {
                foreach ($keys as $key => $val) {
                    $dbKey = $key;
                    if ($key === 'seminar_rate') {
                        $dbKey = 'seminar_hour_to_credit_rate';
                    }

                    // For non-teaching, strictly enforce seminar rate is 0
                    if ($type === 'NON_TEACHING' && $dbKey === 'seminar_hour_to_credit_rate') {
                        $val = 0;
                    }

                    DB::table('leave_credit_settings')
                        ->where('employee_type', $type)
                        ->where('setting_key', $dbKey)
                        ->update(['setting_value' => $val]);
                }
            }
        });

        return back()->with('success', 'Leave credit settings updated successfully.');
    }
}
