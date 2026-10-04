@extends('layouts.app')

@section('content')
@php
    $payrollPeriod = $payrollPeriod ?? $period;
    $monthName = \Carbon\Carbon::create()->month((int)$payrollPeriod->period_month)->format('F');
    $status = strtoupper($payrollPeriod->status ?? 'DRAFT');
    $statusBadge = match($status) {
        'FINALIZED', 'APPROVED', 'COMPLETED' => 'bg-success text-white',
        'PROCESSED', 'SUBMITTED' => 'bg-info text-dark',
        'PENDING' => 'bg-warning text-dark',
        default => 'bg-secondary text-white',
    };

    $payrollType = $payrollPeriod->payroll_type ?? \App\Enums\PayrollType::Regular;
    $isBonusPayroll = $payrollType->isBonus();

    // On bonus sheets these columns are always zero — keep them in place for
    // column structure, but mute them so HR reads past them.
    $deductionClass = $isBonusPayroll ? 'text-muted opacity-50' : 'text-danger';
    $formatDeduction = function ($value) use ($isBonusPayroll) {
        $amount = (float) $value;

        if ($isBonusPayroll) {
            return number_format($amount, 2);
        }

        return $amount > 0 ? number_format($amount, 2) : '—';
    };

    $records = $records ?? $payrollRecords ?? $payrollPeriod->payrollRecords ?? collect();
    $currentPosition = $currentPosition ?? strtolower(request('position', request('filter', 'all')));
    $currentGender = $currentGender ?? strtolower(request('gender', 'all'));
    $currentFilter = $currentFilter ?? $currentPosition;
    $totalEmployees = $totalEmployees ?? $records->count();
    $totalGross = $totalGross ?? $records->sum('gross_earned');
    $totalAbsences = $totalAbsences ?? $records->sum('absences_amount');
    $totalLates = $totalLates ?? $records->sum('late_deduction');
    $totalTax = $totalTax ?? $records->sum('tax_withheld');
    $totalGsis = $totalGsis ?? $records->sum('gsis_premium');
    $totalPhilhealth = $totalPhilhealth ?? $records->sum('philhealth_premium');
    $totalPagibig = $totalPagibig ?? $records->sum('pagibig_premium');
    $totalLoans = $totalLoans ?? $records->sum('loan_amortization');
    $totalDeductions = $totalDeductions ?? $records->sum('total_deductions');
    $totalNet = $totalNet ?? $records->sum('net_amount');
@endphp

