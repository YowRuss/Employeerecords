<?php

namespace App\Models;

use App\Enums\PayrollType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    protected $fillable = [
        'fund_cluster',
        'payroll_type',
        'period_month',
        'period_year',
        'description',
        'status',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'payroll_type' => PayrollType::Regular->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payroll_type' => PayrollType::class,
        ];
    }

    /**
     * @return HasMany<PayrollRecord, $this>
     */
    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }
}
