@extends('layouts.app')

@section('content')
<style>
    .staff-tabs .nav-link {
        border: none;
        color: #1A3E6F;
        font-weight: 600;
        background: transparent;
        border-radius: 0;
        padding: 1rem 1.5rem;
        opacity: 0.7;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
    }
    .staff-tabs .nav-link:hover {
        opacity: 1;
        border-color: rgba(253, 224, 71, 0.5);
    }
    .staff-tabs .nav-link.active {
        background-color: var(--accent-yellow, #FDE047);
        color: #1A3E6F;
        opacity: 1;
        border-color: var(--accent-yellow, #FDE047);
        border-radius: 8px 8px 0 0;
    }
    .sex-pills .nav-link {
        border-radius: 6px;
        padding: 0.38rem 1.1rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #1A3E6F;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease-in-out;
    }
    .sex-pills .nav-link:hover {
        background-color: #e2e8f0;
        color: #1A3E6F;
    }
    .sex-pills .nav-link.active {
        background-color: var(--accent-yellow, #FDE047) !important;
        color: #1A3E6F !important;
        border-color: var(--accent-yellow, #FDE047) !important;
        font-weight: 700;
        box-shadow: 0 2px 5px rgba(253, 224, 71, 0.45);
    }
</style>
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-6 mb-3 mb-md-0">
            <h4 class="text-header-blue fw-bold mb-0">
                <i class="bi bi-coin me-2 text-header-blue"></i> Allowances & Other Incomes
            </h4>
            <p class="text-muted small mb-0">Assign PERA, bonuses, and statutory allowances to employees.</p>
        </div>
        <div class="col-12 col-md-6 text-md-end">
            <a href="{{ route('hr.payroll.index') }}" class="btn btn-outline-secondary fw-semibold shadow-sm px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Back to Payroll
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Employee Table --}}
    <div class="card shadow-sm rounded-3 bg-white">
        <form method="GET" action="{{ route('payroll.allowances.index') }}" id="staff-filter-form">
            <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-accent" style="font-size: 0.85rem;">
                            {{ $totalEmployees }} {{ Str::plural('Employee', $totalEmployees) }}
                        </span>
                        <span class="text-muted small">Active employees in the system</span>
                    </div>
                    <div class="search-container" style="max-width: 350px; width: 100%;">
                        <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                            <span class="input-group-text bg-light border-end-0 border-light"><i class="bi bi-search text-muted"></i></span>
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                class="form-control bg-light border-start-0 border-light ps-0 focus-ring"
                                style="box-shadow: none;"
                                placeholder="Search by name..."
                            >
                            @if(request('search'))
                            <a href="{{ route('payroll.allowances.index', request()->except('search')) }}" class="btn btn-light border-light text-danger">
                                <i class="bi bi-x-circle-fill"></i>
                            </a>
                            @else
                            <button type="submit" class="btn btn-accent fw-bold px-3">
                                Search
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tabbed Navigation -->
                <ul class="nav nav-tabs staff-tabs border-bottom-0" id="staffTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('payroll.allowances.index', request()->except('category', 'page')) }}" class="nav-link {{ !request('category') || request('category') == 'all' ? 'active' : '' }}">
                            All Employees
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('payroll.allowances.index', array_merge(request()->query(), ['category' => 'teaching', 'page' => null])) }}" class="nav-link {{ request('category') == 'teaching' ? 'active' : '' }}">
                            Teaching Positions
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('payroll.allowances.index', array_merge(request()->query(), ['category' => 'non-teaching', 'page' => null])) }}" class="nav-link {{ request('category') == 'non-teaching' ? 'active' : '' }}">
                            Non-Teaching Positions
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0 border-top border-light">
                <input type="hidden" name="category" id="active_category" value="{{ request('category') }}">
                <input type="hidden" name="sex" id="sex_filter_input" value="{{ request('sex') }}">
                
                <!-- Global Filters: Sex -->
                <div class="p-3 bg-light border-bottom d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <ul class="nav nav-pills sex-pills gap-1" id="sexFilterTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button type="button"
                                        class="nav-link {{ !request()->has('sex') || request('sex') === '' || request('sex') === 'all' ? 'active' : '' }}"
                                        onclick="document.getElementById('sex_filter_input').value = 'all'; document.getElementById('staff-filter-form').submit();">
                                    All
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button type="button"
                                        class="nav-link {{ request('sex') === 'male' ? 'active' : '' }}"
                                        onclick="document.getElementById('sex_filter_input').value = 'male'; document.getElementById('staff-filter-form').submit();">
                                    Male
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button type="button"
                                        class="nav-link {{ request('sex') === 'female' ? 'active' : '' }}"
                                        onclick="document.getElementById('sex_filter_input').value = 'female'; document.getElementById('staff-filter-form').submit();">
                                    Female
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-muted small fw-bold text-uppercase py-3 ps-4 border-0" style="width: 50px;">#</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0" style="min-width: 220px;">Employee Name</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0" style="min-width: 180px;">Position</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0" style="min-width: 200px;">Assigned Allowances</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0 text-end pe-4" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $index => $employee)
                        @php
                            $employeeName = $employee->last_name ? $employee->last_name . ', ' . $employee->first_name : $employee->name;
                        @endphp
                        <tr class="employee-row" data-name="{{ strtolower($employeeName) }}">
                            <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-semibold" style="color: #1A3E6F;">{{ $employeeName }}</div>
                                @if($employee->email)
                                    <small class="text-muted">{{ $employee->email }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="text-secondary">{{ $employee->position->position_name ?? 'No position' }}</span>
                            </td>
                            <td>
                                @if($employee->allowances->isEmpty())
                                    <span class="badge bg-light text-muted border">None</span>
                                @else
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($employee->allowances as $allowance)
                                            <span class="badge" style="background-color: rgba(25, 135, 84, 0.1); color: #198754; border: 1px solid rgba(25, 135, 84, 0.2);">
                                                {{ $allowance->allowance_name }} (₱{{ number_format($allowance->amount, 2) }})
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-accent fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#manageModal{{ $employee->id }}">
                                    <i class="bi bi-sliders me-1"></i> Manage
                                </button>
                            </td>
                        </tr>

                        {{-- Manage Allowances Modal --}}
                        <div class="modal fade" id="manageModal{{ $employee->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-0 bg-accent">
                                        <h5 class="modal-title fw-bold" style="color: #1A3E6F;">
                                            <i class="bi bi-wallet2 me-2"></i> Manage Allowances
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('payroll.allowances.update', $employee->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <p class="text-muted small mb-4">Assign standard allowances and incomes to <strong>{{ $employeeName }}</strong>.</p>
                                            
                                            <div class="bg-light p-3 rounded border">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="fw-bold mb-0" style="color: #1A3E6F;"><i class="bi bi-plus-circle me-1"></i> Allowances & Incomes</h6>
                                                    <button type="button" class="btn btn-sm btn-accent fw-bold shadow-sm add-income-btn" data-record="{{ $employee->id }}">
                                                        <i class="bi bi-plus"></i> Add Item
                                                    </button>
                                                </div>
                                                
                                                <div id="incomeRepeater{{ $employee->id }}">
                                                    @foreach($employee->allowances as $allowance)
                                                        <div class="row g-2 mb-2 align-items-end income-row">
                                                            <div class="col-md-5 col-sm-5">
                                                                <label class="form-label small fw-semibold text-secondary mb-1">Allowance Type</label>
                                                                <select name="income_types[]" class="form-select form-select-sm income-type-select" data-placeholder="Select Type">
                                                                    <option value=""></option>
                                                                    @foreach($incomeTypes as $type)
                                                                        <option value="{{ $type->id }}" data-default="{{ $type->default_amount }}" {{ $allowance->income_type_id == $type->id ? 'selected' : '' }}>
                                                                            {{ $type->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-5 col-sm-5">
                                                                <label class="form-label small fw-semibold text-secondary mb-1">Amount</label>
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text bg-light text-success border-end-0">₱</span>
                                                                    <input type="number" step="0.01" min="0" name="income_amounts[]" class="form-control font-monospace border-start-0 ps-1" value="{{ $allowance->amount }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2 col-sm-2">
                                                                <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-income-row" title="Remove">
                                                                    <i class="bi bi-trash3"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light border-top-0">
                                            <button type="button" class="btn btn-light border shadow-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-accent fw-bold shadow-sm px-4">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                No active employees found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employees->hasPages())
                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top bg-light">
                    <div class="text-muted small">
                        Showing <strong>{{ $employees->firstItem() ?? 0 }}</strong> to <strong>{{ $employees->lastItem() ?? 0 }}</strong> of <strong>{{ $employees->total() }}</strong> employees
                    </div>
                    <div class="pagination-centered">
                        {{ $employees->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Income types data from server
    const incomeTypesData = @json($incomeTypes->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'default_amount' => $t->default_amount]));

    function initAllowanceTypeSelect(select) {
        if (typeof $ === 'undefined' || !$.fn.select2 || $(select).hasClass('select2-hidden-accessible')) {
            return;
        }

        $(select).select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: select.dataset.placeholder || 'Select Type',
            allowClear: true,
            dropdownParent: $(select).closest('.modal'),
        });
    }

    function buildIncomeOptions() {
        let html = '<option value=""></option>';
        incomeTypesData.forEach(function (type) {
            html += '<option value="' + type.id + '" data-default="' + type.default_amount + '">' + type.name + '</option>';
        });
        return html;
    }

    // Add Income Row
    document.querySelectorAll('.add-income-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const recordId = this.getAttribute('data-record');
            const container = document.getElementById('incomeRepeater' + recordId);

            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 align-items-end income-row';
            row.innerHTML = `
                <div class="col-md-5 col-sm-5">
                    <label class="form-label small fw-semibold text-secondary mb-1">Allowance Type</label>
                    <select name="income_types[]" class="form-select form-select-sm income-type-select" data-placeholder="Select Type">
                        ${buildIncomeOptions()}
                    </select>
                </div>
                <div class="col-md-5 col-sm-5">
                    <label class="form-label small fw-semibold text-secondary mb-1">Amount</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-success border-end-0">₱</span>
                        <input type="number" step="0.01" min="0" name="income_amounts[]" class="form-control font-monospace border-start-0 ps-1" placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-2 col-sm-2">
                    <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-income-row" title="Remove">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
            bindIncomeRowEvents(row);
        });
    });

    function bindIncomeRowEvents(row) {
        const select = row.querySelector('.income-type-select');
        const amountInput = row.querySelector('input[name="income_amounts[]"]');
        const removeBtn = row.querySelector('.remove-income-row');

        if (select && amountInput) {
            const fillDefaultAmount = function () {
                const selected = select.options[select.selectedIndex];
                if (!selected) {
                    return;
                }

                const defaultAmount = parseFloat(selected.getAttribute('data-default') || 0);

                if (!amountInput.value || parseFloat(amountInput.value) === 0) {
                    amountInput.value = defaultAmount > 0 ? defaultAmount.toFixed(2) : '';
                }
            };

            select.addEventListener('change', fillDefaultAmount);
            $(select).on('select2:select', fillDefaultAmount);
        }

        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                row.remove();
            });
        }

        if (select) {
            initAllowanceTypeSelect(select);
        }
    }

    // Bind existing rows
    document.querySelectorAll('.income-row').forEach(function (row) {
        bindIncomeRowEvents(row);
    });
});
</script>
@endsection
