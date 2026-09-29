@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/bootstrap5@6.1.10/index.global.min.js"></script>
<style>
    #calendar {
        border-top: 4px solid var(--accent-yellow, #fde047);
    }
    #calendar .fc-toolbar-title {
        color: #1A3E6F;
        font-size: 1.25rem;
    }
    #calendar .btn-primary {
        text-transform: capitalize;
        background: linear-gradient(135deg, #fde047, #fef08a) !important;
        color: #1e293b !important;
        border-color: #facc15 !important;
        font-weight: 600;
        box-shadow: none;
    }
    #calendar .btn-primary:hover,
    #calendar .btn-primary:focus,
    #calendar .btn-primary.active,
    #calendar .btn-primary:disabled {
        background: #fde047 !important;
        color: #0f172a !important;
        border-color: #facc15 !important;
        opacity: 1;
    }
    #calendar .btn-primary.active {
        box-shadow: 0 2px 5px rgba(253, 224, 71, 0.45);
    }
    #calendar .fc-daygrid-day {
        cursor: pointer;
    }
    @media (max-width: 575.98px) {
        #calendar .fc-header-toolbar {
            flex-direction: column;
            align-items: stretch;
            gap: 0.5rem;
        }
        #calendar .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
        }
    }
</style>
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-7 mb-3 mb-md-0">
            <h4 class="text-header-blue fw-bold mb-0">
                <i class="bi bi-calendar2-week-fill me-2 text-header-blue"></i> Holiday Calendar
            </h4>
            <p class="text-muted small mb-0">Declare national holidays, special non-working days, and class suspensions used when reviewing monthly attendance.</p>
        </div>
        <div class="col-12 col-md-5 text-md-end">
            <button type="button" class="btn btn-accent fw-bold shadow-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
                <i class="bi bi-plus-lg me-1"></i> Add New Holiday
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-center mb-1">
            <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
            <strong class="fw-bold">Please check the following errors:</strong>
        </div>
        <ul class="mb-0 small ps-4">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="d-flex flex-wrap align-items-center gap-3 mb-3 small text-muted">
        <span class="fw-semibold" style="color: #1A3E6F;">Click a day to declare a holiday. Click an event to edit it.</span>
        @foreach($types as $type)
            <span class="d-inline-flex align-items-center gap-1">
                <span class="rounded-circle d-inline-block" style="width: 0.7rem; height: 0.7rem; background-color: {{ $type->calendarColor() }};"></span>
                {{ $type->value }}
            </span>
        @endforeach
    </div>

    <div id="calendar" class="p-3 bg-white rounded shadow-sm"></div>
</div>

{{-- Add Holiday Modal --}}
<div class="modal fade" id="addHolidayModal" tabindex="-1" aria-labelledby="addHolidayModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('payroll.holidays.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0 bg-accent">
                    <h5 class="modal-title fw-bold" id="addHolidayModalLabel">
                        <i class="bi bi-plus-circle me-2"></i> Add New Holiday
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="holiday_title" class="form-label small fw-bold text-muted">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="holiday_title" class="form-control" value="{{ old('title') }}" required maxlength="255" placeholder="e.g., Independence Day, Typhoon Suspension">
                    </div>
                    <div class="mb-3">
                        <label for="holidayDate" class="form-label small fw-bold text-muted">Date <span class="text-danger">*</span></label>
                        <input type="date" name="holiday_date" id="holidayDate" class="form-control" value="{{ old('holiday_date') }}" required>
                    </div>
                    <div class="mb-0">
                        <label for="holiday_type" class="form-label small fw-bold text-muted">Type <span class="text-danger">*</span></label>
                        <select name="type" id="holiday_type" class="form-select" required>
                            @foreach($types as $type)
                                <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent fw-bold shadow-sm">
                        <i class="bi bi-check-lg me-1"></i> Save Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($holidays as $holiday)
<div class="modal fade" id="editHolidayModal{{ $holiday->id }}" tabindex="-1" aria-labelledby="editHolidayModalLabel{{ $holiday->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('payroll.holidays.update', $holiday) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 bg-accent">
                    <h5 class="modal-title fw-bold" id="editHolidayModalLabel{{ $holiday->id }}">
                        <i class="bi bi-pencil-square me-2"></i> Edit Holiday
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $holiday->title) }}" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Date <span class="text-danger">*</span></label>
                        <input type="date" name="holiday_date" class="form-control" value="{{ old('holiday_date', $holiday->holiday_date->toDateString()) }}" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            @foreach($types as $type)
                                <option value="{{ $type->value }}" @selected(old('type', $holiday->type->value) === $type->value)>{{ $type->value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-light border text-danger" onclick="if (confirm('Remove this holiday from the calendar?')) { document.getElementById('deleteHolidayForm{{ $holiday->id }}').submit(); }">
                        <i class="bi bi-trash-fill me-1"></i> Remove
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-accent fw-bold shadow-sm">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<form id="deleteHolidayForm{{ $holiday->id }}" action="{{ route('payroll.holidays.destroy', $holiday) }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek'
            },
            themeSystem: 'bootstrap5',
            events: @json(route('payroll.holidays.events')),
            selectable: true,
            height: 'auto',
            dateClick: function (info) {
                document.getElementById('holidayDate').value = info.dateStr;
                bootstrap.Modal.getOrCreateInstance(document.getElementById('addHolidayModal')).show();
            },
            eventClick: function (info) {
                info.jsEvent.preventDefault();
                var editModal = document.getElementById('editHolidayModal' + info.event.id);
                if (editModal) {
                    bootstrap.Modal.getOrCreateInstance(editModal).show();
                }
            }
        });
        calendar.render();

        @if($errors->any())
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addHolidayModal')).show();
        @endif
    });
</script>
@endsection
