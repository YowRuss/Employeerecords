<?php

namespace App\Http\Controllers;

use App\Models\LeaveCreditBalance;
use App\Models\LeaveCreditLog;
use App\Models\User;
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

    public function updateBalances(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }
        if (! $this->isManagement()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'vl_balance' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'sl_balance' => ['required', 'numeric', 'min:0', 'max:9999.99'],
        ]);

        $user = User::query()->where('role_id', 1)->findOrFail($id);
        $newVl = round((float) $validated['vl_balance'], 2);
        $newSl = round((float) $validated['sl_balance'], 2);

        DB::transaction(function () use ($user, $newVl, $newSl) {
            $balance = LeaveCreditBalance::firstOrCreate(
                ['user_id' => $user->id],
                ['vl_balance' => 0, 'sl_balance' => 0, 'service_credits' => 0, 'seminar_credits' => 0]
            );

            $oldVl = round((float) $balance->vl_balance, 2);
            $oldSl = round((float) $balance->sl_balance, 2);

            $balance->vl_balance = $newVl;
            $balance->sl_balance = $newSl;
            $balance->last_updated_at = now();
            $balance->save();

            $this->logBalanceSet($user->id, 'VL', $oldVl, $newVl);
            $this->logBalanceSet($user->id, 'SL', $oldSl, $newSl);
        });

        $name = trim($user->first_name.' '.$user->last_name);

        return redirect()->back()->with('success', 'Leave balances updated successfully for '.$name)->with('active_tab', 'balances');
    }

    private function logBalanceSet(int $userId, string $bucket, float $oldBalance, float $newBalance): void
    {
        if ($oldBalance === $newBalance) {
            return;
        }

        LeaveCreditLog::create([
            'user_id' => $userId,
            'source' => 'manual_adjustment',
            'leave_bucket' => $bucket,
            'amount' => round($newBalance - $oldBalance, 2),
            'balance_after' => $newBalance,
            'remarks' => 'Manual balance set from Employee Balances.',
        ]);
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
