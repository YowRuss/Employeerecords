<?php

namespace App\Http\Controllers;

use App\Enums\PayrollType;
use App\Enums\PositionCategory;
use App\Models\DeductionCategory;
use App\Models\DeductionType;
use App\Models\IncomeType;
use App\Models\LateDeduction;
use App\Models\LearningArea;
use App\Models\Loan;
use App\Models\PayrollIncome;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\SalaryGrade;
use App\Models\User;
use App\Services\PayrollCalculationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
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
     * Display a listing of employee payroll profiles with NOSI step tracking.
     */
    public function employees(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $category = strtolower((string) $request->input('category', 'all'));
        $eligibility = strtolower((string) $request->input('eligibility', 'all'));
        $sex = in_array((string) $request->input('sex'), ['0', '1'], true) ? (string) $request->input('sex') : '';
        $learningAreaId = $category === 'teaching' ? (int) $request->input('learning_area_id') : 0;

        // Load all salary grades matrix keyed by grade_step
        $salaryMatrix = SalaryGrade::all()->keyBy(fn ($sg) => $sg->grade.'_'.$sg->step);

        // Roster-wide counts stay on this lighter query. Step logs load only for the current page.
        $allEmployees = User::where('role_id', 1)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Inactive');
            })
            ->with([
                'position',
                'learningArea',
                'pdsPersonalInfo',
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Calculate counts across the full active employee roster
        $totalCount = $allEmployees->count();
        $teachingCount = $allEmployees->filter(fn ($u) => $u->isTeaching())->count();
        $nonTeachingCount = $allEmployees->filter(fn ($u) => ! $u->isTeaching())->count();
        $eligibleCount = $allEmployees->filter(function ($u) {
            $eligibilityDate = $u->next_eligibility_date;
            $step = $u->step_increment ?: 1;
            $grade = $u->position?->salary_grade;

            return $grade && $step < 8 && $eligibilityDate && Carbon::parse($eligibilityDate)->lte(Carbon::today());
        })->count();

        // Apply filters
        $filtered = $allEmployees;

        if ($search !== '') {
            $term = strtolower($search);
            $filtered = $filtered->filter(function ($u) use ($term) {
                $fullName = strtolower(($u->first_name ?? '').' '.($u->middle_name ?? '').' '.($u->last_name ?? ''));
                $employeeNo = strtolower($u->pdsPersonalInfo?->employee_no ?? '');
                $position = strtolower($u->position?->position_name ?? '');
                $email = strtolower($u->email ?? '');

                return str_contains($fullName, $term)
                    || str_contains($employeeNo, $term)
                    || str_contains($position, $term)
                    || str_contains($email, $term);
            });
        }

        if ($category === 'teaching') {
            $filtered = $filtered->filter(fn ($u) => $u->isTeaching());
        } elseif ($category === 'non-teaching') {
            $filtered = $filtered->filter(fn ($u) => ! $u->isTeaching());
        }

        if ($eligibility === 'eligible') {
            $filtered = $filtered->filter(function ($u) {
                $eligibilityDate = $u->next_eligibility_date;
                $step = $u->step_increment ?: 1;
                $grade = $u->position?->salary_grade;

                return $grade && $step < 8 && $eligibilityDate && Carbon::parse($eligibilityDate)->lte(Carbon::today());
            });
        }

        if ($sex !== '') {
            $wantedSex = $sex === '1' ? 'Male' : 'Female';
            $filtered = $filtered->filter(fn ($u) => $u->applicantSex() === $wantedSex);
        }

        if ($learningAreaId > 0) {
            $filtered = $filtered->filter(fn ($u) => (int) $u->learning_area_id === $learningAreaId);
        }

        $employees = $this->paginateEmployees($filtered->values());

        return view('hr.payroll.employees', [
            'employees' => $employees,
            'salaryMatrix' => $salaryMatrix,
            'learningAreas' => LearningArea::orderBy('name')->get(),
            'counts' => [
                'total' => $totalCount,
                'teaching' => $teachingCount,
                'non_teaching' => $nonTeachingCount,
                'eligible' => $eligibleCount,
            ],
            'currentSearch' => $search,
            'currentCategory' => $category,
            'currentEligibility' => $eligibility,
            'currentSex' => $sex,
            'currentLearningAreaId' => $learningAreaId,
        ]);
    }

    /**
     * Page the already-filtered roster and load NOSI history only for that page.
     */
    private function paginateEmployees($employees): LengthAwarePaginator
    {
        $perPage = 5;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $pageModels = new Collection(
            $employees->forPage($page, $perPage)->values()->all()
        );

        if ($pageModels->isNotEmpty()) {
            $pageModels->load([
                'stepIncrementLogs' => fn ($query) => $query->with('approver')->orderByDesc('effective_date'),
            ]);
        }

        return (new LengthAwarePaginator(
            $pageModels,
            $employees->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        ))->withQueryString();
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
            'payroll_type' => ['required', Rule::enum(PayrollType::class)],
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer|min:2000|max:2100',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['status'] = 'DRAFT';
        $payrollType = PayrollType::from($validated['payroll_type']);

        try {
            $payrollPeriod = DB::transaction(function () use ($validated, $payrollType) {
                // 1. Create the payroll period
                $period = PayrollPeriod::create($validated);

                // 2. Fetch all active standard employees (role_id = 1)
                $employees = User::with(['position', 'allowances', 'loans'])->where('role_id', 1)
                    ->where('status', 'active')
                    ->get();

                $periodMonth = (int) $period->period_month;
                $periodYear = (int) $period->period_year;

                // 3. Build a payroll record for each employee
                $records = [];

                foreach ($employees as $employee) {
                    // --- Basic Rate: from base_salary accessor ---
                    $basicRate = $employee->base_salary;

                    // Bonus runs skip the entire monthly deduction pipeline.
                    if ($payrollType->isBonus()) {
                        $bonusData = $this->buildBonusRecord($period, $employee, $payrollType, $basicRate);
                        $bonusData['other_deductions'] = [];
                        unset($bonusData['created_at'], $bonusData['updated_at']);
                        PayrollRecord::create($bonusData);

                        continue;
                    }

                    // --- PERA (conditionally assigned per employee) ---
                    $peraAmount = $employee->active_pera;

                    // --- Additional Allowances ---
                    $otherAllowances = $employee->allowances()
                        ->where('is_active', true)
                        ->where('allowance_name', '!=', 'PERA')
                        ->get();
                    $otherAllowancesSum = $otherAllowances->sum('amount');

                    // --- Earned for Period (basic + PERA before absence deductions) ---
                    $earnedForPeriod = $basicRate;

                    // --- Gross Earned (basic + PERA + other allowances) ---
                    $grossEarned = $basicRate + $peraAmount + $otherAllowancesSum;

                    // --- LWOP / Absence deduction (from approved leave applications) ---
                    $daysWithoutPay = $this->resolveLwopDays($employee->id, $periodMonth, $periodYear);
                    $dailyRate = self::WORKING_DAYS_PER_MONTH > 0
                        ? $basicRate / self::WORKING_DAYS_PER_MONTH
                        : 0;
                    $formalLwopDeduction = round($dailyRate * $daysWithoutPay, 2);

                    // --- Unexcused absences (from Attendance & Lates tracker) ---
                    $currentPeriodString = sprintf('%04d-%02d', $periodYear, $periodMonth);
                    $attendanceRecord = LateDeduction::where('user_id', $employee->id)
                        ->where('payroll_period', $currentPeriodString)
                        ->first();

                    $unexcusedAbsentDays = $attendanceRecord ? (float) $attendanceRecord->unexcused_absences : 0;
                    $unexcusedAbsenceDeduction = $attendanceRecord ? (float) $attendanceRecord->absence_deduction_amount : 0;

                    // --- Combined absences (formal LWOP + unexcused AWOL) ---
                    $totalAbsentDays = $daysWithoutPay + $unexcusedAbsentDays;
                    $absencesAmount = round($formalLwopDeduction + $unexcusedAbsenceDeduction, 2);

                    // --- Late / Tardiness deduction (pre-computed in Attendance module) ---
                    $lateDeduction = $attendanceRecord ? (float) $attendanceRecord->computed_amount : 0.00;

                    // ========================================================
                    // FULL MONTH LWOP INTERCEPTOR
                    // ========================================================
                    if ($totalAbsentDays >= self::WORKING_DAYS_PER_MONTH || $absencesAmount >= $basicRate) {
                        PayrollRecord::create([
                            'payroll_period_id' => $period->id,
                            'user_id' => $employee->id,
                            'basic_rate' => $basicRate,
                            'earned_for_period' => 0,
                            'pera_amount' => 0,
                            'gross_earned' => 0,
                            'absences_amount' => 0,
                            'late_deduction' => 0,
                            'tax_withheld' => 0,
                            'gsis_premium' => 0,
                            'philhealth_premium' => 0,
                            'pagibig_premium' => 0,
                            'loan_amortization' => 0,
                            'other_deductions' => [],
                            'total_deductions' => 0,
                            'net_amount' => 0,
                            'is_full_lwop' => true,
                        ]);

                        continue;
                    }

                    // --- Statutory deductions (via PayrollCalculationService) ---
                    $deductions = $this->calculationService->calculateMandatoryDeductions($basicRate);
                    $gsisPremium = $deductions['gsis_premium'];
                    $philhealthPremium = $deductions['philhealth_premium'];
                    $pagibigPremium = $deductions['pagibig_premium'];
                    $taxWithheld = $this->calculationService->calculateWithholdingTax(
                        $basicRate,
                        $gsisPremium,
                        $philhealthPremium,
                        $pagibigPremium
                    );
                    $otherDeductions = [];

                    // --- Loan amortization (sum of all outstanding active loans) ---
                    $loanDeduction = $employee->active_loan_deductions;

                    // --- Totals ---
                    $totalDeductions = $absencesAmount + $lateDeduction + $taxWithheld + $gsisPremium
                        + $philhealthPremium + $pagibigPremium + $loanDeduction;

                    $netAmount = $grossEarned - $totalDeductions;

                    $record = PayrollRecord::create([
                        'payroll_period_id' => $period->id,
                        'user_id' => $employee->id,
                        'basic_rate' => $basicRate,
                        'earned_for_period' => $earnedForPeriod,
                        'pera_amount' => $peraAmount,
                        'gross_earned' => $grossEarned,
                        'absences_amount' => $absencesAmount,
                        'late_deduction' => $lateDeduction,
                        'tax_withheld' => $taxWithheld,
                        'gsis_premium' => $gsisPremium,
                        'philhealth_premium' => $philhealthPremium,
                        'pagibig_premium' => $pagibigPremium,
                        'loan_amortization' => $loanDeduction,
                        'other_deductions' => $otherDeductions,
                        'total_deductions' => $totalDeductions,
                        'net_amount' => $netAmount,
                        'is_full_lwop' => false,
                    ]);

                    // Insert attached allowances as payroll incomes
                    if ($otherAllowances->isNotEmpty()) {
                        $incomeData = $otherAllowances->map(function ($allowance) use ($record, $employee) {
                            return [
                                'payroll_record_id' => $record->id,
                                'user_id' => $employee->id,
                                'income_type_id' => $allowance->income_type_id,
                                'amount' => $allowance->amount,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        })->toArray();

                        PayrollIncome::insert($incomeData);
                    }
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
            ->with('success', $payrollType->value.' payroll generated successfully!');
    }

    /**
     * Build a payroll record for a Mid-Year or Year-End Bonus run.
     *
     * A bonus pays one month of basic salary — Year-End adds the statutory
     * ₱5,000 cash gift. No PERA, statutory premiums, absences, lates, or loan
     * amortization are applied; the only withholding is tax on the portion
     * above the TRAIN Law exemption ceiling.
     *
     * @return array<string, mixed>
     */
    private function buildBonusRecord(PayrollPeriod $period, User $employee, PayrollType $payrollType, float $basicRate): array
    {
        $grossEarned = round($basicRate + $payrollType->cashGift(), 2);
        $taxWithheld = $this->calculationService->calculateBonusTax($grossEarned);

        return [
            'payroll_period_id' => $period->id,
            'user_id' => $employee->id,
            'basic_rate' => $basicRate,
            'earned_for_period' => $basicRate,
            'pera_amount' => 0,
            'gross_earned' => $grossEarned,
            'absences_amount' => 0,
            'late_deduction' => 0,
            'tax_withheld' => $taxWithheld,
            'gsis_premium' => 0,
            'philhealth_premium' => 0,
            'pagibig_premium' => 0,
            'loan_amortization' => 0,
            'other_deductions' => json_encode([]),
            'total_deductions' => $taxWithheld,
            'net_amount' => round($grossEarned - $taxWithheld, 2),
            'is_full_lwop' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Display the payroll master sheet for a specific period.
     */
    public function show(Request $request, string $id)
    {
        $period = PayrollPeriod::findOrFail($id);
        $this->applyStatutoryDeductions($period);

        $gender = strtolower((string) $request->query('gender', ''));
        $position = strtolower((string) $request->query('position', $request->query('filter', '')));

        // Base query for payroll records in this period
        $query = PayrollRecord::with([
            'employee.position',
            'employee.pdsPersonalInfo',
            'employee.serviceRecords',
            'user.position',
            'user.pdsPersonalInfo',
            'user.serviceRecords',
            'user.stepIncrementLogs.approver',
            'payrollIncomes.incomeType',
        ])->where('payroll_period_id', $period->id);

        // Conditional whereHas('employee', ...) filters
        if (in_array($gender, ['male', '1'])) {
            $query->whereHas('employee', function ($q) {
                $q->whereHas('pdsPersonalInfo', function ($pdsQ) {
                    $pdsQ->where('sex', 1);
                });
            });
        } elseif (in_array($gender, ['female', '0'])) {
            $query->whereHas('employee', function ($q) {
                $q->whereHas('pdsPersonalInfo', function ($pdsQ) {
                    $pdsQ->where('sex', 0);
                });
            });
        }

        if ($position === 'teaching') {
            $query->whereHas('employee', function ($q) {
                $q->where('employee_type', 1)
                    ->orWhereHas('position', fn ($pos) => $pos->where('category', PositionCategory::Teaching->value));
            });
        } elseif ($position === 'non-teaching') {
            $query->whereHas('employee', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('employee_type', 0)
                        ->whereDoesntHave('position', fn ($pos) => $pos->where('category', PositionCategory::Teaching->value));
                })->orWhereHas('position', fn ($pos) => $pos->where('category', PositionCategory::NonTeaching->value));
            });
        }

        // Crucial Math Isolation: Clone the $query builder before paginating it to calculate Summary Cards
        // Exclude full LWOP records from summary totals to prevent inflated deductions
        $unpaginatedQuery = clone $query;
        $summaryQuery = (clone $unpaginatedQuery)->where('payroll_records.is_full_lwop', false);
        $totalEmployees = (clone $unpaginatedQuery)->count();
        $totalGross = (float) (clone $summaryQuery)->sum('gross_earned');
        $totalAbsences = (float) (clone $summaryQuery)->sum('absences_amount');
        $totalLates = (float) (clone $summaryQuery)->sum('late_deduction');
        $totalTax = (float) (clone $summaryQuery)->sum('tax_withheld');
        $totalGsis = (float) (clone $summaryQuery)->sum('gsis_premium');
        $totalPhilhealth = (float) (clone $summaryQuery)->sum('philhealth_premium');
        $totalPagibig = (float) (clone $summaryQuery)->sum('pagibig_premium');
        $totalLoans = (float) (clone $summaryQuery)->sum('loan_amortization');
        $totalDeductions = (float) (clone $summaryQuery)->sum('total_deductions');
        $totalNet = (float) (clone $summaryQuery)->sum('net_amount');

        // Position count badges (scoped to the selected gender if any)
        $basePositionQuery = PayrollRecord::where('payroll_period_id', $period->id);
        if (in_array($gender, ['male', '1'])) {
            $basePositionQuery->whereHas('employee', function ($q) {
                $q->whereHas('pdsPersonalInfo', fn ($pdsQ) => $pdsQ->where('sex', 1));
            });
        } elseif (in_array($gender, ['female', '0'])) {
            $basePositionQuery->whereHas('employee', function ($q) {
                $q->whereHas('pdsPersonalInfo', fn ($pdsQ) => $pdsQ->where('sex', 0));
            });
        }

        $totalEmployeesCount = (clone $basePositionQuery)->count();
        $teachingEmployeesCount = (clone $basePositionQuery)->whereHas('employee', function ($q) {
            $q->where('employee_type', 1)
                ->orWhereHas('position', fn ($pos) => $pos->where('category', PositionCategory::Teaching->value));
        })->count();
        $nonTeachingEmployeesCount = (clone $basePositionQuery)->whereHas('employee', function ($q) {
            $q->where(function ($sub) {
                $sub->where('employee_type', 0)
                    ->whereDoesntHave('position', fn ($pos) => $pos->where('category', PositionCategory::Teaching->value));
            })->orWhereHas('position', fn ($pos) => $pos->where('category', PositionCategory::NonTeaching->value));
        })->count();

        // Gender count badges (scoped to the selected position if any)
        $baseGenderQuery = PayrollRecord::where('payroll_period_id', $period->id);
        if ($position === 'teaching') {
            $baseGenderQuery->whereHas('employee', function ($q) {
                $q->where('employee_type', 1)
                    ->orWhereHas('position', fn ($pos) => $pos->where('category', PositionCategory::Teaching->value));
            });
        } elseif ($position === 'non-teaching') {
            $baseGenderQuery->whereHas('employee', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('employee_type', 0)
                        ->whereDoesntHave('position', fn ($pos) => $pos->where('category', PositionCategory::Teaching->value));
                })->orWhereHas('position', fn ($pos) => $pos->where('category', PositionCategory::NonTeaching->value));
            });
        }

        $maleEmployeesCount = (clone $baseGenderQuery)->whereHas('employee.pdsPersonalInfo', fn ($pdsQ) => $pdsQ->where('sex', 1))->count();
        $femaleEmployeesCount = (clone $baseGenderQuery)->whereHas('employee.pdsPersonalInfo', fn ($pdsQ) => $pdsQ->where('sex', 0))->count();

        // Order by employee last name and paginate with active query string
        $query->join('users', 'payroll_records.user_id', '=', 'users.id')
            ->select('payroll_records.*')
            ->orderBy('users.last_name', 'asc')
            ->orderBy('users.first_name', 'asc');

        $payrollRecords = $query->paginate(15)->withQueryString();

        $layoutVersion = session('deduction_version', 'v1');

        $categories = DeductionCategory::with(['types' => function ($tQuery) {
            $tQuery->active()->orderBy('name');
        }])
            ->active()
            ->where('profile_version', $layoutVersion)
            ->orderBy('sort_order')
            ->get();

        $incomeTypes = IncomeType::active()->orderBy('name')->get();

        return view('hr.payroll.show', [
            'period' => $period,
            'payrollPeriod' => $period,
            'payrollRecords' => $payrollRecords,
            'records' => $payrollRecords,
            'categories' => $categories,
            'incomeTypes' => $incomeTypes,
            'layoutVersion' => $layoutVersion,
            'currentPosition' => $position,
            'currentGender' => $gender,
            'counts' => [
                'all' => $totalEmployeesCount,
                'teaching' => $teachingEmployeesCount,
                'non_teaching' => $nonTeachingEmployeesCount,
            ],
            'genderCounts' => [
                'all' => (clone $baseGenderQuery)->count(),
                'male' => $maleEmployeesCount,
                'female' => $femaleEmployeesCount,
            ],
            'totalEmployees' => $totalEmployees,
            'totalGross' => $totalGross,
            'totalAbsences' => $totalAbsences,
            'totalLates' => $totalLates,
            'totalTax' => $totalTax,
            'totalGsis' => $totalGsis,
            'totalPhilhealth' => $totalPhilhealth,
            'totalPagibig' => $totalPagibig,
            'totalLoans' => $totalLoans,
            'totalDeductions' => $totalDeductions,
            'totalNet' => $totalNet,
        ]);
    }

    /**
     * Fill GSIS, PhilHealth, Pag-IBIG, and TRAIN withholding tax on a draft
     * regular payroll that was saved before those amounts were calculated.
     * Bonus runs stay at zero premiums. Finalized periods are left unchanged.
     */
    private function applyStatutoryDeductions(PayrollPeriod $period): void
    {
        if ($period->status !== 'DRAFT' || $period->payroll_type->isBonus()) {
            return;
        }

        $records = PayrollRecord::where('payroll_period_id', $period->id)
            ->where('is_full_lwop', false)
            ->where('basic_rate', '>', 0)
            ->get();

        foreach ($records as $record) {
            $basic = (float) $record->basic_rate;
            $gsis = (float) $record->gsis_premium;
            $philhealth = (float) $record->philhealth_premium;
            $pagibig = (float) $record->pagibig_premium;
            $premiumsMissing = $gsis <= 0 && $philhealth <= 0 && $pagibig <= 0;

            if ($premiumsMissing) {
                $computed = $this->calculationService->calculateMandatoryDeductions($basic);
                $gsis = $computed['gsis_premium'];
                $philhealth = $computed['philhealth_premium'];
                $pagibig = $computed['pagibig_premium'];
            }

            $tax = $this->calculationService->calculateWithholdingTax($basic, $gsis, $philhealth, $pagibig);
            $taxUnchanged = round((float) $record->tax_withheld, 2) === $tax;
            $premiumsUnchanged = ! $premiumsMissing;

            if ($taxUnchanged && $premiumsUnchanged) {
                continue;
            }

            $delta = round(
                ($tax - (float) $record->tax_withheld)
                + ($gsis - (float) $record->gsis_premium)
                + ($philhealth - (float) $record->philhealth_premium)
                + ($pagibig - (float) $record->pagibig_premium),
                2
            );

            $record->gsis_premium = $gsis;
            $record->philhealth_premium = $philhealth;
            $record->pagibig_premium = $pagibig;
            $record->tax_withheld = $tax;
            $record->total_deductions = round((float) $record->total_deductions + $delta, 2);
            $record->net_amount = round((float) $record->net_amount - $delta, 2);
            $record->save();
        }
    }

    /**
     * Toggle the deduction UI layout version stored in the session.
     */
    public function toggleDeductionVersion(Request $request)
    {
        $request->validate(['deduction_version' => 'required|string|in:v1,v2,v3']);

        session(['deduction_version' => $request->deduction_version]);

        return redirect()->back()->with('success', 'Deduction layout updated to '.strtoupper($request->deduction_version).'.');
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
            'filter' => 'nullable|string',
        ]);

        $rawOtherDeductions = $request->input('other_deductions', []);
        if (! is_array($rawOtherDeductions)) {
            $rawOtherDeductions = [];
        }

        // Dynamically derive standard keys from active deduction types
        $standardKeys = DeductionType::active()->pluck('code')->toArray();
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

        // --- Recalculate gross and net ---
        // Gross = basic_rate + PERA + additional incomes
        $basicRate = (float) $record->basic_rate;
        $peraAmount = (float) $record->pera_amount;
        $totalAdditionalIncome = $record->payrollIncomes()->sum('amount');
        $grossEarned = round($basicRate + $peraAmount + $totalAdditionalIncome, 2);

        // Mandatory & statutory deductions
        $lwop = (float) $record->absences_amount;
        $late = (float) $record->late_deduction;
        $tax = (float) $record->tax_withheld;
        $gsis = (float) $record->gsis_premium;
        $philhealth = (float) $record->philhealth_premium;
        $pagibig = (float) $record->pagibig_premium;
        $loanAmortization = (float) $record->loan_amortization;

        // Recalculate total deductions and net pay
        $totalDeductions = round($lwop + $late + $tax + $gsis + $philhealth + $pagibig + $loanAmortization + $otherDeductionsSum, 2);
        $netAmount = round($grossEarned - $totalDeductions, 2);

        // Save updated totals to PayrollRecord
        $record->other_deductions = $otherDeductions;
        $record->gross_earned = $grossEarned;
        $record->total_deductions = $totalDeductions;
        $record->net_amount = $netAmount;
        $record->save();

        $redirectParams = ['id' => $record->payroll_period_id];
        if ($request->filled('position')) {
            $redirectParams['position'] = $request->input('position');
        } elseif ($request->filled('filter')) {
            $redirectParams['position'] = $request->input('filter');
        }
        if ($request->filled('gender')) {
            $redirectParams['gender'] = $request->input('gender');
        }
        if ($request->filled('page')) {
            $redirectParams['page'] = $request->input('page');
        }

        return redirect()->route('hr.payroll.show', $redirectParams)
            ->with('success', 'Employee payroll (incomes + deductions) recalculated successfully.');
    }

    /**
     * Finalize the payroll period and post the withheld loan amortizations
     * against each employee's outstanding loan balance.
     */
    public function approve(string $id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if ($period->status === 'FINALIZED') {
            return back()->with('error', 'Payroll period is already finalized.');
        }

        try {
            $settledLoans = DB::transaction(function () use ($period) {
                // Re-read under a row lock so two concurrent requests cannot
                // amortize the same period twice.
                $locked = PayrollPeriod::whereKey($period->id)->lockForUpdate()->firstOrFail();

                if ($locked->status === 'FINALIZED') {
                    return null;
                }

                $settled = $this->amortizeLoansForPeriod($locked);

                $locked->status = 'FINALIZED';
                $locked->save();

                return $settled;
            });
        } catch (\Throwable $e) {
            Log::error('Payroll finalization failed: '.$e->getMessage(), [
                'payroll_period_id' => $period->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to finalize payroll period. '.$e->getMessage());
        }

        if ($settledLoans === null) {
            return back()->with('error', 'Payroll period is already finalized.');
        }

        $message = 'Payroll period has been finalized successfully.';

        if ($settledLoans > 0) {
            $message .= ' '.$settledLoans.' '.Str::plural('loan', $settledLoans).' fully paid and closed.';
        }

        return back()->with('success', $message);
    }

    /**
     * Deduct one month of amortization from every outstanding loan belonging to
     * employees who actually had a loan withheld in this period. Loans whose
     * balance reaches zero are closed out as Paid.
     *
     * @return int Number of loans fully settled by this run.
     */
    private function amortizeLoansForPeriod(PayrollPeriod $period): int
    {
        $userIds = PayrollRecord::where('payroll_period_id', $period->id)
            ->where('loan_amortization', '>', 0)
            ->pluck('user_id');

        if ($userIds->isEmpty()) {
            return 0;
        }

        $loans = Loan::whereIn('user_id', $userIds)
            ->outstanding()
            ->lockForUpdate()
            ->get();

        $settled = 0;

        foreach ($loans as $loan) {
            $balance = round((float) $loan->running_balance - (float) $loan->monthly_amortization, 2);

            if ($balance <= 0) {
                $balance = 0.00;
                $loan->status = 'Paid';
                $settled++;
            }

            $loan->running_balance = $balance;
            $loan->save();
        }

        return $settled;
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
    public function exportExcel(Request $request, string $id)
    {
        $period = PayrollPeriod::findOrFail($id);

        $gender = strtolower((string) $request->query('gender', ''));
        $position = strtolower((string) $request->query('position', $request->query('filter', '')));

        $recordsQuery = PayrollRecord::with([
            'employee.position',
            'employee.pdsPersonalInfo',
            'employee.serviceRecords',
            'user.position',
            'user.pdsPersonalInfo',
            'user.serviceRecords',
        ])->where('payroll_period_id', $period->id);

        if (in_array($gender, ['male', '1'])) {
            $recordsQuery->whereHas('employee', function ($q) {
                $q->whereHas('pdsPersonalInfo', function ($pdsQ) {
                    $pdsQ->where('sex', 1);
                });
            });
        } elseif (in_array($gender, ['female', '0'])) {
            $recordsQuery->whereHas('employee', function ($q) {
                $q->whereHas('pdsPersonalInfo', function ($pdsQ) {
                    $pdsQ->where('sex', 0);
                });
            });
        }

        if ($position === 'teaching') {
            $recordsQuery->whereHas('employee', function ($q) {
                $q->where('employee_type', 1)
                    ->orWhereHas('position', fn ($pos) => $pos->where('category', PositionCategory::Teaching->value));
            });
        } elseif ($position === 'non-teaching') {
            $recordsQuery->whereHas('employee', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('employee_type', 0)
                        ->whereDoesntHave('position', fn ($pos) => $pos->where('category', PositionCategory::Teaching->value));
                })->orWhereHas('position', fn ($pos) => $pos->where('category', PositionCategory::NonTeaching->value));
            });
        }

        $records = $recordsQuery->get()->sortBy(function ($rec) {
            $user = $rec->user ?? $rec->employee;

            return $user ? ($user->last_name.', '.$user->first_name) : 'ZZZZZ';
        })->values();

        $templatePath = storage_path('app/templates/General Payroll.xlsx');

        if (! file_exists($templatePath)) {
            return back()->with('error', 'Excel template file not found. Please upload General Payroll.xlsx to storage/app/templates/.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // --- Header Info ---
        $monthName = Carbon::create()->month((int) $period->period_month)->format('F');
        $filterParts = [];
        if ($position === 'teaching') {
            $filterParts[] = 'TEACHING';
        } elseif ($position === 'non-teaching') {
            $filterParts[] = 'NON-TEACHING';
        }
        if (in_array($gender, ['male', '1'])) {
            $filterParts[] = 'MALE';
        } elseif (in_array($gender, ['female', '0'])) {
            $filterParts[] = 'FEMALE';
        }
        $filterHeader = ! empty($filterParts) ? ' ('.implode(' - ', $filterParts).' STAFF)' : '';

        $sheet->setCellValue('A2', "FOR THE PERIOD : {$monthName} {$period->period_year}{$filterHeader}");
        $sheet->setCellValue('A4', "FUND CLUSTER: {$period->fund_cluster}");

        // --- Data rows start at row 11, totals row is currently at row 12 ---
        $dataStartRow = 11;
        $totalRows = $records->count();
        $totalsRow = $dataStartRow + $totalRows; // Push the TOTAL PAYROLL row down

        // Insert blank rows above the totals row to make space for data
        if ($totalRows > 1) {
            $sheet->insertNewRowBefore($dataStartRow + 1, $totalRows - 1);
        }

        // Fetch active deduction types with Excel column mappings (once, outside the loop)
        $activeTypes = DeductionType::active()->whereNotNull('excel_column')->get();

        $currentRow = $dataStartRow;
        foreach ($records as $index => $record) {
            $user = $record->user;

            // Build employee name (Last, First M. Suffix)
            $employeeName = 'Unknown';
            if ($user) {
                if (! empty($user->last_name) || ! empty($user->first_name)) {
                    $employeeName = $user->last_name.', '.$user->first_name;
                    if (! empty($user->middle_name)) {
                        $employeeName .= ' '.strtoupper(substr($user->middle_name, 0, 1)).'.';
                    }
                    if (! empty($user->suffix)) {
                        $employeeName .= ' '.$user->suffix;
                    }
                } elseif (! empty($user->name)) {
                    $employeeName = $user->name;
                }
            }

            // Position name from relationship
            $positionName = ($user && $user->position) ? $user->position->position_name : '';

            // Decode other_deductions JSON
            $otherDeductions = is_array($record->other_deductions) ? $record->other_deductions : [];

            $pds = $user->pdsPersonalInfo ?? null;
            $firstServiceRecord = $user->serviceRecords ? $user->serviceRecords->sortBy('date_from')->first() : null;
            $appointmentDate = '';
            if ($firstServiceRecord && ! empty($firstServiceRecord->date_from)) {
                try {
                    $appointmentDate = \Carbon\Carbon::parse($firstServiceRecord->date_from)->format('m/d/Y');
                } catch (\Throwable $e) {
                }
            }

            // A: Row number
            $sheet->setCellValue("A{$currentRow}", $index + 1);
            // B: Name
            $sheet->setCellValue("B{$currentRow}", $employeeName);
            // C: Position
            $sheet->setCellValue("C{$currentRow}", $positionName);

            // D-J: Demographics
            $sheet->setCellValueExplicit("D{$currentRow}", (string) ($pds->employee_no ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValue("E{$currentRow}", $appointmentDate);
            $sheet->setCellValueExplicit("F{$currentRow}", (string) ($pds->tin_no ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("G{$currentRow}", (string) ($pds->gsis_id_no ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("H{$currentRow}", (string) ($pds->pagibig_id_no ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("I{$currentRow}", (string) ($pds->philhealth_no ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValue("J{$currentRow}", 'CNHS-JHS');

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

            // DEDUCTIONS — Custom Loans (dynamically mapped from deduction_types)
            foreach ($activeTypes as $type) {
                $value = (float) ($otherDeductions[$type->code] ?? 0);
                $sheet->setCellValue($type->excel_column.$currentRow, $value);
            }

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
        $filterSuffix = ! empty($filterParts) ? '_'.implode('_', array_map('ucfirst', array_map('strtolower', $filterParts))) : '';
        $fileName = 'Payroll_Master_'.$monthName.'_'.$period->period_year.$filterSuffix.'.xlsx';
        $tempPath = storage_path('app/temp/'.$fileName);

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
