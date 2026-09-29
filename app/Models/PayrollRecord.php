<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRecord extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'user_id',
        'basic_rate',
        'earned_for_period',
        'pera_amount',
        'gross_earned',
        'absences_amount',
        'late_deduction',
        'tax_withheld',
        'gsis_premium',
        'philhealth_premium',
        'pagibig_premium',
        'loan_amortization',
        'other_deductions',
        'total_deductions',
        'net_amount',
        'is_full_lwop',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'other_deductions' => 'array',
            'is_full_lwop' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<PayrollPeriod, $this>
     */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias for user relation representing the employee.
     *
     * @return BelongsTo<User, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Additional income/allowance line items attached to this record.
     *
     * @return HasMany<PayrollIncome, $this>
     */
    public function payrollIncomes(): HasMany
    {
        return $this->hasMany(PayrollIncome::class);
    }
}
