<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\User;
use App\Services\PayrollCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PayrollController extends Controller
{
    /** Standard monthly PERA for government employees (₱2,000). */
    private const DEFAULT_PERA = 2000.00;

    /** Working days in a standard government month. */
    private const WORKING_DAYS_PER_MONTH = 22;

    public function __construct(
        private PayrollCalculationService $calculationService
    ) {}

    /**
     * Display a listing of all payroll periods.
     */
    public function index()
    {
        $periods = PayrollPeriod::orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->get();

        return view('hr.payroll.index', compact('periods'));
    }

    /**
     * Show the form for creating a new payroll period.
     */
    public function create()
    {
        return view('hr.payroll.create');
    }

    /**
     * Store a newly created payroll period and generate records for all active employees.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fund_cluster' => 'required|string|max:255',
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer|min:2000|max:2100',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['status'] = 'DRAFT';

        try {
            $payrollPeriod = DB::transaction(function () use ($validated) {
                // 1. Create the payroll period
                $period = PayrollPeriod::create($validated);

                // 2. Fetch all active standard employees (role_id = 1)
                $employees = User::where('role_id', 1)
                    ->where('status', 'active')
                    ->get();

                $periodMonth = (int) $period->period_month;
                $periodYear = (int) $period->period_year;

                // 3. Build a payroll record for each employee
                $records = [];

                foreach ($employees as $employee) {
                    // --- Basic Rate: from latest service record with a valid salary ---
                    $basicRate = $this->resolveBasicRate($employee->id);

                    // --- PERA (standard government allowance) ---
                    $peraAmount = self::DEFAULT_PERA;

                    // --- Earned for Period (basic + PERA before absence deductions) ---
                    $earnedForPeriod = $basicRate;

                    // --- Gross Earned (basic + PERA) ---
                    $grossEarned = $basicRate + $peraAmount;

                    // --- LWOP / Absence deduction ---
                    $daysWithoutPay = $this->resolveLwopDays($employee->id, $periodMonth, $periodYear);
                    $dailyRate = self::WORKING_DAYS_PER_MONTH > 0
                        ? $basicRate / self::WORKING_DAYS_PER_MONTH
                        : 0;
                    $absencesAmount = round($dailyRate * $daysWithoutPay, 2);

                    // --- Statutory deductions (via PayrollCalculationService) ---
                    $deductions = $this->calculationService->calculateMandatoryDeductions($basicRate);
                    $taxWithheld = 0.00; // Placeholder — withholding tax formula TBD
                    $gsisPremium = $deductions['gsis_premium'];
                    $philhealthPremium = $deductions['philhealth_premium'];
                    $pagibigPremium = $deductions['pagibig_premium'];
                    $otherDeductions = [];

                    // --- Totals ---
                    $totalDeductions = $absencesAmount + $taxWithheld + $gsisPremium
                        + $philhealthPremium + $pagibigPremium;

                    $netAmount = $grossEarned - $totalDeductions;

                    $records[] = [
                        'payroll_period_id' => $period->id,
                        'user_id' => $employee->id,
                        'basic_rate' => $basicRate,
                        'earned_for_period' => $earnedForPeriod,
                        'pera_amount' => $peraAmount,
                        'gross_earned' => $grossEarned,
                        'absences_amount' => $absencesAmount,
                        'tax_withheld' => $taxWithheld,
                        'gsis_premium' => $gsisPremium,
                        'philhealth_premium' => $philhealthPremium,
                        'pagibig_premium' => $pagibigPremium,
                        'other_deductions' => json_encode($otherDeductions),
                        'total_deductions' => $totalDeductions,
                        'net_amount' => $netAmount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // 4. Bulk insert all records
                if (! empty($records)) {
                    PayrollRecord::insert($records);
                }

                return $period;
            });
        } catch (\Throwable $e) {
            Log::error('Payroll generation failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'Failed to generate payroll period. '.$e->getMessage());
        }

        return redirect()->route('hr.payroll.show', $payrollPeriod->id)
            ->with('success', 'Payroll period generated successfully!');
    }

    /**
     * Display the payroll master sheet for a specific period.
     */
    public function show(string $id)
    {
        $period = PayrollPeriod::with('payrollRecords.user')
            ->findOrFail($id);

        return view('hr.payroll.show', [
            'period' => $period,
            'payrollPeriod' => $period,
        ]);
    }

    /**
     * Update variable other deductions (custom loans) for a specific payroll record
     * and recalculate total deductions and net pay.
     */
    public function updateRecord(Request $request, string $id)
    {
        $record = PayrollRecord::findOrFail($id);

        $request->validate([
            'other_deductions' => 'nullable|array',
            'other_deductions.*' => 'nullable|numeric|min:0',
        ]);

        $rawOtherDeductions = $request->input('other_deductions', []);
        if (! is_array($rawOtherDeductions)) {
            $rawOtherDeductions = [];
        }

        // Expected custom loans: GSIS Conso, Pag-IBIG MPL, Landbank, CNHS Multi-coop
        $standardKeys = ['gsis_conso', 'pagibig_mpl', 'landbank_loan', 'cnhs_multicoop'];
        $allKeys = array_unique(array_merge($standardKeys, array_keys($rawOtherDeductions)));

        $otherDeductions = [];
        $otherDeductionsSum = 0.00;

        foreach ($allKeys as $key) {
            $val = $rawOtherDeductions[$key] ?? null;
            // Empty, null, or zero inputs are safely converted to 0.00
            $amount = (! empty($val) && is_numeric($val) && (float) $val > 0)
                ? round((float) $val, 2)
                : 0.00;

            $otherDeductions[$key] = $amount;
            $otherDeductionsSum += $amount;
        }

        // Mandatory & statutory deductions
        $lwop = (float) $record->absences_amount;
        $tax = (float) $record->tax_withheld;
        $gsis = (float) $record->gsis_premium;
        $philhealth = (float) $record->philhealth_premium;
        $pagibig = (float) $record->pagibig_premium;

        // Recalculate total deductions and net pay
        $totalDeductions = round($lwop + $tax + $gsis + $philhealth + $pagibig + $otherDeductionsSum, 2);
        $grossEarned = (float) $record->gross_earned;
        $netAmount = round($grossEarned - $totalDeductions, 2);

        // Save updated totals to PayrollRecord
        $record->other_deductions = $otherDeductions;
        $record->total_deductions = $totalDeductions;
        $record->net_amount = $netAmount;
        $record->save();

        return redirect()->route('hr.payroll.show', $record->payroll_period_id)
            ->with('success', 'Employee deductions and net salary recalculated successfully.');
    }

    /**
     * Show the form for editing a payroll period.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified payroll period.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified payroll period.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Export the payroll master sheet to Excel using the official General Payroll template.
     *
     * Template column mapping (from inspecting General Payroll.xlsx):
     *   A  = #                       B  = Name              C  = Position
     *   L  = Rate per Month          M  = Earned for Period N  = PERA
     *   O  = Gross Amount Earned     P  = Absences          Q  = BIR Withholding Tax
     *   R  = GSIS Life & Retirement  V  = GSIS Conso Loan   AC = HDMF Premiums
     *   AD = HDMF Multi-Purpose Loan AE = HDMF Calamity Loan AH = PhilHealth
     *   AK = LANDBANK Salary Loan    AN = CNHS Multi-coop   AT = TOTAL DEDUCTIONS
     *   AV = Net Amount Received
     */
    public function exportExcel(string $id)
    {
        $period = PayrollPeriod::with(['payrollRecords.user.position'])
            ->findOrFail($id);

        $templatePath = storage_path('app/templates/General Payroll.xlsx');

        if (! file_exists($templatePath)) {
            return back()->with('error', 'Excel template file not found. Please upload General Payroll.xlsx to storage/app/templates/.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // --- Header Info ---
        $monthName = Carbon::create()->month((int) $period->period_month)->format('F');
        $sheet->setCellValue('A2', "FOR THE PERIOD : {$monthName} {$period->period_year}");
        $sheet->setCellValue('A4', "FUND CLUSTER: {$period->fund_cluster}");

        // --- Data rows start at row 11, totals row is currently at row 12 ---
        $records = $period->payrollRecords->sortBy(function ($rec) {
            $user = $rec->user;

            return $user ? ($user->last_name . ', ' . $user->first_name) : 'ZZZZZ';
        });

        $dataStartRow = 11;
        $totalRows = $records->count();
        $totalsRow = $dataStartRow + $totalRows; // Push the TOTAL PAYROLL row down

        // Insert blank rows above the totals row to make space for data
        if ($totalRows > 1) {
            $sheet->insertNewRowBefore($dataStartRow + 1, $totalRows - 1);
        }

        $currentRow = $dataStartRow;
        foreach ($records as $index => $record) {
            $user = $record->user;

            // Build employee name (Last, First M. Suffix)
            $employeeName = 'Unknown';
            if ($user) {
                if (! empty($user->last_name) || ! empty($user->first_name)) {
                    $employeeName = $user->last_name . ', ' . $user->first_name;
                    if (! empty($user->middle_name)) {
                        $employeeName .= ' ' . strtoupper(substr($user->middle_name, 0, 1)) . '.';
                    }
                    if (! empty($user->suffix)) {
                        $employeeName .= ' ' . $user->suffix;
                    }
                } elseif (! empty($user->name)) {
                    $employeeName = $user->name;
                }
            }

            // Position name from relationship
            $positionName = ($user && $user->position) ? $user->position->position_name : '';

            // Decode other_deductions JSON
            $otherDeductions = is_array($record->other_deductions) ? $record->other_deductions : [];

            // A: Row number
            $sheet->setCellValue("A{$currentRow}", $index + 1);
            // B: Name
            $sheet->setCellValue("B{$currentRow}", $employeeName);
            // C: Position
            $sheet->setCellValue("C{$currentRow}", $positionName);

            // COMPENSATION columns
            $sheet->setCellValue("L{$currentRow}", (float) $record->basic_rate);           // Rate per Month
            $sheet->setCellValue("M{$currentRow}", (float) $record->earned_for_period);    // Earned for Period
            $sheet->setCellValue("N{$currentRow}", (float) $record->pera_amount);           // PERA
            $sheet->setCellValue("O{$currentRow}", (float) $record->gross_earned);          // Gross Amount Earned

            // DEDUCTIONS — Statutory
            $sheet->setCellValue("P{$currentRow}", (float) $record->absences_amount);       // Absences / LWOP
            $sheet->setCellValue("Q{$currentRow}", (float) $record->tax_withheld);          // BIR Withholding Tax
            $sheet->setCellValue("R{$currentRow}", (float) $record->gsis_premium);          // GSIS Life & Retirement
            $sheet->setCellValue("AC{$currentRow}", (float) $record->pagibig_premium);      // HDMF Membership Premiums
            $sheet->setCellValue("AH{$currentRow}", (float) $record->philhealth_premium);   // PhilHealth

            // DEDUCTIONS — Variable Loans (from other_deductions JSON)
            $sheet->setCellValue("V{$currentRow}", (float) ($otherDeductions['gsis_conso'] ?? 0));      // GSIS Consolidated Loan
            $sheet->setCellValue("AD{$currentRow}", (float) ($otherDeductions['pagibig_mpl'] ?? 0));    // HDMF Multi-Purpose Loan
            $sheet->setCellValue("AK{$currentRow}", (float) ($otherDeductions['landbank_loan'] ?? 0));  // LANDBANK Salary Loan
            $sheet->setCellValue("AN{$currentRow}", (float) ($otherDeductions['cnhs_multicoop'] ?? 0)); // CNHS Multi-coop Loan

            // TOTALS
            $sheet->setCellValue("AT{$currentRow}", (float) $record->total_deductions);     // Total Deductions
            $sheet->setCellValue("AV{$currentRow}", (float) $record->net_amount);           // Net Amount Received

            $currentRow++;
        }

        // --- Update TOTAL PAYROLL SUM formulas to span all data rows ---
        $lastDataRow = $dataStartRow + $totalRows - 1;
        $sumColumns = [
            'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM',
            'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AV',
        ];

        foreach ($sumColumns as $col) {
            $sheet->setCellValue("{$col}{$totalsRow}", "=SUM({$col}{$dataStartRow}:{$col}{$lastDataRow})");
        }

        // --- Write to temp file and return download ---
        $fileName = 'Payroll_Master_' . $monthName . '_' . $period->period_year . '.xlsx';
        $tempPath = storage_path('app/temp/' . $fileName);

        // Ensure temp directory exists
        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Resolve the employee's basic monthly rate from the latest service record
     * that has a valid numeric salary. Returns 0 if none found.
     */
    private function resolveBasicRate(int $userId): float
    {
        $record = DB::table('service_records')
            ->where('user_id', $userId)
            ->whereNotNull('salary')
            ->where('salary', '!=', '')
            ->where('salary', '!=', 'TBD')
            ->orderByDesc('date_from')
            ->first();

        if (! $record) {
            return 0.00;
        }

        // Salary may contain commas (e.g. "100,000") — strip them before casting.
        return (float) str_replace(',', '', $record->salary);
    }

    /**
     * Sum LWOP days from approved leave applications whose inclusive date range
     * overlaps the given payroll month/year.
     *
     * Inclusive dates are stored as:
     *   "APRIL 13, 2026 TO MAY 2, 2026"  or  "AUG 29, 2026 - AUG 31, 2026"
     */
    private function resolveLwopDays(int $userId, int $month, int $year): float
    {
        $leaves = DB::table('leave_applications')
            ->where('user_id', $userId)
            ->where('status', 'APPROVED')
            ->get(['inclusive_dates', 'days_without_pay', 'working_days']);

        $totalLwop = 0.0;
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        foreach ($leaves as $leave) {
            // Parse the date range from the inclusive_dates string
            $dates = $this->parseInclusiveDates($leave->inclusive_dates);

            if (! $dates) {
                continue;
            }

            [$leaveStart, $leaveEnd] = $dates;

            // Check if the leave overlaps with the payroll period month
            if ($leaveEnd->lt($periodStart) || $leaveStart->gt($periodEnd)) {
                continue; // No overlap
            }

            // Use days_without_pay if available, otherwise fall back to working_days
            $lwopDays = (float) ($leave->days_without_pay ?? 0);

            if ($lwopDays > 0) {
                $totalLwop += $lwopDays;
            }
        }

        return $totalLwop;
    }

    /**
     * Parse an inclusive_dates string into [Carbon $start, Carbon $end].
     *
     * Handles formats:
     *   "APRIL 13, 2026 TO MAY 2, 2026"
     *   "AUG 29, 2026 - AUG 31, 2026"
     *
     * @return array{0: Carbon, 1: Carbon}|null
     */
    private function parseInclusiveDates(?string $raw): ?array
    {
        if (empty($raw)) {
            return null;
        }

        // Normalise separators: " TO " or " - "
        $parts = preg_split('/\s+TO\s+|\s+-\s+/i', trim($raw));

        if (count($parts) !== 2) {
            return null;
        }

        try {
            $start = Carbon::parse(trim($parts[0]));
            $end = Carbon::parse(trim($parts[1]));

            return [$start, $end];
        } catch (\Throwable) {
            return null;
        }
    }
}
