        <!-- Tab: My Credit Ledger -->
        <div class="tab-pane fade" id="ledger" role="tabpanel" aria-labelledby="ledger-tab">
            <!-- Header Widget -->
            <div class="row mb-4">
                <div class="col-md-5">
                    <div class="card shadow-sm border-0" style="border-left: 5px solid #FDE047 !important;">
                        <div class="card-body py-3">
                            <h6 class="text-muted text-uppercase fw-bold mb-2">Total Leave Credits</h6>
                            <h2 class="fw-bold mb-0" style="color: #1A3E6F;">{{ number_format($currentBalance ?? 0, 2) }} <span class="fs-6 text-muted fw-normal">Credits</span></h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 border-top border-4 border-accent mb-5">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-muted">Credit Ledger</h6>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.85rem;">
                        <thead class="table-light text-muted">
                            <tr>
                                <th>Date</th>
                                <th>Source</th>
                                <th>Amount</th>
                                <th class="d-none d-md-table-cell">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($creditLogs ?? [] as $log)
                            <tr>
                                <td class="align-middle">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}</td>
                                <td class="align-middle"><span class="badge bg-secondary">{{ strtoupper($log->source) }}</span></td>
                                <td class="align-middle fw-bold {{ $log->amount > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $log->amount > 0 ? '+' : '' }}{{ number_format($log->amount, 2) }}
                                </td>
                                <td class="align-middle d-none d-md-table-cell text-muted">{{ $log->remarks ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No credit logs found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
