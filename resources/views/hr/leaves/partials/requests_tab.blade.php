        <!-- Tab 1: Leave Requests -->
        <div class="tab-pane fade show active" id="requests" role="tabpanel" aria-labelledby="requests-tab">
            <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-warning">
                <div class="card-body py-3">
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Pending Requests</h6>
                    <h3 class="fw-bold text-warning mb-0">{{ $stats['pending'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-success">
                <div class="card-body py-3">
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Approved Leaves</h6>
                    <h3 class="fw-bold text-success mb-0">{{ $stats['approved'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-danger">
                <div class="card-body py-3">
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Denied Leaves</h6>
                    <h3 class="fw-bold text-danger mb-0">{{ $stats['denied'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Applications Ledger -->
    <div class="card shadow-sm border-0">
        <style>
            #requests .ledger-tabs .nav-link {
                border: none;
                color: #1A3E6F;
                font-weight: 600;
                background: transparent;
                border-radius: 0;
                padding: 0.85rem 1.25rem;
                opacity: 0.7;
                border-bottom: 3px solid transparent;
            }
            #requests .ledger-tabs .nav-link:hover {
                opacity: 1;
                border-color: rgba(253, 224, 71, 0.5);
            }
            #requests .ledger-tabs .nav-link.active {
                background-color: var(--accent-yellow, #FDE047);
                color: #1A3E6F;
                opacity: 1;
                border-color: var(--accent-yellow, #FDE047);
                border-radius: 8px 8px 0 0;
            }
            #requests .sex-pills .nav-link {
                border-radius: 6px;
                padding: 0.38rem 1.1rem;
                font-size: 0.82rem;
                font-weight: 600;
                color: #1A3E6F;
                background-color: #f1f5f9;
                border: 1px solid #e2e8f0;
            }
            #requests .sex-pills .nav-link:hover {
                background-color: #e2e8f0;
                color: #1A3E6F;
            }
            #requests .sex-pills .nav-link.active {
                background-color: var(--accent-yellow, #FDE047) !important;
                color: #1A3E6F !important;
                border-color: var(--accent-yellow, #FDE047) !important;
                font-weight: 700;
                box-shadow: 0 2px 5px rgba(253, 224, 71, 0.45);
            }
        </style>
        @php
            $ledgerCategory = in_array(request('category'), ['teaching', 'non-teaching'], true) ? request('category') : '';
            $ledgerSex = in_array(request('sex'), ['male', 'female'], true) ? request('sex') : '';
            $ledgerStatus = request('status');
            $ledgerUrl = function (array $overrides) use ($ledgerCategory, $ledgerSex, $ledgerStatus) {
                $params = [
                    'category' => array_key_exists('category', $overrides) ? $overrides['category'] : $ledgerCategory,
                    'sex' => array_key_exists('sex', $overrides) ? $overrides['sex'] : $ledgerSex,
                    'status' => array_key_exists('status', $overrides) ? $overrides['status'] : $ledgerStatus,
                ];
                $params = array_filter($params, fn ($value) => $value !== null && $value !== '' && ! in_array($value, ['all', 'All'], true));

                return route('hr.leave.index', $params);
            };
        @endphp
        <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <h6 class="fw-bold text-dark m-0">Leave Applications Ledger</h6>

            <form method="GET" action="{{ route('hr.leave.index') }}" class="d-flex align-items-center">
                @if($ledgerCategory !== '')
                    <input type="hidden" name="category" value="{{ $ledgerCategory }}">
                @endif
                @if($ledgerSex !== '')
                    <input type="hidden" name="sex" value="{{ $ledgerSex }}">
                @endif
                <label class="small text-muted me-2 fw-bold text-nowrap">Filter By:</label>
                <select name="status" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()">
                    <option value="All" {{ request('status') == 'All' || request('status') == '' ? 'selected' : '' }}>All Requests</option>
                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pending Only</option>
                    <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>Approved Only</option>
                    <option value="DISAPPROVED" {{ request('status') == 'DISAPPROVED' ? 'selected' : '' }}>Denied Only</option>
                </select>
            </form>
        </div>

        <ul class="nav nav-tabs ledger-tabs border-bottom-0 px-2 pt-2" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $ledgerCategory === '' ? 'active' : '' }}" href="{{ $ledgerUrl(['category' => '']) }}">
                    All Employees <span class="badge bg-white text-dark ms-1">{{ $ledgerCounts['all'] ?? 0 }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $ledgerCategory === 'teaching' ? 'active' : '' }}" href="{{ $ledgerUrl(['category' => 'teaching']) }}">
                    Teaching Positions <span class="badge bg-white text-dark ms-1">{{ $ledgerCounts['teaching'] ?? 0 }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $ledgerCategory === 'non-teaching' ? 'active' : '' }}" href="{{ $ledgerUrl(['category' => 'non-teaching']) }}">
                    Non-Teaching Positions <span class="badge bg-white text-dark ms-1">{{ $ledgerCounts['non_teaching'] ?? 0 }}</span>
                </a>
            </li>
        </ul>

        <div class="p-3 bg-light border-bottom d-flex align-items-center flex-wrap gap-2">
            <ul class="nav nav-pills sex-pills gap-1 mb-0">
                <li class="nav-item">
                    <a class="nav-link {{ $ledgerSex === '' ? 'active' : '' }}" href="{{ $ledgerUrl(['sex' => '']) }}">All</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $ledgerSex === 'male' ? 'active' : '' }}" href="{{ $ledgerUrl(['sex' => 'male']) }}">Male</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $ledgerSex === 'female' ? 'active' : '' }}" href="{{ $ledgerUrl(['sex' => 'female']) }}">Female</a>
                </li>
            </ul>
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
                        @forelse($leaves ?? [] as $leave)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">
                                {{ $leave->last_name }}, {{ $leave->first_name }}
                                
                                <!-- Mobile Only Details -->
                                <div class="d-md-none mt-2 p-2 bg-light rounded-3 border fw-normal">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.75rem;">
                                            {{ $leave->leave_type ?? 'Standard Leave' }}
                                        </span>
                                        @if($leave->status == 'APPROVED')
                                            <span class="badge bg-success" style="font-size: 0.7rem;"><i class="bi bi-check-circle me-1"></i> Approved</span>
                                        @elseif($leave->status == 'DISAPPROVED')
                                            <span class="badge bg-danger" style="font-size: 0.7rem;"><i class="bi bi-x-circle me-1"></i> Denied</span>
                                        @else
                                            <span class="badge bg-warning text-dark" style="font-size: 0.7rem;"><i class="bi bi-hourglass-split me-1"></i> Pending</span>
                                        @endif
                                    </div>
                                    <div class="small text-dark mb-1">
                                        <strong>Dates:</strong> {{ $leave->inclusive_dates ?? 'Date not set' }} <span class="text-muted">({{ $leave->working_days }} days)</span>
                                    </div>
                                    <div class="small text-muted text-wrap mb-1">
                                        <strong>Details:</strong> {{ Str::limit($leave->leave_type_others ?: ($leave->leave_details_specific ?: ($leave->leave_details ?: 'N/A')), 50) }}
                                    </div>
                                    @if($leave->hr_remarks)
                                        <div class="small text-muted mt-1 fst-italic border-top pt-1">
                                            "{{ Str::limit($leave->hr_remarks, 40) }}"
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-1">
                                    {{ $leave->leave_type ?? 'Standard Leave' }}
                                </span>
                                <div class="small text-muted text-wrap" style="max-width: 250px;">
                                    <strong>Details:</strong> {{ Str::limit($leave->leave_type_others ?: ($leave->leave_details_specific ?: ($leave->leave_details ?: 'N/A')), 50) }}
                                </div>
                            </td>
                            <td class="small d-none d-md-table-cell">
                                <div class="fw-bold text-dark">{{ $leave->inclusive_dates ?? 'Date not set' }}</div>
                                <div class="text-muted">{{ $leave->working_days }} day(s)</div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if($leave->status == 'APPROVED')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Approved</span>
                                @elseif($leave->status == 'DISAPPROVED')
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Denied</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Pending</span>
                                @endif
                                
                                @if($leave->hr_remarks)
                                    <div class="small text-muted mt-1 fst-italic">
                                        "{{ Str::limit($leave->hr_remarks, 40) }}"
                                    </div>
                                @endif
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <!-- Action Button triggers a Modal so HR can add a comment before deciding -->
                                <button type="button" class="btn btn-sm btn-light border text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $leave->id }}" data-type="{{ $leave->leave_type }}" data-days="{{ $leave->working_days }}">
                                    Review
                                </button>
                                @if(in_array($leave->status, ['APPROVED', 'DISAPPROVED']))
                                    <a href="{{ route('hr.leave.print', $leave->id) }}" target="_blank" rel="noopener" class="btn btn-sm ms-1" style="background-color: #ffffff; color: #1A3E6F; border: 1px solid #1A3E6F; font-weight: bold;">
                                        <i class="bi bi-eye"></i> View PDF
                                    </a>
                                @endif
                            </td>
                        </tr>

                        <!-- Review Modal for this Leave Request -->
                        <div class="modal fade text-start" id="reviewModal{{ $leave->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title fw-bold text-dark">Review Leave Application</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('hr.leave.update_status', $leave->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="small fw-bold text-muted">Employee</label>
                                                <div class="p-2 bg-light rounded text-dark fw-bold border">
                                                    {{ $leave->last_name }}, {{ $leave->first_name }}
                                                    <span class="d-block mt-1 small text-muted fw-normal">
                                                        Balances: VL: {{ number_format($leave->vl_balance, 3) }} | SL: {{ number_format($leave->sl_balance, 3) }} | Service Credits: {{ number_format($leave->service_credits, 3) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="small fw-bold text-muted">Leave Type</label>
                                                <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $leave->leave_type }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="small fw-bold text-muted">Inclusive Dates</label>
                                                <div class="p-2 bg-light rounded text-dark border">{{ $leave->inclusive_dates ?? 'N/A' }} ({{ $leave->working_days }} days)</div>
                                            </div>
                                            <div class="mb-4">
                                                <label class="small fw-bold text-muted">Leave Details</label>
                                                <div class="p-3 bg-light rounded text-dark small border">
                                                    {{ $leave->leave_details_specific ?: ($leave->leave_details ?: 'None provided.') }}
                                                    @if($leave->leave_type_others)
                                                        <div class="mt-2"><strong>Specified:</strong> {{ $leave->leave_type_others }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($leave->status !== 'APPROVED')
                                            <div class="mb-4" id="approveLeaveModal{{ $leave->id }}">
                                                <label class="small fw-bold text-uppercase text-muted">Leave Credit Deduction</label>
                                                <div class="row g-2">
                                                    <div class="col-sm-6">
                                                        <label class="form-label small mb-1" for="vl_deduct_{{ $leave->id }}">Deduct from Vacation Leave</label>
                                                        <input type="number" step="0.5" min="0" name="vl_deduct" id="vl_deduct_{{ $leave->id }}" class="form-control" value="0">
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <label class="form-label small mb-1" for="sl_deduct_{{ $leave->id }}">Deduct from Sick Leave</label>
                                                        <input type="number" step="0.5" min="0" name="sl_deduct" id="sl_deduct_{{ $leave->id }}" class="form-control" value="0">
                                                    </div>
                                                </div>
                                                <div class="form-text">For special statutory leaves or "Others", leave these at 0 unless a specific deduction is required.</div>
                                                @error('vl_deduct')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            @endif

                                            <hr class="text-muted opacity-25">
                                            
                                            <div class="mb-3">
                                                <div id="payStatusPreview{{ $leave->id }}" class="p-2 border rounded fw-bold small text-center"></div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="small fw-bold text-brand text-uppercase tracking-wide">HR Decision <span class="text-danger">*</span></label>
                                                <select name="status" class="form-select form-select-lg" required>
                                                    <option value="" disabled {{ $leave->status == 'PENDING' ? 'selected' : '' }}>Select action...</option>
                                                    <option value="APPROVED" {{ $leave->status == 'APPROVED' ? 'selected' : '' }}>Approve Leave</option>
                                                    <option value="DISAPPROVED" {{ $leave->status == 'DISAPPROVED' ? 'selected' : '' }}>Deny Leave</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="small fw-bold text-muted">Remarks / Comments (Optional)</label>
                                                <textarea name="hr_remarks" class="form-control" rows="2" placeholder="Explain your decision...">{{ $leave->hr_remarks }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light border-top-0 d-flex justify-content-between">
                                            <div>
                                                @if(in_array($leave->status, ['APPROVED', 'DISAPPROVED']))
                                                    <a href="{{ route('hr.leave.print', $leave->id) }}" target="_blank" rel="noopener" class="btn btn-sm" style="background-color: #ffffff; color: #1A3E6F; border: 1px solid #1A3E6F; font-weight: bold;">
                                                        <i class="bi bi-eye"></i> View PDF
                                                    </a>
                                                @endif
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary fw-bold px-4">Save Decision</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                var modalEl = document.getElementById('reviewModal{{ $leave->id }}');
                                var vlInput = document.getElementById('vl_deduct_{{ $leave->id }}');
                                var slInput = document.getElementById('sl_deduct_{{ $leave->id }}');
                                var previewEl = document.getElementById('payStatusPreview{{ $leave->id }}');

                                function suggestedDeductions(leaveType, workingDays) {
                                    if (leaveType === 'Vacation Leave') {
                                        return { vl: workingDays, sl: 0 };
                                    }
                                    if (leaveType === 'Sick Leave') {
                                        return { vl: 0, sl: workingDays };
                                    }
                                    return { vl: 0, sl: 0 };
                                }

                                function renderPreview(leaveType, workingDays) {
                                    var vl = vlInput ? (parseFloat(vlInput.value) || 0) : 0;
                                    var sl = slInput ? (parseFloat(slInput.value) || 0) : 0;

                                    if (leaveType === 'Vacation Leave' || leaveType === 'Sick Leave') {
                                        var withPay = Math.min(workingDays, vl + sl);
                                        var withoutPay = Math.max(0, workingDays - withPay);
                                        previewEl.className = withoutPay > 0
                                            ? 'p-2 border rounded fw-bold small text-center bg-warning text-dark'
                                            : 'p-2 border rounded fw-bold small text-center bg-success text-white';
                                        previewEl.innerText = workingDays + ' days requested — ' + withPay + ' with pay / ' + withoutPay + ' without pay';
                                        return;
                                    }

                                    previewEl.className = 'p-2 border rounded fw-bold small text-center bg-success text-white';
                                    if (vl === 0 && sl === 0) {
                                        previewEl.innerText = 'No VL or SL deduction. This leave stays with pay.';
                                    } else {
                                        previewEl.innerText = 'Manual deduction: ' + vl + ' VL / ' + sl + ' SL. This leave stays with pay.';
                                    }
                                }

                                modalEl.addEventListener('show.bs.modal', function (event) {
                                    var button = event.relatedTarget;
                                    var leaveType = button ? button.getAttribute('data-type') : @json($leave->leave_type);
                                    var workingDays = button ? parseFloat(button.getAttribute('data-days')) : {{ (float) $leave->working_days }};

                                    if (!vlInput || !slInput) {
                                        previewEl.className = 'p-2 border rounded fw-bold small text-center bg-light text-dark';
                                        previewEl.innerText = 'This application is already approved. Credit balances are not changed again from here.';
                                        return;
                                    }

                                    var amounts = suggestedDeductions(leaveType, workingDays);
                                    vlInput.value = amounts.vl;
                                    slInput.value = amounts.sl;
                                    renderPreview(leaveType, workingDays);

                                    vlInput.oninput = function () { renderPreview(leaveType, workingDays); };
                                    slInput.oninput = function () { renderPreview(leaveType, workingDays); };
                                });
                            });
                        </script>
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
        @if($leaves instanceof \Illuminate\Contracts\Pagination\Paginator && $leaves->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            <div class="d-flex justify-content-center">
                {{ $leaves->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
        </div>
