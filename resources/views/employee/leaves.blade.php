@extends('layouts.app')

@section('content')
<!-- CSS Assets -->
<link rel="stylesheet" href="{{ asset('build/assets/css/pds.css') }}">

<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="text-header-blue fw-bold m-0"><i class="bi bi-calendar-range me-2 text-header-blue"></i> Application for Leave</h4>
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
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small text-uppercase fw-semibold">Available Service Credits</div>
                <div class="fs-3 fw-bolder mb-0" style="color: #1A3E6F;">{{ number_format($user->available_credits, 1) }} days</div>
            </div>
            <i class="bi bi-award-fill fs-1" style="color: #ffc107;"></i>
        </div>
    </div>
    <div class="alert alert-info border-0 shadow-sm" role="alert">
        <i class="bi bi-info-circle-fill me-1"></i> Service credits can be used to offset unexcused absences to prevent salary deductions.
    </div>

    <div class="d-flex flex-nowrap overflow-x-auto pb-2 mb-4" style="scrollbar-width: none; -ms-overflow-style: none;">
        <ul class="nav nav-tabs flex-nowrap staff-tabs w-100 mb-0" id="leaveTabs" role="tablist">
            <li class="nav-item text-nowrap" role="presentation">
                <button class="nav-link active fw-bold text-nowrap" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab" aria-controls="history" aria-selected="true" style="color: #1A3E6F;">
                    <i class="bi bi-clock-history"></i> My Leave History
                </button>
            </li>
            <li class="nav-item text-nowrap" role="presentation">
                <button class="nav-link text-secondary fw-bold text-nowrap" id="apply-tab" data-bs-toggle="tab" data-bs-target="#apply" type="button" role="tab" aria-controls="apply" aria-selected="false">
                    <i class="bi bi-pencil-square"></i> Apply for Leave
                </button>
            </li>
            <li class="nav-item text-nowrap" role="presentation">
                <button class="nav-link text-secondary fw-bold text-nowrap" id="ledger-tab" data-bs-toggle="tab" data-bs-target="#ledger" type="button" role="tab" aria-controls="ledger" aria-selected="false">
                    <i class="bi bi-journal-text"></i> My Credit Ledger
                </button>
            </li>
            @if(auth()->user()->isTeaching() || (auth()->user()?->position && auth()->user()->position->category === \App\Enums\PositionCategory::Teaching))
            <li class="nav-item text-nowrap" role="presentation">
                <button class="nav-link text-secondary fw-bold text-nowrap" id="seminars-tab" data-bs-toggle="tab" data-bs-target="#seminars" type="button" role="tab" aria-controls="seminars" aria-selected="false">
                    <i class="bi bi-award"></i> My Seminars
                </button>
            </li>
            @endif
        </ul>
    </div>

    <div class="tab-content" id="leaveTabsContent">
        @include('employee.partials.history_tab')
        @include('employee.partials.apply_tab')
        @include('employee.partials.ledger_tab')
        @if(auth()->user()->isTeaching() || (auth()->user()?->position && auth()->user()->position->category === \App\Enums\PositionCategory::Teaching))
        @include('employee.partials.seminars_tab')
        @endif
    </div>
</div>

@if(auth()->user()->isTeaching() || (auth()->user()?->position && auth()->user()->position->category === \App\Enums\PositionCategory::Teaching))
@include('employee.partials.claim_seminar_modal')
@if($errors->hasAny(['title', 'hours', 'date_attended', 'certificate']))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var seminarsTab = document.getElementById('seminars-tab');
        if (seminarsTab && window.bootstrap) {
            window.bootstrap.Tab.getOrCreateInstance(seminarsTab).show();
        }
        var claimModal = document.getElementById('claimSeminarModal');
        if (claimModal && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(claimModal).show();
        }
    });
</script>
@endif
@endif

<script>
    function checkLeaveType(select) {
        var leaveType = select.value;
        var othersInput = document.getElementById('leave_type_others');
        var othersLabel = document.getElementById('leave_type_others_label');
        var detailsSection = document.getElementById('leave_details_section');
        var detailsSelect = document.getElementById('leave_details_select');
        var detailsSpecific = detailsSection.querySelector('input[name="leave_details_specific"]');
        var optgroups = detailsSelect.querySelectorAll('optgroup');

        // "Others" free-text toggle
        if (leaveType === 'Others') {
            othersInput.classList.remove('d-none');
            if (othersLabel) othersLabel.classList.remove('d-none');
            othersInput.required = true;
        } else {
            othersInput.classList.add('d-none');
            if (othersLabel) othersLabel.classList.add('d-none');
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