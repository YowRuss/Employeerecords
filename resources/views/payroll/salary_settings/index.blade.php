@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-header-blue fw-bolder mb-1" style="letter-spacing: -0.5px;">
                <i class="bi bi-cash-stack me-2 text-header-blue"></i>Salary Settings
            </h4>
            <p class="text-muted small mb-0">Configure and manage SSL Salary Matrix grades and steps.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-4 mb-4" role="alert" style="background-color: #d1e7dd; color: #0f5132;">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm rounded-4 overflow-hidden" style="background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px);">
        <div class="card-header p-4 border-bottom-0 bg-white">
            <div class="d-flex align-items-center">
                <div class="d-flex align-items-center justify-content-center rounded-circle me-3 shadow-sm bg-accent" style="width: 48px; height: 48px;">
                    <i class="bi bi-table fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.3px;">SSL Salary Matrix Overview</h5>
                    <div class="text-muted small">Update monthly compensation amounts for each grade and step.</div>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="--bs-table-hover-bg: #f8fafc;">
                    <thead style="background-color: #f1f5f9;">
                        <tr>
                            <th class="ps-5 text-secondary small fw-bolder text-uppercase py-3 border-0" style="letter-spacing: 0.5px;">Salary Grade</th>
                            <th class="text-secondary small fw-bolder text-uppercase py-3 border-0" style="letter-spacing: 0.5px;">Step Increment</th>
                            <th class="text-secondary small fw-bolder text-uppercase py-3 border-0" style="letter-spacing: 0.5px;">Monthly Amount</th>
                            <th class="text-end pe-5 text-secondary small fw-bolder text-uppercase py-3 border-0" style="letter-spacing: 0.5px;">Action</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($salaryGrades as $grade)
                        <tr class="transition-all" style="transition: background-color 0.2s;">
                            <td class="ps-5 py-4 border-light">
                                <span class="badge rounded-pill bg-accent fw-bold shadow-sm" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                                    SG {{ $grade->grade }}
                                </span>
                            </td>
                            <td class="py-4 border-light">
                                <span class="badge rounded-pill bg-light text-secondary border border-secondary border-opacity-25 fw-medium shadow-sm" style="padding: 0.45rem 0.85rem;">
                                    Step {{ $grade->step }}
                                </span>
                            </td>
                            <td class="py-4 border-light">
                                <span class="fw-bolder" style="color: #1A3E6F; font-size: 1.1rem; letter-spacing: -0.3px;">
                                    ₱ {{ number_format($grade->amount, 2) }}
                                </span>
                            </td>
                            <td class="text-end pe-5 py-4 border-light">
                                <button type="button" class="btn btn-sm btn-accent fw-bold shadow-sm" title="Edit Amount" aria-label="Edit Amount" data-bs-toggle="modal" data-bs-target="#editSalaryGradeModal{{ $grade->id }}">
                                    <i class="bi bi-pencil-fill me-1"></i> Edit
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="text-muted d-flex flex-column align-items-center">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 64px; height: 64px;">
                                        <i class="bi bi-inbox fs-2 opacity-50"></i>
                                    </div>
                                    <span class="fw-medium">No salary grades found in the matrix.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($salaryGrades->hasPages())
        <div class="card-footer bg-white border-top py-3">
            <div class="d-flex justify-content-center pagination-centered">
                {{ $salaryGrades->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Edit Modals -->
@foreach($salaryGrades as $grade)
<div class="modal fade text-start" id="editSalaryGradeModal{{ $grade->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 bg-accent px-4">
                <h5 class="modal-title fw-bold" style="color: #1A3E6F;">
                    <i class="bi bi-pencil-square me-2"></i>Update Salary Amount
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('payroll.salary_settings.update', $grade->id) }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-4">
                    <div class="d-flex align-items-center mb-4 p-3 rounded-4 shadow-sm" style="background: linear-gradient(to right, #f8fafc, #f1f5f9); border: 1px solid rgba(0,0,0,0.05);">
                        <div class="me-3">
                            <span class="badge rounded-pill bg-accent fw-bold mb-1" style="padding: 0.4rem 0.8rem;">
                                SG {{ $grade->grade }}
                            </span>
                            <span class="badge rounded-pill bg-white text-secondary border border-secondary border-opacity-25 ms-1" style="padding: 0.4rem 0.8rem;">
                                Step {{ $grade->step }}
                            </span>
                        </div>
                        <div class="ms-auto text-end">
                            <div class="small text-muted mb-1 fw-medium">Current Amount</div>
                            <div class="fw-bold" style="color: #475569;">₱ {{ number_format($grade->amount, 2) }}</div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-2">
                        <label class="form-label small fw-bold text-secondary text-uppercase tracking-wider mb-2">New Monthly Amount</label>
                        <div class="input-group input-group-lg shadow-sm rounded-4 overflow-hidden border focus-ring-group" style="transition: all 0.2s; border-color: #e2e8f0;">
                            <span class="input-group-text border-0 fw-bold text-muted px-4" style="background-color: #f8fafc;">₱</span>
                            <input type="number" step="0.01" name="amount" class="form-control border-0 fw-bold shadow-none" value="{{ $grade->amount }}" required style="font-size: 1.25rem; color: #1A3E6F; background-color: #f8fafc;" onfocus="this.parentElement.style.borderColor='#facc15'; this.parentElement.style.boxShadow='0 0 0 0.25rem rgba(253, 224, 71, 0.45)';" onblur="this.parentElement.style.borderColor='#e2e8f0'; this.parentElement.style.boxShadow='var(--bs-box-shadow-sm)';">
                        </div>
                        <div class="form-text mt-2 small text-muted"><i class="bi bi-info-circle me-1"></i> Enter the new base amount for this step.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light px-4 fw-bold rounded-pill border shadow-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent px-4 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<style>
    .pagination-centered nav {
        width: 100%;
        display: flex;
        justify-content: center;
    }
    .pagination-centered .d-flex.justify-content-between.flex-fill.d-sm-none {
        display: none !important;
    }
    .pagination-centered .d-none.flex-sm-fill.d-sm-flex.align-items-sm-center.justify-content-sm-between > div:first-child {
        display: none !important;
    }
    .pagination-centered .d-none.flex-sm-fill.d-sm-flex.align-items-sm-center.justify-content-sm-between > div:last-child {
        width: 100%;
        display: flex;
        justify-content: center;
    }
    .pagination-centered .pagination {
        display: flex;
        gap: 6px;
        margin: 0;
        padding: 0;
    }
    .pagination-centered .page-item .page-link {
        color: #1A3E6F;
        font-weight: 600;
        font-size: 0.875rem;
        min-width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px !important;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease-in-out;
        text-decoration: none;
    }
    .pagination-centered .page-item:not(.active):not(.disabled) .page-link:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
    }
    .pagination-centered .page-item.active .page-link {
        background: linear-gradient(135deg, var(--accent-yellow, #fde047), #fef08a);
        border-color: #facc15;
        color: #1e293b;
        box-shadow: 0 2px 5px rgba(253, 224, 71, 0.45);
    }
    .pagination-centered .page-item.disabled .page-link {
        background-color: #f8fafc;
        border-color: #f1f5f9;
        color: #94a3b8;
        opacity: 0.6;
        cursor: not-allowed;
    }
    .pagination-centered .page-link:focus {
        box-shadow: 0 0 0 3px rgba(253, 224, 71, 0.45);
    }
    .table-hover tbody tr:hover td {
        background-color: var(--bs-table-hover-bg) !important;
    }
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>
@endsection
