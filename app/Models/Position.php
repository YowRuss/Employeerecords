<?php

namespace App\Models;

use App\Enums\PositionCategory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'position_name',
        'category',
    ];

    /**
     * @return array<string, class-string>
     */
    protected function casts(): array
    {
        return [
            'category' => PositionCategory::class,
        ];
    }
}
