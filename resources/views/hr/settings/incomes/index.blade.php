@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-6 mb-3 mb-md-0">
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('hr.payroll.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1">
                    <i class="bi bi-arrow-left me-1"></i> Back to Payroll
                </a>
            </div>
            <h4 class="text-header-blue fw-bold mb-0">
                <i class="bi bi-wallet2 me-2 text-header-blue"></i> Income Types & Allowances
            </h4>
            <p class="text-muted small mb-0">Manage standard government allowances and bonus types available for payroll.</p>
        </div>
        <div class="col-12 col-md-6 text-md-end d-flex flex-wrap justify-content-md-end gap-2">
            <a href="{{ route('hr.settings.deductions.index') }}" class="btn btn-outline-secondary fw-semibold shadow-sm px-3 py-2">
                <i class="bi bi-sliders me-1"></i> Deduction Settings
            </a>
            <button type="button" class="btn fw-bold shadow-sm text-white px-3 py-2" style="background-color: #1A3E6F;" data-bs-toggle="modal" data-bs-target="#addIncomeTypeModal">
                <i class="bi bi-plus-circle me-1"></i> Add Income Type
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
            <strong>Please fix the following errors:</strong>
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
                <span class="text-muted small fw-bold text-uppercase">Total Types</span>
                <h4 class="fw-bold mb-0 mt-1" style="color: #1A3E6F;">{{ $incomeTypes->count() }}</h4>
                <span class="small text-muted">Income/allowance types</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                <span class="text-muted small fw-bold text-uppercase">Active</span>
                <h4 class="fw-bold mb-0 mt-1 text-success">{{ $incomeTypes->where('is_active', true)->count() }}</h4>
                <span class="small text-muted">Currently enabled</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4 border-warning">
                <span class="text-muted small fw-bold text-uppercase">Inactive</span>
                <h4 class="fw-bold mb-0 mt-1 text-warning">{{ $incomeTypes->where('is_active', false)->count() }}</h4>
                <span class="small text-muted">Currently disabled</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4 border-info">
                <span class="text-muted small fw-bold text-uppercase">With Default ₱</span>
                <h4 class="fw-bold mb-0 mt-1 text-info">{{ $incomeTypes->where('default_amount', '>', 0)->count() }}</h4>
                <span class="small text-muted">Pre-set default amounts</span>
            </div>
        </div>
    </div>

    {{-- Income Types Data Table --}}
    <div class="card shadow-sm border-0 rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill" style="background-color: rgba(26, 62, 111, 0.1); color: #1A3E6F; font-size: 0.85rem;">
                    {{ $incomeTypes->count() }} {{ Str::plural('Type', $incomeTypes->count()) }}
                </span>
                <span class="text-muted small">Registered in the system</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-muted small fw-bold text-uppercase py-3 ps-4 border-0" style="width: 40px;">#</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0">Name</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0" style="min-width: 200px;">Description</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0 text-end" style="min-width: 130px;">Default Amount</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0 text-center">Status</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0 text-end pe-4" style="min-width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incomeTypes as $index => $type)
                        <tr class="{{ !$type->is_active ? 'table-light opacity-75' : '' }}">
                            <td class="ps-4 text-muted small fw-semibold">{{ $index + 1 }}</td>
                            <td class="fw-bold" style="color: #1A3E6F;">
                                <i class="bi bi-wallet2 me-1 text-secondary" style="font-size: 0.8rem;"></i>
                                {{ $type->name }}
                            </td>
                            <td class="text-muted small">{{ $type->description ?? '—' }}</td>
                            <td class="text-end font-monospace fw-semibold" style="color: #1A3E6F;">
                                @if((float) $type->default_amount > 0)
                                    ₱{{ number_format((float) $type->default_amount, 2) }}
                                @else
                                    <span class="text-muted fst-italic">Varies</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($type->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-check-circle-fill me-1"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-x-circle-fill me-1"></i>Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary px-2 py-1" data-bs-toggle="modal" data-bs-target="#editIncomeTypeModal{{ $type->id }}" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form action="{{ route('hr.settings.incomes.toggle', $type->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $type->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} px-2 py-1" title="{{ $type->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi {{ $type->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('hr.settings.incomes.destroy', $type->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete income type &quot;{{ $type->name }}&quot;? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="Delete">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-wallet2 text-muted display-4 d-block mb-3 opacity-50"></i>
                                    <h6 class="fw-bold text-secondary mb-1">No income types defined yet</h6>
                                    <p class="text-muted small mb-0">Click "Add Income Type" to create your first allowance type.</p>
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

{{-- Add Income Type Modal --}}
<div class="modal fade" id="addIncomeTypeModal" tabindex="-1" aria-labelledby="addIncomeTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('hr.settings.incomes.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3" style="background-color: #f8fafc;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: rgba(26, 62, 111, 0.1); color: #1A3E6F;">
                            <i class="bi bi-plus-circle fs-5"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0" id="addIncomeTypeModalLabel" style="color: #1A3E6F;">
                                Add Income Type
                            </h6>
                            <div class="text-muted small">Define a new allowance or bonus type</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="add_name" class="form-label small fw-semibold text-secondary">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_name" name="name" placeholder="e.g., PERA, Clothing Allowance" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_description" class="form-label small fw-semibold text-secondary">Description</label>
                        <textarea class="form-control" id="add_description" name="description" rows="2" placeholder="Optional description..."></textarea>
                    </div>
                    <div class="mb-0">
                        <label for="add_default_amount" class="form-label small fw-semibold text-secondary">Default Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">₱</span>
                            <input type="number" step="0.01" min="0" class="form-control font-monospace" id="add_default_amount" name="default_amount" value="0.00" placeholder="0.00" required>
                        </div>
                        <div class="form-text small text-muted">Set to ₱0.00 if the amount varies per employee (e.g., Mid-Year Bonus).</div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm text-white px-4 shadow-sm" style="background-color: #1A3E6F;">
                        <i class="bi bi-check2-circle me-1"></i> Save Income Type
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Income Type Modals (one per type) --}}
@foreach($incomeTypes as $type)
<div class="modal fade" id="editIncomeTypeModal{{ $type->id }}" tabindex="-1" aria-labelledby="editIncomeTypeModalLabel{{ $type->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('hr.settings.incomes.update', $type->id) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3" style="background-color: #f8fafc;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: rgba(26, 62, 111, 0.1); color: #1A3E6F;">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0" id="editIncomeTypeModalLabel{{ $type->id }}" style="color: #1A3E6F;">
                                Edit Income Type
                            </h6>
                            <div class="text-muted small">{{ $type->name }}</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_name_{{ $type->id }}" class="form-label small fw-semibold text-secondary">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name_{{ $type->id }}" name="name" value="{{ $type->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description_{{ $type->id }}" class="form-label small fw-semibold text-secondary">Description</label>
                        <textarea class="form-control" id="edit_description_{{ $type->id }}" name="description" rows="2">{{ $type->description }}</textarea>
                    </div>
                    <div class="mb-0">
                        <label for="edit_default_amount_{{ $type->id }}" class="form-label small fw-semibold text-secondary">Default Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">₱</span>
                            <input type="number" step="0.01" min="0" class="form-control font-monospace" id="edit_default_amount_{{ $type->id }}" name="default_amount" value="{{ number_format((float) $type->default_amount, 2, '.', '') }}" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm text-white px-4 shadow-sm" style="background-color: #1A3E6F;">
                        <i class="bi bi-check2-circle me-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
