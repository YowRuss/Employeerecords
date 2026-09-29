@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-6 mb-3 mb-md-0">
            <h4 class="text-header-blue fw-bold mb-0">
                <i class="bi bi-file-earmark-ruled me-2 text-header-blue"></i> BIR Form 2316 Generator
            </h4>
            <p class="text-muted small mb-0">Generate year-end Certificate of Compensation Payment / Tax Withheld for employees.</p>
        </div>
        <div class="col-12 col-md-6 text-md-end">
            {{-- Year Selector --}}
            <form method="GET" action="{{ route('hr.bir2316.index') }}" class="d-inline-flex align-items-center gap-2">
                <label for="yearSelect" class="fw-semibold text-muted small text-nowrap mb-0">Tax Year:</label>
                <select name="year" id="yearSelect" class="form-select form-select-sm shadow-sm border"
                        style="width: 120px; border-color: #1A3E6F;" onchange="this.form.submit()">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </div>

    {{-- Flash Messages --}}
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

    {{-- Employees Table --}}
    <div class="card shadow-sm border-0 rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill" style="background-color: rgba(26, 62, 111, 0.1); color: #1A3E6F; font-size: 0.85rem;">
                    {{ $employees->total() }} {{ Str::plural('Employee', $employees->total()) }}
                </span>
                <span class="text-muted small">Active employees for tax year <strong>{{ $year }}</strong></span>
            </div>
        </div>

        <div class="card-body p-0">
            @if($employees->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-people fs-1 text-muted"></i>
                    <p class="text-muted mt-2 mb-0">No active employees found.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-uppercase small text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <th class="ps-4 py-3" style="width: 50px;">#</th>
                                <th class="py-3">Employee Name</th>
                                <th class="py-3">Position</th>
                                <th class="py-3">TIN</th>
                                <th class="text-center py-3 pe-4" style="width: 180px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $index => $emp)
                                @php
                                    $empName = trim(($emp->last_name ?? '') . ', ' . ($emp->first_name ?? ''));
                                    if (!empty($emp->middle_name)) {
                                        $empName .= ' ' . strtoupper(substr($emp->middle_name, 0, 1)) . '.';
                                    }
                                    if (!empty($emp->suffix)) {
                                        $empName .= ' ' . $emp->suffix;
                                    }
                                    $position = $emp->position->position_name ?? $emp->position->name ?? '—';
                                    $tin = $tins[$emp->id] ?? '—';
                                @endphp
                                <tr>
                                    <td class="ps-4 text-muted small fw-semibold">{{ ($employees->firstItem() ?? 1) + $index }}</td>
                                    <td>
                                        <span class="fw-semibold" style="color: #1A3E6F;">{{ $empName }}</span>
                                    </td>
                                    <td class="text-muted small">{{ $position }}</td>
                                    <td>
                                        @if($tin !== '—')
                                            <code class="text-dark">{{ $tin }}</code>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <a href="{{ route('hr.bir2316.generate', ['user_id' => $emp->id, 'year' => $year]) }}"
                                           class="btn btn-sm fw-semibold text-white rounded-pill px-3 shadow-sm"
                                           style="background-color: #1A3E6F;"
                                           target="_blank">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> Generate 2316
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($employees->hasPages())
                <div class="border-top bg-white py-3 pagination-centered">
                    {{ $employees->links('pagination::bootstrap-5') }}
                </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Help Card --}}
    <div class="card shadow-sm border-0 rounded-3 mt-4" style="border-left: 4px solid #1A3E6F !important;">
        <div class="card-body py-3 px-4">
            <div class="d-flex align-items-start gap-3">
                <i class="bi bi-info-circle-fill fs-5" style="color: #1A3E6F; margin-top: 2px;"></i>
                <div>
                    <p class="fw-semibold mb-1" style="color: #1A3E6F;">How this works</p>
                    <ul class="text-muted small mb-0 ps-3">
                        <li>The system aggregates all <strong>approved</strong> payroll periods for the selected tax year.</li>
                        <li>Non-Taxable compensation includes GSIS, PhilHealth, Pag-IBIG premiums, and PERA allowance.</li>
                        <li>Employee demographic data (TIN, address) is pulled from their Personal Data Sheet (PDS).</li>
                        <li>The generated PDF can be printed directly or saved for filing.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
