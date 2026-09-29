<?php

namespace App\Models;

use App\Enums\HolidayType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'title',
        'holiday_date',
        'type',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'type' => HolidayType::Regular->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
            'type' => HolidayType::class,
        ];
    }

    /**
     * Holidays that fall in the given calendar month.
     *
     * @param  Builder<Holiday>  $query
     */
    public function scopeForMonth(Builder $query, int $month, int $year): void
    {
        $query->whereMonth('holiday_date', $month)
            ->whereYear('holiday_date', $year);
    }
}
