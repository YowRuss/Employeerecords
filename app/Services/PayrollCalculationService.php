<?php

namespace App\Services;

class PayrollCalculationService
{
    /** GSIS employee premium share rate (9%). */
    private const GSIS_RATE = 0.09;

    /** PhilHealth total premium rate (5%), employee pays half (2.5%). */
    private const PHILHEALTH_EMPLOYEE_RATE = 0.025;

    /** PhilHealth maximum basic salary cap for premium computation (₱100,000). */
    private const PHILHEALTH_SALARY_CAP = 100_000.00;

    /** Standard Pag-IBIG employee monthly contribution. */
    private const PAGIBIG_FIXED = 200.00;

    /** TRAIN Law exemption ceiling for 13th month pay and other benefits (₱90,000). */
    private const BONUS_TAX_EXEMPT_CEILING = 90_000.00;

    /** Placeholder flat rate applied to the taxable excess of a bonus. */
    private const BONUS_TAX_RATE = 0.20;

    /**
     * Calculate mandatory Philippine government deductions for an employee.
     *
     * @return array{gsis_premium: float, philhealth_premium: float, pagibig_premium: float}
     */
    public function calculateMandatoryDeductions(float $basicSalary): array
    {
        return [
            'gsis_premium' => $this->computeGsis($basicSalary),
            'philhealth_premium' => $this->computePhilHealth($basicSalary),
            'pagibig_premium' => $this->computePagibig(),
        ];
    }

    /**
     * Withholding tax on a Mid-Year or Year-End Bonus.
     *
     * Under the TRAIN Law the first ₱90,000 of 13th month pay and other
     * benefits is exempt; only the excess is taxable. The ceiling is applied
     * per payout here rather than against the employee's running annual total.
     */
    public function calculateBonusTax(float $grossBonus): float
    {
        $taxableAmount = max(0, $grossBonus - self::BONUS_TAX_EXEMPT_CEILING);

        return round($taxableAmount * self::BONUS_TAX_RATE, 2);
    }

    /**
     * GSIS employee premium: 9% of basic salary.
     */
    private function computeGsis(float $basicSalary): float
    {
        return round($basicSalary * self::GSIS_RATE, 2);
    }

    /**
     * PhilHealth employee share: 2.5% of basic salary,
     * capped at the maximum salary ceiling of ₱100,000
     * (max employee deduction = ₱2,500).
     */
    private function computePhilHealth(float $basicSalary): float
    {
        $computationBase = min($basicSalary, self::PHILHEALTH_SALARY_CAP);

        return round($computationBase * self::PHILHEALTH_EMPLOYEE_RATE, 2);
    }

    /**
     * Pag-IBIG: fixed monthly employee contribution of ₱200.
     */
    private function computePagibig(): float
    {
        return self::PAGIBIG_FIXED;
    }
}
