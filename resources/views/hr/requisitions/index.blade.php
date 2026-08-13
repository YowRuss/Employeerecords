@extends('layouts.app')

@section('content')
<style>
    /* ---- Tab Navigation ---- */
    .req-tabs {
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none; /* Firefox */
    }
    .req-tabs::-webkit-scrollbar { display: none; }
    .req-tabs .nav-item { flex-shrink: 0; }
    .req-tabs .nav-link {
        border: none;
        color: #1A3E6F;
        font-weight: 600;
        background: transparent;
        border-radius: 0;
        padding: 1rem 1.5rem;
        opacity: 0.7;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
        white-space: nowrap;
    }
    .req-tabs .nav-link:hover {
        opacity: 1;
        border-color: rgba(253, 224, 71, 0.5);
    }
    .req-tabs .nav-link.active {
        background-color: var(--accent-yellow, #FDE047);
        color: #1A3E6F;
        font-weight: 700;
        opacity: 1;
        border-color: #EAB308;
        border-radius: 8px 8px 0 0;
    }

    /* ---- Action Buttons ---- */
    .btn-req-action {
        background-color: #FDE047;
        color: #1F2937;
        border-color: #EAB308;
        font-weight: 700;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .btn-req-action:hover {
        background-color: #EAB308;
        color: #1F2937;
        border-color: #CA8A04;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    /* ---- History Tables ---- */
    .history-table th {
        font-size: 0.8rem;
        text-transform: uppercase;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.5px;
    }
    .history-table td {
        font-size: 0.9rem;
        vertical-align: middle;
    }
    .tab-empty-state {
        padding: 3rem 1rem;
        text-align: center;
    }
    .tab-empty-state i {
        font-size: 2.5rem;
        opacity: 0.3;
        margin-bottom: 0.75rem;
        display: block;
    }

    /* ---- Mobile Inline Details (visible < md) ---- */
    .mobile-details {
        display: none;
        margin-top: 0.4rem;
    }
    .mobile-details .badge {
        font-size: 0.75rem;
    }

    /* ---- Mobile Breakpoints ---- */
    @media (max-width: 767.98px) {
        .req-tabs .nav-link {
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
        }
        .tab-section-header {
            padding: 1rem 1rem 0.5rem !important;
        }
        .tab-section-header h6 { font-size: 0.9rem; }
        .tab-history-label {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
        .btn-req-action {
            width: 100%;
            border-radius: 8px !important;
        }
        .mobile-details { display: block; }
        .history-table td { padding-left: 0.75rem !important; padding-right: 0.75rem !important; }
    }
    @media (max-width: 575.98px) {
        .req-tabs .nav-link {
            padding: 0.65rem 0.75rem;
            font-size: 0.8rem;
        }
        .req-tabs .nav-link i { display: none; }
        .tab-empty-state { padding: 2rem 0.75rem; }
        .tab-empty-state i { font-size: 2rem; }
    }
</style>

<div class="container-fluid py-2">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold m-0" style="color: #1A3E6F;"><i class="bi bi-clipboard-data me-2"></i> Personnel Requisitions</h4>
            <p class="text-muted small mt-1 mb-0">Manage employee lifecycle — hiring, promotions, transfers, and separations.</p>
        </div>
    </div>

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

    {{-- Main Card with Tabs --}}
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
            <ul class="nav nav-tabs req-tabs border-bottom-0" id="requisitionTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="newhires-tab" data-bs-toggle="tab" data-bs-target="#newhires" type="button" role="tab" aria-controls="newhires" aria-selected="true">
                        <i class="bi bi-person-plus-fill me-1"></i> New Hires / Employment
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="promotions-tab" data-bs-toggle="tab" data-bs-target="#promotions" type="button" role="tab" aria-controls="promotions" aria-selected="false">
                        <i class="bi bi-arrow-up-circle me-1"></i> Promotions
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="transfers-tab" data-bs-toggle="tab" data-bs-target="#transfers" type="button" role="tab" aria-controls="transfers" aria-selected="false">
                        <i class="bi bi-arrow-left-right me-1"></i> Reassignments & Transfers
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="terminations-tab" data-bs-toggle="tab" data-bs-target="#terminations" type="button" role="tab" aria-controls="terminations" aria-selected="false">
                        <i class="bi bi-person-x me-1"></i> Terminations & Offboarding
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-0 border-top border-light">
            <div class="tab-content" id="requisitionTabsContent">

                {{-- ============================================ --}}
                {{-- TAB 1: NEW HIRES / EMPLOYMENT --}}
                {{-- ============================================ --}}
                <div class="tab-pane fade show active" id="newhires" role="tabpanel" aria-labelledby="newhires-tab">
                    <div class="p-4 pb-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 tab-section-header">
                        <div>
                            <h6 class="fw-bold text-dark m-0"><i class="bi bi-person-plus me-1 text-muted"></i> Employee Onboarding</h6>
                            <small class="text-muted">Register new employees and initialize their records.</small>
                        </div>
                        <a href="{{ route('employees.create') }}" class="btn btn-req-action rounded-pill px-4 shadow-sm">
                            <i class="bi bi-plus-lg me-1"></i> Add New Employee
                        </a>
                    </div>

                    <div class="px-4 pb-2 tab-history-label">
                        <h6 class="fw-bold text-muted small text-uppercase mb-3"><i class="bi bi-clock-history me-1"></i> Recent Hires (Last 30 Days)</h6>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 history-table">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0">Employee</th>
                                    <th class="py-3 border-0 d-none d-md-table-cell">Position</th>
                                    <th class="py-3 border-0 d-none d-lg-table-cell">Date Hired</th>
                                    <th class="py-3 border-0 text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentHires as $hire)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-accent text-dark d-flex align-items-center justify-content-center fw-bold me-3 shadow-sm flex-shrink-0" style="width: 40px; height: 40px; font-size: 0.95rem;">
                                                {{ strtoupper(substr($hire->first_name, 0, 1)) }}{{ strtoupper(substr($hire->last_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark text-uppercase" style="font-size: 0.9rem;">{{ $hire->last_name }}, {{ $hire->first_name }} {{ $hire->middle_name }}</div>
                                                <div class="text-muted small">ID: {{ str_pad($hire->id, 4, '0', STR_PAD_LEFT) }}</div>
                                                {{-- Mobile inline details --}}
                                                <div class="mobile-details">
                                                    <span class="badge bg-light text-dark border px-2 py-1 me-1"><i class="bi bi-briefcase-fill me-1 text-muted"></i>{{ $hire->position->position_name ?? 'NOT ASSIGNED' }}</span>
                                                    <span class="badge bg-light text-muted border px-2 py-1"><i class="bi bi-calendar me-1"></i>{{ $hire->created_at->format('M d, Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 d-none d-md-table-cell">
                                        <span class="badge bg-light text-dark border shadow-sm px-2 py-1">{{ $hire->position->position_name ?? 'NOT ASSIGNED' }}</span>
                                    </td>
                                    <td class="py-3 text-muted d-none d-lg-table-cell">{{ $hire->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 text-end pe-4">
                                        <a href="{{ route('hr.view_profile', $hire->id) }}" class="btn btn-sm btn-light border shadow-sm" data-bs-toggle="tooltip" title="View Profile">
                                            <i class="bi bi-person-lines-fill text-info"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="tab-empty-state">
                                            <i class="bi bi-person-plus text-muted"></i>
                                            <h6 class="fw-bold text-dark">No recent hires</h6>
                                            <p class="text-muted small mb-0">Employees registered in the last 30 days will appear here.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- TAB 2: PROMOTIONS --}}
                {{-- ============================================ --}}
                <div class="tab-pane fade" id="promotions" role="tabpanel" aria-labelledby="promotions-tab">
                    <div class="p-4 pb-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 tab-section-header">
                        <div>
                            <h6 class="fw-bold text-dark m-0"><i class="bi bi-arrow-up-circle me-1 text-muted"></i> Process Promotion</h6>
                            <small class="text-muted">Search for an employee and assign a new position.</small>
                        </div>
                        <button class="btn btn-req-action rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#promoteEmployeeModal">
                            <i class="bi bi-plus-lg me-1"></i> Process Promotion
                        </button>
                    </div>

                    <div class="px-4 pb-2 tab-history-label">
                        <h6 class="fw-bold text-muted small text-uppercase mb-3"><i class="bi bi-clock-history me-1"></i> Recent Promotions (Last 90 Days)</h6>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 history-table">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0">Employee</th>
                                    <th class="py-3 border-0 d-none d-md-table-cell">New Position</th>
                                    <th class="py-3 border-0 d-none d-lg-table-cell">Effective Date</th>
                                    <th class="py-3 border-0 d-none d-lg-table-cell">Processed On</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPromotions as $promo)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold text-dark text-uppercase" style="font-size: 0.9rem;">{{ $promo->last_name }}, {{ $promo->first_name }} {{ $promo->middle_name }}</div>
                                        {{-- Mobile inline details --}}
                                        <div class="mobile-details">
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 me-1">{{ $promo->designation ?? 'N/A' }}</span>
                                            <span class="badge bg-light text-muted border px-2 py-1"><i class="bi bi-calendar me-1"></i>{{ $promo->date_from ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 d-none d-md-table-cell">
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">{{ $promo->designation ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-3 text-muted d-none d-lg-table-cell">{{ $promo->date_from ?? 'N/A' }}</td>
                                    <td class="py-3 text-muted d-none d-lg-table-cell">{{ \Carbon\Carbon::parse($promo->created_at)->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="tab-empty-state">
                                            <i class="bi bi-arrow-up-circle text-muted"></i>
                                            <h6 class="fw-bold text-dark">No recent promotions</h6>
                                            <p class="text-muted small mb-0">Promotions processed in the last 90 days will appear here.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- TAB 3: REASSIGNMENTS & TRANSFERS --}}
                {{-- ============================================ --}}
                <div class="tab-pane fade" id="transfers" role="tabpanel" aria-labelledby="transfers-tab">
                    <div class="p-4 pb-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 tab-section-header">
                        <div>
                            <h6 class="fw-bold text-dark m-0"><i class="bi bi-arrow-left-right me-1 text-muted"></i> Initiate Transfer</h6>
                            <small class="text-muted">Reassign an employee to a different position or department.</small>
                        </div>
                        <button class="btn btn-req-action rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#reassignEmployeeModal">
                            <i class="bi bi-plus-lg me-1"></i> Initiate Transfer
                        </button>
                    </div>

                    <div class="px-4 pb-2 tab-history-label">
                        <h6 class="fw-bold text-muted small text-uppercase mb-3"><i class="bi bi-clock-history me-1"></i> Transfer History (Last 90 Days)</h6>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 history-table">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0">Employee</th>
                                    <th class="py-3 border-0 d-none d-md-table-cell">New Position</th>
                                    <th class="py-3 border-0 d-none d-lg-table-cell">Department</th>
                                    <th class="py-3 border-0 d-none d-lg-table-cell">Effective Date</th>
                                    <th class="py-3 border-0 d-none d-lg-table-cell">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transferHistory as $transfer)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold text-dark text-uppercase" style="font-size: 0.9rem;">{{ $transfer->last_name }}, {{ $transfer->first_name }} {{ $transfer->middle_name }}</div>
                                        {{-- Mobile inline details --}}
                                        <div class="mobile-details">
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 me-1">{{ $transfer->designation ?? 'N/A' }}</span>
                                            <br class="d-sm-none">
                                            <span class="badge bg-light text-muted border px-2 py-1 mt-1"><i class="bi bi-building me-1"></i>{{ $transfer->branch ?? 'N/A' }}</span>
                                            <span class="badge bg-light text-muted border px-2 py-1 mt-1"><i class="bi bi-calendar me-1"></i>{{ $transfer->date_from ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 d-none d-md-table-cell">
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">{{ $transfer->designation ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-3 text-muted d-none d-lg-table-cell">{{ $transfer->branch ?? 'N/A' }}</td>
                                    <td class="py-3 text-muted d-none d-lg-table-cell">{{ $transfer->date_from ?? 'N/A' }}</td>
                                    <td class="py-3 text-muted d-none d-lg-table-cell small">{{ $transfer->separation_cause !== 'NONE' ? $transfer->separation_cause : '—' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="tab-empty-state">
                                            <i class="bi bi-arrow-left-right text-muted"></i>
                                            <h6 class="fw-bold text-dark">No recent transfers</h6>
                                            <p class="text-muted small mb-0">Reassignments processed in the last 90 days will appear here.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- TAB 4: TERMINATIONS & OFFBOARDING --}}
                {{-- ============================================ --}}
                <div class="tab-pane fade" id="terminations" role="tabpanel" aria-labelledby="terminations-tab">
                    <div class="p-4 pb-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 tab-section-header">
                        <div>
                            <h6 class="fw-bold text-dark m-0"><i class="bi bi-person-x me-1 text-muted"></i> Terminate / Offboard Employee</h6>
                            <small class="text-muted">Process employee separation, retirement, or offboarding.</small>
                        </div>
                        <button class="btn btn-req-action rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#offboardEmployeeModal">
                            <i class="bi bi-plus-lg me-1"></i> Terminate Employee
                        </button>
                    </div>

                    <div class="px-4 pb-2 tab-history-label">
                        <h6 class="fw-bold text-muted small text-uppercase mb-3"><i class="bi bi-clock-history me-1"></i> Separation History</h6>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 history-table">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0">Employee</th>
                                    <th class="py-3 border-0 d-none d-md-table-cell">Last Position</th>
                                    <th class="py-3 border-0 d-none d-lg-table-cell">Reason</th>
                                    <th class="py-3 border-0 d-none d-lg-table-cell">Separation Date</th>
                                    <th class="py-3 border-0 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($separationHistory as $sep)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center fw-bold me-3 flex-shrink-0" style="width: 40px; height: 40px; font-size: 0.95rem;">
                                                {{ strtoupper(substr($sep->first_name, 0, 1)) }}{{ strtoupper(substr($sep->last_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark text-uppercase" style="font-size: 0.9rem;">{{ $sep->last_name }}, {{ $sep->first_name }} {{ $sep->middle_name }}</div>
                                                <div class="text-muted small">ID: {{ str_pad($sep->id, 4, '0', STR_PAD_LEFT) }}</div>
                                                {{-- Mobile inline details --}}
                                                <div class="mobile-details">
                                                    <span class="badge bg-light text-dark border px-2 py-1 me-1"><i class="bi bi-briefcase-fill me-1 text-muted"></i>{{ $sep->position->position_name ?? 'N/A' }}</span>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 me-1">{{ $sep->separation_reason ?? 'N/A' }}</span>
                                                    <br class="d-sm-none">
                                                    <span class="badge bg-light text-muted border px-2 py-1 mt-1"><i class="bi bi-calendar me-1"></i>{{ $sep->separation_date ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 d-none d-md-table-cell">
                                        <span class="badge bg-light text-dark border px-2 py-1">{{ $sep->position->position_name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-3 d-none d-lg-table-cell">
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">{{ $sep->separation_reason ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-3 text-muted d-none d-lg-table-cell">{{ $sep->separation_date ?? 'N/A' }}</td>
                                    <td class="py-3 text-center d-none d-md-table-cell">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1"><i class="bi bi-x-circle me-1"></i> Inactive</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="tab-empty-state">
                                            <i class="bi bi-person-x text-muted"></i>
                                            <h6 class="fw-bold text-dark">No separation records</h6>
                                            <p class="text-muted small mb-0">Offboarded employees will appear here.</p>
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

        <div class="card-footer bg-white py-3 border-top border-light text-muted small d-flex justify-content-center">
            <span>Personnel Requisitions — CNHS-JHS HR System</span>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- PROMOTE EMPLOYEE MODAL (with Select2) --}}
{{-- ============================================ --}}
<div class="modal fade" id="promoteEmployeeModal" tabindex="-1" aria-labelledby="promoteEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="promoteEmployeeModalLabel" style="color: #1A3E6F;">Promote Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('hr.promote_employee') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Select Employee <span class="text-danger">*</span></label>
                        <select name="user_id" id="promote_employee_select" class="form-select" required>
                            <option value="" disabled selected>Search for an employee...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" data-position="{{ $emp->position->position_name ?? 'NOT ASSIGNED' }}">
                                    {{ $emp->last_name }}, {{ $emp->first_name }} {{ $emp->middle_name }} — {{ $emp->position->position_name ?? 'NOT ASSIGNED' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Current Position</label>
                        <input type="text" class="form-control bg-light" id="promote_current_position" readonly placeholder="Select an employee above">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">New Position <span class="text-danger">*</span></label>
                        <select name="position_id" id="promote_new_position" class="form-select" required>
                            <option value="" disabled selected>Select New Position...</option>
                            @foreach($positions as $position)
                                <option value="{{ $position->id }}">{{ $position->position_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Effective Date (Optional)</label>
                        <input type="date" name="effective_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn fw-bold shadow-sm" style="background-color: var(--accent-yellow, #FDE047); color: #1A3E6F;">Confirm Promotion</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- OFFBOARD EMPLOYEE MODAL (with Select2) --}}
{{-- ============================================ --}}
<div class="modal fade" id="offboardEmployeeModal" tabindex="-1" aria-labelledby="offboardEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-danger" id="offboardEmployeeModalLabel">Offboard Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('hr.offboard_employee') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Select Employee <span class="text-danger">*</span></label>
                        <select name="user_id" id="offboard_employee_select" class="form-select" required>
                            <option value="" disabled selected>Search for an employee...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">
                                    {{ $emp->last_name }}, {{ $emp->first_name }} {{ $emp->middle_name }} — {{ $emp->position->position_name ?? 'NOT ASSIGNED' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Reason for Separation <span class="text-danger">*</span></label>
                        <select name="separation_reason" id="offboard_reason" class="form-select" required>
                            <option value="" disabled selected>Select Reason...</option>
                            <option value="Resignation">Resignation</option>
                            <option value="Retirement">Retirement</option>
                            <option value="Termination">Termination</option>
                            <option value="Transfer to other agency">Transfer to other agency</option>
                            <option value="End of Contract">End of Contract</option>
                            <option value="Death">Death</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Effective Date <span class="text-danger">*</span></label>
                        <input type="date" name="effective_date" id="offboard_effective_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold shadow-sm">Confirm Offboarding</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- REASSIGN EMPLOYEE MODAL (with Select2) --}}
{{-- ============================================ --}}
<div class="modal fade" id="reassignEmployeeModal" tabindex="-1" aria-labelledby="reassignEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content shadow">
            <div class="modal-header border-bottom-0 bg-light">
                <h5 class="modal-title fw-bold" style="color: #1A3E6F;" id="reassignEmployeeModalLabel">Reassign Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reassignForm" action="{{ route('hr.reassign_employee', ['id' => 0]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Select Employee <span class="text-danger">*</span></label>
                        <select name="user_id_select" id="reassign_employee_select" class="form-select" required>
                            <option value="" disabled selected>Search for an employee...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" data-position="{{ $emp->position->position_name ?? 'NOT ASSIGNED' }}">
                                    {{ $emp->last_name }}, {{ $emp->first_name }} {{ $emp->middle_name }} — {{ $emp->position->position_name ?? 'NOT ASSIGNED' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Current Position</label>
                        <input type="text" class="form-control bg-light" id="reassign_current_position" readonly placeholder="Select an employee above">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">New Position <span class="text-danger">*</span></label>
                        <select class="form-select focus-ring" name="position_id" id="reassign_new_position" required>
                            <option value="">Select new position...</option>
                            @foreach($positions as $position)
                                <option value="{{ $position->id }}">{{ $position->position_name }} ({{ $position->category }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">New Department/Unit <span class="text-danger">*</span></label>
                        <select class="form-select focus-ring" name="learning_area_id" id="reassign_new_department" required>
                            <option value="">Select new department...</option>
                            @foreach($learningAreas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Effective Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control focus-ring" name="effective_date" id="reassign_effective_date" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Remarks / Reason for Transfer</label>
                        <textarea class="form-control focus-ring" name="remarks" id="reassign_remarks" rows="2" placeholder="Optional remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn fw-bold shadow-sm" style="background-color: #FDE047; color: #1F2937; border-color: #EAB308;">Confirm Reassignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 on all employee search dropdowns
    const select2Config = {
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Search for an employee...',
        allowClear: true
    };

    // Promote modal Select2
    $('#promote_employee_select').select2({
        ...select2Config,
        dropdownParent: $('#promoteEmployeeModal')
    });

    // Offboard modal Select2
    $('#offboard_employee_select').select2({
        ...select2Config,
        dropdownParent: $('#offboardEmployeeModal')
    });

    // Reassign modal Select2
    $('#reassign_employee_select').select2({
        ...select2Config,
        dropdownParent: $('#reassignEmployeeModal')
    });

    // Auto-populate current position when employee is selected (Promote)
    $('#promote_employee_select').on('change', function() {
        const selected = $(this).find(':selected');
        const position = selected.data('position') || 'NOT ASSIGNED';
        document.getElementById('promote_current_position').value = position;
    });

    // Auto-populate current position when employee is selected (Reassign)
    $('#reassign_employee_select').on('change', function() {
        const selected = $(this).find(':selected');
        const position = selected.data('position') || 'NOT ASSIGNED';
        document.getElementById('reassign_current_position').value = position;

        // Update form action with selected employee ID
        const userId = $(this).val();
        const form = document.getElementById('reassignForm');
        form.action = `/hr/employees/${userId}/reassign`;
    });

    // Reset modals on close
    $('#promoteEmployeeModal').on('hidden.bs.modal', function() {
        $('#promote_employee_select').val(null).trigger('change');
        document.getElementById('promote_current_position').value = '';
        document.getElementById('promote_new_position').selectedIndex = 0;
        $(this).find('input[type="date"]').val('');
    });

    $('#offboardEmployeeModal').on('hidden.bs.modal', function() {
        $('#offboard_employee_select').val(null).trigger('change');
        document.getElementById('offboard_reason').selectedIndex = 0;
        document.getElementById('offboard_effective_date').value = '';
    });

    $('#reassignEmployeeModal').on('hidden.bs.modal', function() {
        $('#reassign_employee_select').val(null).trigger('change');
        document.getElementById('reassign_current_position').value = '';
        document.getElementById('reassign_new_position').selectedIndex = 0;
        document.getElementById('reassign_new_department').selectedIndex = 0;
        document.getElementById('reassign_effective_date').value = '';
        document.getElementById('reassign_remarks').value = '';
    });
});
</script>
@endsection
