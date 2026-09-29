<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    /**
     * Lending institutions keyed by the token searched for in the loan type.
     *
     * @var array<string, string>
     */
    public const AGENCIES = [
        'GSIS' => 'GSIS',
        'PAG-IBIG' => 'Pag-IBIG',
        'PHILHEALTH' => 'PhilHealth',
        'LANDBANK' => 'LANDBANK',
        'CNHS' => 'CNHS',
    ];

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'loan_type',
        'principal_amount',
        'monthly_amortization',
        'running_balance',
        'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'principal_amount' => 0.00,
        'monthly_amortization' => 0.00,
        'running_balance' => 0.00,
        'status' => 'Active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'principal_amount' => 'decimal:2',
            'monthly_amortization' => 'decimal:2',
            'running_balance' => 'decimal:2',
        ];
    }

    /**
     * Loans still being amortised — active status with an outstanding balance.
     *
     * @param  Builder<Loan>  $query
     */
    public function scopeOutstanding(Builder $query): void
    {
        $query->where('status', 'Active')->where('running_balance', '>', 0);
    }

    /**
     * Portion of the principal already settled.
     */
    public function getAmountPaidAttribute(): float
    {
        return max(0, (float) $this->principal_amount - (float) $this->running_balance);
    }

    /**
     * Lending institution behind the loan type, used for grouping and filtering.
     */
    public function getAgencyAttribute(): string
    {
        $type = strtoupper((string) $this->loan_type);

        foreach (self::AGENCIES as $needle => $label) {
            if (str_contains($type, $needle)) {
                return $label;
            }
        }

        return 'Other';
    }

    /**
     * Instalments left before the balance clears, derived from the outstanding
     * balance rather than a stored term.
     */
    public function getRemainingMonthsAttribute(): ?int
    {
        $monthly = (float) $this->monthly_amortization;
        $balance = (float) $this->running_balance;

        if ($monthly <= 0 || $balance <= 0) {
            return null;
        }

        return (int) ceil($balance / $monthly);
    }

    /**
     * Repayment progress as a percentage of the principal.
     */
    public function getProgressPercentageAttribute(): float
    {
        $principal = (float) $this->principal_amount;

        if ($principal <= 0) {
            return 100.0;
        }

        return round(min(100, ($this->amount_paid / $principal) * 100), 2);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
