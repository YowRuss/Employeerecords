@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-0" style="color: #1A3E6F;">Leave Credit Management</h4>
            <p class="text-muted small mb-0">Manage employee leave balances and system settings.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-start justify-content-lg-end">
            <a href="{{ route('hr.seminars.queue') }}" class="btn fw-bold shadow-sm" style="background-color: #FDE047; color: #1A3E6F;">
                <i class="bi bi-card-checklist me-1"></i> Review Pending Seminars ({{ $pendingSeminarsCount ?? 0 }})
            </a>
            <button type="button" class="btn fw-bold shadow-sm" style="background-color: #1A3E6F; color: white;" data-bs-toggle="modal" data-bs-target="#configureRatesModal">
                <i class="bi bi-gear me-1"></i> Configure Rates
            </button>
        </div>
    </div>

    <!-- Master Table -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-muted small fw-bold text-uppercase py-3 ps-4 border-0">Employee</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0">Employee Type</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0">Current Balance</th>
                            <th class="text-muted small fw-bold text-uppercase py-3 border-0 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees ?? [] as $emp)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold" style="color: #1A3E6F;">{{ $emp->last_name }}, {{ $emp->first_name }}</div>
                                <div class="small text-muted">{{ $emp->email }}</div>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-secondary">{{ $emp->employee_type ?? 'N/A' }}</span>
                            </td>
                            <td class="py-3 fw-bold" style="color: #1A3E6F;">
                                {{ number_format($emp->creditBalance->balance ?? 0, 2) }}
                            </td>
                            <td class="py-3 text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-secondary fw-bold" data-bs-toggle="modal" data-bs-target="#adjustModal-{{ $emp->id }}">
                                    <i class="bi bi-sliders"></i> Adjust
                                </button>
                            </td>
                        </tr>

                        <!-- Adjust Modal for Employee -->
                        <div class="modal fade" id="adjustModal-{{ $emp->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="{{ route('hr.credits.adjust', $emp->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header text-white" style="background-color: #1A3E6F;">
                                            <h5 class="modal-title fw-bold">Adjust Credits: {{ $emp->first_name }} {{ $emp->last_name }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-start p-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-muted small">Adjustment Type</label>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="adjustment_type" id="add-{{ $emp->id }}" value="add" checked>
                                                        <label class="form-check-label fw-bold text-success" for="add-{{ $emp->id }}">Add (+)</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="adjustment_type" id="deduct-{{ $emp->id }}" value="deduct">
                                                        <label class="form-check-label fw-bold text-danger" for="deduct-{{ $emp->id }}">Deduct (-)</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-muted small">Amount</label>
                                                <input type="number" name="amount" step="0.5" min="0.5" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-muted small">Remarks / Reason</label>
                                                <textarea name="remarks" class="form-control" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn fw-bold shadow-sm" style="background-color: #FDE047; color: #1A3E6F;">Apply Adjustment</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No employees found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(isset($employees) && method_exists($employees, 'links'))
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-center">
                    {{ $employees->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- System Settings Modal -->
<div class="modal fade" id="configureRatesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('hr.credits.settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" style="background-color: #1A3E6F;">
                    <h5 class="modal-title fw-bold">Configure Leave Credit Rates</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-sm mb-4">
                        <i class="bi bi-info-circle me-2"></i> Update the global multipliers and accrual rates for different employee types.
                    </div>
                    
                    <div class="row g-4">
                        <!-- Teaching Settings -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3 pb-2 border-bottom" style="color: #1A3E6F;">Teaching Staff</h6>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">Monthly Accrual Rate</label>
                                <input type="number" name="settings[TEACHING][monthly_accrual_rate]" step="0.1" class="form-control" value="{{ $settings['TEACHING']['monthly_accrual_rate'] ?? 0 }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">Seminar Multiplier (Credits per hr)</label>
                                <input type="number" name="settings[TEACHING][seminar_rate]" step="0.1" class="form-control" value="{{ $settings['TEACHING']['seminar_rate'] ?? 0 }}">
                            </div>
                        </div>

                        <!-- Non-Teaching Settings -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3 pb-2 border-bottom" style="color: #1A3E6F;">Non-Teaching Staff</h6>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">Monthly Accrual Rate</label>
                                <input type="number" name="settings[NON_TEACHING][monthly_accrual_rate]" step="0.1" class="form-control" value="{{ $settings['NON_TEACHING']['monthly_accrual_rate'] ?? 1.25 }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">Seminar Multiplier (Credits per hr)</label>
                                <input type="number" name="settings[NON_TEACHING][seminar_rate]" step="0.1" class="form-control bg-light" value="0" readonly>
                                <small class="text-muted fst-italic">Not applicable — non-teaching does not earn credits via seminar.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn fw-bold shadow-sm" style="background-color: #FDE047; color: #1A3E6F;">Save Settings</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
