<div class="tab-pane fade" id="apply" role="tabpanel" aria-labelledby="apply-tab">
    <!-- NEW LEAVE APPLICATION FORM -->
    <div class="card shadow-sm border-0 border-start border-4 border-accent">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-1 text-accent text-center">CS FORM NO. 6 (Revised 2020)</h5>
            <h6 class="text-center text-muted fw-bold mb-4">APPLICATION FOR LEAVE</h6>

            <form action="{{ route('leave.store') }}" method="POST">
                @csrf

                <!-- Tabbed Navigation inside Form -->
                <ul class="nav nav-tabs mb-4" id="leaveFormTabs" role="tablist" style="border-bottom: 2px solid #dee2e6;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold px-4 py-2.5 text-nowrap" id="leave-details-tab" data-bs-toggle="tab" data-bs-target="#leave-details" type="button" role="tab" aria-controls="leave-details" aria-selected="true" style="color: #1A3E6F; border-bottom: 3px solid #1A3E6F;">
                            <i class="bi bi-file-earmark-text me-2"></i> Leave Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-secondary fw-bold px-4 py-2.5 text-nowrap" id="apply-credits-tab" data-bs-toggle="tab" data-bs-target="#apply-credits" type="button" role="tab" aria-controls="apply-credits" aria-selected="false">
                            <i class="bi bi-award me-2"></i> Apply Credits (Optional)
                        </button>
                    </li>
                </ul>

                <!-- Form Tabs Content -->
                <div class="tab-content" id="leaveFormTabsContent">
                    <!-- Pane 1: Primary Leave Inputs -->
                    <div class="tab-pane fade show active" id="leave-details" role="tabpanel" aria-labelledby="leave-details-tab">
                        <!-- SECTION 1-5 -->
                        <div class="pds-section-card mb-4">
                            <div class="pds-section-header text-center">APPLICATION FOR LEAVE (CS FORM NO. 6)</div>
                            <div class="pds-section-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1">1. Office / Dept</label>
                                        <input type="text" class="form-control form-control-sm text-uppercase" value="CNHS-JH" readonly disabled>
                                    </div>
                                    <div class="col-md-9">
                                        <label class="form-label small fw-bold text-muted mb-1">2. Name (Last, First, Middle, Suffix)</label>
                                        <input type="text" class="form-control form-control-sm text-uppercase fw-bold" value="{{ trim(($user->last_name ?? '') . ', ' . ($user->first_name ?? '') . ' ' . ($user->middle_name ?? '') . ' ' . ($user->suffix ?? '')) }}" readonly disabled>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted mb-1">3. Date of Filing <span class="text-danger">*</span></label>
                                        <input type="date" name="date_of_filing" class="form-control form-control-sm @error('date_of_filing') is-invalid @enderror" value="{{ old('date_of_filing', date('Y-m-d')) }}" required>
                                        @error('date_of_filing')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted mb-1">4. Position</label>
                                        <input type="text" name="position" id="position" class="form-control form-control-sm text-uppercase bg-light" style="color: #1A3E6F;" value="{{ $current_position }}" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted mb-1">5. Salary</label>
                                        <input type="text" name="salary" id="salary" class="form-control form-control-sm bg-light" value="{{ $current_salary }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 6 -->
                        <div class="pds-section-card mb-4">
                            <div class="pds-section-header text-center">6. DETAILS OF APPLICATION</div>
                            <div class="pds-section-body">
                                <!-- SECTION 6.A & 6.B -->
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6 border-end-md pe-md-4 mb-4 mb-md-0">
                                        <h6 class="fw-bold text-muted small border-bottom pb-2">6.A TYPE OF LEAVE TO BE AVAILED OF</h6>
                                        <select name="leave_type" class="form-select form-select-sm mb-2 @error('leave_type') is-invalid @enderror" onchange="checkLeaveType(this)" required>
                                            <option value="" disabled {{ old('leave_type') ? '' : 'selected' }}>Select Leave Type...</option>
                                            @foreach ([
                                            'Vacation Leave' => 'Vacation Leave (Sec. 51, Rule XVI)',
                                            'Mandatory/Forced Leave' => 'Mandatory/Forced Leave',
                                            'Sick Leave' => 'Sick Leave',
                                            ] as $value => $label)
                                            <option value="{{ $value }}" {{ old('leave_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                            @if($user->applicantSex() === 'Female')
                                            <option value="Maternity Leave" {{ old('leave_type') == 'Maternity Leave' ? 'selected' : '' }}>Maternity Leave</option>
                                            @endif
                                            @if($user->applicantSex() === 'Male')
                                            <option value="Paternity Leave" {{ old('leave_type') == 'Paternity Leave' ? 'selected' : '' }}>Paternity Leave</option>
                                            @endif
                                            @foreach ([
                                            'Special Privilege Leave' => 'Special Privilege Leave',
                                            'Solo Parent Leave' => 'Solo Parent Leave',
                                            'Study Leave' => 'Study Leave',
                                            '10-Day VAWC Leave' => '10-Day VAWC Leave',
                                            'Rehabilitation Privilege' => 'Rehabilitation Privilege',
                                            'Special Leave Benefits for Women' => 'Special Leave Benefits for Women',
                                            'Special Emergency (Calamity) Leave' => 'Special Emergency (Calamity) Leave',
                                            'Adoption Leave' => 'Adoption Leave',
                                            'Others' => 'Others (Specify)',
                                            ] as $value => $label)
                                            <option value="{{ $value }}" {{ old('leave_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('leave_type')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror

                                        <label for="leave_type_others" id="leave_type_others_label" class="form-label small fw-bold mt-2 {{ old('leave_type') == 'Others' ? '' : 'd-none' }}">Specify Details <span class="text-danger">*</span></label>
                                        <input type="text" name="leave_type_others" id="leave_type_others"
                                            class="form-control form-control-sm text-uppercase {{ old('leave_type') == 'Others' ? '' : 'd-none' }} @error('leave_type_others') is-invalid @enderror"
                                            placeholder="Specify the special leave being requested"
                                            value="{{ old('leave_type_others') }}"
                                            {{ old('leave_type') == 'Others' ? 'required' : '' }}>
                                        @error('leave_type_others')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6" id="leave_details_section" style="{{ old('leave_type') == 'Maternity Leave' ? 'display:none;' : '' }}">
                                        <h6 class="fw-bold text-muted small border-bottom pb-2">6.B DETAILS OF LEAVE <span class="fw-normal text-muted">(optional)</span></h6>
                                        <select name="leave_details" id="leave_details_select" class="form-select form-select-sm mb-2 @error('leave_details') is-invalid @enderror">
                                            <option value="">Select Details (If applicable)...</option>

                                            <optgroup label="Vacation/Special Privilege Leave" data-leave-types="Vacation Leave,Special Privilege Leave">
                                                <option value="Within the Philippines" {{ old('leave_details') == 'Within the Philippines' ? 'selected' : '' }}>Within the Philippines</option>
                                                <option value="Abroad" {{ old('leave_details') == 'Abroad' ? 'selected' : '' }}>Abroad</option>
                                            </optgroup>

                                            <optgroup label="Sick Leave" data-leave-types="Sick Leave">
                                                <option value="In Hospital" {{ old('leave_details') == 'In Hospital' ? 'selected' : '' }}>In Hospital</option>
                                                <option value="Out Patient" {{ old('leave_details') == 'Out Patient' ? 'selected' : '' }}>Out Patient</option>
                                            </optgroup>

                                            <optgroup label="Study Leave" data-leave-types="Study Leave">
                                                <option value="Completion of Master's Degree" {{ old('leave_details') == "Completion of Master's Degree" ? 'selected' : '' }}>Completion of Master's Degree</option>
                                                <option value="BAR/Board Examination Review" {{ old('leave_details') == 'BAR/Board Examination Review' ? 'selected' : '' }}>BAR/Board Examination Review</option>
                                            </optgroup>

                                            <optgroup label="Other Purpose" data-leave-types="Vacation Leave,Mandatory/Forced Leave,Sick Leave,Paternity Leave,Special Privilege Leave,Solo Parent Leave,Study Leave,10-Day VAWC Leave,Rehabilitation Privilege,Special Leave Benefits for Women,Special Emergency (Calamity) Leave,Adoption Leave,Others">
                                                <option value="Monetization of Leave Credits" {{ old('leave_details') == 'Monetization of Leave Credits' ? 'selected' : '' }}>Monetization of Leave Credits</option>
                                                <option value="Terminal Leave" {{ old('leave_details') == 'Terminal Leave' ? 'selected' : '' }}>Terminal Leave</option>
                                            </optgroup>
                                        </select>
                                        @error('leave_details')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror

                                        <input type="text" name="leave_details_specific" class="form-control form-control-sm text-uppercase @error('leave_details_specific') is-invalid @enderror" value="{{ old('leave_details_specific') }}" placeholder="Specify Location / Illness (if required)">
                                        @error('leave_details_specific')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- SECTION 6.C & 6.D -->
                                <div class="row g-4 mb-2">
                                    <div class="col-md-6 border-end-md pe-md-4 mb-4 mb-md-0">
                                        <h6 class="fw-bold text-muted small border-bottom pb-2">6.C NUMBER OF WORKING DAYS APPLIED FOR</h6>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="small text-muted fw-bold">Start Date <span class="text-danger">*</span></label>
                                                <input type="date" name="start_date" class="form-control form-control-sm @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required>
                                                @error('start_date')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-6">
                                                <label class="small text-muted fw-bold">End Date <span class="text-danger">*</span></label>
                                                <input type="date" name="end_date" class="form-control form-control-sm @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" required>
                                                @error('end_date')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-muted small border-bottom pb-2">6.D COMMUTATION</h6>
                                        <div class="d-flex gap-3 mt-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="commutation" id="commNotReq" value="Not Requested" {{ old('commutation', 'Not Requested') == 'Not Requested' ? 'checked' : '' }} required>
                                                <label class="form-check-label small" for="commNotReq">Not Requested</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="commutation" id="commReq" value="Requested" {{ old('commutation') == 'Requested' ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="commReq">Requested</label>
                                            </div>
                                        </div>
                                        @error('commutation')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pane 2: Dedicated Credits Application -->
                    <div class="tab-pane fade" id="apply-credits" role="tabpanel" aria-labelledby="apply-credits-tab">
                        <div class="pds-section-card mb-4">
                            <div class="pds-section-header text-center">APPLY CREDITS (OPTIONAL)</div>
                            <div class="pds-section-body p-4">
                                <p class="text-muted small mb-4">
                                    If you are a teaching personnel or wish to use your accumulated service or seminar credits to cover working days for this leave application, specify the credit amounts below.
                                </p>
                                <div class="row g-4">
                                    <div class="col-md-6 border-end-md pe-md-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="small fw-bold text-muted m-0">
                                                <i class="bi bi-star-fill text-warning me-1"></i> Service Credits Applied
                                            </label>
                                            <span class="badge bg-light text-dark border shadow-sm">
                                                Available: {{ number_format($user->leaveCreditBalance->service_credits ?? 0, 3) }}
                                            </span>
                                        </div>
                                        <input type="number" step="0.001" min="0" max="{{ $user->leaveCreditBalance->service_credits ?? 0 }}" name="service_credits_used" class="form-control form-control-sm @error('service_credits_used') is-invalid @enderror" value="{{ old('service_credits_used') }}" placeholder="0.000">
                                        @error('service_credits_used')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text text-muted small mt-1">
                                            Deducts from your earned DepEd service credit balance.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="small fw-bold text-muted m-0">
                                                <i class="bi bi-award-fill text-info me-1"></i> Seminar Credits Applied
                                            </label>
                                            <span class="badge bg-light text-dark border shadow-sm">
                                                Available: {{ number_format($user->leaveCreditBalance->seminar_credits ?? 0, 3) }}
                                            </span>
                                        </div>
                                        <input type="number" step="0.001" min="0" max="{{ $user->leaveCreditBalance->seminar_credits ?? 0 }}" name="seminar_credits_used" class="form-control form-control-sm @error('seminar_credits_used') is-invalid @enderror" value="{{ old('seminar_credits_used') }}" placeholder="0.000">
                                        @error('seminar_credits_used')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text text-muted small mt-1">
                                            Deducts from approved professional development & seminar attendance credits.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Placed Outside & Below .tab-content -->
                <div class="d-grid d-md-flex justify-content-md-end mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-accent px-5 py-2 fw-bold shadow-sm">
                        <i class="bi bi-send-fill me-2"></i> Submit Application for Leave
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #leaveFormTabs .nav-link {
        color: #64748b;
        border: none;
        border-bottom: 3px solid transparent;
        background: transparent;
        transition: all 0.2s ease;
    }
    #leaveFormTabs .nav-link:hover {
        color: #1A3E6F;
        border-bottom-color: rgba(26, 62, 111, 0.3);
    }
    #leaveFormTabs .nav-link.active {
        color: #1A3E6F !important;
        font-weight: 700;
        border-bottom: 3px solid #1A3E6F !important;
        background-color: transparent !important;
    }
</style>

@if($errors->has('service_credits_used') || $errors->has('seminar_credits_used'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var creditsTabBtn = document.querySelector('#apply-credits-tab');
        if (creditsTabBtn) {
            var tab = new bootstrap.Tab(creditsTabBtn);
            tab.show();
        }
    });
</script>
@endif
