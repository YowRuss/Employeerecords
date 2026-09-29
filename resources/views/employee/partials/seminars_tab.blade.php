        <!-- Tab: My Seminars -->
        <div class="tab-pane fade" id="seminars" role="tabpanel" aria-labelledby="seminars-tab">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn fw-bold px-4 shadow-sm" style="background-color: #1A3E6F; color: white;" data-bs-toggle="modal" data-bs-target="#claimSeminarModal">
                    <i class="bi bi-plus-circle me-2"></i> Claim Seminar
                </button>
            </div>

            <div class="card shadow-sm border-0 border-top border-4 border-accent mb-5">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-muted">Submitted Seminars</h6>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.85rem;">
                        <thead class="table-light text-muted">
                            <tr>
                                <th>Seminar Title</th>
                                <th>Date</th>
                                <th>Hours</th>
                                <th class="d-none d-md-table-cell">Credits Earned</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mySeminars ?? [] as $seminar)
                            <tr>
                                <td class="align-middle text-start ps-3 fw-bold" style="color: #1A3E6F;">{{ $seminar->title }}</td>
                                <td class="align-middle">{{ \Carbon\Carbon::parse($seminar->date_attended)->format('M d, Y') }}</td>
                                <td class="align-middle">{{ $seminar->hours }} hrs</td>
                                <td class="align-middle d-none d-md-table-cell fw-bold text-success">{{ $seminar->credits_earned ? '+'.number_format($seminar->credits_earned, 2) : '-' }}</td>
                                <td class="align-middle">
                                    @if(strtoupper((string) $seminar->status) === 'APPROVED')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif(strtoupper((string) $seminar->status) === 'REJECTED')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No seminars submitted.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
