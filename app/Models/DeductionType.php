<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeductionType extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'code',
        'excel_column',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<DeductionCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(DeductionCategory::class, 'category_id');
    }

    /**
     * Scope to only active deduction types.
     *
     * @param  Builder<DeductionType>  $query
     * @return Builder<DeductionType>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
