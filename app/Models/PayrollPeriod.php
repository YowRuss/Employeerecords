<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    protected $fillable = [
        'fund_cluster',
        'period_month',
        'period_year',
        'description',
        'status',
    ];

    /**
     * @return HasMany<PayrollRecord, $this>
     */
    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }
}
