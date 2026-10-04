@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-6 mb-3 mb-md-0">
            <h4 class="text-header-blue fw-bold mb-0">
                <i class="bi bi-cash-stack me-2 text-header-blue"></i> Payroll Management
            </h4>
            <p class="text-muted small mb-0">Manage payroll periods, generate records, and review master sheets.</p>
        </div>
        <div class="col-12 col-md-6 text-md-end d-flex flex-wrap justify-content-md-end gap-2">
            <a href="{{ route('hr.settings.deductions.index') }}" class="btn btn-outline-secondary fw-semibold shadow-sm px-3 py-2">
                <i class="bi bi-sliders me-1"></i> Deduction Settings
            </a>
            <button type="button" class="btn btn-accent fw-bold shadow-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#newPayrollPeriodModal">
                <i class="bi bi-plus-circle me-1"></i> Generate New Payroll Period
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

    {{-- Payroll Periods Table Card --}}
    <div class="card shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill bg-accent" style="font-size: 0.85rem;">
                    {{ count($periods) }} {{ Str::plural('Period', count($periods)) }}
                </span>
                <span class="text-muted small">Recorded in the system</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-muted small fw-bold text-uppercase py-3 ps-4 border-0">Fund Cluster</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0">Payroll Type</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0">Period (Month / Year)</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0">Description</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0 text-center">Status</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0 text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $period)
                        @php
                            $monthName = \Carbon\Carbon::create()->month((int)$period->period_month)->format('F');
                            $status = strtoupper($period->status ?? 'DRAFT');
                            $statusBadge = match($status) {
                                'APPROVED', 'COMPLETED' => 'bg-success text-white',
                                'PROCESSED', 'SUBMITTED' => 'bg-info text-dark',
                                'PENDING' => 'bg-warning text-dark',
                                default => 'bg-secondary text-white',
                            };
                        @endphp
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="badge bg-light text-dark border px-2 py-1 fw-semibold">
                                    <i class="bi bi-tag-fill me-1 text-muted"></i>{{ $period->fund_cluster }}
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="badge border {{ $period->payroll_type->badgeClass() }} px-2 py-1 fw-semibold" style="font-size: 0.75rem;">
                                    <i class="bi {{ $period->payroll_type->isBonus() ? 'bi-gift-fill' : 'bi-calendar-check-fill' }} me-1"></i>{{ $period->payroll_type->value }}
                                </span>
                            </td>
                            <td class="py-3">
                                <div class="fw-bold text-header-blue">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ $monthName }} {{ $period->period_year }}
                                </div>
                                <div class="text-muted small">Month code: {{ str_pad($period->period_month, 2, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td class="py-3">
                                @if($period->description)
                                    <span class="text-dark">{{ $period->description }}</span>
                                @else
                                    <span class="text-muted fst-italic">No description provided</span>
                                @endif
                            </td>
                            <td class="py-3 text-center">
                                <span class="badge rounded-pill {{ $statusBadge }} px-3 py-1 fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="py-3 text-end pe-4">
                                <a href="{{ route('hr.payroll.show', $period->id) }}" class="btn btn-sm btn-accent fw-bold shadow-sm px-3">
                                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> View Master Sheet
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-3">
                                    <i class="bi bi-inbox text-muted display-4 d-block mb-3 opacity-50"></i>
                                    <h6 class="fw-bold text-secondary mb-1">No Payroll Periods Found</h6>
                                    <p class="text-muted small mb-3">Get started by creating your first payroll period.</p>
                                    <button type="button" class="btn btn-sm btn-accent fw-bold shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#newPayrollPeriodModal">
                                        <i class="bi bi-plus-circle me-1"></i> Generate New Period
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Generate New Payroll Period Modal --}}
<div class="modal fade" id="newPayrollPeriodModal" tabindex="-1" aria-labelledby="newPayrollPeriodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('hr.payroll.store') }}" method="POST" class="w-100">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-accent">
                    <h5 class="modal-title fw-bold" id="newPayrollPeriodModalLabel">
                        <i class="bi bi-calendar-plus me-2"></i> Generate New Payroll Period
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    {{-- Fund Cluster --}}
                    <div class="mb-3">
                        <label for="fund_cluster" class="form-label fw-bold small text-muted text-uppercase">
                            Fund Cluster <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="fund_cluster" 
                               id="fund_cluster" 
                               class="form-control @error('fund_cluster') is-invalid @enderror" 
                               value="{{ old('fund_cluster', '01') }}" 
                               placeholder="e.g. 01 or 01 - Regular Agency Fund" 
                               required>
                        @error('fund_cluster')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text small">e.g. 01 for General Fund / Regular Agency Fund.</div>
                    </div>

                    {{-- Payroll Type --}}
                    <div class="mb-3">
                        <label for="payroll_type" class="form-label fw-bold small text-muted text-uppercase">
                            Payroll Type <span class="text-danger">*</span>
                        </label>
                        <select name="payroll_type"
                                id="payroll_type"
                                class="form-select @error('payroll_type') is-invalid @enderror"
                                required>
                            @foreach(\App\Enums\PayrollType::cases() as $type)
                                <option value="{{ $type->value }}"
                                        data-bonus="{{ $type->isBonus() ? '1' : '0' }}"
                                        {{ old('payroll_type', \App\Enums\PayrollType::Regular->value) === $type->value ? 'selected' : '' }}>
                                    {{ $type->value }}
                                </option>
                            @endforeach
                        </select>
                        @error('payroll_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="alert border-0 small mt-2 mb-0 py-2 d-none" id="bonusPayrollNotice" style="background-color: rgba(253, 224, 71, 0.35); color: #854d0e;">
                            <i class="bi bi-info-circle me-1"></i>
                            Bonus runs pay one month of basic salary with <strong>no statutory deductions</strong>
                            (GSIS, PhilHealth, Pag-IBIG), no absences, lates, or loan amortization.
                            Year-End Bonus adds the 5,000 cash gift, and tax applies only above 90,000.
                        </div>
                    </div>

                    {{-- Period Month & Year in a row --}}
                    <div class="row g-3 mb-3">
                        <div class="col-sm-7">
                            <label for="period_month" class="form-label fw-bold small text-muted text-uppercase">
                                Period Month <span class="text-danger">*</span>
                            </label>
                            <select name="period_month" 
                                    id="period_month" 
                                    class="form-select @error('period_month') is-invalid @enderror" 
                                    required>
                                <option value="" disabled {{ old('period_month') ? '' : 'selected' }}>Select Month</option>
                                @foreach(range(1, 12) as $m)
                                    @php
                                        $name = \Carbon\Carbon::create()->month($m)->format('F');
                                    @endphp
                                    <option value="{{ $m }}" {{ old('period_month', date('n')) == $m ? 'selected' : '' }}>
                                        {{ $name }} ({{ str_pad($m, 2, '0', STR_PAD_LEFT) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('period_month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-5">
                            <label for="period_year" class="form-label fw-bold small text-muted text-uppercase">
                                Period Year <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   name="period_year" 
                                   id="period_year" 
                                   class="form-control @error('period_year') is-invalid @enderror" 
                                   value="{{ old('period_year', date('Y')) }}" 
                                   min="2000" 
                                   max="2100" 
                                   required>
                            @error('period_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="mb-2">
                        <label for="description" class="form-label fw-bold small text-muted text-uppercase">
                            Description / Remarks
                        </label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="3" 
                                  class="form-control @error('description') is-invalid @enderror" 
                                  placeholder="e.g. Regular monthly payroll for JHS teaching and non-teaching personnel">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer bg-light border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent fw-bold shadow-sm px-4">
                        <i class="bi bi-check2-circle me-1"></i> Save & Generate Period
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    /**
     * Surface the bonus computation rules when a bonus payout is selected.
     */
    (function () {
        const select = document.getElementById('payroll_type');
        const notice = document.getElementById('bonusPayrollNotice');

        function toggleNotice() {
            const isBonus = select.options[select.selectedIndex].dataset.bonus === '1';
            notice.classList.toggle('d-none', !isBonus);
        }

        select.addEventListener('change', toggleNotice);
        toggleNotice();
    })();
</script>
@endsection
