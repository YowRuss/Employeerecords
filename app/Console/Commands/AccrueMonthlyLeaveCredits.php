<?php

namespace App\Console\Commands;

use App\Enums\PositionCategory;
use App\Models\LeaveCreditBalance;
use App\Models\LeaveCreditLog;
use App\Models\LeaveCreditSetting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AccrueMonthlyLeaveCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:accrue-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Accrue monthly leave credits for Non-Teaching employees';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting monthly leave credit accrual...');

        // Fetch the monthly_accrual_rate for 'NON_TEACHING'
        $setting = LeaveCreditSetting::where('employee_type', 'NON_TEACHING')
            ->where('setting_key', 'monthly_accrual_rate')
            ->first();

        $rate = $setting ? (float) $setting->setting_value : 0;

        if ($rate <= 0) {
            $this->info('Monthly accrual rate is 0 or setting not found. Aborting.');

            return;
        }

        // Fetch all active Non-Teaching users via position.category relationship
        User::whereHas('position', fn ($q) => $q->where('category', PositionCategory::NonTeaching))
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Inactive');
            })
            ->chunk(100, function ($users) use ($rate) {
                foreach ($users as $user) {
                    DB::transaction(function () use ($user, $rate) {
                        // Add the rate to their LeaveCreditBalance
                        $balance = LeaveCreditBalance::firstOrCreate(
                            ['user_id' => $user->id],
                            ['vl_balance' => 0, 'sl_balance' => 0, 'service_credits' => 0]
                        );

                        $balance->increment('vl_balance', $rate);
                        $balance->increment('sl_balance', $rate);

                        // Insert LeaveCreditLog for VL
                        LeaveCreditLog::create([
                            'user_id' => $user->id,
                            'source' => 'monthly_accrual',
                            'leave_bucket' => 'VL',
                            'amount' => $rate,
                            'balance_after' => $balance->fresh()->vl_balance,
                            'reference_type' => 'system',
                            'reference_id' => null,
                            'remarks' => 'Monthly leave credit accrual for Vacation Leave',
                        ]);

                        // Insert LeaveCreditLog for SL
                        LeaveCreditLog::create([
                            'user_id' => $user->id,
                            'source' => 'monthly_accrual',
                            'leave_bucket' => 'SL',
                            'amount' => $rate,
                            'balance_after' => $balance->fresh()->sl_balance,
                            'reference_type' => 'system',
                            'reference_id' => null,
                            'remarks' => 'Monthly leave credit accrual for Sick Leave',
                        ]);
                    });
                }
            });

        $this->info('Monthly leave credit accrual completed successfully.');
    }
}
