<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollIncome extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'payroll_record_id',
        'user_id',
        'income_type_id',
        'amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<PayrollRecord, $this>
     */
    public function payrollRecord(): BelongsTo
    {
        return $this->belongsTo(PayrollRecord::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<IncomeType, $this>
     */
    public function incomeType(): BelongsTo
    {
        return $this->belongsTo(IncomeType::class);
    }
}
