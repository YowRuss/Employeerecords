<?php

namespace App\Services;

use App\Models\LeaveCreditBalance;
use App\Models\LeaveCreditLog;
use App\Models\LeaveCreditSetting;
use App\Models\Seminar;
use Illuminate\Support\Facades\DB;

class LeaveCreditService
{
    /**
     * Process seminar approval to calculate and add leave credits.
     *
     * @param  mixed  $seminar
     * @return void
     */
    public function processSeminarApproval($seminar)
    {
        DB::transaction(function () use ($seminar) {
            $user = $seminar->user;
            $employeeType = $user->employee_type ?? 'NON_TEACHING';

            $setting = LeaveCreditSetting::where('employee_type', $employeeType)
                ->where('setting_key', 'seminar_hour_to_credit_rate')
                ->first();
            $rate = $setting ? (float) $setting->setting_value : 0;

            if ($employeeType === 'TEACHING') {
                $creditsEarned = $seminar->hours * $rate;
            } else {
                $creditsEarned = 0;
                $rate = 0;
            }

            $seminar->update([
                'status' => 'APPROVED',
                'rate_applied' => $rate,
                'credits_earned' => $creditsEarned,
            ]);

            if ($creditsEarned > 0) {
                $balanceRecord = LeaveCreditBalance::firstOrCreate(
                    ['user_id' => $seminar->user_id],
                    ['vl_balance' => 0, 'sl_balance' => 0, 'service_credits' => 0]
                );

                $balanceRecord->increment('service_credits', $creditsEarned);

                LeaveCreditLog::create([
                    'user_id' => $seminar->user_id,
                    'source' => 'seminar',
                    'leave_bucket' => 'SERVICE_CREDIT',
                    'amount' => $creditsEarned,
                    'balance_after' => $balanceRecord->fresh()->service_credits,
                    'reference_type' => 'seminars',
                    'reference_id' => $seminar->id,
                    'remarks' => 'Credits earned from approved seminar participation: '.$seminar->title,
                ]);
            }
        });
    }

    public function processLeaveDeduction($leave, $workingDays)
    {
        DB::transaction(function () use ($leave, $workingDays) {
            if (in_array($leave->leave_type, ['Maternity Leave', 'Paternity Leave'])) {
                $leave->update([
                    'pay_status' => 'WITH_PAY',
                    'credits_deducted' => 0,
                ]);

                return;
            }

            $balanceRecord = LeaveCreditBalance::firstOrCreate(
                ['user_id' => $leave->user_id],
                ['vl_balance' => 0, 'sl_balance' => 0, 'service_credits' => 0]
            );

            // Determine bucket
            if ($leave->leave_type === 'Sick Leave') {
                $bucketColumn = 'sl_balance';
                $leaveBucketEnum = 'SL';
            } else {
                // Vacation Leave, Others, Special Privilege Leave, etc.
                $bucketColumn = 'vl_balance';
                $leaveBucketEnum = 'VL';
            }

            $available = $balanceRecord->{$bucketColumn};
            $toDeduct = min($available, $workingDays);

            // If we deducted anything, we mark it WITH_PAY. If balance was 0, it's WITHOUT_PAY.
            $payStatus = $toDeduct > 0 ? 'WITH_PAY' : 'WITHOUT_PAY';

            if ($toDeduct > 0) {
                $balanceRecord->decrement($bucketColumn, $toDeduct);

                LeaveCreditLog::create([
                    'user_id' => $leave->user_id,
                    'source' => 'leave_deduction',
                    'leave_bucket' => $leaveBucketEnum,
                    'amount' => -$toDeduct,
                    'balance_after' => $balanceRecord->fresh()->{$bucketColumn},
                    'reference_type' => 'leave_applications',
                    'reference_id' => $leave->id,
                    'remarks' => 'Credits deducted for leave application: '.$leave->leave_type,
                ]);
            }

            $leave->update([
                'pay_status' => $payStatus,
                'credits_deducted' => $toDeduct,
            ]);
        });
    }

    /**
     * Process leave refund when an application is reverted to disapproved.
     *
     * @param  mixed  $leave
     * @return void
     */
    public function processLeaveRefund($leave)
    {
        if ($leave->credits_deducted > 0) {
            DB::transaction(function () use ($leave) {
                $balanceRecord = LeaveCreditBalance::firstOrCreate(
                    ['user_id' => $leave->user_id],
                    ['vl_balance' => 0, 'sl_balance' => 0, 'service_credits' => 0]
                );

                $bucketColumn = null;
                $leaveBucketEnum = null;

                if ($leave->leave_type === 'Sick Leave') {
                    $bucketColumn = 'sl_balance';
                    $leaveBucketEnum = 'SL';
                } elseif (in_array($leave->leave_type, ['Vacation Leave', 'Mandatory/Forced Leave'])) {
                    $bucketColumn = 'vl_balance';
                    $leaveBucketEnum = 'VL';
                }

                if ($bucketColumn) {
                    $balanceRecord->increment($bucketColumn, $leave->credits_deducted);

                    LeaveCreditLog::create([
                        'user_id' => $leave->user_id,
                        'source' => 'manual_adjustment',
                        'leave_bucket' => $leaveBucketEnum,
                        'amount' => $leave->credits_deducted,
                        'balance_after' => $balanceRecord->fresh()->{$bucketColumn},
                        'reference_type' => 'leave_applications',
                        'reference_id' => $leave->id,
                        'remarks' => 'Leave reverted to disapproved: '.$leave->leave_type,
                    ]);
                }

                $leave->update([
                    'pay_status' => 'WITH_PAY',
                    'credits_deducted' => 0,
                ]);
            });
        }
    }
}
