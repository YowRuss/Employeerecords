<?php

namespace App\Models;

use App\Enums\PositionCategory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'position_name',
        'category',
        'salary_grade',
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

    public function isTeaching(): bool
    {
        if ($this->category === PositionCategory::Teaching) {
            return true;
        }

        $raw = $this->getRawOriginal('category');
        if ($raw === '0' || $raw === 0 || (is_string($raw) && strcasecmp($raw, 'teaching') === 0)) {
            return true;
        }

        if (preg_match('/(teacher|instructor|master\s*teacher|principal)/i', (string) $this->position_name)) {
            return true;
        }

        return false;
    }
}
