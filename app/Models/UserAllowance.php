<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAllowance extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'income_type_id',
        'allowance_name',
        'amount',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
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
