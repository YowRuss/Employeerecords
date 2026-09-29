<?php

namespace App\Enums;

enum RemittanceAgency: string
{
    case Gsis = 'GSIS';
    case PhilHealth = 'PhilHealth';
    case PagIbig = 'PagIBIG';
    case Bir = 'BIR';

    /**
     * Human-readable agency name used in headings and file names.
     */
    public function label(): string
    {
        return match ($this) {
            self::Gsis => 'GSIS',
            self::PhilHealth => 'PhilHealth',
            self::PagIbig => 'Pag-IBIG',
            self::Bir => 'BIR',
        };
    }

    /**
     * The payroll_records column holding this agency's withholding.
     */
    public function deductionColumn(): string
    {
        return match ($this) {
            self::Gsis => 'gsis_premium',
            self::PhilHealth => 'philhealth_premium',
            self::PagIbig => 'pagibig_premium',
            self::Bir => 'tax_withheld',
        };
    }

    /**
     * The pds_personal_info column holding the employee's membership number
     * for this agency, which the official upload templates key on.
     */
    public function identifierColumn(): string
    {
        return match ($this) {
            self::Gsis => 'gsis_no',
            self::PhilHealth => 'philhealth_no',
            self::PagIbig => 'pagibig_no',
            self::Bir => 'tin_no',
        };
    }

    /**
     * Column heading for the membership number.
     */
    public function identifierLabel(): string
    {
        return match ($this) {
            self::Gsis => 'GSIS BP No.',
            self::PhilHealth => 'PhilHealth No.',
            self::PagIbig => 'Pag-IBIG MID No.',
            self::Bir => 'TIN',
        };
    }

    /**
     * What the withheld amount represents on the report.
     */
    public function amountLabel(): string
    {
        return $this === self::Bir ? 'Tax Withheld' : 'Premium Withheld';
    }

    /**
     * Bootstrap badge classes used to colour-code the agency on payroll screens.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Gsis => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
            self::PhilHealth => 'bg-success-subtle text-success-emphasis border-success-subtle',
            self::PagIbig => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
            self::Bir => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
        };
    }
}
