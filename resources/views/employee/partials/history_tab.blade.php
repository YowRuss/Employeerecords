        <div class="tab-pane fade show active" id="history" role="tabpanel" aria-labelledby="history-tab">
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print / Export</button>
            </div>

    <!-- RECENT LEAVE APPLICATIONS TABLE -->
    <div class="card shadow-sm border-0 border-top border-4 border-accent mb-5">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold text-muted">My Leave History</h6>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle text-center mb-0" style="font-size: 0.85rem;">
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
                    @forelse($leaves ?? [] as $leave)
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

                            <!-- ADD THIS SO THE EMPLOYEE SEES HR's COMMENT -->
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
        </div>
    </div>
        </div>
