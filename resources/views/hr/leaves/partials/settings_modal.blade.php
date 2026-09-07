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
                                <input type="number" name="settings[NON_TEACHING][seminar_rate]" step="0.1" class="form-control" value="{{ $settings['NON_TEACHING']['seminar_rate'] ?? 0 }}">
                            </div>
                        </div>
                    </div>

                    <!-- Global PDF Settings -->
                    <div class="row mt-4 pt-3 border-top g-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3" style="color: #1A3E6F;">Global PDF Settings</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">Certifying Officer Name</label>
                                <input type="text" name="settings[GLOBAL][certifying_officer_name]" class="form-control" value="{{ $settings['GLOBAL']['certifying_officer_name'] ?? 'RHEA MARIE A. ASUNCION' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">Certifying Officer Position</label>
                                <input type="text" name="settings[GLOBAL][certifying_officer_position]" class="form-control" value="{{ $settings['GLOBAL']['certifying_officer_position'] ?? 'Administrative Officer IV' }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn fw-bold shadow-sm" style="background-color: #FDE047; color: #1A3E6F;">Save Settings</button>
                </div>
            </div>
        </form>
    </div>
</div>
