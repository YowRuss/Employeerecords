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
            $employeeType = $user->employee_type_label ?? 'NON_TEACHING';

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
                    ['vl_balance' => 0, 'sl_balance' => 0, 'service_credits' => 0, 'seminar_credits' => 0]
                );

                $balanceRecord->increment('seminar_credits', $creditsEarned);

                LeaveCreditLog::create([
                    'user_id' => $seminar->user_id,
                    'source' => 'seminar',
                    'leave_bucket' => 'SEMINAR_CREDIT',
                    'amount' => $creditsEarned,
                    'balance_after' => $balanceRecord->fresh()->seminar_credits,
                    'reference_type' => 'seminars',
                    'reference_id' => $seminar->id,
                    'remarks' => 'Credits earned from approved seminar participation: '.$seminar->title,
                ]);
            }
        });
    }

    /**
     * @return array{vl: float, sl: float}
     */
    public function processLeaveDeduction($leave, float $vlDeduct, float $slDeduct): array
    {
        return DB::transaction(function () use ($leave, $vlDeduct, $slDeduct) {
            $balanceRecord = LeaveCreditBalance::firstOrCreate(
                ['user_id' => $leave->user_id],
                ['vl_balance' => 0, 'sl_balance' => 0, 'service_credits' => 0, 'seminar_credits' => 0]
            );

            $balanceRecord = LeaveCreditBalance::whereKey($balanceRecord->id)->lockForUpdate()->first();

            // Deduct explicit service credits if any
            $serviceUsed = (float) $leave->service_credits_used;
            if ($serviceUsed > 0) {
                $balanceRecord->decrement('service_credits', $serviceUsed);
                LeaveCreditLog::create([
                    'user_id' => $leave->user_id,
                    'source' => 'leave_deduction',
                    'leave_bucket' => 'SERVICE_CREDIT',
                    'amount' => -$serviceUsed,
                    'balance_after' => $balanceRecord->fresh()->service_credits,
                    'reference_type' => 'leave_applications',
                    'reference_id' => $leave->id,
                    'remarks' => 'Service credits deducted for leave application: '.$leave->leave_type,
                ]);
            }

            // Deduct explicit seminar credits if any
            $seminarUsed = (float) $leave->seminar_credits_used;
            if ($seminarUsed > 0) {
                $balanceRecord->decrement('seminar_credits', $seminarUsed);
                LeaveCreditLog::create([
                    'user_id' => $leave->user_id,
                    'source' => 'leave_deduction',
                    'leave_bucket' => 'SEMINAR_CREDIT',
                    'amount' => -$seminarUsed,
                    'balance_after' => $balanceRecord->fresh()->seminar_credits,
                    'reference_type' => 'leave_applications',
                    'reference_id' => $leave->id,
                    'remarks' => 'Seminar credits deducted for leave application: '.$leave->leave_type,
                ]);
            }

            $vlApplied = min(max(0, $vlDeduct), (float) $balanceRecord->vl_balance);
            $slApplied = min(max(0, $slDeduct), (float) $balanceRecord->sl_balance);

            $this->deductLeaveBucket($balanceRecord, $leave, 'vl_balance', 'VL', $vlApplied);
            $this->deductLeaveBucket($balanceRecord, $leave, 'sl_balance', 'SL', $slApplied);

            $workingDays = (float) $leave->working_days;
            $explicitCredits = $serviceUsed + $seminarUsed;
            $usesLeaveCredits = in_array($leave->leave_type, ['Vacation Leave', 'Sick Leave'], true);
            $daysWithPay = $usesLeaveCredits
                ? min($workingDays, $vlApplied + $slApplied + $explicitCredits)
                : $workingDays;

            $leave->update([
                'pay_status' => $daysWithPay >= $workingDays ? 'WITH_PAY' : 'WITHOUT_PAY',
                'credits_deducted' => $vlApplied + $slApplied,
            ]);

            return ['vl' => $vlApplied, 'sl' => $slApplied];
        });
    }

    private function deductLeaveBucket(LeaveCreditBalance $balanceRecord, $leave, string $column, string $bucket, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $balanceRecord->decrement($column, $amount);

        LeaveCreditLog::create([
            'user_id' => $leave->user_id,
            'source' => 'leave_deduction',
            'leave_bucket' => $bucket,
            'amount' => -$amount,
            'balance_after' => $balanceRecord->fresh()->{$column},
            'reference_type' => 'leave_applications',
            'reference_id' => $leave->id,
            'remarks' => 'Credits deducted for leave application: '.$leave->leave_type,
        ]);
    }

    /**
     * Process leave refund when an application is reverted to disapproved.
     *
     * @param  mixed  $leave
     * @return void
     */
    public function processLeaveRefund($leave)
    {
        $hasBucketDeduction = $leave->credits_deducted > 0;
        $hasServiceDeduction = $leave->service_credits_used > 0;
        $hasSeminarDeduction = $leave->seminar_credits_used > 0;

        if ($hasBucketDeduction || $hasServiceDeduction || $hasSeminarDeduction) {
            DB::transaction(function () use ($leave, $hasBucketDeduction, $hasServiceDeduction, $hasSeminarDeduction) {
                $balanceRecord = LeaveCreditBalance::firstOrCreate(
                    ['user_id' => $leave->user_id],
                    ['vl_balance' => 0, 'sl_balance' => 0, 'service_credits' => 0, 'seminar_credits' => 0]
                );

                $bucketLogs = LeaveCreditLog::query()
                    ->where('source', 'leave_deduction')
                    ->where('reference_type', 'leave_applications')
                    ->where('reference_id', $leave->id)
                    ->whereIn('leave_bucket', ['VL', 'SL'])
                    ->get();

                if ($bucketLogs->isNotEmpty()) {
                    foreach ($bucketLogs as $log) {
                        $bucketColumn = $log->leave_bucket === 'SL' ? 'sl_balance' : 'vl_balance';
                        $amount = abs((float) $log->amount);
                        $balanceRecord->increment($bucketColumn, $amount);

                        LeaveCreditLog::create([
                            'user_id' => $leave->user_id,
                            'source' => 'manual_adjustment',
                            'leave_bucket' => $log->leave_bucket,
                            'amount' => $amount,
                            'balance_after' => $balanceRecord->fresh()->{$bucketColumn},
                            'reference_type' => 'leave_applications',
                            'reference_id' => $leave->id,
                            'remarks' => 'Leave reverted to disapproved: '.$leave->leave_type,
                        ]);
                    }
                } elseif ($hasBucketDeduction) {
                    $bucketColumn = null;
                    $leaveBucketEnum = null;

                    if ($leave->leave_type === 'Sick Leave') {
                        $bucketColumn = 'sl_balance';
                        $leaveBucketEnum = 'SL';
                    } elseif (in_array($leave->leave_type, ['Vacation Leave', 'Mandatory/Forced Leave'], true)) {
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
                }

                if ($hasServiceDeduction) {
                    $balanceRecord->increment('service_credits', $leave->service_credits_used);

                    LeaveCreditLog::create([
                        'user_id' => $leave->user_id,
                        'source' => 'manual_adjustment',
                        'leave_bucket' => 'SERVICE_CREDIT',
                        'amount' => $leave->service_credits_used,
                        'balance_after' => $balanceRecord->fresh()->service_credits,
                        'reference_type' => 'leave_applications',
                        'reference_id' => $leave->id,
                        'remarks' => 'Service credits reverted for disapproved leave: '.$leave->leave_type,
                    ]);
                }

                if ($hasSeminarDeduction) {
                    $balanceRecord->increment('seminar_credits', $leave->seminar_credits_used);

                    LeaveCreditLog::create([
                        'user_id' => $leave->user_id,
                        'source' => 'manual_adjustment',
                        'leave_bucket' => 'SEMINAR_CREDIT',
                        'amount' => $leave->seminar_credits_used,
                        'balance_after' => $balanceRecord->fresh()->seminar_credits,
                        'reference_type' => 'leave_applications',
                        'reference_id' => $leave->id,
                        'remarks' => 'Seminar credits reverted for disapproved leave: '.$leave->leave_type,
                    ]);
                }

                $leave->update([
                    'pay_status' => 'WITH_PAY',
                    'credits_deducted' => 0,
                    'service_credits_used' => 0,
                    'seminar_credits_used' => 0,
                ]);
            });
        }
    }
}
