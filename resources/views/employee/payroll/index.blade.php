@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <div>
            <h4 class="text-header-blue fw-bold m-0">
                <i class="bi bi-receipt me-2 text-header-blue"></i> My Payslips
            </h4>
            <p class="text-muted small mb-0 mt-1">Your historical payroll records and digital payslips.</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Payroll Table --}}
    <div class="card shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 py-3 px-4">
            <i class="bi bi-table" style="color: #1A3E6F;"></i>
            <span class="fw-semibold" style="color: #1A3E6F;">Payroll History</span>
            <span class="badge bg-accent ms-auto">{{ $records->count() }} {{ Str::plural('record', $records->count()) }}</span>
        </div>

        <div class="card-body p-0">
            @if($records->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-2 mb-0">No payroll records found yet.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background-color: #f8fafc;">
                            <tr class="text-uppercase small text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <th class="ps-4 py-3">#</th>
                                <th class="py-3">Period</th>
                                <th class="text-end py-3">Gross Pay</th>
                                <th class="text-end py-3">Total Deductions</th>
                                <th class="text-end py-3">Net Take Home</th>
                                <th class="text-center py-3 pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $index => $record)
                                @php
                                    $period = $record->payrollPeriod;
                                    $periodLabel = $period
                                        ? \Carbon\Carbon::create()->month((int) $period->period_month)->format('F') . ' ' . $period->period_year
                                        : 'Unknown Period';
                                @endphp
                                <tr>
                                    <td class="ps-4 text-muted small fw-semibold">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="fw-semibold" style="color: #1A3E6F;">{{ $periodLabel }}</span>
                                    </td>
                                    <td class="text-end font-monospace">
                                        {{ number_format((float) $record->gross_earned, 2) }}
                                    </td>
                                    <td class="text-end font-monospace text-danger">
                                        {{ number_format((float) $record->total_deductions, 2) }}
                                    </td>
                                    <td class="text-end font-monospace fw-bold" style="color: #1A3E6F;">
                                        {{ number_format((float) $record->net_amount, 2) }}
                                    </td>
                                    <td class="text-center pe-4">
                                        <a href="{{ route('employee.payroll.show', $record->id) }}"
                                           class="btn btn-sm btn-accent fw-bold shadow-sm text-nowrap">
                                            <i class="bi bi-eye me-1"></i> View Payslip
                                        </a>
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
@endsection
