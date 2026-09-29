@extends('layouts.app')

@section('content')
@php
    $period = $record->payrollPeriod;
    $monthName = $period ? \Carbon\Carbon::create()->month((int) $period->period_month)->format('F') : 'Unknown';
    $periodYear = $period->period_year ?? '';
    $otherDeductions = is_array($record->other_deductions) ? $record->other_deductions : [];

    // Use the dynamic deduction dictionary from the controller, falling back to an empty array
    $loanLabels = $deductionDictionary ?? [];
@endphp

<div class="container-fluid py-4">
    {{-- Top Navigation --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <div>
            <a href="{{ route('employee.payroll.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 mb-2">
                <i class="bi bi-arrow-left me-1"></i> Back to Payslips
            </a>
            <h4 class="text-header-blue fw-bold m-0">
                <i class="bi bi-file-earmark-text me-2 text-header-blue"></i> Payslip for {{ $monthName }} {{ $periodYear }}
            </h4>
        </div>
        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Payslip
        </button>
    </div>

    {{-- Payslip Card --}}
    <div class="card shadow-sm border-0">
        {{-- Header --}}
        <div class="card-header bg-white border-bottom py-4 px-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                <img src="{{ asset('build/assets/images/logo.png') }}" alt="School Logo"
                     class="rounded-circle border shadow-sm" style="width: 60px; height: 60px; object-fit: contain;">
                <div>
                    <h5 class="fw-bold mb-0" style="color: #1A3E6F;">Cagayan National High School — JHS</h5>
                    <p class="text-muted small mb-0">Department of Education &bull; Schools Division of Tuguegarao City</p>
                    <p class="text-muted small mb-0">
                        <strong>Fund Cluster:</strong> {{ $period->fund_cluster ?? 'N/A' }}
                        &bull; <strong>Period:</strong> {{ $monthName }} {{ $periodYear }}
                    </p>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                {{-- LEFT COLUMN — Earnings --}}
                <div class="col-md-6">
                    <div class="card border h-100">
                        <div class="card-header bg-light border-bottom py-2">
                            <h6 class="fw-bold mb-0" style="color: #1A3E6F;">
                                <i class="bi bi-cash-coin me-1"></i> Earnings
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <td class="ps-3 py-2 text-muted">Basic Rate (Monthly Salary)</td>
                                        <td class="text-end pe-3 py-2 font-monospace fw-semibold">
                                            ₱{{ number_format((float) $record->basic_rate, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3 py-2 text-muted">PERA Allowance</td>
                                        <td class="text-end pe-3 py-2 font-monospace fw-semibold">
                                            ₱{{ number_format((float) $record->pera_amount, 2) }}
                                        </td>
                                    </tr>

                                    {{-- Additional Allowances / Incomes --}}
                                    @if($record->payrollIncomes && $record->payrollIncomes->isNotEmpty())
                                        @foreach($record->payrollIncomes as $payrollIncome)
                                            @if((float) $payrollIncome->amount > 0)
                                            <tr>
                                                <td class="ps-3 py-2 text-muted">
                                                    <i class="bi bi-wallet2 me-1 text-success" style="font-size: 0.7rem;"></i>
                                                    {{ $payrollIncome->incomeType->name ?? 'Allowance' }}
                                                </td>
                                                <td class="text-end pe-3 py-2 font-monospace fw-semibold text-success">
                                                    ₱{{ number_format((float) $payrollIncome->amount, 2) }}
                                                </td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr class="border-top">
                                        <td class="ps-3 py-2 fw-bold" style="color: #1A3E6F;">Gross Earned</td>
                                        <td class="text-end pe-3 py-2 font-monospace fw-bold" style="color: #1A3E6F;">
                                            ₱{{ number_format((float) $record->gross_earned, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN — Deductions --}}
                <div class="col-md-6">
                    <div class="card border h-100">
                        <div class="card-header bg-light border-bottom py-2">
                            <h6 class="fw-bold mb-0 text-danger">
                                <i class="bi bi-dash-circle me-1"></i> Deductions
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    {{-- Absence / LWOP --}}
                                    <tr>
                                        <td class="ps-3 py-2 text-muted">Absences (LWOP)</td>
                                        <td class="text-end pe-3 py-2 font-monospace text-danger">
                                            {{ (float) $record->absences_amount > 0 ? '₱' . number_format((float) $record->absences_amount, 2) : '—' }}
                                        </td>
                                    </tr>

                                    {{-- Tax --}}
                                    <tr>
                                        <td class="ps-3 py-2 text-muted">Withholding Tax</td>
                                        <td class="text-end pe-3 py-2 font-monospace text-danger">
                                            {{ (float) $record->tax_withheld > 0 ? '₱' . number_format((float) $record->tax_withheld, 2) : '—' }}
                                        </td>
                                    </tr>

                                    {{-- GSIS --}}
                                    <tr>
                                        <td class="ps-3 py-2 text-muted">GSIS Premium</td>
                                        <td class="text-end pe-3 py-2 font-monospace text-danger">
                                            {{ (float) $record->gsis_premium > 0 ? '₱' . number_format((float) $record->gsis_premium, 2) : '—' }}
                                        </td>
                                    </tr>

                                    {{-- PhilHealth --}}
                                    <tr>
                                        <td class="ps-3 py-2 text-muted">PhilHealth Premium</td>
                                        <td class="text-end pe-3 py-2 font-monospace text-danger">
                                            {{ (float) $record->philhealth_premium > 0 ? '₱' . number_format((float) $record->philhealth_premium, 2) : '—' }}
                                        </td>
                                    </tr>

                                    {{-- Pag-IBIG --}}
                                    <tr>
                                        <td class="ps-3 py-2 text-muted">Pag-IBIG Premium</td>
                                        <td class="text-end pe-3 py-2 font-monospace text-danger">
                                            {{ (float) $record->pagibig_premium > 0 ? '₱' . number_format((float) $record->pagibig_premium, 2) : '—' }}
                                        </td>
                                    </tr>

                                    {{-- Other Deductions (Loans from JSON) --}}
                                    @foreach($otherDeductions as $key => $amount)
                                        @if((float) $amount > 0)
                                            <tr>
                                                <td class="ps-3 py-2 text-muted">
                                                    <i class="bi bi-tag-fill me-1 text-secondary" style="font-size: 0.7rem;"></i>
                                                    {{ $loanLabels[$key] ?? ucwords(str_replace('_', ' ', $key)) }}
                                                </td>
                                                <td class="text-end pe-3 py-2 font-monospace text-danger">
                                                    ₱{{ number_format((float) $amount, 2) }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="border-top">
                                        <td class="ps-3 py-2 fw-bold text-danger">Total Deductions</td>
                                        <td class="text-end pe-3 py-2 font-monospace fw-bold text-danger">
                                            ₱{{ number_format((float) $record->total_deductions, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- NET PAY FOOTER --}}
            <div class="mt-4 p-4 rounded-3 text-center" style="background: linear-gradient(135deg, #1A3E6F 0%, #2A5298 100%);">
                <p class="text-white-50 small mb-1 text-uppercase fw-semibold" style="letter-spacing: 1px;">Net Take Home Pay</p>
                <h2 class="text-white fw-bold mb-0" style="font-size: 2.2rem; letter-spacing: -0.5px;">
                    ₱{{ number_format((float) $record->net_amount, 2) }}
                </h2>
            </div>
        </div>
    </div>
</div>
@endsection
