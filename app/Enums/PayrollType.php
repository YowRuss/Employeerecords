<?php

namespace App\Enums;

enum PayrollType: string
{
    case Regular = 'Regular Monthly';
    case MidYearBonus = 'Mid-Year Bonus';
    case YearEndBonus = 'Year-End Bonus';

    /**
     * Statutory cash gift released together with the Year-End Bonus.
     */
    private const YEAR_END_CASH_GIFT = 5_000.00;

    /**
     * Bonus runs pay one month of basic salary and carry no statutory
     * premiums, absences, lates, or loan amortization.
     */
    public function isBonus(): bool
    {
        return $this !== self::Regular;
    }

    /**
     * Cash gift added on top of the basic salary for this payout.
     */
    public function cashGift(): float
    {
        return $this === self::YearEndBonus ? self::YEAR_END_CASH_GIFT : 0.00;
    }

    /**
     * Bootstrap badge classes used to colour-code the payout on payroll screens.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Regular => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
            self::MidYearBonus => 'bg-info-subtle text-info-emphasis border-info-subtle',
            self::YearEndBonus => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
        };
    }
}
