@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-accent fw-bold m-0">Review Leave Application</h4>
            <a href="{{ route('leaves.monitor') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="card p-0 overflow-hidden mb-4 border border-secondary shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-light text-center border-bottom border-secondary fw-bold" style="border-radius: 0;">
                DETAILS OF APPLICATION (CSC Form No. 6)
            </div>
            
            <div class="card-body p-4">
                <h5 class="text-accent fw-bold mb-4 border-bottom pb-2">Applicant: {{ $leave->full_name }}</h5>

                <div class="row mb-3">
                    <div class="col-md-5 fw-bold text-muted small">Type of Leave:</div>
                    <div class="col-md-7 fw-bold">{{ $leave->leave_type }} {{ $leave->leave_type_others ? '('.$leave->leave_type_others.')' : '' }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-5 fw-bold text-muted small">Specific Details:</div>
                    <div class="col-md-7">{{ $leave->details_of_leave ?? 'N/A' }} {{ $leave->details_of_leave_specify ? ' - ' . $leave->details_of_leave_specify : '' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-5 fw-bold text-muted small">Number of Working Days:</div>
                    <div class="col-md-7">{{ $leave->working_days }} days</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-5 fw-bold text-muted small">Inclusive Dates:</div>
                    <div class="col-md-7">
                        {{ \Carbon\Carbon::parse($leave->start_date)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($leave->end_date)->format('F d, Y') }}
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-5 fw-bold text-muted small">Commutation:</div>
                    <div class="col-md-7">{{ $leave->commutation }}</div>
                </div>

                <div class="alert bg-light border text-center p-2 mb-0">
                    <span class="small text-muted">Submitted on: {{ \Carbon\Carbon::parse($leave->created_at)->format('M d, Y h:i A') }}</span>
                </div>
            </div>

            <div class="card-footer bg-white border-top border-secondary p-4">
                
                <h6 class="fw-bold mb-3">Application Status: 
                    @if($leave->status == 'Pending')
                        <span class="text-warning"><i class="bi bi-clock-history"></i> Pending HR Verification</span>
                    @elseif($leave->status == 'HR Approved')
                        <span class="text-info"><i class="bi bi-person-check-fill"></i> HR Verified. Pending Principal Approval</span>
                    @elseif($leave->status == 'Approved')
                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> Fully Approved</span>
                    @else
                        <span class="text-danger"><i class="bi bi-x-circle-fill"></i> Rejected</span>
                    @endif
                </h6>

                @if($leave->status == 'Pending' && $role_id == 2)
                    <div class="d-flex gap-2 mt-4">
                        <button type="button" class="btn btn-info w-50 fw-bold text-white" data-bs-toggle="modal" data-bs-target="#approveModal">
                            Verify & Forward to Principal
                        </button>
                        <button type="button" class="btn btn-danger w-50 fw-bold" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            Reject Application
                        </button>
                    </div>

                @elseif($leave->status == 'HR Approved' && $role_id == 4)
                    <div class="d-flex gap-2 mt-4">
                        <button type="button" class="btn btn-success w-50 fw-bold" data-bs-toggle="modal" data-bs-target="#approveModal">
                            Grant Final Approval
                        </button>
                        <button type="button" class="btn btn-danger w-50 fw-bold" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            Reject Application
                        </button>
                    </div>

                @elseif($leave->status == 'Pending' && $role_id == 4)
                    <div class="alert alert-warning text-center mt-3 mb-0">
                        <i class="bi bi-info-circle"></i> This application is waiting for HR verification before you can approve it.
                    </div>
                @endif

            </div>
        </div>

    </div>
</div>

<div class="modal fade text-start" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header {{ $role_id == 2 ? 'bg-info' : 'bg-success' }} text-white border-0">
                <h5 class="modal-title fw-bold" id="approveModalLabel">
                    <i class="bi bi-check-circle-fill me-2"></i> Confirm Approval
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0 fs-5 text-center">
                    @if($role_id == 2)
                        Are you sure you want to <strong>verify</strong> this leave and forward it to the Principal?
                    @elseif($role_id == 4)
                        Are you sure you want to grant <strong>final approval</strong> for this leave request?
                    @endif
                </p>
            </div>
            <div class="modal-footer border-0 bg-light d-flex justify-content-center">
                <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('leaves.update_status', $leave->id) }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="status" value="Approved">
                    <button type="submit" class="btn {{ $role_id == 2 ? 'btn-info text-white' : 'btn-success' }} fw-bold px-4">Yes, Proceed</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade text-start" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold" id="rejectModalLabel">
                    <i class="bi bi-x-circle-fill me-2"></i> Confirm Rejection
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0 fs-5 text-center">Are you sure you want to <strong class="text-danger">REJECT</strong> this leave application?</p>
            </div>
            <div class="modal-footer border-0 bg-light d-flex justify-content-center">
                <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('leaves.update_status', $leave->id) }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="status" value="Rejected">
                    <button type="submit" class="btn btn-danger fw-bold px-4">Yes, Reject Leave</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection