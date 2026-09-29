<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomeType extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'default_amount',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<PayrollIncome, $this>
     */
    public function payrollIncomes(): HasMany
    {
        return $this->hasMany(PayrollIncome::class);
    }

    /**
     * Scope to only active income types.
     *
     * @param  Builder<IncomeType>  $query
     * @return Builder<IncomeType>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