<div class="container-fluid py-4">
    {{-- Top Navigation & Period Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('hr.payroll.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1">
                    <i class="bi bi-arrow-left me-1"></i> Back to Periods
                </a>
                <span class="badge rounded-pill {{ $statusBadge }} px-3 py-1 fw-semibold" style="font-size: 0.8rem;">
                    {{ $status }}
                </span>
                <span class="badge rounded-pill border {{ $payrollType->badgeClass() }} px-3 py-1 fw-semibold" style="font-size: 0.8rem;">
                    <i class="bi {{ $isBonusPayroll ? 'bi-gift-fill' : 'bi-calendar-check-fill' }} me-1"></i>{{ $payrollType->value }}
                </span>
            </div>
            <h4 class="text-header-blue fw-bold mb-1">
                <i class="bi bi-file-earmark-spreadsheet me-2 text-header-blue"></i>
                {{ $isBonusPayroll ? $payrollType->value : 'Payroll' }} for {{ $monthName }} {{ $payrollPeriod->period_year }}
            </h4>
            <div class="text-muted small d-flex flex-wrap align-items-center gap-3">
                <span><i class="bi bi-tag me-1 text-secondary"></i><strong>Fund Cluster:</strong> {{ $payrollPeriod->fund_cluster }}</span>
                <span><i class="bi bi-wallet2 me-1 text-secondary"></i><strong>Type:</strong> {{ $payrollType->value }}</span>
                <span><i class="bi bi-calendar3 me-1 text-secondary"></i><strong>Period:</strong> {{ $monthName }} {{ $payrollPeriod->period_year }}</span>
                @if($payrollPeriod->description)
                    <span><i class="bi bi-info-circle me-1 text-secondary"></i>{{ $payrollPeriod->description }}</span>
                @endif
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($status === 'DRAFT')
                <form action="{{ route('hr.payroll.approve', $payrollPeriod->id) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm shadow-sm" onclick="return confirm('Are you sure you want to finalize this payroll period? This will lock it for BIR 2316 generation.')">
                        <i class="bi bi-check-circle me-1"></i> Finalize Payroll
                    </button>
                </form>
            @endif
            <a href="{{ route('hr.payroll.export', ['id' => $payrollPeriod->id, 'position' => request('position', request('filter')), 'gender' => request('gender')]) }}" class="btn btn-outline-success btn-sm shadow-sm">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Sheet
            </button>
        </div>
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(isset($errors) && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-center mb-2">
            <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
            <strong class="mb-0">Please check the submitted loan deduction values:</strong>
        </div>
        <ul class="mb-0 small ps-4">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Bonus Computation Notice --}}
    @if($isBonusPayroll)
    <div class="alert border-0 shadow-sm rounded-3 mb-4 d-flex align-items-start gap-2" role="alert" style="background-color: rgba(255, 193, 7, 0.12); color: #7a5200;">
        <i class="bi bi-gift-fill fs-5 mt-1"></i>
        <div class="small">
            <strong class="d-block mb-1">{{ $payrollType->value }} sheet — statutory deductions do not apply.</strong>
            Each employee receives one month of basic salary{{ $payrollType === \App\Enums\PayrollType::YearEndBonus ? ' plus the 5,000 cash gift' : '' }}.
            GSIS, PhilHealth, Pag-IBIG, absences, lates, and loan amortization are all 0.00 and shown greyed out below.
            Withholding tax applies only to the amount above the 90,000 TRAIN Law exemption.
        </div>
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm rounded-3 bg-white p-3 h-100">
                <span class="text-muted small fw-bold text-uppercase">Total Employees</span>
                <h4 class="fw-bold mb-0 mt-1 text-header-blue">{{ $totalEmployees }}</h4>
                <span class="small text-muted">Active in period</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-info">
                <span class="text-muted small fw-bold text-uppercase">Total Gross Earned</span>
                <h4 class="fw-bold mb-0 mt-1 text-info">{{ number_format($totalGross, 2) }}</h4>
                <span class="small text-muted">Gross payroll payout</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-danger">
                <span class="text-muted small fw-bold text-uppercase">Total Deductions</span>
                <h4 class="fw-bold mb-0 mt-1 text-danger">{{ number_format($totalDeductions, 2) }}</h4>
                <span class="small text-muted">Statutory & other cuts</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                <span class="text-muted small fw-bold text-uppercase">Total Net Amount</span>
                <h4 class="fw-bold mb-0 mt-1 text-success">{{ number_format($totalNet, 2) }}</h4>
                <span class="small text-muted">Net disbursement</span>
            </div>
        </div>
    </div>

    {{-- Deduction UI Profile Toggle --}}
    <div class="card shadow-sm rounded-3 mb-3">
        <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 bg-white">
            <div>
                <h6 class="mb-0 fw-bold text-header-blue">
                    <i class="bi bi-layout-text-sidebar-reverse me-2"></i>Deduction UI Profile
                </h6>
                <small class="text-muted">Select which deduction layout to display in the "Manage Deductions & Loans" modal.</small>
            </div>
            <form action="{{ route('hr.payroll.toggle_deduction_version') }}" method="POST" class="d-flex align-items-center gap-2 mb-0">
                @csrf
                <select name="deduction_version" class="form-select form-select-sm fw-bold shadow-sm" style="min-width: 250px; border-color: #facc15; background-color: #fde047; color: #1e293b;" onchange="this.form.submit()">
                    <option value="v1" {{ session('deduction_version', 'v1') == 'v1' ? 'selected' : '' }}>Option 1: Dynamic (Database)</option>
                    <option value="v2" {{ session('deduction_version') == 'v2' ? 'selected' : '' }}>Option 2: Hardcoded (Legacy)</option>
                    {{-- Future options can easily be added here --}}
                </select>
            </form>
        </div>
    </div>

    {{-- Master Sheet Table Card --}}
    <div class="card shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                <div>
                    <h6 class="fw-bold mb-0 text-header-blue">
                        <i class="bi bi-table me-2"></i> Payroll Master Sheet Records
                    </h6>
                    <p class="text-muted small mb-0">Complete breakdown of earnings, statutory withholdings, and net salaries.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border px-3 py-2">
                        <i class="bi bi-people-fill me-1 text-secondary"></i>
                        @if(method_exists($payrollRecords, 'total'))
                            {{ $payrollRecords->total() }} Records Found
                        @else
                            {{ $records->count() }} Records Shown
                        @endif
                    </span>
                </div>
            </div>

            {{-- Filter Bar: Position & Gender --}}
            @php
                $isAllPosActive = !in_array($currentPosition, ['teaching', 'non-teaching']);
                $isTeachingActive = ($currentPosition === 'teaching');
                $isNonTeachingActive = ($currentPosition === 'non-teaching');

                $isAllGenderActive = !in_array($currentGender, ['male', 'female', '1', '0']);
                $isMaleActive = in_array($currentGender, ['male', '1']);
                $isFemaleActive = in_array($currentGender, ['female', '0']);
            @endphp
            <div class="d-flex flex-column gap-2 pt-2 border-top">
                {{-- Position Filter Row --}}
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="text-muted small fw-semibold me-1" style="min-width: 60px;">
                        <i class="bi bi-briefcase me-1 text-secondary"></i>Position:
                    </span>
                    <a href="{{ route('hr.payroll.show', ['id' => $payrollPeriod->id, 'position' => 'all', 'gender' => request('gender')]) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1 text-decoration-none d-inline-flex align-items-center gap-1 {{ $isAllPosActive ? 'bg-accent fw-bold shadow-sm' : 'btn-outline-secondary' }}">
                        <i class="bi bi-people me-1"></i> All Employees
                        <span class="badge {{ $isAllPosActive ? 'bg-white text-dark' : 'bg-light text-secondary border' }} ms-1">
                            {{ $counts['all'] ?? $totalEmployees }}
                        </span>
                    </a>

                    <a href="{{ route('hr.payroll.show', ['id' => $payrollPeriod->id, 'position' => 'teaching', 'gender' => request('gender')]) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1 text-decoration-none d-inline-flex align-items-center gap-1 {{ $isTeachingActive ? 'bg-accent fw-bold shadow-sm' : 'btn-outline-secondary' }}">
                        <i class="bi bi-book-half me-1"></i> Teaching
                        <span class="badge {{ $isTeachingActive ? 'bg-white text-dark' : 'bg-light text-secondary border' }} ms-1">
                            {{ $counts['teaching'] ?? 0 }}
                        </span>
                    </a>

                    <a href="{{ route('hr.payroll.show', ['id' => $payrollPeriod->id, 'position' => 'non-teaching', 'gender' => request('gender')]) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1 text-decoration-none d-inline-flex align-items-center gap-1 {{ $isNonTeachingActive ? 'bg-accent fw-bold shadow-sm' : 'btn-outline-secondary' }}">
                        <i class="bi bi-buildings me-1"></i> Non-Teaching
                        <span class="badge {{ $isNonTeachingActive ? 'bg-white text-dark' : 'bg-light text-secondary border' }} ms-1">
                            {{ $counts['non_teaching'] ?? 0 }}
                        </span>
                    </a>
                </div>

                {{-- Gender Toggle Buttons Row --}}
                <div class="d-flex flex-wrap align-items-center gap-2 pt-2 border-top">
                    <span class="text-muted small fw-semibold me-1" style="min-width: 60px;">
                        <i class="bi bi-gender-ambiguous me-1 text-secondary"></i>Gender:
                    </span>
                    <a href="{{ route('hr.payroll.show', ['id' => $payrollPeriod->id, 'gender' => 'all', 'position' => request('position', request('filter'))]) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1 text-decoration-none d-inline-flex align-items-center gap-1 {{ $isAllGenderActive ? 'bg-accent fw-bold shadow-sm' : 'btn-outline-secondary' }}">
                        <i class="bi bi-people me-1"></i> All
                        @if(isset($genderCounts['all']))
                            <span class="badge {{ $isAllGenderActive ? 'bg-white text-dark' : 'bg-light text-secondary border' }} ms-1">
                                {{ $genderCounts['all'] }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('hr.payroll.show', ['id' => $payrollPeriod->id, 'gender' => 'male', 'position' => request('position', request('filter'))]) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1 text-decoration-none d-inline-flex align-items-center gap-1 {{ $isMaleActive ? 'bg-accent fw-bold shadow-sm' : 'btn-outline-secondary' }}">
                        <i class="bi bi-gender-male me-1"></i> Male
                        @if(isset($genderCounts['male']))
                            <span class="badge {{ $isMaleActive ? 'bg-white text-dark' : 'bg-light text-secondary border' }} ms-1">
                                {{ $genderCounts['male'] }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('hr.payroll.show', ['id' => $payrollPeriod->id, 'gender' => 'female', 'position' => request('position', request('filter'))]) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1 text-decoration-none d-inline-flex align-items-center gap-1 {{ $isFemaleActive ? 'bg-accent fw-bold shadow-sm' : 'btn-outline-secondary' }}">
                        <i class="bi bi-gender-female me-1"></i> Female
                        @if(isset($genderCounts['female']))
                            <span class="badge {{ $isFemaleActive ? 'bg-white text-dark' : 'bg-light text-secondary border' }} ms-1">
                                {{ $genderCounts['female'] }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            {{-- Responsive Horizontally Scrollable Table --}}
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0 text-nowrap">
                    <thead class="bg-light text-secondary">
                        <tr style="font-size: 0.8rem; letter-spacing: 0.5px;">
                            <th class="py-3 ps-3 text-center border-bottom text-uppercase" style="width: 40px;">#</th>
                            <th class="py-3 ps-3 border-bottom text-uppercase" style="min-width: 220px;">Employee Name</th>
                            <th class="py-3 text-end border-bottom text-uppercase" style="min-width: 120px;">Basic Rate</th>
                            <th class="py-3 text-end border-bottom text-uppercase" style="min-width: 130px;">Gross Earned</th>
                            <th class="py-3 text-end border-bottom text-uppercase {{ $deductionClass }}" style="min-width: 110px;">Absences</th>
                            <th class="py-3 text-end border-bottom text-uppercase {{ $deductionClass }}" style="min-width: 110px;">Lates</th>
                            <th class="py-3 text-end border-bottom text-uppercase text-danger" style="min-width: 110px;">Tax</th>
                            <th class="py-3 text-end border-bottom text-uppercase {{ $deductionClass }}" style="min-width: 110px;">GSIS</th>
                            <th class="py-3 text-end border-bottom text-uppercase {{ $deductionClass }}" style="min-width: 110px;">PhilHealth</th>
                            <th class="py-3 text-end border-bottom text-uppercase {{ $deductionClass }}" style="min-width: 110px;">Pag-IBIG</th>
                            <th class="py-3 text-end border-bottom text-uppercase {{ $deductionClass }}" style="min-width: 110px;">Loans</th>
                            <th class="py-3 text-end border-bottom text-uppercase text-danger fw-bold" style="min-width: 140px;">Total Deductions</th>
                            <th class="py-3 pe-3 text-end border-bottom text-uppercase fw-bold" style="min-width: 140px;">Net Amount</th>
                            <th class="py-3 pe-3 text-center border-bottom text-uppercase" style="min-width: 160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $index => $record)
                        @php
                            $user = $record->user;
                            $employeeName = 'Unknown Employee';
                            $employeeMeta = null;

                            if ($user) {
                                if (!empty($user->last_name) || !empty($user->first_name)) {
                                    $employeeName = $user->last_name . ', ' . $user->first_name;
                                    if (!empty($user->middle_name)) {
                                        $employeeName .= ' ' . strtoupper(substr($user->middle_name, 0, 1)) . '.';
                                    }
                                    if (!empty($user->suffix)) {
                                        $employeeName .= ' ' . $user->suffix;
                                    }
                                } elseif (!empty($user->name)) {
                                    $employeeName = $user->name;
                                } else {
                                    $employeeName = 'Employee #' . $user->id;
                                }

                                $isTeachingUser = ($user->position && $user->position->category === \App\Enums\PositionCategory::Teaching)
                                    || ($user->isTeaching());
                                $employeeMeta = $isTeachingUser ? 'TEACHING' : 'NON_TEACHING';
                            }

                            $otherDeductionsList = is_array($record->other_deductions) ? $record->other_deductions : [];
                            $loansSum = array_sum($otherDeductionsList);
                        @endphp
                        <tr>
                            <td class="text-center ps-3 text-muted small fw-semibold">
                                {{ method_exists($payrollRecords, 'firstItem') && $payrollRecords->firstItem() ? ($payrollRecords->firstItem() + $index) : ($index + 1) }}
                            </td>
                            <td class="ps-3 py-2">
                                <a href="#" class="text-decoration-none fw-bold text-header-blue" data-bs-toggle="modal" data-bs-target="#profileModal{{ $user->id ?? $record->user_id }}">
                                    {{ $employeeName }}
                                    <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.65rem; opacity: 0.5;"></i>
                                </a>
                                @if($employeeMeta)
                                    <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">{{ $employeeMeta }}</span>
                                @elseif($user && $user->username)
                                    <small class="text-muted">{{ $user->username }}</small>
                                @endif
                            </td>
                            <td class="text-end text-muted font-monospace">
                                {{ number_format((float)$record->basic_rate, 2) }}
                            </td>
                            @if($record->is_full_lwop)
                            {{-- Full Month LWOP: span across all financial columns --}}
                            <td colspan="10" class="text-center py-3" style="background-color: #fff3f3;">
                                <span class="text-danger fw-bold" style="letter-spacing: 1.5px; font-size: 0.85rem;">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> LEAVE WITHOUT PAY
                                </span>
                            </td>
                            <td class="text-center py-2 pe-3">
                                <span class="badge bg-danger bg-opacity-10 text-danger border px-2 py-1" style="font-size: 0.72rem;">LWOP</span>
                            </td>
                            @else
                            <td class="text-end fw-semibold font-monospace" style="color: #1A3E6F;">
                                {{ number_format((float)$record->gross_earned, 2) }}
                            </td>
                            <td class="text-end font-monospace {{ $deductionClass }}">
                                {{ $formatDeduction($record->absences_amount) }}
                            </td>
                            <td class="text-end font-monospace {{ $deductionClass }}">
                                {{ $formatDeduction($record->late_deduction) }}
                            </td>
                            <td class="text-end text-danger font-monospace">
                                {{ (float)$record->tax_withheld > 0 ? number_format((float)$record->tax_withheld, 2) : '—' }}
                            </td>
                            <td class="text-end font-monospace {{ $deductionClass }}">
                                {{ $formatDeduction($record->gsis_premium) }}
                            </td>
                            <td class="text-end font-monospace {{ $deductionClass }}">
                                {{ $formatDeduction($record->philhealth_premium) }}
                            </td>
                            <td class="text-end font-monospace {{ $deductionClass }}">
                                {{ $formatDeduction($record->pagibig_premium) }}
                            </td>
                            <td class="text-end font-monospace {{ $deductionClass }}">
                                {{ $formatDeduction($record->loan_amortization) }}
                            </td>
                            <td class="text-end text-danger fw-bold font-monospace bg-light bg-opacity-50">
                                <div>{{ number_format((float)$record->total_deductions, 2) }}</div>
                                @if($loansSum > 0)
                                    <div class="text-muted fw-normal" style="font-size: 0.68rem;">
                                        incl. {{ number_format($loansSum, 2) }} loans
                                    </div>
                                @endif
                            </td>
                            <td class="pe-3 text-end fw-bold font-monospace" style="font-size: 0.95rem;">
                                {{ number_format((float)$record->net_amount, 2) }}
                            </td>
                            <td class="text-center py-2 pe-3">
                                <button type="button" 
                                        class="btn btn-sm btn-accent shadow-sm d-inline-flex align-items-center gap-1" 
                                        style="font-size: 0.78rem; padding: 0.3rem 0.75rem;"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deductionsModal{{ $record->id }}"
                                        title="Manage loans and variable deductions">
                                    <i class="bi bi-pencil-square"></i>
                                    <span>Manage Deductions</span>
                                </button>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="14" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-person-x text-muted display-4 d-block mb-3 opacity-50"></i>
                                    <h6 class="fw-bold text-secondary mb-1">No records found for this filter</h6>
                                    <p class="text-muted small mb-0">Try adjusting your position or gender filters to view records.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($records->isNotEmpty())
                    <tfoot class="table-light fw-bold text-dark border-top-2">
                        <tr style="font-size: 0.85rem;">
                            <td colspan="2" class="ps-3 py-3 text-uppercase text-header-blue">
                                Total ({{ $totalEmployees }} Employees)
                            </td>
                            <td class="text-end font-monospace text-muted py-3">—</td>
                            <td class="text-end font-monospace py-3 text-header-blue">
                                {{ number_format($totalGross, 2) }}
                            </td>
                            <td class="text-end font-monospace py-3 {{ $deductionClass }}">
                                {{ number_format($totalAbsences, 2) }}
                            </td>
                            <td class="text-end font-monospace py-3 {{ $deductionClass }}">
                                {{ number_format($totalLates, 2) }}
                            </td>
                            <td class="text-end font-monospace text-danger py-3">
                                {{ number_format($totalTax, 2) }}
                            </td>
                            <td class="text-end font-monospace py-3 {{ $deductionClass }}">
                                {{ number_format($totalGsis, 2) }}
                            </td>
                            <td class="text-end font-monospace py-3 {{ $deductionClass }}">
                                {{ number_format($totalPhilhealth, 2) }}
                            </td>
                            <td class="text-end font-monospace py-3 {{ $deductionClass }}">
                                {{ number_format($totalPagibig, 2) }}
                            </td>
                            <td class="text-end font-monospace py-3 {{ $deductionClass }}">
                                {{ number_format($totalLoans, 2) }}
                            </td>
                            <td class="text-end font-monospace text-danger py-3">
                                {{ number_format($totalDeductions, 2) }}
                            </td>
                            <td class="pe-3 text-end font-monospace py-3 fw-bold" style="font-size: 1rem;">
                                {{ number_format($totalNet, 2) }}
                            </td>
                            <td class="text-center font-monospace text-muted py-3 pe-3">—</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            {{-- Pagination UI --}}
            <div class="d-flex justify-content-end mt-3 px-3 pb-3">
                {{ $payrollRecords->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    {{-- Modals for Managing Employee Incomes & Deductions (Rendered outside table to prevent overflow clipping) --}}
    <style>
        .deductions-scroll-modal .modal-content > form {
            max-height: 100%;
            min-height: 0;
        }
        .deductions-scroll-modal .modal-body {
            overflow-y: auto;
            min-height: 0;
        }
    </style>
    @foreach($records as $record)
    @php
        $modalUser = $record->user;
        $modalEmpName = 'Employee #' . $record->user_id;

        if ($modalUser) {
            if (!empty($modalUser->last_name) || !empty($modalUser->first_name)) {
                $modalEmpName = $modalUser->last_name . ', ' . $modalUser->first_name;
                if (!empty($modalUser->middle_name)) {
                    $modalEmpName .= ' ' . strtoupper(substr($modalUser->middle_name, 0, 1)) . '.';
                }
                if (!empty($modalUser->suffix)) {
                    $modalEmpName .= ' ' . $modalUser->suffix;
                }
            } elseif (!empty($modalUser->name)) {
                $modalEmpName = $modalUser->name;
            }
        }

        $deductions = is_array($record->other_deductions) ? $record->other_deductions : [];
        $existingIncomes = $record->payrollIncomes ?? collect();
    @endphp
    <div class="modal fade deductions-scroll-modal" id="deductionsModal{{ $record->id }}" tabindex="-1" aria-labelledby="deductionsModalLabel{{ $record->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('hr.payroll.update_record', $record->id) }}" method="POST" class="d-flex flex-column overflow-hidden">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="position" value="{{ request('position', request('filter')) }}">
                    <input type="hidden" name="gender" value="{{ request('gender') }}">
                    <input type="hidden" name="page" value="{{ request('page', 1) }}">
                    <input type="hidden" name="filter" value="{{ request('filter', request('position')) }}">
                    <div class="modal-header border-bottom py-3 flex-shrink-0" style="background-color: #f8fafc;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-accent" style="width: 38px; height: 38px;">
                                <i class="bi bi-wallet2 fs-5"></i>
                            </div>
                            <div>
                                <h6 class="modal-title fw-bold mb-0 text-header-blue" id="deductionsModalLabel{{ $record->id }}">
                                    Manage Deductions & Loans
                                </h6>
                                <div class="text-muted small">
                                    <strong>Employee:</strong> {{ $modalEmpName }}
                                    @php
                                        $isTeachingModalUser = ($modalUser && $modalUser->position && $modalUser->position->category === \App\Enums\PositionCategory::Teaching)
                                            || ($modalUser && $modalUser->isTeaching());
                                        $modalEmployeeCategory = $isTeachingModalUser ? 'TEACHING' : 'NON_TEACHING';
                                    @endphp
                                    <span class="badge bg-light text-secondary border ms-1">{{ $modalEmployeeCategory }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary copyDataBtn" title="Copy deduction values">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary pasteDataBtn" title="Paste deduction values">
                                <i class="bi bi-clipboard-check"></i> Paste
                            </button>
                            <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>

                    <div class="modal-body p-4">
                        {{-- Financial Overview Ribbon --}}
                        <div class="row g-2 mb-4 p-3 rounded-3 bg-light border">
                            <div class="col-sm-3 col-6 text-center border-end">
                                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Basic Salary</span>
                                <div class="fw-bold font-monospace text-secondary">{{ number_format((float)$record->basic_rate, 2) }}</div>
                            </div>
                            <div class="col-sm-3 col-6 text-center border-end">
                                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Gross Earned</span>
                                <div class="fw-bold font-monospace" style="color: #1A3E6F;">{{ number_format((float)$record->gross_earned, 2) }}</div>
                            </div>
                            <div class="col-sm-3 col-6 text-center border-end">
                                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Mandatory Cuts</span>
                                <div class="fw-bold font-monospace text-danger">{{ number_format((float)($record->gsis_premium + $record->philhealth_premium + $record->pagibig_premium + $record->absences_amount + $record->tax_withheld), 2) }}</div>
                            </div>
                            <div class="col-sm-3 col-6 text-center">
                                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Current Net Pay</span>
                                <div class="fw-bold font-monospace text-success">{{ number_format((float)$record->net_amount, 2) }}</div>
                            </div>
                        </div>

                        {{-- ====== DEDUCTIONS SECTION (Versioned Layout) ====== --}}
                        @switch($layoutVersion)
                            @case('v2')
                                {{-- OPTION 2: HARDCODED LEGACY LAYOUT --}}
                                <div class="alert alert-warning py-2 px-3 small d-flex align-items-center mb-3">
                                    <i class="bi bi-exclamation-triangle-fill me-2 flex-shrink-0 fs-6"></i>
                                    <div>
                                        <strong>Legacy Layout (V2):</strong> This layout uses hardcoded deduction fields. Changes to deduction categories in settings will not be reflected here.
                                    </div>
                                </div>

                                {{-- GSIS Loans --}}
                                <h6 class="fw-bold mt-3 mb-3" style="color: #1A3E6F; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px;">
                                    <i class="bi bi-building me-2"></i>GSIS Loans
                                </h6>
                                <div class="row g-3">
                                    @php
                                        $gsisFields = [
                                            'gsis_conso'     => 'GSIS Consolidated Loan',
                                            'gsis_policy'    => 'GSIS Policy Loan',
                                            'gsis_ouli'      => 'GSIS Optional / OULI',
                                            'gsis_eml'       => 'GSIS Emergency Loan',
                                            'gsis_uoli'      => 'GSIS UOLI',
                                            'gsis_edu'       => 'GSIS Education Loan',
                                            'gsis_enhanced'  => 'GSIS Enhanced Loan',
                                            'gsis_rlip'      => 'GSIS RLIP',
                                        ];
                                    @endphp
                                    @foreach($gsisFields as $fieldKey => $fieldLabel)
                                        <div class="col-md-4 col-sm-6">
                                            <label for="{{ $fieldKey }}_{{ $record->id }}" class="form-label small fw-semibold text-secondary mb-1 dynamic-label">{{ $fieldLabel }}</label>
                                            <div class="input-group input-group-sm">
                                                                                                <input type="number" step="0.01" min="0"
                                                       class="form-control font-monospace dynamic-input"
                                                       id="{{ $fieldKey }}_{{ $record->id }}"
                                                       name="other_deductions[{{ $fieldKey }}]"
                                                       value="{{ isset($deductions[$fieldKey]) && (float)$deductions[$fieldKey] > 0 ? number_format((float)$deductions[$fieldKey], 2, '.', '') : '' }}"
                                                       placeholder="0.00">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Pag-IBIG Loans --}}
                                <h6 class="fw-bold mt-4 mb-3" style="color: #1A3E6F; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px;">
                                    <i class="bi bi-house-door me-2"></i>Pag-IBIG (HDMF) Loans
                                </h6>
                                <div class="row g-3">
                                    @php
                                        $pagibigFields = [
                                            'pagibig_mpl'      => 'Pag-IBIG Multi-Purpose Loan',
                                            'pagibig_calamity' => 'Pag-IBIG Calamity Loan',
                                            'pagibig_mp2'      => 'Pag-IBIG MP2 Savings',
                                            'pagibig_housing'  => 'Pag-IBIG Housing Loan',
                                        ];
                                    @endphp
                                    @foreach($pagibigFields as $fieldKey => $fieldLabel)
                                        <div class="col-md-4 col-sm-6">
                                            <label for="{{ $fieldKey }}_{{ $record->id }}" class="form-label small fw-semibold text-secondary mb-1 dynamic-label">{{ $fieldLabel }}</label>
                                            <div class="input-group input-group-sm">
                                                                                                <input type="number" step="0.01" min="0"
                                                       class="form-control font-monospace dynamic-input"
                                                       id="{{ $fieldKey }}_{{ $record->id }}"
                                                       name="other_deductions[{{ $fieldKey }}]"
                                                       value="{{ isset($deductions[$fieldKey]) && (float)$deductions[$fieldKey] > 0 ? number_format((float)$deductions[$fieldKey], 2, '.', '') : '' }}"
                                                       placeholder="0.00">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Other / Institutional Loans --}}
                                <h6 class="fw-bold mt-4 mb-3" style="color: #1A3E6F; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px;">
                                    <i class="bi bi-bank me-2"></i>Other / Institutional Loans
                                </h6>
                                <div class="row g-3">
                                    @php
                                        $otherFields = [
                                            'landbank_salary'  => 'LandBank Salary Loan',
                                            'philhealth_loan'  => 'PhilHealth Loan',
                                            'coop_loan'        => 'Multi-Purpose Coop Loan',
                                            'salary_loan_other'=> 'Other Salary Loan',
                                        ];
                                    @endphp
                                    @foreach($otherFields as $fieldKey => $fieldLabel)
                                        <div class="col-md-4 col-sm-6">
                                            <label for="{{ $fieldKey }}_{{ $record->id }}" class="form-label small fw-semibold text-secondary mb-1 dynamic-label">{{ $fieldLabel }}</label>
                                            <div class="input-group input-group-sm">
                                                                                                <input type="number" step="0.01" min="0"
                                                       class="form-control font-monospace dynamic-input"
                                                       id="{{ $fieldKey }}_{{ $record->id }}"
                                                       name="other_deductions[{{ $fieldKey }}]"
                                                       value="{{ isset($deductions[$fieldKey]) && (float)$deductions[$fieldKey] > 0 ? number_format((float)$deductions[$fieldKey], 2, '.', '') : '' }}"
                                                       placeholder="0.00">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @break

                            @case('v3')
                                {{-- OPTION 3: Placeholder for future layout --}}
                                <div class="alert alert-info d-flex align-items-center mb-0">
                                    <i class="bi bi-gear-wide-connected me-2 fs-5"></i>
                                    <div>
                                        <strong>Layout V3</strong> is under development. Switch back to Option 1 or Option 2 for now.
                                    </div>
                                </div>
                                @break

                            @default
                                {{-- OPTION 1 (DEFAULT): DYNAMIC DATABASE LAYOUT --}}
                                <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-3">
                                    <i class="bi bi-info-circle-fill me-2 flex-shrink-0 fs-6"></i>
                                    <div>
                                        Enter monthly loan deduction amounts below. Blank or empty inputs will automatically be converted to <strong>0.00</strong>. Totals and net pay will be recalculated immediately upon saving.
                                    </div>
                                </div>

                                {{-- Dynamic Loan Input Fields (from deduction_categories/types) --}}
                                @foreach($categories as $category)
                                    <h6 class="fw-bold mt-4 mb-3" style="color: #1A3E6F; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px;">
                                        <i class="bi bi-folder2 me-2"></i>{{ $category->name }}
                                    </h6>
                                    <div class="row g-3">
                                        @foreach($category->types as $type)
                                            @php
                                                $val = isset($deductions[$type->code]) && (float)$deductions[$type->code] > 0
                                                    ? number_format((float)$deductions[$type->code], 2, '.', '')
                                                    : '';
                                            @endphp
                                            <div class="col-md-4 col-sm-6">
                                                <label for="{{ $type->code }}_{{ $record->id }}" class="form-label small fw-semibold text-secondary mb-1 text-truncate w-100 dynamic-label" title="{{ $type->name }}">
                                                    {{ $type->name }}
                                                </label>
                                                <div class="input-group input-group-sm">
                                                                                                        <input type="number"
                                                           step="0.01"
                                                           min="0"
                                                           class="form-control font-monospace dynamic-input"
                                                           id="{{ $type->code }}_{{ $record->id }}"
                                                           name="other_deductions[{{ $type->code }}]"
                                                           value="{{ $val }}"
                                                           placeholder="0.00">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                        @endswitch
                    </div>

                    <div class="modal-footer bg-light py-2 px-4 border-top flex-shrink-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-accent px-4 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Employee Payroll Profile Modals --}}
    @foreach($records as $record)
    @php
        $profileUser = $record->user;
        if (!$profileUser) continue;

        $profileName = 'Employee #' . $record->user_id;
        if (!empty($profileUser->last_name) || !empty($profileUser->first_name)) {
            $profileName = $profileUser->last_name . ', ' . $profileUser->first_name;
            if (!empty($profileUser->middle_name)) {
                $profileName .= ' ' . strtoupper(substr($profileUser->middle_name, 0, 1)) . '.';
            }
            if (!empty($profileUser->suffix)) {
                $profileName .= ' ' . $profileUser->suffix;
            }
        } elseif (!empty($profileUser->name)) {
            $profileName = $profileUser->name;
        }

        $pdsProfile = $profileUser->pdsPersonalInfo ?? null;
        $employeeId = $pdsProfile->employee_no ?? 'N/A';
        $positionName = ($profileUser->position) ? $profileUser->position->position_name : 'Unassigned';
        $salaryGrade = ($profileUser->position) ? $profileUser->position->salary_grade : null;
        $currentStep = $profileUser->step_increment ?: 1;
        $currentRate = $profileUser->base_salary;
        $nextStepRate = $profileUser->next_step_rate;
        $lastIncrementDate = $profileUser->last_increment_date
            ? \Carbon\Carbon::parse($profileUser->last_increment_date)->format('M d, Y')
            : 'N/A';
        $nextEligibilityDate = $profileUser->next_eligibility_date
            ? \Carbon\Carbon::parse($profileUser->next_eligibility_date)->format('M d, Y')
            : 'N/A';
        $dateHired = $profileUser->date_hired
            ? \Carbon\Carbon::parse($profileUser->date_hired)->format('M d, Y')
            : 'N/A';
        $employmentStatus = $profileUser->employment_status;
        $incrementLogs = $profileUser->stepIncrementLogs->sortByDesc('effective_date');
        $isAtMaxStep = $currentStep >= 8;
    @endphp
    <div class="modal fade" id="profileModal{{ $profileUser->id }}" tabindex="-1" aria-labelledby="profileModalLabel{{ $profileUser->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                {{-- Modal Header --}}
                <div class="modal-header border-0 pb-0 bg-accent">
                    <div class="d-flex align-items-center gap-3 py-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-white" style="width: 48px; height: 48px;">
                            <i class="bi bi-person-badge fs-4 text-header-blue"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0" id="profileModalLabel{{ $profileUser->id }}">
                                {{ $profileName }}
                            </h6>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                <span class="badge bg-white text-dark" style="font-size: 0.72rem;">
                                    <i class="bi bi-hash me-1"></i>{{ $employeeId }}
                                </span>
                                <span class="badge bg-white text-dark" style="font-size: 0.72rem;">
                                    <i class="bi bi-briefcase me-1"></i>{{ $positionName }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    {{-- Employment Info Card (derived from service_records) --}}
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 h-100" style="background-color: #f0f4f8;">
                                <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">
                                    <i class="bi bi-calendar-event me-1"></i>Date Hired
                                </div>
                                <div class="fw-bold" style="color: #1A3E6F;">{{ $dateHired }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 h-100" style="background-color: #f0f4f8;">
                                <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">
                                    <i class="bi bi-shield-check me-1"></i>Employment Status
                                </div>
                                <div class="fw-bold" style="color: #1A3E6F;">
                                    <span class="badge {{ $employmentStatus === 'Permanent' ? 'bg-success' : ($employmentStatus === 'Unassigned' ? 'bg-secondary' : 'bg-info text-dark') }} px-2 py-1">
                                        {{ $employmentStatus }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Salary Details Card --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-2">
                            <h6 class="fw-bold mb-0 small" style="color: #1A3E6F;">
                                <i class="bi bi-cash-stack me-2"></i>Salary Details
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-borderless mb-0 small">
                                <tbody>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-2 ps-3" style="width: 50%;">Current Salary Grade</td>
                                        <td class="fw-bold text-end pe-3 py-2" style="color: #1A3E6F;">
                                            {{ $salaryGrade ? 'SG-' . $salaryGrade : 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-2 ps-3">Current Step</td>
                                        <td class="fw-bold text-end pe-3 py-2" style="color: #1A3E6F;">
                                            Step {{ $currentStep }}
                                            @if($isAtMaxStep)
                                                <span class="badge bg-success ms-1" style="font-size: 0.65rem;">MAX</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-2 ps-3">Current Rate</td>
                                        <td class="fw-bold text-end pe-3 py-2 font-monospace" style="color: #1A3E6F;">
                                            {{ number_format($currentRate, 2) }}
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-2 ps-3">Next Step Rate (Step {{ $currentStep + 1 }})</td>
                                        <td class="fw-bold text-end pe-3 py-2 font-monospace {{ $nextStepRate ? 'text-success' : 'text-muted' }}">
                                            {{ $nextStepRate ? number_format($nextStepRate, 2) : ($isAtMaxStep ? 'At Maximum' : 'N/A') }}
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="text-muted py-2 ps-3">Last Increment Date</td>
                                        <td class="fw-bold text-end pe-3 py-2" style="color: #1A3E6F;">{{ $lastIncrementDate }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-2 ps-3">Next Eligibility Date</td>
                                        <td class="fw-bold text-end pe-3 py-2">
                                            @if($profileUser->next_eligibility_date && \Carbon\Carbon::parse($profileUser->next_eligibility_date)->isPast())
                                                <span class="text-success">
                                                    <i class="bi bi-check-circle-fill me-1"></i>{{ $nextEligibilityDate }}
                                                    <span class="badge bg-success ms-1" style="font-size: 0.65rem;">ELIGIBLE</span>
                                                </span>
                                            @else
                                                <span style="color: #1A3E6F;">{{ $nextEligibilityDate }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Step Increment Action (HR/Admin only) --}}
                    @if(in_array((int)session('role_id'), [2, 3]))
                    <div class="card border-0 shadow-sm mb-4 {{ $isAtMaxStep ? 'opacity-75' : '' }}">
                        <div class="card-header bg-white border-bottom py-2">
                            <h6 class="fw-bold mb-0 small" style="color: #1A3E6F;">
                                <i class="bi bi-arrow-up-circle me-2"></i>Process Step Increment (NOSI)
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($isAtMaxStep)
                                <div class="alert alert-success py-2 px-3 mb-0 small d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    This employee is already at the maximum salary step (Step 8). No further increments are available.
                                </div>
                            @else
                                <form action="{{ route('hr.payroll.step_increment.process', $profileUser->id) }}" method="POST" class="d-flex flex-column flex-sm-row align-items-sm-end gap-3">
                                    @csrf
                                    <div class="flex-grow-1">
                                        <label for="effective_date_{{ $profileUser->id }}" class="form-label small fw-semibold text-muted mb-1">
                                            Effective Date
                                        </label>
                                        <input type="date"
                                               class="form-control form-control-sm"
                                               id="effective_date_{{ $profileUser->id }}"
                                               name="effective_date"
                                               value="{{ now()->toDateString() }}">
                                    </div>
                                    <div class="flex-shrink-0">
                                        <button type="submit" class="btn btn-sm btn-accent px-4 shadow-sm"
                                                onclick="return confirm('Process step increment from Step {{ $currentStep }} to Step {{ $currentStep + 1 }} for {{ $profileName }}?')">
                                            <i class="bi bi-arrow-up-circle me-1"></i> Process Increment
                                        </button>
                                    </div>
                                </form>
                                <div class="mt-2 small text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    This will advance from <strong>Step {{ $currentStep }}</strong> ({{ number_format($currentRate, 2) }}) to <strong>Step {{ $currentStep + 1 }}</strong>
                                    @if($nextStepRate)
                                        ({{ number_format($nextStepRate, 2) }})
                                    @endif
                                    and will be reflected in future payroll calculations.
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Increment History / Audit Trail --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 small" style="color: #1A3E6F;">
                                <i class="bi bi-clock-history me-2"></i>Increment History
                            </h6>
                            @if($incrementLogs->count() > 0)
                                <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">
                                    {{ $incrementLogs->count() }} {{ Str::plural('record', $incrementLogs->count()) }}
                                </span>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            @if($incrementLogs->isEmpty())
                                <div class="text-center py-4 text-muted small">
                                    <i class="bi bi-inbox d-block mb-2" style="font-size: 1.5rem; opacity: 0.4;"></i>
                                    No increment history recorded yet.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm mb-0 small align-middle">
                                        <thead class="bg-light text-secondary" style="font-size: 0.72rem;">
                                            <tr>
                                                <th class="py-2 ps-3">Effective Date</th>
                                                <th class="py-2 text-center">Step Change</th>
                                                <th class="py-2 text-end">Old Rate</th>
                                                <th class="py-2 text-end">New Rate</th>
                                                <th class="py-2 pe-3">Approved By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($incrementLogs as $log)
                                            <tr>
                                                <td class="ps-3 py-2">
                                                    {{ $log->effective_date ? \Carbon\Carbon::parse($log->effective_date)->format('M d, Y') : '—' }}
                                                </td>
                                                <td class="text-center py-2">
                                                    <span class="badge bg-light text-dark border font-monospace">
                                                        {{ $log->old_step }} <i class="bi bi-arrow-right mx-1 text-muted"></i> {{ $log->new_step }}
                                                    </span>
                                                </td>
                                                <td class="text-end py-2 font-monospace text-muted">
                                                    {{ number_format((float)$log->old_rate, 2) }}
                                                </td>
                                                <td class="text-end py-2 font-monospace fw-semibold text-success">
                                                    {{ number_format((float)$log->new_rate, 2) }}
                                                </td>
                                                <td class="pe-3 py-2 text-muted">
                                                    @if($log->approver)
                                                        {{ $log->approver->last_name }}, {{ $log->approver->first_name }}
                                                    @else
                                                        <span class="text-muted fst-italic">System</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    /**
     * Universal Dynamic Copy/Paste for Deductions.
     *
     * Matches data by the visible label text (.dynamic-label) rather than
     * input name attributes, making it work across v1 (database) and v2
     * (hardcoded) layouts without any changes.
     */

    // Scope copy/paste to the currently visible (open) modal
    function getActiveModal() {
        return document.querySelector('.modal.show');
    }

    // --- COPY ---
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.copyDataBtn');
        if (!btn) return;

        const modal = btn.closest('.modal') || getActiveModal();
        if (!modal) return;

        let data = {};
        modal.querySelectorAll('.dynamic-input').forEach(function (input) {
            const labelEl = input.closest('.input-group')?.previousElementSibling
                         || input.closest('div')?.previousElementSibling;

            if (labelEl && labelEl.classList.contains('dynamic-label') && input.value) {
                const labelText = labelEl.innerText.trim();
                data[labelText] = input.value;
            }
        });

        if (Object.keys(data).length === 0) {
            btn.innerHTML = '<i class="bi bi-x-circle"></i> Nothing to copy';
            setTimeout(function () { btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy'; }, 2000);
            return;
        }

        navigator.clipboard.writeText(JSON.stringify(data)).then(function () {
            btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            setTimeout(function () {
                btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 2000);
        });
    });

    // --- PASTE ---
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.pasteDataBtn');
        if (!btn) return;

        const modal = btn.closest('.modal') || getActiveModal();
        if (!modal) return;

        navigator.clipboard.readText().then(function (text) {
            let data;
            try {
                data = JSON.parse(text);
            } catch (_) {
                alert('Clipboard does not contain valid deduction data.');
                return;
            }

            const allLabels = Array.from(modal.querySelectorAll('.dynamic-label'));
            let matched = 0;

            for (const key in data) {
                const matchedLabel = allLabels.find(function (l) {
                    return l.innerText.trim() === key;
                });

                if (matchedLabel) {
                    const input = matchedLabel.nextElementSibling
                        ? matchedLabel.nextElementSibling.querySelector('.dynamic-input')
                        : null;

                    if (input) {
                        input.value = data[key];
                        matched++;
                    }
                }
            }

            if (matched > 0) {
                btn.innerHTML = '<i class="bi bi-check2"></i> Pasted ' + matched + '!';
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-success');
            } else {
                btn.innerHTML = '<i class="bi bi-x-circle"></i> No matches';
            }

            setTimeout(function () {
                btn.innerHTML = '<i class="bi bi-clipboard-check"></i> Paste';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        }).catch(function () {
            alert('Unable to read clipboard. Please allow clipboard access.');
        });
    });
});
</script>
@endsection
