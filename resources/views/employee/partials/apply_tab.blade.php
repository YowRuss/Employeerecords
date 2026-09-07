        <div class="tab-pane fade" id="apply" role="tabpanel" aria-labelledby="apply-tab">

    <!-- NEW LEAVE APPLICATION FORM -->
    <div class="card shadow-sm border-0 border-start border-4 border-accent">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-1 text-accent text-center">CS FORM NO. 6 (Revised 2020)</h5>
            <h6 class="text-center text-muted fw-bold mb-4">APPLICATION FOR LEAVE</h6>

            <form action="{{ route('leave.store') }}" method="POST">
                @csrf

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
                            <!-- Update Section 4 and Section 5 inside your Leave Application Form -->
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted mb-1">4. Position <span class="text-danger">*</span></label>
                                <!-- Autofilled from the user's official position -->
                                <input type="text" name="position" class="form-control form-control-sm text-uppercase bg-light" style="color: #1A3E6F;" value="{{ $user->position->name ?? $user->position->position_name ?? 'No Position Assigned' }}" required readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted mb-1">5. Salary <span class="text-danger">*</span></label>
                                <!-- Autofilled from the latest Service Record -->
                                <input type="text" name="salary" class="form-control form-control-sm text-uppercase" value="{{ $current_salary ?? '' }}" placeholder="e.g. 27,000.00" required>
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
                                    'Maternity Leave' => 'Maternity Leave',
                                    'Paternity Leave' => 'Paternity Leave',
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

                                <input type="text" name="leave_type_others" id="leave_type_others"
                                    class="form-control form-control-sm text-uppercase mt-2 {{ old('leave_type') == 'Others' ? '' : 'd-none' }} @error('leave_type_others') is-invalid @enderror"
                                    placeholder="Please specify other leave type"
                                    value="{{ old('leave_type_others') }}">
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
                        <div class="row g-4 mb-4">
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

        <div class="d-grid d-md-flex justify-content-md-end mt-4">
            <button type="submit" class="btn btn-accent px-5 py-2 fw-bold shadow-sm">
                <i class="bi bi-send-fill me-2"></i> Submit Application for Leave
            </button>
        </div>
        </form>
    </div>
        </div>
