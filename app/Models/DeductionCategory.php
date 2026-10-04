<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeductionCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'is_active',
        'profile_version',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<DeductionType, $this>
     */
    public function types(): HasMany
    {
        return $this->hasMany(DeductionType::class, 'category_id');
    }

    /**
     * Scope to only active categories.
     *
     * @param  Builder<DeductionCategory>  $query
     * @return Builder<DeductionCategory>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
