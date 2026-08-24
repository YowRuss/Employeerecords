<?php

namespace App\Enums;

enum PositionCategory: string
{
    case Teaching = '0';
    case NonTeaching = '1';

    /**
     * Get the human-readable label for this category.
     */
    public function label(): string
    {
        return match ($this) {
            self::Teaching => 'Teaching',
            self::NonTeaching => 'Non-Teaching',
        };
    }
}
