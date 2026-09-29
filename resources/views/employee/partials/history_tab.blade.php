@php
    $leaveRequests = $leaveRequests ?? $leaves ?? [];
@endphp

<div class="tab-pane fade show active" id="history" role="tabpanel" aria-labelledby="history-tab">
    <!-- RECENT LEAVE APPLICATIONS TABLE -->
    <div class="card shadow-sm border-0 border-top border-4 border-accent mb-5">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-muted"><i class="bi bi-clock-history me-1"></i> My Leave History</h6>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
        </div>
        <div class="card-body p-0 table-responsive">
            <!-- Desktop & Tablet Table View -->
            <table class="table table-hover align-middle text-center mb-0 d-none d-md-table" style="font-size: 0.85rem;">
                <thead class="table-light text-muted">
                    <tr>
                        <th>Date Filed</th>
                        <th>Type of Leave</th>
                        <th class="d-none d-md-table-cell">Inclusive Dates</th>
                        <th class="d-none d-md-table-cell">Days</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveRequests as $leave)
                    <tr>
                        <td class="align-middle">{{ \Carbon\Carbon::parse($leave->date_of_filing)->format('M d, Y') }}</td>
                        <td class="text-start align-middle">
                            <div class="fw-bold">{{ $leave->leave_type }}</div>
                            <div class="d-md-none mt-1">
                                <span class="badge bg-light text-dark border mb-1 d-block text-wrap text-start">Dates: {{ $leave->inclusive_dates }}</span>
                                <span class="badge bg-light text-dark border">Days: {{ $leave->working_days }}</span>
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell align-middle">{{ $leave->inclusive_dates }}</td>
                        <td class="d-none d-md-table-cell align-middle">{{ $leave->working_days }}</td>
                        <td class="align-middle">
                            @if($leave->status == 'PENDING')
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">PENDING</span>
                            @elseif($leave->status == 'APPROVED')
                            <span class="badge bg-success px-3 py-2 rounded-pill">APPROVED</span>
                            @else
                            <span class="badge bg-danger px-3 py-2 rounded-pill">DISAPPROVED</span>
                            @endif

                            @if($leave->status == 'DISAPPROVED' && $leave->hr_remarks)
                            <div class="alert alert-danger mt-2 py-1 px-2 small mb-0 text-start text-wrap" style="font-size: 0.7rem; max-width: 150px; margin: 0 auto;">
                                <strong>Reason:</strong> {{ $leave->hr_remarks }}
                            </div>
                            @elseif($leave->hr_remarks)
                            <div class="mt-1 small text-muted fst-italic text-wrap" style="font-size: 0.7rem; max-width: 150px; margin: 0 auto;">{{ $leave->hr_remarks }}</div>
                            @endif
                        </td>
                        <td class="align-middle">
                            @if($leave->status == 'PENDING')
                            <form action="{{ route('leave.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Cancel this pending leave application?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Cancel Leave"><i class="bi bi-x-circle d-md-none"></i><span class="d-none d-md-inline"><i class="bi bi-x-circle me-1"></i>Cancel</span></button>
                            </form>
                            @elseif(in_array($leave->status, ['APPROVED', 'DISAPPROVED']))
                            <a href="{{ route('leave.export_pdf', $leave->id) }}" class="btn btn-sm" style="background-color: #fff; color: #1A3E6F; border: 1px solid #1A3E6F;" title="Download PDF">
                                <i class="bi bi-file-earmark-pdf"></i><span class="d-none d-md-inline ms-1">Download PDF</span>
                            </a>
                            @else
                            <span class="text-muted small"><i class="bi bi-lock-fill"></i> <span class="d-none d-md-inline">Locked</span></span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No leave applications found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Mobile Card-based View -->
            <div class="d-block d-md-none p-3 bg-light-subtle">
                @forelse($leaveRequests as $leave)
                <div class="card shadow-sm mb-3 border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom-0 pt-3">
                        <h6 class="fw-bold mb-0 text-dark">{{ $leave->leave_type }}</h6>
                        <div>
                            @if($leave->status == 'PENDING')
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">PENDING</span>
                            @elseif($leave->status == 'APPROVED')
                            <span class="badge bg-success px-3 py-2 rounded-pill">APPROVED</span>
                            @else
                            <span class="badge bg-danger px-3 py-2 rounded-pill">DISAPPROVED</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body pt-1 pb-2">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <span class="text-muted small d-block">Date Filed</span>
                                <span class="fw-semibold small text-dark">
                                    {{ \Carbon\Carbon::parse($leave->date_of_filing)->format('M d, Y') }}
                                </span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Days</span>
                                <span class="fw-semibold small text-dark">
                                    {{ $leave->working_days }} {{ Str::plural('Day', $leave->working_days) }}
                                </span>
                            </div>
                            <div class="col-12 mt-1">
                                <span class="text-muted small d-block">Dates Requested</span>
                                <span class="fw-semibold small text-dark">
                                    <i class="bi bi-calendar-range me-1 text-muted"></i>{{ $leave->inclusive_dates }}
                                </span>
                            </div>
                        </div>

                        @if($leave->status == 'DISAPPROVED' && $leave->hr_remarks)
                        <div class="alert alert-danger py-1 px-2 mt-2 mb-0 small">
                            <strong>Reason:</strong> {{ $leave->hr_remarks }}
                        </div>
                        @elseif($leave->hr_remarks)
                        <div class="alert alert-secondary py-1 px-2 mt-2 mb-0 small">
                            <strong>Remarks:</strong> {{ $leave->hr_remarks }}
                        </div>
                        @endif
                    </div>
                    <div class="card-footer bg-white border-top-0 pb-3 d-flex justify-content-end gap-2">
                        @if($leave->status == 'PENDING')
                        <form action="{{ route('leave.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Cancel this pending leave application?');" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel Request">
                                <i class="bi bi-x-circle me-1"></i> Cancel Request
                            </button>
                        </form>
                        @elseif(in_array($leave->status, ['APPROVED', 'DISAPPROVED']))
                        <a href="{{ route('leave.export_pdf', $leave->id) }}" class="btn btn-sm" style="background-color: #fff; color: #1A3E6F; border: 1px solid #1A3E6F;" title="Download PDF">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                        </a>
                        @else
                        <span class="text-muted small align-self-center"><i class="bi bi-lock-fill me-1"></i> Locked</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                    No leave applications found.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
