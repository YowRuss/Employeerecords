<?php

namespace App\Enums;

enum HolidayType: string
{
    case Regular = 'Regular';
    case SpecialNonWorking = 'Special Non-Working';
    case Suspension = 'Suspension';

    /**
     * Bootstrap badge classes for the holiday type.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Regular => 'bg-danger',
            self::SpecialNonWorking => 'bg-warning text-dark',
            self::Suspension => 'bg-primary',
        };
    }

    /**
     * FullCalendar event color for this holiday type.
     */
    public function calendarColor(): string
    {
        return match ($this) {
            self::Regular => '#dc3545',
            self::SpecialNonWorking => '#fd7e14',
            self::Suspension => '#6c757d',
        };
    }
}
