@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-brand fw-bold m-0"><i class="bi bi-check-circle-fill me-2"></i> Leave Approvals</h4>
            <p class="text-muted small m-0">Review, approve, or deny employee leave applications.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-light border shadow-sm btn-sm fw-bold text-muted px-3">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success shadow-sm border-0 rounded-3">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    </div>
    @endif

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-warning">
                <div class="card-body py-3">
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Pending Requests</h6>
                    <h3 class="fw-bold text-warning mb-0">{{ $stats['pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-success">
                <div class="card-body py-3">
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Approved Leaves</h6>
                    <h3 class="fw-bold text-success mb-0">{{ $stats['approved'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-danger">
                <div class="card-body py-3">
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Denied Leaves</h6>
                    <h3 class="fw-bold text-danger mb-0">{{ $stats['denied'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Applications Ledger -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark m-0">Leave Applications Ledger</h6>
            
            <!-- Filter -->
            <form method="GET" action="{{ route('principal.leaves.index') }}" class="d-flex align-items-center">
                <label class="small text-muted me-2 fw-bold text-nowrap">Filter By:</label>
                <select name="status" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()">
                    <option value="All" {{ request('status') == 'All' ? 'selected' : '' }}>All Requests</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending Only</option>
                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved Only</option>
                    <option value="Denied" {{ request('status') == 'Denied' ? 'selected' : '' }}>Denied Only</option>
                </select>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th class="ps-4">Employee</th>
                            <th class="d-none d-md-table-cell">Leave Details</th>
                            <th class="d-none d-md-table-cell">Dates</th>
                            <th class="d-none d-md-table-cell">Status & Comments</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaves as $leave)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">
                                {{ $leave->last_name }}, {{ $leave->first_name }}
                                
                                <!-- Mobile Only Details -->
                                <div class="d-md-none mt-2 p-2 bg-light rounded-3 border fw-normal">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.75rem;">
                                            {{ $leave->leave_type ?? 'Standard Leave' }}
                                        </span>
                                        @if($leave->status == 'Approved')
                                            <span class="badge bg-success" style="font-size: 0.7rem;"><i class="bi bi-check-circle me-1"></i> Approved</span>
                                        @elseif($leave->status == 'Denied')
                                            <span class="badge bg-danger" style="font-size: 0.7rem;"><i class="bi bi-x-circle me-1"></i> Denied</span>
                                        @else
                                            <span class="badge bg-warning text-dark" style="font-size: 0.7rem;"><i class="bi bi-hourglass-split me-1"></i> Pending</span>
                                        @endif
                                    </div>
                                    <div class="small text-dark mb-1">
                                        <strong>Dates:</strong> {{ $leave->inclusive_dates ?? 'Date not set' }} <span class="text-muted">({{ $leave->working_days }} days)</span>
                                    </div>
                                    <div class="small text-muted text-wrap mb-1">
                                        <strong>Details:</strong> {{ Str::limit($leave->leave_details_specific ?: ($leave->leave_details ?: 'N/A'), 50) }}
                                    </div>
                                    @if($leave->principal_comment)
                                        <div class="small text-muted mt-1 fst-italic border-top pt-1">
                                            "{{ Str::limit($leave->principal_comment, 40) }}"
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-1">
                                    {{ $leave->leave_type ?? 'Standard Leave' }}
                                </span>
                                <div class="small text-muted text-wrap" style="max-width: 250px;">
                                    <strong>Details:</strong> {{ Str::limit($leave->leave_details_specific ?: ($leave->leave_details ?: 'N/A'), 50) }}
                                </div>
                            </td>
                            <td class="small d-none d-md-table-cell">
                                <div class="fw-bold text-dark">{{ $leave->inclusive_dates ?? 'Date not set' }}</div>
                                <div class="text-muted">{{ $leave->working_days }} day(s)</div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if($leave->status == 'Approved')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Approved</span>
                                @elseif($leave->status == 'Denied')
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Denied</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Pending</span>
                                @endif
                                
                                @if($leave->principal_comment)
                                    <div class="small text-muted mt-1 fst-italic">
                                        "{{ Str::limit($leave->principal_comment, 40) }}"
                                    </div>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <!-- Action Button triggers a Modal so the Principal can add a comment before deciding -->
                                <button type="button" class="btn btn-sm btn-light border text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $leave->id }}">
                                    Review
                                </button>
                            </td>
                        </tr>

                        <!-- Review Modal for this Leave Request -->
                        <div class="modal fade" id="reviewModal{{ $leave->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title fw-bold text-dark">Review Leave Application</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('principal.leaves.update', $leave->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="small fw-bold text-muted">Employee</label>
                                                <div class="p-2 bg-light rounded text-dark fw-bold border">{{ $leave->last_name }}, {{ $leave->first_name }}</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="small fw-bold text-muted">Inclusive Dates</label>
                                                <div class="p-2 bg-light rounded text-dark border">{{ $leave->inclusive_dates ?? 'N/A' }} ({{ $leave->working_days }} days)</div>
                                            </div>
                                            <div class="mb-4">
                                                <label class="small fw-bold text-muted">Leave Details</label>
                                                <div class="p-3 bg-light rounded text-dark small border">{{ $leave->leave_details_specific ?: ($leave->leave_details ?: 'None provided.') }}</div>
                                            </div>

                                            <hr class="text-muted opacity-25">

                                            <div class="mb-3">
                                                <label class="small fw-bold text-brand text-uppercase tracking-wide">Principal's Decision <span class="text-danger">*</span></label>
                                                <select name="status" class="form-select form-select-lg" required>
                                                    <option value="" disabled {{ $leave->status == 'Pending' ? 'selected' : '' }}>Select action...</option>
                                                    <option value="Approved" {{ $leave->status == 'Approved' ? 'selected' : '' }}>Approve Leave</option>
                                                    <option value="Denied" {{ $leave->status == 'Denied' ? 'selected' : '' }}>Deny Leave</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="small fw-bold text-muted">Remarks / Comments (Optional)</label>
                                                <textarea name="principal_comment" class="form-control" rows="2" placeholder="Explain your decision...">{{ $leave->principal_comment }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light border-top-0">
                                            <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary fw-bold px-4">Save Decision</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No leave applications found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection