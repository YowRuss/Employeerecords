<?php

namespace App\Http\Controllers;

use App\Models\PayrollRecord;
use App\Models\PdsPersonalInfo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use setasign\Fpdi\Tcpdf\Fpdi;

class Bir2316Controller extends Controller
{
    /** Template path relative to storage/app. */
    private const TEMPLATE_PATH = 'templates/2316 Sep 2021 ENCS_Final_corrected.pdf';

    /** DepEd employer TIN — update if different for your school. */
    private const EMPLOYER_TIN = '000-529-464-00000';

    /** RDO code for Tuguegarao City. */
    private const EMPLOYER_RDO = '009';

    /**
     * Display the BIR 2316 generation dashboard for HR.
     */
    public function index(Request $request)
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        $year = (int) $request->input('year', Carbon::now()->year);

        $employees = User::where('role_id', 1)
            ->where('status', 'active')
            ->with('position')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(5)
            ->withQueryString();

        // Grab TIN from pds_personal_info for the employees on this page.
        $tins = PdsPersonalInfo::whereIn('user_id', $employees->pluck('id'))
            ->pluck('tin_no', 'user_id');

        return view('hr.bir2316.index', compact('employees', 'year', 'tins'));
    }

    /**
     * Aggregate yearly payroll data and generate a pre-filled BIR Form 2316 PDF.
     */
    public function generatePdf(string $userId, int $year, Request $request)
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        // --- 1. Fetch employee and demographics ---
        $employee = User::with('position')->findOrFail($userId);
        $pds = PdsPersonalInfo::where('user_id', $userId)->first();

        // --- 2. Aggregate all FINALIZED payroll records for the year ---
        $records = PayrollRecord::whereHas('payrollPeriod', function ($query) use ($year) {
            $query->where('period_year', $year)
                ->where('status', 'FINALIZED');
        })->where('user_id', $userId)->get();

        if ($records->isEmpty()) {
            return back()->with('error', "No approved payroll records found for {$employee->last_name} in {$year}.");
        }

        $totals = [
            'gross_earned' => $records->sum('gross_earned'),
            'basic_rate' => $records->sum('basic_rate'),
            'pera_amount' => $records->sum('pera_amount'),
            'gsis_premium' => $records->sum('gsis_premium'),
            'philhealth_premium' => $records->sum('philhealth_premium'),
            'pagibig_premium' => $records->sum('pagibig_premium'),
            'tax_withheld' => $records->sum('tax_withheld'),
            'absences_amount' => $records->sum('absences_amount'),
            'total_deductions' => $records->sum('total_deductions'),
            'net_amount' => $records->sum('net_amount'),
        ];

        // Non-Taxable Compensation = Mandatory contributions (GSIS + PhilHealth + Pag-IBIG) + PERA
        $nonTaxable = $totals['gsis_premium']
            + $totals['philhealth_premium']
            + $totals['pagibig_premium']
            + $totals['pera_amount'];

        // Taxable Compensation = Gross - Non-Taxable
        $taxable = $totals['gross_earned'] - $nonTaxable;

        // --- 3. Build PDF ---
        $templatePath = storage_path('app/'.self::TEMPLATE_PATH);

        if (! file_exists($templatePath)) {
            return back()->with('error', 'BIR 2316 PDF template not found. Please upload it to storage/app/templates/.');
        }

        $debug = config('app.debug') && $request->boolean('debug');

        try {
            $pdf = new Fpdi('P', 'mm', [215.9, 330.2], true, 'UTF-8', false);

            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetCellPadding(0);
            $pdf->SetAutoPageBreak(false, 0);
            $pdf->SetTitle("BIR Form 2316 - {$year} - {$employee->last_name}");
            $pdf->SetCreator('CNHS-JHS HR System');

            $pdf->setSourceFile($templatePath);
            $tplIdx = $pdf->importPage(1);
            $size = $pdf->getTemplateSize($tplIdx);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplIdx, 0, 0, $size['width'], $size['height']);

            $pdf->SetTextColor(0, 0, 0);

            // --- Helpers (same pattern as LeaveController) ---
            $box = function (array $b) use ($pdf, $debug) {
                if ($debug) {
                    $pdf->SetDrawColor(220, 0, 0);
                    $pdf->SetLineWidth(0.25);
                    $pdf->Rect($b[0], $b[1], $b[2], $b[3]);
                }
            };

            $writeBox = function (array $b, ?string $text, string $align = 'L', float $fontSize = 8) use ($pdf, $box) {
                $box($b);
                $text = trim((string) $text);
                if ($text === '') {
                    return;
                }
                $pdf->SetFont('helvetica', '', $fontSize);
                $pdf->SetXY($b[0], $b[1]);
                $pdf->Cell($b[2], $b[3], $text, 0, 0, $align, false, '', 1, true, 'T', 'M');
            };

            $writeBoxBold = function (array $b, ?string $text, string $align = 'L', float $fontSize = 8) use ($pdf, $box) {
                $box($b);
                $text = trim((string) $text);
                if ($text === '') {
                    return;
                }
                $pdf->SetFont('helvetica', 'B', $fontSize);
                $pdf->SetXY($b[0], $b[1]);
                $pdf->Cell($b[2], $b[3], $text, 0, 0, $align, false, '', 1, true, 'T', 'M');
                $pdf->SetFont('helvetica', '', $fontSize);
            };

            $money = function (float $amount): string {
                return number_format($amount, 2, '.', ',');
            };

            // --- Coordinate layout for BIR Form 2316 (Sep 2021 ENCS) ---
            // All coordinates are [x, y, width, height] in mm
            // These are calibrated for the 215.9 x 330.2 mm template
            $layout = $this->pdfLayout();

            // === PART I — Employee Information ===

            // For the calendar year
            $writeBox($layout['year_ending'], (string) $year, 'C', 9);

            // Employee TIN
            $tin = $pds->tin_no ?? '';
            $writeBox($layout['employee_tin'], $tin, 'C', 9);

            // Employee Name
            $writeBox($layout['employee_last_name'], strtoupper($employee->last_name ?? ''), 'L', 8);
            $writeBox($layout['employee_first_name'], strtoupper($employee->first_name ?? ''), 'L', 8);
            $writeBox($layout['employee_middle_name'], strtoupper($employee->middle_name ?? ''), 'L', 8);

            // Registered Address (from PDS residential)
            $address = $this->buildAddress($pds);
            $writeBox($layout['employee_address'], $address, 'L', 7);

            // Zip Code
            $zip = $pds->res_zipcode ?? $pds->res_zip ?? '';
            $writeBox($layout['employee_zip'], $zip, 'C', 8);

            // Date of Birth
            $dob = '';
            if ($pds && $pds->date_of_birth) {
                try {
                    $dob = Carbon::parse($pds->date_of_birth)->format('m/d/Y');
                } catch (\Throwable) {
                    $dob = $pds->date_of_birth;
                }
            }
            $writeBox($layout['employee_dob'], $dob, 'C', 8);

            // === PART II — Employer Information ===
            $writeBox($layout['employer_tin'], self::EMPLOYER_TIN, 'C', 8);
            $writeBox($layout['employer_rdo'], self::EMPLOYER_RDO, 'C', 8);
            $writeBox($layout['employer_name'], 'DEPARTMENT OF EDUCATION', 'L', 7);

            // Employer Address
            $writeBox($layout['employer_address'], 'CAGAYAN NATIONAL HIGH SCHOOL - JHS, TUGUEGARAO CITY, CAGAYAN', 'L', 6.5);

            // === PART IV — Summary / Details of Compensation ===

            // 25 — Gross Compensation Income (present employer)
            $writeBox($layout['gross_compensation'], $money($totals['gross_earned']), 'R', 8);

            // 26 — Less: Non-Taxable / Exempt Compensation
            // 26A — Mandatory contributions (GSIS, PhilHealth, Pag-IBIG)
            $mandatoryContrib = $totals['gsis_premium'] + $totals['philhealth_premium'] + $totals['pagibig_premium'];
            $writeBox($layout['non_tax_mandatory'], $money($mandatoryContrib), 'R', 8);

            // 26B — PERA (total for the year)
            $writeBox($layout['non_tax_pera'], $money($totals['pera_amount']), 'R', 8);

            // 26D — Total Non-Taxable Compensation
            $writeBox($layout['non_tax_total'], $money($nonTaxable), 'R', 8);

            // 27 — Taxable Compensation Income
            $writeBox($layout['taxable_compensation'], $money($taxable), 'R', 8);

            // 28 — Tax Due
            $writeBox($layout['tax_due'], $money($totals['tax_withheld']), 'R', 8);

            // 29 — Tax Withheld
            // 29A — Tax withheld Jan-Nov (we use total since monthly is already broken down)
            $writeBox($layout['tax_withheld_jan_nov'], $money($totals['tax_withheld']), 'R', 8);

            // 30 — Total Tax Withheld and adjusted
            $writeBox($layout['total_tax_withheld'], $money($totals['tax_withheld']), 'R', 8);

            // === PART V — Details of Compensation (breakdown) ===

            // Basic Salary (total for the year)
            $writeBox($layout['detail_basic_salary'], $money($totals['basic_rate']), 'R', 8);

            // PERA
            $writeBox($layout['detail_pera'], $money($totals['pera_amount']), 'R', 8);

            // Gross Total
            $writeBox($layout['detail_gross_total'], $money($totals['gross_earned']), 'R', 8);

            // Mandatory GSIS
            $writeBox($layout['detail_gsis'], $money($totals['gsis_premium']), 'R', 8);

            // Mandatory PhilHealth
            $writeBox($layout['detail_philhealth'], $money($totals['philhealth_premium']), 'R', 8);

            // Mandatory Pag-IBIG
            $writeBox($layout['detail_pagibig'], $money($totals['pagibig_premium']), 'R', 8);

            // Total Non-Taxable (in detail section)
            $writeBox($layout['detail_non_tax_total'], $money($nonTaxable), 'R', 8);

            // Taxable Compensation (in detail section)
            $writeBox($layout['detail_taxable'], $money($taxable), 'R', 8);

            // Net Take Home after deductions
            $writeBox($layout['detail_net_pay'], $money($totals['net_amount']), 'R', 8);

            // --- Output PDF ---
            $fileName = "BIR_2316_{$year}_{$employee->last_name}.pdf";

            return response($pdf->Output($fileName, 'S'), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"{$fileName}\"",
            ]);
        } catch (\Throwable $e) {
            Log::error('BIR 2316 PDF generation failed: '.$e->getMessage(), [
                'user_id' => $userId,
                'year' => $year,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to generate BIR 2316 PDF. '.$e->getMessage());
        }
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Build a one-line address string from the PDS residential address fields.
     */
    private function buildAddress(?PdsPersonalInfo $pds): string
    {
        if (! $pds) {
            return '';
        }

        $parts = array_filter([
            $pds->res_house_no,
            $pds->res_street,
            $pds->res_subdivision,
        ]);

        // Try to resolve named location references
        $barangay = $pds->barangay?->brgy_desc ?? '';
        $city = $pds->city?->city_name ?? '';
        $province = $pds->province?->prov_desc ?? '';

        if ($barangay) {
            $parts[] = 'BRGY. '.strtoupper($barangay);
        }
        if ($city) {
            $parts[] = strtoupper($city);
        }
        if ($province) {
            $parts[] = strtoupper($province);
        }

        $assembled = implode(', ', array_filter($parts));

        // Fallback to the concatenated residential_address if it exists and parts were empty
        if ($assembled === '' && ! empty($pds->residential_address)) {
            $assembled = strtoupper($pds->residential_address);
        }

        return $assembled;
    }

    /**
     * PDF coordinate layout for BIR Form 2316 (Sep 2021 ENCS revision).
     *
     * Each entry is [x, y, width, height] in mm, calibrated for the
     * 215.9 × 330.2 mm legal-size template.
     *
     * Use ?debug=1 on the generate URL to draw red rectangles on each
     * field so you can visually fine-tune the coordinates.
     *
     * @return array<string, array{0: float, 1: float, 2: float, 3: float}>
     */
    private function pdfLayout(): array
    {
        return [
            // --- PART I: Employee Information ---
            'year_ending' => [155, 22, 40, 5],
            'employee_tin' => [30, 38, 65, 5],
            'employee_last_name' => [30, 48, 55, 5],
            'employee_first_name' => [88, 48, 55, 5],
            'employee_middle_name' => [146, 48, 45, 5],
            'employee_address' => [30, 58, 155, 5],
            'employee_zip' => [188, 58, 20, 5],
            'employee_dob' => [30, 68, 45, 5],

            // --- PART II: Employer Information ---
            'employer_tin' => [30, 88, 65, 5],
            'employer_rdo' => [100, 88, 20, 5],
            'employer_name' => [30, 98, 160, 5],
            'employer_address' => [30, 108, 155, 5],

            // --- PART IV: Summary ---
            'gross_compensation' => [145, 178, 55, 5],
            'non_tax_mandatory' => [145, 188, 55, 5],
            'non_tax_pera' => [145, 195, 55, 5],
            'non_tax_total' => [145, 208, 55, 5],
            'taxable_compensation' => [145, 218, 55, 5],
            'tax_due' => [145, 228, 55, 5],
            'tax_withheld_jan_nov' => [145, 238, 55, 5],
            'total_tax_withheld' => [145, 258, 55, 5],

            // --- PART V: Details of Compensation ---
            'detail_basic_salary' => [145, 272, 55, 5],
            'detail_pera' => [145, 279, 55, 5],
            'detail_gross_total' => [145, 286, 55, 5],
            'detail_gsis' => [145, 293, 55, 5],
            'detail_philhealth' => [145, 300, 55, 5],
            'detail_pagibig' => [145, 307, 55, 5],
            'detail_non_tax_total' => [145, 314, 55, 5],
            'detail_taxable' => [145, 320, 55, 5],
            'detail_net_pay' => [145, 326, 55, 5],
        ];
    }
}
