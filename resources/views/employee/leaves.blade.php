@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="text-accent fw-bold m-0"><i class="bi bi-calendar-range me-2"></i> Application for Leave</h4>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print / Export</button>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> Please correct the errors below.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

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
                        <th>Inclusive Dates</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($leave->date_of_filing)->format('M d, Y') }}</td>
                        <td class="fw-bold">{{ $leave->leave_type }}</td>
                        <td>{{ $leave->inclusive_dates }}</td>
                        <td>{{ $leave->working_days }}</td>
                        <td>
                            @if($leave->status == 'PENDING')
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">PENDING</span>
                            @elseif($leave->status == 'APPROVED')
                            <span class="badge bg-success px-3 py-2 rounded-pill">APPROVED</span>
                            @else
                            <span class="badge bg-danger px-3 py-2 rounded-pill">DISAPPROVED</span>
                            @endif

                            <!-- ADD THIS SO THE EMPLOYEE SEES HR's COMMENT -->
                            @if($leave->hr_remarks)
                            <div class="mt-1 small text-muted fst-italic" style="font-size: 0.7rem;">{{ $leave->hr_remarks }}</div>
                            @endif
                        </td>
                        <td>
                            @if($leave->status == 'PENDING')
                            <form action="{{ route('leave.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Cancel this pending leave application?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Cancel Leave"><i class="bi bi-x-circle"></i> Cancel</button>
                            </form>
                            @else
                            <span class="text-muted small"><i class="bi bi-lock-fill"></i> Locked</span>
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

    <!-- NEW LEAVE APPLICATION FORM -->
    <div class="card shadow-sm border-0 border-start border-4 border-accent">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-1 text-accent text-center">CS FORM NO. 6 (Revised 2020)</h5>
            <h6 class="text-center text-muted fw-bold mb-4">APPLICATION FOR LEAVE</h6>

            <form action="{{ route('leave.store') }}" method="POST">
                @csrf

                <!-- SECTION 1-5 -->
                <div class="row g-3 mb-4 bg-light p-3 rounded border">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">1. Office / Dept</label>
                        <input type="text" class="form-control form-control-sm text-uppercase" value="CNHS-JH" readonly disabled>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label small fw-bold text-muted mb-1">2. Name (Last, First, Middle, Suffix)</label>
                        <input type="text" class="form-control form-control-sm text-uppercase fw-bold" value="{{ trim($user->last_name . ', ' . $user->first_name . ' ' . $user->middle_name . ' ' . $user->suffix) }}" readonly disabled>
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
                        <!-- Autofilled from the designation in the latest Service Record -->
                        <input type="text" name="position" class="form-control form-control-sm text-uppercase" value="{{ $current_position }}" placeholder="e.g. TEACHER I" required readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">5. Salary <span class="text-danger">*</span></label>
                        <!-- Autofilled from the latest Service Record -->
                        <input type="text" name="salary" class="form-control form-control-sm text-uppercase" value="{{ $current_salary }}" placeholder="e.g. 27,000.00" required>
                    </div>
                </div>

                <div class="bg-secondary text-white fw-bold p-2 mb-3 rounded-1 text-center">
                    6. DETAILS OF APPLICATION
                </div>

                <!-- SECTION 6.A & 6.B -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6 border-end-md pe-md-4">
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
                    <div class="col-md-6 border-end-md pe-md-4">
                        <h6 class="fw-bold text-muted small border-bottom pb-2">6.C NUMBER OF WORKING DAYS APPLIED FOR</h6>
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="small text-muted fw-bold">No. of Days <span class="text-danger">*</span></label>
                                <input type="number" name="working_days" class="form-control form-control-sm @error('working_days') is-invalid @enderror" value="{{ old('working_days') }}" min="1" required>
                                @error('working_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-8">
                                <label class="small text-muted fw-bold">Inclusive Dates <span class="text-danger">*</span></label>
                                <input type="text" name="inclusive_dates" class="form-control form-control-sm text-uppercase @error('inclusive_dates') is-invalid @enderror" value="{{ old('inclusive_dates') }}" placeholder="e.g. Oct 12 - Oct 15, 2026" required>
                                @error('inclusive_dates')
                                <div class="invalid-feedback">{{ $message }}</div>
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

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-accent px-5 py-2 fw-bold shadow-sm">
                        <i class="bi bi-send-fill me-2"></i> Submit Application for Leave
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function checkLeaveType(select) {
        var leaveType = select.value;
        var othersInput = document.getElementById('leave_type_others');
        var detailsSection = document.getElementById('leave_details_section');
        var detailsSelect = document.getElementById('leave_details_select');
        var detailsSpecific = detailsSection.querySelector('input[name="leave_details_specific"]');
        var optgroups = detailsSelect.querySelectorAll('optgroup');

        // "Others" free-text toggle
        if (leaveType === 'Others') {
            othersInput.classList.remove('d-none');
            othersInput.required = true;
        } else {
            othersInput.classList.add('d-none');
            othersInput.required = false;
            othersInput.value = '';
        }

        // Maternity Leave has no details/reason at all
        if (leaveType === 'Maternity Leave') {
            detailsSection.style.display = 'none';
            detailsSelect.value = '';
            detailsSpecific.value = '';
            return;
        }
        detailsSection.style.display = '';

        // Show only the optgroups relevant to the selected leave type
        var currentSelectionStillValid = false;
        optgroups.forEach(function(group) {
            var allowedTypes = group.getAttribute('data-leave-types').split(',');
            var isAllowed = allowedTypes.includes(leaveType);
            group.hidden = !isAllowed;

            if (isAllowed) {
                group.querySelectorAll('option').forEach(function(opt) {
                    if (opt.selected) currentSelectionStillValid = true;
                });
            }
        });

        // If the previously selected detail no longer applies, reset it
        if (!currentSelectionStillValid) {
            detailsSelect.value = '';
            detailsSpecific.value = '';
        }
    }

    // Run on load too, in case old('leave_type') is set after a failed submission
    document.addEventListener('DOMContentLoaded', function() {
        var leaveTypeSelect = document.querySelector('select[name="leave_type"]');
        if (leaveTypeSelect && leaveTypeSelect.value) {
            checkLeaveType(leaveTypeSelect);
        }
    });
</script>
@endsection