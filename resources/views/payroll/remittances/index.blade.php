@extends('layouts.app')

@section('content')
@php
    $monthName = \Carbon\Carbon::create()->month($selectedMonth)->format('F');
    $totalRemittance = $results ? (float) $results->sum('amount_withheld') : 0.0;
@endphp

<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-7 mb-3 mb-md-0">
            <h4 class="text-header-blue fw-bold mb-0">
                <i class="bi bi-building-check me-2 text-header-blue"></i> Statutory Remittances
            </h4>
            <p class="text-muted small mb-0">Extract finalized withholdings per agency and export them as an upload-ready file.</p>
        </div>
        <div class="col-12 col-md-5 text-md-end">
            <a href="{{ route('hr.payroll.index') }}" class="btn btn-outline-secondary fw-semibold shadow-sm px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Back to Payroll
            </a>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-1 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="card shadow-sm rounded-3 bg-white mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <span class="fw-bold" style="color: #1A3E6F;"><i class="bi bi-funnel-fill me-2"></i>Report Filters</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('payroll.remittances.report') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="period_month" class="form-label fw-bold small text-muted text-uppercase">
                        Month <span class="text-danger">*</span>
                    </label>
                    <select name="period_month" id="period_month" class="form-select" required>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" @selected($selectedMonth === $m)>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 col-lg-2">
                    <label for="period_year" class="form-label fw-bold small text-muted text-uppercase">
                        Year <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="period_year" id="period_year" class="form-control"
                           value="{{ $selectedYear }}" min="2000" max="2100" required>
                </div>

                <div class="col-12 col-md-4 col-lg-3">
                    <label for="agency" class="form-label fw-bold small text-muted text-uppercase">
                        Agency <span class="text-danger">*</span>
                    </label>
                    <select name="agency" id="agency" class="form-select" required>
                        @foreach($agencies as $agency)
                            <option value="{{ $agency->value }}" @selected($selectedAgency === $agency)>
                                {{ $agency->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-lg-4 d-flex gap-2">
                    <button type="submit" class="btn btn-accent fw-bold shadow-sm px-4 py-2">
                        <i class="bi bi-play-circle me-1"></i> Generate Report
                    </button>
                    @if($results && $selectedAgency && $results->isNotEmpty())
                        <a href="{{ route('payroll.remittances.export', ['period_month' => $selectedMonth, 'period_year' => $selectedYear, 'agency' => $selectedAgency->value]) }}"
                           class="btn btn-outline-success fw-semibold shadow-sm px-3 py-2">
                            <i class="bi bi-filetype-csv me-1"></i> Export to CSV
                        </a>
                    @endif
                </div>
            </form>
            <div class="form-text small mt-3 mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Only <strong>finalized</strong> payroll periods are included. Employees with no withholding for the selected agency are omitted.
            </div>
        </div>
    </div>

    @if($results !== null && $selectedAgency)
        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4" style="border-left-color: #1A3E6F !important;">
                    <span class="text-muted small fw-bold text-uppercase">Reporting Agency</span>
                    <h5 class="fw-bold mb-0 mt-1" style="color: #1A3E6F;">{{ $selectedAgency->label() }}</h5>
                    <span class="small text-muted">{{ $monthName }} {{ $selectedYear }}</span>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4 border-info">
                    <span class="text-muted small fw-bold text-uppercase">Employees Covered</span>
                    <h5 class="fw-bold mb-0 mt-1 text-info">{{ $results->count() }}</h5>
                    <span class="small text-muted">With a withholding on record</span>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                    <span class="text-muted small fw-bold text-uppercase">Total Remittance</span>
                    <h5 class="fw-bold mb-0 mt-1 text-success">{{ number_format($totalRemittance, 2) }}</h5>
                    <span class="small text-muted">Due to {{ $selectedAgency->label() }}</span>
                </div>
            </div>
        </div>

        {{-- Report Table --}}
        <div class="card shadow-sm rounded-3 bg-white">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge border {{ $selectedAgency->badgeClass() }} px-2 py-1 fw-semibold" style="font-size: 0.8rem;">
                        {{ $selectedAgency->label() }}
                    </span>
                    <span class="fw-bold" style="color: #1A3E6F;">Remittance Report — {{ $monthName }} {{ $selectedYear }}</span>
                </div>
                @if($results->isNotEmpty())
                    <div class="input-group" style="max-width: 280px;">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="remittanceSearch" class="form-control border-start-0 ps-0" placeholder="Search employee..." autocomplete="off">
                    </div>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="remittanceTable">
                        <thead class="bg-light">
                            <tr style="font-size: 0.8rem; letter-spacing: 0.5px;">
                                <th class="text-muted fw-bold text-uppercase py-3 ps-4 border-0" style="width: 50px;">#</th>
                                <th class="text-muted fw-bold text-uppercase py-3 border-0" style="min-width: 130px;">Employee ID</th>
                                <th class="text-muted fw-bold text-uppercase py-3 border-0" style="min-width: 150px;">{{ $selectedAgency->identifierLabel() }}</th>
                                <th class="text-muted fw-bold text-uppercase py-3 border-0" style="min-width: 220px;">Employee Name</th>
                                <th class="text-muted fw-bold text-uppercase py-3 border-0" style="min-width: 180px;">Position</th>
                                <th class="text-muted fw-bold text-uppercase py-3 pe-4 border-0 text-end" style="min-width: 150px;">Amount Withheld</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($results as $index => $row)
                            @php
                                $employeeName = trim(($row->last_name ?? '').', '.($row->first_name ?? ''), ', ');
                                if (!empty($row->middle_name)) {
                                    $employeeName .= ' ' . strtoupper(substr($row->middle_name, 0, 1)) . '.';
                                }
                                if (!empty($row->suffix)) {
                                    $employeeName .= ' ' . $row->suffix;
                                }
                                $employeeName = $employeeName !== '' ? $employeeName : 'Employee #' . $row->user_id;
                            @endphp
                            <tr class="remittance-row" data-search="{{ strtolower($employeeName.' '.$row->employee_no.' '.$row->agency_identifier) }}">
                                <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                <td class="font-monospace small">
                                    {{ $row->employee_no ?: '—' }}
                                </td>
                                <td class="font-monospace small">
                                    @if($row->agency_identifier)
                                        {{ $row->agency_identifier }}
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 0.7rem;">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Missing
                                        </span>
                                    @endif
                                </td>
                                <td class="fw-semibold" style="color: #1A3E6F;">{{ $employeeName }}</td>
                                <td class="text-secondary">{{ $row->position_name ?: '—' }}</td>
                                <td class="pe-4 text-end fw-semibold font-monospace">{{ number_format((float) $row->amount_withheld, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    <h6 class="fw-bold text-secondary mb-1">No {{ $selectedAgency->label() }} withholdings for {{ $monthName }} {{ $selectedYear }}</h6>
                                    <p class="small mb-0">Check that the payroll period for this month exists and has been finalized.</p>
                                </td>
                            </tr>
                            @endforelse
                            <tr id="noRemittanceResults" class="d-none">
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-search fs-1 d-block mb-2 opacity-50"></i>
                                    No employees match your search.
                                </td>
                            </tr>
                        </tbody>
                        @if($results->isNotEmpty())
                        <tfoot class="table-light fw-bold border-top-2">
                            <tr>
                                <td colspan="5" class="ps-4 py-3 text-uppercase" style="color: #1A3E6F;">
                                    Total Remittance to {{ $selectedAgency->label() }} ({{ $results->count() }} {{ Str::plural('Employee', $results->count()) }})
                                </td>
                                <td class="pe-4 py-3 text-end font-monospace" style="color: #1A3E6F; font-size: 1rem;">
                                    {{ number_format($totalRemittance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm rounded-3 bg-white">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-clipboard-data fs-1 d-block mb-3 opacity-50"></i>
                <h6 class="fw-bold text-secondary mb-1">No report generated yet</h6>
                <p class="small mb-0">Choose a month, year, and agency above, then select <strong>Generate Report</strong>.</p>
            </div>
        </div>
    @endif
</div>

@if($results !== null && $results->isNotEmpty())
<script>
    /**
     * Filter the generated report rows by employee name or ID number.
     */
    (function () {
        const input = document.getElementById('remittanceSearch');
        const emptyRow = document.getElementById('noRemittanceResults');

        input.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            let visible = 0;

            document.querySelectorAll('.remittance-row').forEach(row => {
                const show = (row.dataset.search || '').includes(query);
                row.classList.toggle('d-none', !show);
                if (show) visible++;
            });

            emptyRow.classList.toggle('d-none', visible > 0);
        });
    })();
</script>
@endif
@endsection
