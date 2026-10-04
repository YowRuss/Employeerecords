@extends('layouts.app')

@section('content')
<style>
    .loan-filters .btn {
        color: #1A3E6F;
        background: #fff;
        border-color: #e2e8f0;
        font-weight: 600;
    }
    .loan-filters .btn:hover {
        background: #fef9c3;
        color: #1A3E6F;
        border-color: #facc15;
    }
    .loan-filters .btn.active {
        background: var(--accent-yellow, #fde047) !important;
        color: #1e293b !important;
        border-color: #facc15 !important;
        box-shadow: 0 2px 5px rgba(253, 224, 71, 0.45);
    }
    .loan-status-menu .dropdown-item.active {
        background-color: var(--accent-yellow, #fde047);
        color: #1e293b;
    }
</style>
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-7 mb-3 mb-md-0">
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('hr.payroll.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1">
                    <i class="bi bi-arrow-left me-1"></i> Back to Payroll
                </a>
                <span class="badge rounded-pill bg-accent px-3 py-1 fw-semibold" style="font-size: 0.8rem;">
                    DepEd HR Payroll
                </span>
            </div>
            <h4 class="text-header-blue fw-bold mb-0">
                <i class="bi bi-bank me-2 text-header-blue"></i> Loan Management & Amortization
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Track employee loans, monitor outstanding balances, and withhold the monthly amortization automatically on every payroll run.
            </p>
        </div>
        <div class="col-12 col-md-5 text-md-end d-flex flex-wrap justify-content-md-end gap-2">
            <a href="{{ route('payroll.remittances.index') }}" class="btn btn-outline-secondary fw-semibold shadow-sm px-3 py-2">
                <i class="bi bi-building-check me-1"></i> Remittances
            </a>
            <button type="button" class="btn btn-accent fw-bold shadow-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#addLoanModal">
                <i class="bi bi-plus-circle me-1"></i> Add New Loan
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
        <div class="d-flex align-items-center mb-1">
            <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
            <strong class="fw-bold">Please check the following form errors:</strong>
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
                <span class="text-muted small fw-bold text-uppercase">Total Active Loans</span>
                <h4 class="fw-bold mb-0 mt-1" style="color: #1A3E6F;">{{ $activeCount }}</h4>
                <span class="small text-muted">Currently deducting</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4 border-warning">
                <span class="text-muted small fw-bold text-uppercase">Total Principal</span>
                <h4 class="fw-bold mb-0 mt-1 text-warning">{{ number_format($totalPrincipal, 2) }}</h4>
                <span class="small text-muted">Active loan total</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4 border-info">
                <span class="text-muted small fw-bold text-uppercase">Monthly Withholding</span>
                <h4 class="fw-bold mb-0 mt-1 text-info">{{ number_format($totalMonthlyAmortization, 2) }}</h4>
                <span class="small text-muted">Total deducted per month</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                <span class="text-muted small fw-bold text-uppercase">Fully Paid</span>
                <h4 class="fw-bold mb-0 mt-1 text-success">{{ $paidCount }}</h4>
                <span class="small text-muted">Historical loans closed</span>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card shadow-sm rounded-3 bg-white">
        {{-- Card Header & Filter Toolbar --}}
        <div class="card-header bg-white border-bottom py-3">
            <form method="GET" action="{{ route('payroll.loans.index') }}" class="m-0">
                <div class="row g-2 align-items-center justify-content-between">
                    {{-- Search Input --}}
                    <div class="col-12 col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text"
                                   name="search"
                                   class="form-control border-start-0 ps-0"
                                   placeholder="Search employee or loan type..."
                                   value="{{ $currentSearch }}">
                            @if($currentSearch)
                                <a href="{{ route('payroll.loans.index', ['type' => $currentType, 'status' => $currentStatus]) }}" class="btn btn-outline-secondary border-start-0" title="Clear Search">
                                    <i class="bi bi-x"></i>
                                </a>
                            @endif
                            <button class="btn btn-accent px-3" type="submit">
                                Search
                            </button>
                        </div>
                    </div>

                    {{-- Agency & Status Filters --}}
                    <div class="col-12 col-md-8 text-md-end d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                        {{-- Agency Pill Group --}}
                        <div class="btn-group btn-group-sm loan-filters" role="group">
                            <a href="{{ route('payroll.loans.index', ['search' => $currentSearch, 'type' => 'all', 'status' => $currentStatus]) }}"
                               class="btn {{ $currentType === 'all' ? 'active fw-bold' : '' }}">
                                All Loans ({{ $typeCounts['all'] }})
                            </a>
                            @foreach($quickFilters as $agency)
                                <a href="{{ route('payroll.loans.index', ['search' => $currentSearch, 'type' => $agency, 'status' => $currentStatus]) }}"
                                   class="btn {{ $currentType === $agency ? 'active fw-bold' : '' }}">
                                    {{ $agency }} ({{ $typeCounts[$agency] }})
                                </a>
                            @endforeach
                        </div>

                        {{-- Status Dropdown --}}
                        @php
                            $statusLabel = in_array($currentStatus, ['Active', 'Paid', 'Suspended'], true) ? $currentStatus : 'All';
                        @endphp
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle fw-semibold" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-funnel me-1"></i> Status: {{ $statusLabel }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 loan-status-menu">
                                @foreach(['all' => 'All Statuses', 'Active' => 'Active', 'Paid' => 'Fully Paid', 'Suspended' => 'Suspended'] as $value => $label)
                                    <li>
                                        <a class="dropdown-item small {{ $currentStatus === $value ? 'active fw-bold' : '' }}"
                                           href="{{ route('payroll.loans.index', ['search' => $currentSearch, 'type' => $currentType, 'status' => $value]) }}">
                                            {{ $label }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Table Content --}}
        <div class="card-body p-0">
            @if($loans->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-bank text-muted" style="font-size: 3rem; opacity: 0.4;"></i>
                    <h5 class="fw-bold mt-3 text-secondary">No Loans Found</h5>
                    <p class="text-muted small mb-0">
                        @if($currentSearch || $currentType !== 'all' || $currentStatus !== 'all')
                            Try changing your search keywords or filter criteria.
                        @else
                            Record your first employee loan to start automatic payroll deductions.
                        @endif
                    </p>
                    @if($currentSearch || $currentType !== 'all' || $currentStatus !== 'all')
                        <a href="{{ route('payroll.loans.index') }}" class="btn btn-sm btn-outline-secondary mt-3">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reset Filters
                        </a>
                    @else
                        <button type="button" class="btn btn-sm btn-accent fw-bold shadow-sm mt-3 px-3" data-bs-toggle="modal" data-bs-target="#addLoanModal">
                            <i class="bi bi-plus-circle me-1"></i> Add New Loan
                        </button>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-muted small fw-bold text-uppercase py-3 ps-4 border-0">Employee</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 border-0">Loan Details</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 border-0">Financials</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 border-0">Running Balance</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 border-0">Status</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 border-0 text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loans as $loan)
                            @php
                                $employee = $loan->user;

                                $fullName = trim(($employee->last_name ?? 'Unknown') . ', ' . ($employee->first_name ?? ''), ', ');
                                if ($employee && !empty($employee->middle_name)) {
                                    $fullName .= ' ' . strtoupper(substr((string) $employee->middle_name, 0, 1)) . '.';
                                }
                                if ($employee && !empty($employee->suffix)) {
                                    $fullName .= ' ' . $employee->suffix;
                                }

                                $employeeNo = $employee?->pdsPersonalInfo?->agency_employee_no
                                    ?: 'EMP-' . str_pad((string) $loan->user_id, 4, '0', STR_PAD_LEFT);

                                $initials = strtoupper(substr((string) ($employee?->first_name ?: 'U'), 0, 1))
                                    . strtoupper(substr((string) ($employee?->last_name ?: ''), 0, 1));

                                $remainingMonths = $loan->remaining_months;

                                // Same compact badge treatment as the PERM chip on Employee Profiles.
                                $statusBadge = match($loan->status) {
                                    'Paid' => 'bg-success-subtle text-success-emphasis border border-success-subtle',
                                    'Suspended' => 'bg-light text-secondary border',
                                    default => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                };
                            @endphp
                            <tr>
                                {{-- Employee --}}
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="flex-shrink-0 text-white fw-bold shadow-sm"
                                             style="width: 35px; height: 35px; border-radius: 50%; background: #1A3E6F; color: white; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <div class="fw-bold" style="color: #1A3E6F;">{{ $fullName }}</div>
                                            <div class="text-muted small">
                                                <i class="bi bi-hash text-secondary"></i>{{ $employeeNo }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Loan Details --}}
                                <td class="py-3">
                                    <div class="fw-semibold text-dark">{{ $loan->loan_type }}</div>
                                    <div class="mt-1">
                                        <span class="badge bg-light text-dark border px-2 py-0" style="font-size: 0.7rem;">
                                            {{ $loan->agency }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Financials --}}
                                <td class="py-3">
                                    <div class="fw-semibold font-monospace text-dark">{{ number_format((float) $loan->principal_amount, 2) }}</div>
                                    <small class="text-muted font-monospace text-nowrap">{{ number_format((float) $loan->monthly_amortization, 2) }} / mo</small>
                                </td>

                                {{-- Running Balance --}}
                                <td class="py-3">
                                    <div class="fw-bold font-monospace" style="color: #1A3E6F;">
                                        {{ number_format((float) $loan->running_balance, 2) }}
                                    </div>
                                    @if($remainingMonths)
                                        <small class="text-muted">{{ $remainingMonths }} {{ Str::plural('month', $remainingMonths) }} left</small>
                                    @else
                                        <small class="text-success"><i class="bi bi-check-circle me-1"></i>Settled</small>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="py-3">
                                    <span class="badge {{ $statusBadge }} px-2 py-0" style="font-size: 0.7rem;">
                                        {{ $loan->status }}
                                    </span>
                                </td>

                                {{-- Action --}}
                                <td class="py-3 pe-4 text-end">
                                    <button type="button"
                                            class="btn btn-accent btn-sm px-3 shadow-sm text-nowrap"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editLoanModal{{ $loan->id }}">
                                        <i class="bi bi-file-earmark-text me-1"></i> Manage Loan
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Card Footer --}}
        <div class="card-footer bg-white border-top py-3 d-flex flex-column align-items-center gap-2">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100">
                <span class="text-muted small">
                    Showing <strong>{{ $loans->firstItem() ?? 0 }}</strong>–<strong>{{ $loans->lastItem() ?? 0 }}</strong> of <strong>{{ $loans->total() }}</strong> loans
                </span>
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1 text-secondary"></i> Running balances are reduced automatically each time a payroll period is finalized.
                </div>
            </div>
            @if($loans->hasPages())
            <div class="pagination-centered w-100">
                {{ $loans->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Add New Loan Modal --}}
