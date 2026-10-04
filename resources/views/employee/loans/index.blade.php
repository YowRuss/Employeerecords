@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <div>
            <h4 class="text-header-blue fw-bold m-0">
                <i class="bi bi-bank me-2 text-header-blue"></i> My Loans
            </h4>
            <p class="text-muted small mb-0 mt-1">Track your active loan balances and deductions.</p>
        </div>
    </div>

    <div class="card shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 py-3 px-4">
            <i class="bi bi-table" style="color: #1A3E6F;"></i>
            <span class="fw-semibold" style="color: #1A3E6F;">Loan History</span>
            <span class="badge bg-accent ms-auto">{{ $loans->count() }} {{ Str::plural('loan', $loans->count()) }}</span>
        </div>
        <div class="card-body p-0">
            @if($loans->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-2 mb-0">No active or paid loans on file.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background-color: #f8fafc;">
                            <tr class="text-uppercase small text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <th class="ps-4 py-3">#</th>
                                <th class="py-3">Loan Type</th>
                                <th class="text-end py-3">Principal</th>
                                <th class="text-end py-3">Monthly Amortization</th>
                                <th class="text-end py-3">Running Balance</th>
                                <th class="text-center py-3 pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loans as $index => $loan)
                                <tr>
                                    <td class="ps-4 text-muted small fw-semibold">{{ $index + 1 }}</td>
                                    <td class="fw-semibold" style="color: #1A3E6F;">{{ $loan->loan_type }}</td>
                                    <td class="text-end font-monospace" style="color: #1A3E6F;">{{ number_format((float) $loan->principal_amount, 2) }}</td>
                                    <td class="text-end font-monospace text-danger">{{ number_format((float) $loan->monthly_amortization, 2) }}</td>
                                    <td class="text-end font-monospace fw-bold" style="color: #1A3E6F;">{{ number_format((float) $loan->running_balance, 2) }}</td>
                                    <td class="text-center pe-4">
                                        <span class="badge {{ $loan->status === 'Paid' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }} px-2 py-0" style="font-size: 0.7rem;">
                                            {{ $loan->status }}
                                        </span>
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
