        <!-- Tab 3: Seminar Approvals -->
        <div class="tab-pane fade" id="seminars" role="tabpanel" aria-labelledby="seminars-tab">
            <div class="card shadow-sm border-0 rounded-3 border-top border-4 border-accent">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-muted">Pending Seminar Claims</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.85rem;">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th class="text-start ps-4">Employee</th>
                                    <th>Seminar Title</th>
                                    <th>Date & Hours</th>
                                    <th>Certificate</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingSeminars ?? [] as $seminar)
                                <tr>
                                    <td class="ps-4 py-3 text-start">
                                        <div class="fw-bold" style="color: #1A3E6F;">{{ $seminar->user->last_name ?? '' }}, {{ $seminar->user->first_name ?? '' }}</div>
                                    </td>
                                    <td class="py-3 fw-bold">{{ $seminar->title }}</td>
                                    <td class="py-3">
                                        <div>{{ \Carbon\Carbon::parse($seminar->date)->format('M d, Y') }}</div>
                                        <div class="small text-muted">{{ $seminar->hours }} hrs</div>
                                    </td>
                                    <td class="py-3">
                                        <a href="{{ Storage::url($seminar->certificate_path) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
                                            <i class="bi bi-file-earmark-text"></i> View
                                        </a>
                                    </td>
                                    <td class="py-3 text-end pe-4">
                                        <form action="{{ route('hr.seminars.approve', $seminar->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success fw-bold me-1">Approve</button>
                                        </form>
                                        <form action="{{ route('hr.seminars.reject', $seminar->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger fw-bold">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No pending seminars in the queue.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
