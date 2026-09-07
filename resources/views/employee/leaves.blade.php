@extends('layouts.app')

@section('content')
<!-- CSS Assets -->
<link rel="stylesheet" href="{{ asset('build/assets/css/pds.css') }}">

<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="text-accent fw-bold m-0"><i class="bi bi-calendar-range me-2"></i> Application for Leave</h4>
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

    <ul class="nav nav-tabs mb-4 staff-tabs" id="leaveTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab" aria-controls="history" aria-selected="true" style="color: #1A3E6F;">
                <i class="bi bi-clock-history"></i> My Leave History
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-secondary fw-bold" id="apply-tab" data-bs-toggle="tab" data-bs-target="#apply" type="button" role="tab" aria-controls="apply" aria-selected="false">
                <i class="bi bi-pencil-square"></i> Apply for Leave
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-secondary fw-bold" id="ledger-tab" data-bs-toggle="tab" data-bs-target="#ledger" type="button" role="tab" aria-controls="ledger" aria-selected="false">
                <i class="bi bi-journal-text"></i> My Credit Ledger
            </button>
        </li>
        @if(auth()->user()?->position && auth()->user()->position->category === \App\Enums\PositionCategory::Teaching)
        <li class="nav-item" role="presentation">
            <button class="nav-link text-secondary fw-bold" id="seminars-tab" data-bs-toggle="tab" data-bs-target="#seminars" type="button" role="tab" aria-controls="seminars" aria-selected="false">
                <i class="bi bi-award"></i> My Seminars
            </button>
        </li>
        @endif
    </ul>

    <div class="tab-content" id="leaveTabsContent">
        @include('employee.partials.history_tab')
        @include('employee.partials.apply_tab')
        @include('employee.partials.ledger_tab')
        @if(auth()->user()?->position && auth()->user()->position->category === \App\Enums\PositionCategory::Teaching)
        @include('employee.partials.seminars_tab')
        @endif
    </div>
</div>

@if(auth()->user()?->position && auth()->user()->position->category === \App\Enums\PositionCategory::Teaching)
@include('employee.partials.claim_seminar_modal')
@endif

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