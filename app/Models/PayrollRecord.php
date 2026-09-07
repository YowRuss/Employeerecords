<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'tax_withheld',
        'gsis_premium',
        'philhealth_premium',
        'pagibig_premium',
        'other_deductions',
        'total_deductions',
        'net_amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'other_deductions' => 'array',
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
}