<div class="modal fade" id="addLoanModal" tabindex="-1" aria-labelledby="addLoanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('payroll.loans.store') }}" method="POST" data-loan-form="create">
                @csrf
                <input type="hidden" name="form_context" value="create">
                <div class="modal-header border-0 bg-accent">
                    <div class="d-flex align-items-center gap-3 py-1">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-white" style="width: 42px; height: 42px;">
                            <i class="bi bi-plus-circle fs-5" style="color: #1A3E6F;"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0" id="addLoanModalLabel" style="color: #1A3E6F;">Add New Loan</h6>
                            <span style="font-size: 0.78rem; color: #1e293b;">Deductions begin on the next generated payroll</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="add_user_id" class="form-label fw-semibold small text-uppercase text-muted">
                            Employee <span class="text-danger">*</span>
                        </label>
                        <select name="user_id" id="add_user_id" class="form-select loan-employee-select"
                                data-placeholder="Search for an employee..." required>
                            <option value=""></option>
                            @foreach($employees as $employee)
                                @php
                                    $optionName = trim(($employee->last_name ?? '') . ', ' . ($employee->first_name ?? ''), ', ');
                                    $optionName = $optionName !== '' ? $optionName : 'Employee #' . $employee->id;
                                @endphp
                                <option value="{{ $employee->id }}" @selected(old('user_id') == $employee->id)>{{ $optionName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="add_loan_type" class="form-label fw-semibold small text-uppercase text-muted">
                            Loan Type <span class="text-danger">*</span>
                        </label>
                        <select name="loan_type" id="add_loan_type" class="form-select" required>
                            @foreach($loanTypes as $type)
                                <option value="{{ $type }}" @selected(old('loan_type') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="add_principal_amount" class="form-label fw-semibold small text-uppercase text-muted">
                                Principal Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                                                <input type="number" step="0.01" min="0.01" name="principal_amount" id="add_principal_amount"
                                       class="form-control font-monospace" value="{{ old('principal_amount') }}" placeholder="0.00"
                                       data-loan-principal required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="add_terms_months" class="form-label fw-semibold small text-uppercase text-muted">
                                Terms (Months to Pay)
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="1" max="600" name="terms_months" id="add_terms_months"
                                       class="form-control font-monospace" value="{{ old('terms_months') }}" placeholder="e.g. 24"
                                       data-loan-terms>
                                <span class="input-group-text bg-light text-muted">months</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 mb-3">
                        <label for="add_monthly_amortization" class="form-label fw-semibold small text-uppercase text-muted">
                            Monthly Amortization <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                                                        <input type="number" step="0.01" min="0.01" name="monthly_amortization" id="add_monthly_amortization"
                                   class="form-control font-monospace" value="{{ old('monthly_amortization') }}" placeholder="0.00"
                                   data-loan-amortization required>
                        </div>
                        <div class="form-text small">
                            Fills in automatically from the principal and terms. You can still type an exact amount to override it.
                        </div>
                    </div>

                    <div class="alert alert-info border-0 small mb-0 py-2 d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle mt-1"></i>
                        <span id="loan_summary_text" data-loan-summary>
                            The running balance starts at the full principal and is reduced each time a payroll period is finalized.
                        </span>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent fw-bold shadow-sm px-4">
                        <i class="bi bi-save me-1"></i> Save Loan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Manage Loan Modals --}}
@foreach($loans as $loan)
@php
    $modalEmployee = $loan->user;
    $modalName = trim(($modalEmployee->last_name ?? '') . ', ' . ($modalEmployee->first_name ?? ''), ', ');
    $modalName = $modalName !== '' ? $modalName : 'Employee #' . $loan->user_id;
@endphp
<div class="modal fade" id="editLoanModal{{ $loan->id }}" tabindex="-1" aria-labelledby="editLoanModalLabel{{ $loan->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('payroll.loans.update', $loan) }}" method="POST" data-loan-form="edit">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 bg-accent">
                    <div class="d-flex align-items-center gap-3 py-1">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-white" style="width: 42px; height: 42px;">
                            <i class="bi bi-file-earmark-text fs-5" style="color: #1A3E6F;"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0" id="editLoanModalLabel{{ $loan->id }}" style="color: #1A3E6F;">Manage Loan</h6>
                            <span style="font-size: 0.78rem; color: #1e293b;">{{ $modalName }} &bull; {{ $loan->loan_type }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-muted">
                            Employee <span class="text-danger">*</span>
                        </label>
                        <select name="user_id" class="form-select loan-employee-select"
                                data-placeholder="Search for an employee..." required>
                            @foreach($employees as $employee)
                                @php
                                    $optionName = trim(($employee->last_name ?? '') . ', ' . ($employee->first_name ?? ''), ', ');
                                    $optionName = $optionName !== '' ? $optionName : 'Employee #' . $employee->id;
                                @endphp
                                <option value="{{ $employee->id }}" @selected($loan->user_id == $employee->id)>{{ $optionName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-muted">
                            Loan Type <span class="text-danger">*</span>
                        </label>
                        <select name="loan_type" class="form-select" required>
                            @foreach($loanTypes as $type)
                                <option value="{{ $type }}" @selected($loan->loan_type === $type)>{{ $type }}</option>
                            @endforeach
                            @unless(in_array($loan->loan_type, $loanTypes, true))
                                <option value="{{ $loan->loan_type }}" selected>{{ $loan->loan_type }}</option>
                            @endunless
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-uppercase text-muted">
                                Principal Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                                                <input type="number" step="0.01" min="0.01" name="principal_amount"
                                       class="form-control font-monospace" value="{{ (float) $loan->principal_amount }}"
                                       data-loan-principal required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-uppercase text-muted">
                                Terms (Months to Pay)
                            </label>
                            <div class="input-group">
                                <input type="number" step="1" min="1" max="600" name="terms_months"
                                       class="form-control font-monospace" placeholder="Recalculate"
                                       data-loan-terms>
                                <span class="input-group-text bg-light text-muted">months</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 mb-3">
                        <label class="form-label fw-semibold small text-uppercase text-muted">
                            Monthly Amortization <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                                                        <input type="number" step="0.01" min="0.01" name="monthly_amortization"
                                   class="form-control font-monospace" value="{{ (float) $loan->monthly_amortization }}"
                                   data-loan-amortization required>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-uppercase text-muted">
                                Running Balance <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                                                <input type="number" step="0.01" min="0" name="running_balance"
                                       class="form-control font-monospace" value="{{ (float) $loan->running_balance }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Status</label>
                            <select name="status" class="form-select">
                                <option value="Active" @selected($loan->status === 'Active')>Active</option>
                                <option value="Suspended" @selected($loan->status === 'Suspended')>Suspended</option>
                                <option value="Paid" @selected($loan->status === 'Paid')>Paid</option>
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 small mt-3 mb-0 py-2 d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle mt-1"></i>
                        <span data-loan-summary>
                            @if($loan->remaining_months)
                                At {{ number_format((float) $loan->monthly_amortization, 2) }} per month, this loan clears in
                                <strong>{{ $loan->remaining_months }} {{ Str::plural('month', $loan->remaining_months) }}</strong>.
                            @else
                                This loan has no outstanding balance remaining.
                            @endif
                        </span>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-danger fw-semibold px-3"
                            onclick="document.getElementById('deleteLoanForm{{ $loan->id }}').requestSubmit()">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary fw-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-accent fw-bold shadow-sm px-4">
                            <i class="bi bi-save me-1"></i> Update Loan
                        </button>
                    </div>
                </div>
            </form>

            <form id="deleteLoanForm{{ $loan->id }}" action="{{ route('payroll.loans.destroy', $loan) }}" method="POST" class="d-none"
                  onsubmit="return confirm('Delete this {{ $loan->loan_type }} loan for {{ $modalName }}? This cannot be undone.');">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
    /**
     * Turn every employee picker into a searchable dropdown. jQuery and Select2 are
     * loaded after this section, so initialization waits for the window load event.
     * dropdownParent keeps the Select2 search box focusable inside its modal.
     */
    function initLoanEmployeeSelects() {
        if (typeof $ === 'undefined' || !$.fn.select2) {
            return;
        }

        $('.loan-employee-select').each(function () {
            const $select = $(this);

            if ($select.hasClass('select2-hidden-accessible')) {
                return;
            }

            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: $select.data('placeholder'),
                dropdownParent: $select.closest('.modal'),
            });
        });
    }

    initLoanEmployeeSelects();
    window.addEventListener('load', initLoanEmployeeSelects);

    /**
     * Derive the monthly amortization from the principal and terms, and restate
     * the deduction schedule in plain language. Applies to the add form and every
     * manage-loan form on the page.
     */
    document.addEventListener('DOMContentLoaded', function () {
        const peso = new Intl.NumberFormat('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        document.querySelectorAll('[data-loan-form]').forEach(function (form) {
            const principalInput = form.querySelector('[data-loan-principal]');
            const termsInput = form.querySelector('[data-loan-terms]');
            const amortizationInput = form.querySelector('[data-loan-amortization]');
            const summaryText = form.querySelector('[data-loan-summary]');

            if (!principalInput || !termsInput || !amortizationInput || !summaryText) {
                return;
            }

            const defaultSummary = summaryText.innerHTML;
            // Blanking the amortization is only safe while creating; on an existing
            // loan it would wipe the stored figure the moment terms are cleared.
            const clearsWhenIncomplete = form.dataset.loanForm === 'create';

            function calculateAmortization() {
                const principal = parseFloat(principalInput.value) || 0;
                const terms = parseInt(termsInput.value, 10) || 0;

                if (principal > 0 && terms > 0) {
                    const monthly = (principal / terms).toFixed(2);
                    amortizationInput.value = monthly;
                    summaryText.innerHTML = `This loan will be automatically deducted at <strong>${peso.format(monthly)}</strong> per month for <strong>${terms} ${terms === 1 ? 'month' : 'months'}</strong>.`;
                } else {
                    if (clearsWhenIncomplete) {
                        amortizationInput.value = '';
                    }
                    summaryText.innerHTML = defaultSummary;
                }
            }

            principalInput.addEventListener('input', calculateAmortization);
            termsInput.addEventListener('input', calculateAmortization);

            // Restore the summary when the modal reopens after a validation failure.
            if (termsInput.value) {
                calculateAmortization();
            }
        });
    });

    /**
     * Re-open the Add Loan modal when validation fails so the user keeps their input.
     */
    @if($errors->any() && old('form_context') === 'create')
        new bootstrap.Modal(document.getElementById('addLoanModal')).show();
    @endif
</script>
@endsection
