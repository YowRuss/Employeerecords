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
