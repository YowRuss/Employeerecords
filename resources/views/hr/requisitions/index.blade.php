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
{{-- PROMOTE EMPLOYEE MODAL --}}
{{-- ============================================ --}}
<div class="modal fade" id="promoteEmployeeModal" tabindex="-1" aria-labelledby="promoteEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="promoteEmployeeModalLabel" style="color: #1A3E6F;">Promote Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('hr.promote_employee') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold text-muted small text-uppercase">Select Employee <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="hidden" name="user_id" id="promote_employee_id" required>
                                <input type="text" id="promote_employee_name" class="form-control bg-light" readonly placeholder="Click 'Browse' to select an employee..." required>
                                <button type="button" class="btn fw-bold btn-browse-employee shadow-sm" style="background-color: var(--accent-yellow, #FDE047); color: #1A3E6F; border-color: #EAB308;" data-target-id="promote_employee_id" data-target-name="promote_employee_name" data-target-action="promote">
                                    <i class="bi bi-search me-1"></i> Browse
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Date From <span class="text-danger">*</span></label>
                            <input type="date" name="date_from" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Date To <span class="text-danger">*</span></label>
                            <input type="text" name="date_to" class="form-control text-uppercase" placeholder="YYYY-MM-DD or Present" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Designation <span class="text-danger">*</span></label>
                            <select name="designation" id="promote_designation_select" class="form-select text-uppercase" required>
                                <option value="" disabled selected>Select Position...</option>
                                @foreach($positions as $position)
                                <option value="{{ $position->position_name }}">{{ $position->position_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Status <span class="text-danger">*</span></label>
                            <input type="text" name="status" class="form-control text-uppercase" placeholder="e.g. Perm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Salary <span class="text-danger">*</span></label>
                            <input type="text" name="salary" class="form-control" placeholder="e.g. 239,280.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Station/Place <span class="text-danger">*</span></label>
                            <input type="text" name="station_place" class="form-control text-uppercase" placeholder="-do- or location name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Branch</label>
                            <input type="text" name="branch" class="form-control text-uppercase" placeholder="-do- or Nat'l">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Leave w/out pay</label>
                            <input type="text" name="leave_without_pay" class="form-control text-uppercase" placeholder="None or -do-">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Separation Date</label>
                            <input type="text" name="separation_date" class="form-control text-uppercase">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Separation Cause</label>
                            <input type="text" name="separation_cause" class="form-control text-uppercase" placeholder="None or NBC 562">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-end border-top-0">
                    <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn fw-bold px-4 shadow-sm" style="background-color: var(--accent-yellow, #FDE047); color: #1A3E6F;">Confirm Promotion</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- OFFBOARD EMPLOYEE MODAL --}}
{{-- ============================================ --}}
<div class="modal fade" id="offboardEmployeeModal" tabindex="-1" aria-labelledby="offboardEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-danger" id="offboardEmployeeModalLabel">Offboard Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('hr.offboard_employee') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold text-muted small text-uppercase">Select Employee <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="hidden" name="user_id" id="offboard_employee_id" required>
                                <input type="text" id="offboard_employee_name" class="form-control bg-light" readonly placeholder="Click 'Browse' to select an employee..." required>
                                <button type="button" class="btn fw-bold btn-browse-employee shadow-sm" style="background-color: var(--accent-yellow, #FDE047); color: #1A3E6F; border-color: #EAB308;" data-target-id="offboard_employee_id" data-target-name="offboard_employee_name" data-target-action="offboard">
                                    <i class="bi bi-search me-1"></i> Browse
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Date From <span class="text-danger">*</span></label>
                            <input type="date" name="date_from" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Date To <span class="text-danger">*</span></label>
                            <input type="text" name="date_to" class="form-control text-uppercase" placeholder="YYYY-MM-DD or Present" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Designation <span class="text-danger">*</span></label>
                            <select name="designation" id="offboard_designation_select" class="form-select text-uppercase" required>
                                <option value="" disabled selected>Select Position...</option>
                                @foreach($positions as $position)
                                <option value="{{ $position->position_name }}">{{ $position->position_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Status <span class="text-danger">*</span></label>
                            <input type="text" name="status" class="form-control text-uppercase" placeholder="e.g. Perm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Salary <span class="text-danger">*</span></label>
                            <input type="text" name="salary" class="form-control" placeholder="e.g. 239,280.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Station/Place <span class="text-danger">*</span></label>
                            <input type="text" name="station_place" class="form-control text-uppercase" placeholder="-do- or location name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Branch</label>
                            <input type="text" name="branch" class="form-control text-uppercase" placeholder="-do- or Nat'l">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Leave w/out pay</label>
                            <input type="text" name="leave_without_pay" class="form-control text-uppercase" placeholder="None or -do-">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Separation Date <span class="text-danger">*</span></label>
                            <input type="date" name="separation_date" class="form-control text-uppercase" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Separation Cause <span class="text-danger">*</span></label>
                            <select name="separation_cause" id="offboard_separation_cause" class="form-select" required>
                                <option value="" disabled selected>Select Reason...</option>
                                <option value="Resignation">Resignation</option>
                                <option value="Retirement">Retirement</option>
                                <option value="Termination">Termination</option>
                                <option value="Transfer to other agency">Transfer to other agency</option>
                                <option value="End of Contract">End of Contract</option>
                                <option value="Death">Death</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-end border-top-0">
                    <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4 shadow-sm">Confirm Offboarding</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- REASSIGN EMPLOYEE MODAL --}}
{{-- ============================================ --}}
<div class="modal fade" id="reassignEmployeeModal" tabindex="-1" aria-labelledby="reassignEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" style="color: #1A3E6F;" id="reassignEmployeeModalLabel">Reassign Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reassignForm" action="{{ route('hr.reassign_employee', ['id' => 0]) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted small fw-bold text-uppercase">Select Employee <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="hidden" name="user_id_select" id="reassign_employee_id" required>
                                <input type="text" id="reassign_employee_name" class="form-control bg-light" readonly placeholder="Click 'Browse' to select an employee..." required>
                                <button type="button" class="btn fw-bold btn-browse-employee shadow-sm" style="background-color: var(--accent-yellow, #FDE047); color: #1A3E6F; border-color: #EAB308;" data-target-id="reassign_employee_id" data-target-name="reassign_employee_name" data-target-action="reassign">
                                    <i class="bi bi-search me-1"></i> Browse
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Date From <span class="text-danger">*</span></label>
                            <input type="date" name="date_from" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Date To <span class="text-danger">*</span></label>
                            <input type="text" name="date_to" class="form-control text-uppercase" placeholder="YYYY-MM-DD or Present" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Designation <span class="text-danger">*</span></label>
                            <select name="designation" id="reassign_designation_select" class="form-select text-uppercase" required>
                                <option value="" disabled selected>Select Position...</option>
                                @foreach($positions as $position)
                                <option value="{{ $position->position_name }}">{{ $position->position_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Status <span class="text-danger">*</span></label>
                            <input type="text" name="status" class="form-control text-uppercase" placeholder="e.g. Perm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Salary <span class="text-danger">*</span></label>
                            <input type="text" name="salary" class="form-control" placeholder="e.g. 239,280.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Station/Place <span class="text-danger">*</span></label>
                            <input type="text" name="station_place" class="form-control text-uppercase" placeholder="-do- or location name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Branch</label>
                            <select class="form-select text-uppercase" name="branch" id="reassign_branch_select">
                                <option value="">Select department...</option>
                                @foreach($learningAreas as $area)
                                    <option value="{{ $area->name }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Leave w/out pay</label>
                            <input type="text" name="leave_without_pay" class="form-control text-uppercase" placeholder="None or -do-">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Separation Date</label>
                            <input type="text" name="separation_date" class="form-control text-uppercase">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Separation Cause</label>
                            <input type="text" name="separation_cause" class="form-control text-uppercase" placeholder="None or NBC 562">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-end border-top-0">
                    <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn fw-bold px-4 shadow-sm" style="background-color: #FDE047; color: #1F2937; border-color: #EAB308;">Confirm Reassignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- DEDICATED EMPLOYEE SELECTION MODAL (AJAX) --}}
{{-- ============================================ --}}
<div class="modal fade" id="employeeSelectionModal" tabindex="-1" aria-labelledby="employeeSelectionModalLabel" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="employeeSelectionModalLabel" style="color: #1A3E6F;">
                    <i class="bi bi-people-fill me-2"></i> Select Employee
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Category Nav Tabs --}}
                <ul class="nav nav-pills mb-3 gap-2" id="empModalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold px-4 py-2" id="emp-tab-teaching" type="button" data-category="teaching">
                            <i class="bi bi-mortarboard-fill me-1"></i> Teaching
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold px-4 py-2" id="emp-tab-non-teaching" type="button" data-category="non-teaching">
                            <i class="bi bi-briefcase-fill me-1"></i> Non-Teaching
                        </button>
                    </li>
                </ul>

                {{-- Search Filter Input --}}
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="search" id="emp-search-input" class="form-control border-start-0 ps-0" placeholder="Search employee by name or position...">
                    </div>
                </div>

                {{-- Content Results Container --}}
                <div id="employee-results" class="position-relative" style="min-height: 220px;">
                    <!-- Dynamically populated via AJAX -->
                </div>

                {{-- Pagination Links Container --}}
                <div id="employee-pagination" class="d-flex justify-content-center mt-3">
                    <!-- Dynamically populated via AJAX -->
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 justify-content-end">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    #empModalTabs .nav-link {
        color: #1A3E6F;
        border-radius: 8px;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    #empModalTabs .nav-link.active {
        background-color: #FDE047 !important;
        color: #1F2937 !important;
        border-color: #EAB308 !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Helper function to initialize Select2 with Bootstrap 5 theme inside modals
    function initSearchableDropdown(selector, modalId, placeholderText) {
        $(selector).select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: placeholderText,
            allowClear: true,
            dropdownParent: $(modalId)
        });
    }

    // Initialize Select2 dropdowns for position and department fields
    initSearchableDropdown('#promote_designation_select', '#promoteEmployeeModal', 'Search & select position...');
    initSearchableDropdown('#offboard_designation_select', '#offboardEmployeeModal', 'Search & select position...');
    initSearchableDropdown('#offboard_separation_cause', '#offboardEmployeeModal', 'Search & select reason...');
    initSearchableDropdown('#reassign_designation_select', '#reassignEmployeeModal', 'Search & select position...');
    initSearchableDropdown('#reassign_branch_select', '#reassignEmployeeModal', 'Search & select department...');

    // =========================================================================
    // Employee Selection Modal State & AJAX Fetching
    // =========================================================================
    let currentCategory = 'teaching';
    let searchQuery = '';
    let currentPage = 1;
    let activeTargetId = null;
    let activeTargetName = null;
    let activeTargetAction = null;
    let activeParentModal = null;
    let isBrowsing = false;
    let debounceTimer = null;

    // When "Browse" button is clicked from any parent modal
    $(document).on('click', '.btn-browse-employee', function(e) {
        e.preventDefault();
        activeTargetId = $(this).data('target-id');
        activeTargetName = $(this).data('target-name');
        activeTargetAction = $(this).data('target-action');
        activeParentModal = $(this).closest('.modal');
        isBrowsing = true;
        
        // Reset search & pagination
        $('#emp-search-input').val('');
        searchQuery = '';
        currentPage = 1;

        // Hide parent modal and open selection modal
        if (activeParentModal.length) {
            bootstrap.Modal.getOrCreateInstance(activeParentModal[0]).hide();
        }

        setTimeout(function() {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('employeeSelectionModal')).show();
            fetchEmployees();
        }, 150);
    });

    // Handle Category Tab switching
    $('#empModalTabs button').on('click', function() {
        $('#empModalTabs button').removeClass('active');
        $(this).addClass('active');
        currentCategory = $(this).data('category');
        currentPage = 1;
        fetchEmployees();
    });

    // Handle Search Input with Debounce
    $('#emp-search-input').on('input', function() {
        clearTimeout(debounceTimer);
        searchQuery = $(this).val().trim();
        currentPage = 1;
        debounceTimer = setTimeout(function() {
            fetchEmployees();
        }, 300);
    });

    // Handle Pagination Clicks
    $(document).on('click', '#employee-pagination .page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page && page !== currentPage) {
            currentPage = page;
            fetchEmployees();
        }
    });

    // Fetch Employees AJAX Request
    function fetchEmployees() {
        $('#employee-results').html(`
            <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                <div class="spinner-border text-warning mb-2" role="status"></div>
                <small>Loading employees...</small>
            </div>
        `);

        $.ajax({
            url: "{{ route('api.employees.get') }}",
            type: "GET",
            data: {
                category: currentCategory,
                search: searchQuery,
                page: currentPage
            },
            dataType: "json",
            success: function(response) {
                console.log('Employee API success:', response);
                renderEmployeeTable(response);
            },
            error: function(xhr, status, error) {
                console.error('Employee API error:', status, error);
                console.error('Response status:', xhr.status);
                console.error('Response text:', xhr.responseText);
                $('#employee-results').html(`
                    <div class="alert alert-danger my-3 text-center">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Failed to load employees. Please try again.
                        <br><small class="text-muted">Status: ${xhr.status} — Check browser console for details.</small>
                    </div>
                `);
                $('#employee-pagination').html('');
            }
        });
    }

    // Render Employee Table
    function renderEmployeeTable(response) {
        const data = response.data || [];
        if (data.length === 0) {
            $('#employee-results').html(`
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-person-x fs-1 d-block mb-2 opacity-50"></i>
                    <p class="mb-0 fw-semibold">No employees found matching criteria.</p>
                </div>
            `);
            $('#employee-pagination').html('');
            return;
        }

        let html = `
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-uppercase text-muted">
                            <th class="ps-3">Employee Name</th>
                            <th>Current Position</th>
                            <th>Department</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        data.forEach(emp => {
            const fullName = `${emp.last_name}, ${emp.first_name} ${emp.middle_name ? emp.middle_name : ''}`.trim();
            const posName = emp.position ? emp.position.position_name : 'NOT ASSIGNED';
            const deptName = emp.learning_area ? emp.learning_area.name : '—';

            html += `
                <tr>
                    <td class="ps-3">
                        <div class="fw-bold text-dark text-uppercase">${fullName}</div>
                        <small class="text-muted">ID: ${String(emp.id).padStart(4, '0')}</small>
                    </td>
                    <td><span class="badge bg-light text-dark border">${posName}</span></td>
                    <td><small class="text-muted">${deptName}</small></td>
                    <td class="text-end pe-3">
                        <button type="button" class="btn btn-sm fw-bold px-3 btn-select-emp shadow-sm" style="background-color: var(--accent-yellow, #FDE047); color: #1A3E6F; border-color: #EAB308;"
                            data-id="${emp.id}" 
                            data-name="${fullName}" 
                            data-position="${posName}">
                            <i class="bi bi-check2 me-1"></i> Select
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `</tbody></table></div>`;
        $('#employee-results').html(html);

        renderPagination(response);
    }

    // Render Pagination Controls
    function renderPagination(response) {
        if (!response.last_page || response.last_page <= 1) {
            $('#employee-pagination').html('');
            return;
        }

        let paginationHtml = '<nav><ul class="pagination pagination-sm mb-0">';

        // Prev
        if (response.current_page > 1) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${response.current_page - 1}">&laquo; Prev</a></li>`;
        } else {
            paginationHtml += `<li class="page-item disabled"><span class="page-link">&laquo; Prev</span></li>`;
        }

        // Page Numbers
        for (let i = 1; i <= response.last_page; i++) {
            if (i === 1 || i === response.last_page || (i >= response.current_page - 1 && i <= response.current_page + 1)) {
                if (i === response.current_page) {
                    paginationHtml += `<li class="page-item active"><span class="page-link bg-warning text-dark border-warning fw-bold">${i}</span></li>`;
                } else {
                    paginationHtml += `<li class="page-item"><a class="page-link text-dark" href="#" data-page="${i}">${i}</a></li>`;
                }
            } else if (i === response.current_page - 2 || i === response.current_page + 2) {
                paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        // Next
        if (response.current_page < response.last_page) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${response.current_page + 1}">Next &raquo;</a></li>`;
        } else {
            paginationHtml += `<li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>`;
        }

        paginationHtml += '</ul></nav>';
        $('#employee-pagination').html(paginationHtml);
    }

    // When an Employee is Selected from the Modal
    $(document).on('click', '.btn-select-emp', function() {
        const empId = $(this).data('id');
        const empName = $(this).data('name');
        const empPosition = $(this).data('position');

        if (activeTargetId) {
            $('#' + activeTargetId).val(empId);
        }
        if (activeTargetName) {
            $('#' + activeTargetName).val(empName);
        }

        // Perform action-specific mappings
        if (activeTargetAction === 'offboard') {
            if (empPosition && empPosition !== 'NOT ASSIGNED') {
                $('#offboard_designation_select').val(empPosition).trigger('change');
            }
        } else if (activeTargetAction === 'reassign') {
            const form = document.getElementById('reassignForm');
            if (form) {
                form.action = `/hr/employees/${empId}/reassign`;
            }
        }

        // Close Employee Selection Modal cleanly (triggering hidden.bs.modal return)
        const modalEl = document.getElementById('employeeSelectionModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) {
            modalInstance.hide();
        }
    });

    // When Employee Selection Modal closes, return user to original parent modal
    $('#employeeSelectionModal').on('hidden.bs.modal', function() {
        if (activeParentModal && activeParentModal.length && isBrowsing) {
            setTimeout(function() {
                bootstrap.Modal.getOrCreateInstance(activeParentModal[0]).show();
                isBrowsing = false;
            }, 150);
        } else {
            isBrowsing = false;
        }
    });

    // Modal Initializations and Form Resets
    $('#promoteEmployeeModal').on('show.bs.modal', function() {
        if (!$(this).find('input[name="status"]').val()) {
            $(this).find('input[name="status"]').val('PROMOTED');
            $(this).find('input[name="date_to"]').val('PRESENT');
            $(this).find('input[name="station_place"]').val('CNHS-JHS');
            $(this).find('input[name="leave_without_pay"]').val('NONE');
            $(this).find('input[name="separation_cause"]').val('NONE');
        }
    }).on('hidden.bs.modal', function() {
        if (isBrowsing) return;
        $(this).find('form')[0].reset();
        $('#promote_employee_id').val('');
        $('#promote_employee_name').val('');
        $('#promote_designation_select').val(null).trigger('change');
    });

    $('#offboardEmployeeModal').on('show.bs.modal', function() {
        if (!$(this).find('input[name="status"]').val()) {
            $(this).find('input[name="status"]').val('SEPARATED');
            $(this).find('input[name="station_place"]').val('CNHS-JHS');
            $(this).find('input[name="leave_without_pay"]').val('NONE');
        }
    }).on('hidden.bs.modal', function() {
        if (isBrowsing) return;
        $(this).find('form')[0].reset();
        $('#offboard_employee_id').val('');
        $('#offboard_employee_name').val('');
        $('#offboard_designation_select').val(null).trigger('change');
        $('#offboard_separation_cause').val(null).trigger('change');
    });

    $('#reassignEmployeeModal').on('show.bs.modal', function() {
        if (!$(this).find('input[name="status"]').val()) {
            $(this).find('input[name="status"]').val('REASSIGNED');
            $(this).find('input[name="date_to"]').val('PRESENT');
            $(this).find('input[name="station_place"]').val('CNHS-JHS');
            $(this).find('input[name="leave_without_pay"]').val('NONE');
            $(this).find('input[name="separation_cause"]').val('NONE');
        }
    }).on('hidden.bs.modal', function() {
        if (isBrowsing) return;
        $(this).find('form')[0].reset();
        $('#reassign_employee_id').val('');
        $('#reassign_employee_name').val('');
        $('#reassign_designation_select').val(null).trigger('change');
        $('#reassign_branch_select').val(null).trigger('change');
    });
});
</script>
@endsection
