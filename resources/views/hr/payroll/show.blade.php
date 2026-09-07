@extends('layouts.app')

@section('content')
@php
    $payrollPeriod = $payrollPeriod ?? $period;
    $monthName = \Carbon\Carbon::create()->month((int)$payrollPeriod->period_month)->format('F');
    $status = strtoupper($payrollPeriod->status ?? 'DRAFT');
    $statusBadge = match($status) {
        'APPROVED', 'COMPLETED' => 'bg-success text-white',
        'PROCESSED', 'SUBMITTED' => 'bg-info text-dark',
        'PENDING' => 'bg-warning text-dark',
        default => 'bg-secondary text-white',
    };

    $records = $payrollPeriod->payrollRecords ?? collect();
    $totalGross = $records->sum('gross_earned');
    $totalAbsences = $records->sum('absences_amount');
    $totalTax = $records->sum('tax_withheld');
    $totalGsis = $records->sum('gsis_premium');
    $totalPhilhealth = $records->sum('philhealth_premium');
    $totalPagibig = $records->sum('pagibig_premium');
    $totalDeductions = $records->sum('total_deductions');
    $totalNet = $records->sum('net_amount');
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
            </div>
            <h4 class="fw-bold mb-1" style="color: #1A3E6F;">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i> Payroll for {{ $monthName }} {{ $payrollPeriod->period_year }}
            </h4>
            <div class="text-muted small d-flex flex-wrap align-items-center gap-3">
                <span><i class="bi bi-tag me-1 text-secondary"></i><strong>Fund Cluster:</strong> {{ $payrollPeriod->fund_cluster }}</span>
                <span><i class="bi bi-calendar3 me-1 text-secondary"></i><strong>Period:</strong> {{ $monthName }} {{ $payrollPeriod->period_year }}</span>
                @if($payrollPeriod->description)
                    <span><i class="bi bi-info-circle me-1 text-secondary"></i>{{ $payrollPeriod->description }}</span>
                @endif
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
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

    @if($errors->any())
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

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4" style="border-left-color: #1A3E6F !important;">
                <span class="text-muted small fw-bold text-uppercase">Total Employees</span>
                <h4 class="fw-bold mb-0 mt-1" style="color: #1A3E6F;">{{ $records->count() }}</h4>
                <span class="small text-muted">Active in period</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4 border-info">
                <span class="text-muted small fw-bold text-uppercase">Total Gross Earned</span>
                <h4 class="fw-bold mb-0 mt-1 text-info">₱{{ number_format($totalGross, 2) }}</h4>
                <span class="small text-muted">Gross payroll payout</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4 border-danger">
                <span class="text-muted small fw-bold text-uppercase">Total Deductions</span>
                <h4 class="fw-bold mb-0 mt-1 text-danger">₱{{ number_format($totalDeductions, 2) }}</h4>
                <span class="small text-muted">Statutory & other cuts</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                <span class="text-muted small fw-bold text-uppercase">Total Net Amount</span>
                <h4 class="fw-bold mb-0 mt-1 text-success">₱{{ number_format($totalNet, 2) }}</h4>
                <span class="small text-muted">Net disbursement</span>
            </div>
        </div>
    </div>

    {{-- Master Sheet Table Card --}}
    <div class="card shadow-sm border-0 rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <div>
                <h6 class="fw-bold mb-0" style="color: #1A3E6F;">
                    <i class="bi bi-table me-2"></i> Payroll Master Sheet Records
                </h6>
                <p class="text-muted small mb-0">Complete breakdown of earnings, statutory withholdings, and net salaries.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border px-3 py-2">
                    <i class="bi bi-people-fill me-1 text-secondary"></i> {{ $records->count() }} Records
                </span>
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
                            <th class="py-3 text-end border-bottom text-uppercase text-danger" style="min-width: 110px;">Absences</th>
                            <th class="py-3 text-end border-bottom text-uppercase text-danger" style="min-width: 110px;">Tax</th>
                            <th class="py-3 text-end border-bottom text-uppercase text-danger" style="min-width: 110px;">GSIS</th>
                            <th class="py-3 text-end border-bottom text-uppercase text-danger" style="min-width: 110px;">PhilHealth</th>
                            <th class="py-3 text-end border-bottom text-uppercase text-danger" style="min-width: 110px;">Pag-IBIG</th>
                            <th class="py-3 text-end border-bottom text-uppercase text-danger fw-bold" style="min-width: 140px;">Total Deductions</th>
                            <th class="py-3 pe-3 text-end border-bottom text-uppercase fw-bold text-primary" style="min-width: 140px; background-color: rgba(26, 62, 111, 0.05); color: #1A3E6F !important;">Net Amount</th>
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

                                if (!empty($user->employee_type)) {
                                    $employeeMeta = $user->employee_type;
                                }
                            }

                            $otherDeductionsList = is_array($record->other_deductions) ? $record->other_deductions : [];
                            $loansSum = array_sum($otherDeductionsList);
                        @endphp
                        <tr>
                            <td class="text-center ps-3 text-muted small fw-semibold">
                                {{ $index + 1 }}
                            </td>
                            <td class="ps-3 py-2">
                                <div class="fw-bold" style="color: #1A3E6F;">{{ $employeeName }}</div>
                                @if($employeeMeta)
                                    <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">{{ $employeeMeta }}</span>
                                @elseif($user && $user->username)
                                    <small class="text-muted">{{ $user->username }}</small>
                                @endif
                            </td>
                            <td class="text-end text-muted font-monospace">
                                ₱{{ number_format((float)$record->basic_rate, 2) }}
                            </td>
                            <td class="text-end fw-semibold font-monospace" style="color: #1A3E6F;">
                                ₱{{ number_format((float)$record->gross_earned, 2) }}
                            </td>
                            <td class="text-end text-danger font-monospace">
                                {{ (float)$record->absences_amount > 0 ? '₱' . number_format((float)$record->absences_amount, 2) : '—' }}
                            </td>
                            <td class="text-end text-danger font-monospace">
                                {{ (float)$record->tax_withheld > 0 ? '₱' . number_format((float)$record->tax_withheld, 2) : '—' }}
                            </td>
                            <td class="text-end text-danger font-monospace">
                                {{ (float)$record->gsis_premium > 0 ? '₱' . number_format((float)$record->gsis_premium, 2) : '—' }}
                            </td>
                            <td class="text-end text-danger font-monospace">
                                {{ (float)$record->philhealth_premium > 0 ? '₱' . number_format((float)$record->philhealth_premium, 2) : '—' }}
                            </td>
                            <td class="text-end text-danger font-monospace">
                                {{ (float)$record->pagibig_premium > 0 ? '₱' . number_format((float)$record->pagibig_premium, 2) : '—' }}
                            </td>
                            <td class="text-end text-danger fw-bold font-monospace bg-light bg-opacity-50">
                                <div>₱{{ number_format((float)$record->total_deductions, 2) }}</div>
                                @if($loansSum > 0)
                                    <div class="text-muted fw-normal" style="font-size: 0.68rem;">
                                        incl. ₱{{ number_format($loansSum, 2) }} loans
                                    </div>
                                @endif
                            </td>
                            <td class="pe-3 text-end fw-bold font-monospace" style="background-color: rgba(26, 62, 111, 0.05); color: #1A3E6F; font-size: 0.95rem;">
                                ₱{{ number_format((float)$record->net_amount, 2) }}
                            </td>
                            <td class="text-center py-2 pe-3">
                                <button type="button" 
                                        class="btn btn-sm text-white shadow-sm d-inline-flex align-items-center gap-1" 
                                        style="background-color: #1A3E6F; font-size: 0.78rem; padding: 0.3rem 0.75rem;"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deductionsModal{{ $record->id }}"
                                        title="Manage loans and variable deductions">
                                    <i class="bi bi-pencil-square"></i>
                                    <span>Manage Deductions</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-person-x text-muted display-4 d-block mb-3 opacity-50"></i>
                                    <h6 class="fw-bold text-secondary mb-1">No Payroll Records for this Period</h6>
                                    <p class="text-muted small mb-0">Payroll records have not been generated yet for {{ $monthName }} {{ $payrollPeriod->period_year }}.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($records->isNotEmpty())
                    <tfoot class="table-light fw-bold text-dark border-top-2">
                        <tr style="font-size: 0.85rem;">
                            <td colspan="2" class="ps-3 py-3 text-uppercase" style="color: #1A3E6F;">
                                Total ({{ $records->count() }} Employees)
                            </td>
                            <td class="text-end font-monospace text-muted py-3">—</td>
                            <td class="text-end font-monospace py-3" style="color: #1A3E6F;">
                                ₱{{ number_format($totalGross, 2) }}
                            </td>
                            <td class="text-end font-monospace text-danger py-3">
                                ₱{{ number_format($totalAbsences, 2) }}
                            </td>
                            <td class="text-end font-monospace text-danger py-3">
                                ₱{{ number_format($totalTax, 2) }}
                            </td>
                            <td class="text-end font-monospace text-danger py-3">
                                ₱{{ number_format($totalGsis, 2) }}
                            </td>
                            <td class="text-end font-monospace text-danger py-3">
                                ₱{{ number_format($totalPhilhealth, 2) }}
                            </td>
                            <td class="text-end font-monospace text-danger py-3">
                                ₱{{ number_format($totalPagibig, 2) }}
                            </td>
                            <td class="text-end font-monospace text-danger py-3">
                                ₱{{ number_format($totalDeductions, 2) }}
                            </td>
                            <td class="pe-3 text-end font-monospace py-3" style="background-color: rgba(26, 62, 111, 0.1); color: #1A3E6F; font-size: 1rem;">
                                ₱{{ number_format($totalNet, 2) }}
                            </td>
                            <td class="text-center font-monospace text-muted py-3 pe-3">—</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Modals for Managing Employee Deductions (Rendered outside table to prevent overflow clipping) --}}
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
        $gsisConso = isset($deductions['gsis_conso']) && (float)$deductions['gsis_conso'] > 0 ? number_format((float)$deductions['gsis_conso'], 2, '.', '') : '';
        $pagibigMpl = isset($deductions['pagibig_mpl']) && (float)$deductions['pagibig_mpl'] > 0 ? number_format((float)$deductions['pagibig_mpl'], 2, '.', '') : '';
        $landbankLoan = isset($deductions['landbank_loan']) && (float)$deductions['landbank_loan'] > 0 ? number_format((float)$deductions['landbank_loan'], 2, '.', '') : '';
        $cnhsMulticoop = isset($deductions['cnhs_multicoop']) && (float)$deductions['cnhs_multicoop'] > 0 ? number_format((float)$deductions['cnhs_multicoop'], 2, '.', '') : '';
    @endphp
    <div class="modal fade" id="deductionsModal{{ $record->id }}" tabindex="-1" aria-labelledby="deductionsModalLabel{{ $record->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('hr.payroll.update_record', $record->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom py-3" style="background-color: #f8fafc;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: rgba(26, 62, 111, 0.1); color: #1A3E6F;">
                                <i class="bi bi-wallet2 fs-5"></i>
                            </div>
                            <div>
                                <h6 class="modal-title fw-bold mb-0" id="deductionsModalLabel{{ $record->id }}" style="color: #1A3E6F;">
                                    Manage Deductions & Loans
                                </h6>
                                <div class="text-muted small">
                                    <strong>Employee:</strong> {{ $modalEmpName }}
                                    @if($modalUser && $modalUser->employee_type)
                                        <span class="badge bg-light text-secondary border ms-1">{{ $modalUser->employee_type }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        {{-- Financial Overview Ribbon --}}
                        <div class="row g-2 mb-4 p-3 rounded-3 bg-light border">
                            <div class="col-sm-3 col-6 text-center border-end">
                                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Basic Salary</span>
                                <div class="fw-bold font-monospace text-secondary">₱{{ number_format((float)$record->basic_rate, 2) }}</div>
                            </div>
                            <div class="col-sm-3 col-6 text-center border-end">
                                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Gross Earned</span>
                                <div class="fw-bold font-monospace" style="color: #1A3E6F;">₱{{ number_format((float)$record->gross_earned, 2) }}</div>
                            </div>
                            <div class="col-sm-3 col-6 text-center border-end">
                                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Mandatory Cuts</span>
                                <div class="fw-bold font-monospace text-danger">₱{{ number_format((float)($record->gsis_premium + $record->philhealth_premium + $record->pagibig_premium + $record->absences_amount + $record->tax_withheld), 2) }}</div>
                            </div>
                            <div class="col-sm-3 col-6 text-center">
                                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Current Net Pay</span>
                                <div class="fw-bold font-monospace text-success">₱{{ number_format((float)$record->net_amount, 2) }}</div>
                            </div>
                        </div>

                        <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-3">
                            <i class="bi bi-info-circle-fill me-2 flex-shrink-0 fs-6"></i>
                            <div>
                                Enter monthly loan deduction amounts below. Blank or empty inputs will automatically be converted to <strong>₱0.00</strong>. Totals and net pay will be recalculated immediately upon saving.
                            </div>
                        </div>

                        {{-- Custom Loan Input Fields --}}
                        <div class="row g-3">
                            {{-- GSIS Consolidated Loan --}}
                            <div class="col-md-6">
                                <label for="gsis_conso_{{ $record->id }}" class="form-label small fw-semibold text-secondary mb-1">
                                    <i class="bi bi-bank2 me-1 text-primary"></i> GSIS Consolidated Loan
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">₱</span>
                                    <input type="number" 
                                           step="0.01" 
                                           min="0" 
                                           class="form-control font-monospace" 
                                           id="gsis_conso_{{ $record->id }}" 
                                           name="other_deductions[gsis_conso]" 
                                           value="{{ $gsisConso }}" 
                                           placeholder="0.00">
                                </div>
                                <div class="form-text small text-muted" style="font-size: 0.75rem;">GSIS Conso-Loan monthly amortization</div>
                            </div>

                            {{-- Pag-IBIG Multi-Purpose Loan --}}
                            <div class="col-md-6">
                                <label for="pagibig_mpl_{{ $record->id }}" class="form-label small fw-semibold text-secondary mb-1">
                                    <i class="bi bi-houses me-1 text-primary"></i> Pag-IBIG Multi-Purpose Loan
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">₱</span>
                                    <input type="number" 
                                           step="0.01" 
                                           min="0" 
                                           class="form-control font-monospace" 
                                           id="pagibig_mpl_{{ $record->id }}" 
                                           name="other_deductions[pagibig_mpl]" 
                                           value="{{ $pagibigMpl }}" 
                                           placeholder="0.00">
                                </div>
                                <div class="form-text small text-muted" style="font-size: 0.75rem;">HDMF MPL / Calamity loan monthly amortization</div>
                            </div>

                            {{-- Landbank Salary Loan --}}
                            <div class="col-md-6">
                                <label for="landbank_loan_{{ $record->id }}" class="form-label small fw-semibold text-secondary mb-1">
                                    <i class="bi bi-cash-coin me-1 text-primary"></i> Landbank Salary Loan
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">₱</span>
                                    <input type="number" 
                                           step="0.01" 
                                           min="0" 
                                           class="form-control font-monospace" 
                                           id="landbank_loan_{{ $record->id }}" 
                                           name="other_deductions[landbank_loan]" 
                                           value="{{ $landbankLoan }}" 
                                           placeholder="0.00">
                                </div>
                                <div class="form-text small text-muted" style="font-size: 0.75rem;">Landbank (LBP) payroll loan deduction</div>
                            </div>

                            {{-- CNHS Multi-coop Loan --}}
                            <div class="col-md-6">
                                <label for="cnhs_multicoop_{{ $record->id }}" class="form-label small fw-semibold text-secondary mb-1">
                                    <i class="bi bi-people me-1 text-primary"></i> CNHS Multi-coop Loan
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">₱</span>
                                    <input type="number" 
                                           step="0.01" 
                                           min="0" 
                                           class="form-control font-monospace" 
                                           id="cnhs_multicoop_{{ $record->id }}" 
                                           name="other_deductions[cnhs_multicoop]" 
                                           value="{{ $cnhsMulticoop }}" 
                                           placeholder="0.00">
                                </div>
                                <div class="form-text small text-muted" style="font-size: 0.75rem;">School faculty cooperative loan deduction</div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-2 px-4 border-top">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-sm text-white px-4 shadow-sm" style="background-color: #1A3E6F;">
                            <i class="bi bi-check2-circle me-1"></i> Save Deductions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
