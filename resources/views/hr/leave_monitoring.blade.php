@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="text-accent fw-bold m-0"><i class="bi bi-inboxes-fill me-2"></i> Leave Applications Monitoring</h4>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print Report</button>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 border-top border-4 border-accent">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle text-center mb-0" style="font-size: 0.85rem;">
                <thead class="table-light text-muted">
                    <tr>
                        <th class="text-start ps-4">Employee</th>
                        <th>Date Filed</th>
                        <th>Type of Leave</th>
                        <th>Inclusive Dates</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                    <tr>
                        <!-- Employee Info -->
                        <td class="text-start ps-4">
                            <div class="d-flex align-items-center">
                                @if($leave->profile_image)
                                    <img src="data:{{ $leave->image_type }};base64,{{ base64_encode($leave->profile_image) }}" class="rounded-circle me-2 object-fit-cover shadow-sm" style="width: 35px; height: 35px;">
                                @else
                                    <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-2 fw-bold shadow-sm" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                        {{ strtoupper(substr($leave->first_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <span class="d-block fw-bold text-uppercase">{{ $leave->last_name }}, {{ $leave->first_name }}</span>
                                    <small class="text-muted">{{ $leave->position }}</small>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Leave Info -->
                        <td>{{ \Carbon\Carbon::parse($leave->date_of_filing)->format('M d, Y') }}</td>
                        <td class="fw-bold">{{ $leave->leave_type }}</td>
                        <td>{{ $leave->inclusive_dates }}</td>
                        <td>{{ $leave->working_days }}</td>
                        
                        <!-- Status Badge -->
                        <td>
                            @if($leave->status == 'PENDING')
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm">PENDING</span>
                            @elseif($leave->status == 'APPROVED')
                                <span class="badge bg-success px-3 py-2 rounded-pill shadow-sm">APPROVED</span>
                            @else
                                <span class="badge bg-danger px-3 py-2 rounded-pill shadow-sm">DISAPPROVED</span>
                            @endif
                            
                            @if($leave->hr_remarks)
                                <div class="mt-1 small text-muted fst-italic" style="font-size: 0.7rem;">{{ $leave->hr_remarks }}</div>
                            @endif
                        </td>
                        
                        <!-- Action Buttons -->
                        <td>
                            @if($leave->status == 'PENDING')
                                <div class="btn-group shadow-sm">
                                    <!-- Approve Button Triggers Modal -->
                                    <button type="button" class="btn btn-sm btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#approveModal{{ $leave->id }}">
                                        <i class="bi bi-check-circle"></i> Approve
                                    </button>
                                    <!-- Reject Button Triggers Modal -->
                                    <button type="button" class="btn btn-sm btn-danger fw-bold" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leave->id }}">
                                        <i class="bi bi-x-circle"></i> Reject
                                    </button>
                                </div>

                                <!-- ================= APPROVE MODAL ================= -->
                                <div class="modal fade text-start" id="approveModal{{ $leave->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title fw-bold"><i class="bi bi-check-circle-fill me-2"></i> Approve Leave Request</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('hr.leave.approve', $leave->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body p-4">
                                                    <p class="mb-3">You are about to approve <strong>{{ $leave->working_days }} days</strong> of {{ $leave->leave_type }} for <strong>{{ $leave->first_name }} {{ $leave->last_name }}</strong>.</p>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-muted">Approval Remarks / Details</label>
                                                        <input type="text" name="hr_remarks" class="form-control text-uppercase" placeholder="e.g. APPROVED WITH PAY" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">Confirm Approval</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- ================= REJECT MODAL ================= -->
                                <div class="modal fade text-start" id="rejectModal{{ $leave->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Disapprove Leave Request</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('hr.leave.reject', $leave->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body p-4">
                                                    <p class="mb-3">You are rejecting the leave application for <strong>{{ $leave->first_name }} {{ $leave->last_name }}</strong>.</p>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-muted">Reason for Disapproval <span class="text-danger">*</span></label>
                                                        <textarea name="hr_remarks" class="form-control text-uppercase" rows="3" placeholder="e.g. LACK OF LEAVE CREDITS" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger px-4 fw-bold shadow-sm">Confirm Rejection</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small"><i class="bi bi-lock-fill"></i> Processed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">No leave applications currently on record.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection