@extends('layouts.app')

@section('content')
@php
    $periodLabel = \Carbon\Carbon::create($periodYear, $periodMonth, 1)->format('F Y');
    $holidayMap = $holidays->mapWithKeys(fn ($holiday) => [$holiday->holiday_date->toDateString() => $holiday->title]);
@endphp
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-6 mb-3 mb-md-0">
            <h4 class="text-header-blue fw-bold mb-0">
                <i class="bi bi-clock-history me-2 text-header-blue"></i> Attendance & Lates
            </h4>
            <p class="text-muted small mb-0">Record employee unexcused absences (AWOL) and tardiness minutes. Deductions are computed using the CSC 22-day divisor formula.</p>
        </div>
        <div class="col-12 col-md-6 text-md-end">
            <a href="{{ route('hr.payroll.index') }}" class="btn btn-outline-secondary fw-semibold shadow-sm px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Back to Payroll
            </a>
        </div>
    </div>

    {{-- Feedback Alerts --}}
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

    {{-- Period Selector --}}
    <div class="card shadow-sm rounded-3 bg-white mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('payroll.attendance.index') }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small fw-bold text-muted mb-1">Month</label>
                    <select name="month" class="form-select form-select-sm" style="min-width: 140px;">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $periodMonth == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small fw-bold text-muted mb-1">Year</label>
                    <select name="year" class="form-select form-select-sm" style="min-width: 100px;">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $periodYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-accent fw-bold shadow-sm px-3">
                        <i class="bi bi-funnel me-1"></i> Load Period
                    </button>
                </div>
                <div class="col-auto ms-auto">
                    <span class="badge rounded-pill bg-accent py-2 px-3" style="font-size: 0.85rem;">
                        <i class="bi bi-calendar3 me-1"></i> {{ $periodLabel }}
                    </span>
                </div>
            </form>
        </div>
    </div>

    @if($holidays->isNotEmpty())
    <div class="alert alert-info shadow-sm border-0 rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-calendar-event-fill fs-5 mt-1"></i>
            <div>
                <strong>Holiday Notice:</strong>
                There {{ $holidays->count() === 1 ? 'is' : 'are' }} {{ $holidays->count() }} declared {{ Str::plural('holiday', $holidays->count()) }} this month
                ({{ $holidays->pluck('title')->join(', ') }}).
                Do not mark employees absent on these dates unless required to report.
            </div>
        </div>
    </div>
    @endif

    {{-- Employee Attendance Form --}}
    <form method="POST" action="{{ route('payroll.attendance.save') }}" id="attendanceForm">
        @csrf
        <input type="hidden" name="period_month" value="{{ $periodMonth }}">
        <input type="hidden" name="period_year" value="{{ $periodYear }}">
        <input type="hidden" name="page" value="{{ $employees->currentPage() }}">
        @if($search !== '')
            <input type="hidden" name="search" value="{{ $search }}">
        @endif

        <div class="card shadow-sm rounded-3 bg-white">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill bg-accent" style="font-size: 0.85rem;">
                        {{ $employees->total() }} {{ Str::plural('Employee', $employees->total()) }}
                    </span>
                    <span class="text-muted small">Active employees</span>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <div class="input-group input-group-sm" style="max-width: 250px;">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Search employee..." autocomplete="off" value="{{ $search }}">
                    </div>
                    <button type="submit" class="btn btn-sm btn-accent fw-bold shadow-sm px-3">
                        <i class="bi bi-check-lg me-1"></i> Save All
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" id="attendanceTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-muted small fw-bold text-uppercase py-3 ps-4" style="width: 50px;">#</th>
                                <th class="text-muted small fw-bold text-uppercase py-3" style="min-width: 220px;">Employee Name</th>
                                <th class="text-muted small fw-bold text-uppercase py-3" style="min-width: 180px;">Position</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 text-end" style="min-width: 130px;">Base Salary</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 text-center text-danger" style="min-width: 220px;">Absent (Days)</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 text-end text-danger" style="min-width: 130px;">Absence Deduction</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 text-center" style="min-width: 220px;">Minutes Late</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 text-end" style="min-width: 130px;">Late Deduction</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $index => $employee)
                            @php
                                $employeeName = 'Employee #' . $employee->id;
                                if (!empty($employee->last_name) || !empty($employee->first_name)) {
                                    $employeeName = $employee->last_name . ', ' . $employee->first_name;
                                    if (!empty($employee->middle_name)) {
                                        $employeeName .= ' ' . strtoupper(substr($employee->middle_name, 0, 1)) . '.';
                                    }
                                } elseif (!empty($employee->name)) {
                                    $employeeName = $employee->name;
                                }

                                $existingRecord = $existingLates->get($employee->id);
                                $existingMinutes = $existingRecord ? $existingRecord->minutes_late : 0;
                                $existingLateAmount = $existingRecord ? (float) $existingRecord->computed_amount : 0;
                                $existingAbsentDays = $existingRecord ? (float) $existingRecord->unexcused_absences : 0;
                                $existingAbsenceAmount = $existingRecord ? (float) $existingRecord->absence_deduction_amount : 0;
                                $existingDates = [];
                                if ($existingRecord && is_array($existingRecord->dates_absent)) {
                                    $rawDates = $existingRecord->dates_absent;
                                    if (array_is_list($rawDates)) {
                                        foreach ($rawDates as $date) {
                                            if (is_string($date)) {
                                                $existingDates[$date] = 1;
                                            }
                                        }
                                    } else {
                                        foreach ($rawDates as $date => $weight) {
                                            $weight = (float) $weight;
                                            if (is_string($date) && ($weight === 1.0 || $weight === 0.5)) {
                                                $existingDates[$date] = $weight;
                                            }
                                        }
                                    }
                                }
                                $existingLateDates = [];
                                if ($existingRecord && is_array($existingRecord->dates_late)) {
                                    foreach ($existingRecord->dates_late as $date => $minutes) {
                                        if (is_string($date) && (int) $minutes > 0) {
                                            $existingLateDates[$date] = (int) $minutes;
                                        }
                                    }
                                }
                                $baseSalary = $employee->base_salary;
                            @endphp
                            <tr class="employee-row" data-name="{{ strtolower($employeeName) }}">
                                <td class="ps-4 text-muted">{{ ($employees->firstItem() ?? 1) + $index }}</td>
                                <td>
                                    <div class="fw-semibold" style="color: #1A3E6F;">{{ $employeeName }}</div>
                                    <small class="text-success d-block">Available Credits: {{ number_format($employee->available_credits, 1) }}</small>
                                    @if($employee->email)
                                        <small class="text-muted">{{ $employee->email }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($employee->position)
                                        <span class="text-secondary">{{ $employee->position->position_name }}</span>
                                    @else
                                        <span class="text-muted fst-italic">No position</span>
                                    @endif
                                </td>
                                <td class="text-end font-monospace text-muted">
                                    ₱{{ number_format($baseSalary, 2) }}
                                </td>
                                <td class="text-center">
                                    <input type="hidden" name="attendance[{{ $employee->id }}][user_id]" value="{{ $employee->id }}">
                                    <input type="hidden"
                                        name="attendance[{{ $employee->id }}][dates_absent]"
                                        class="dates-absent-input"
                                        data-user-id="{{ $employee->id }}"
                                        value="{{ json_encode($existingDates) }}">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number"
                                            name="attendance[{{ $employee->id }}][absent_days]"
                                            class="form-control form-control-sm text-center absent-input"
                                            style="max-width: 70px; background-color: #f8f9fa;"
                                            value="{{ $existingAbsentDays > 0 ? $existingAbsentDays : '' }}"
                                            min="0"
                                            max="22"
                                            step="0.5"
                                            placeholder="0"
                                            readonly
                                            data-base-salary="{{ $baseSalary }}"
                                            data-user-id="{{ $employee->id }}"
                                            data-row-index="{{ $employee->id }}">
                                        <button type="button"
                                            class="btn btn-sm btn-accent fw-bold shadow-sm text-nowrap mark-attendance-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#attendanceCalendarModal"
                                            data-user-id="{{ $employee->id }}"
                                            data-user-name="{{ $employeeName }}"
                                            aria-label="Mark absent days">
                                            <i class="bi bi-calendar-x me-1"></i>Absent
                                        </button>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="font-monospace fw-semibold absence-deduction-display" id="absence-deduction-{{ $employee->id }}"
                                        style="color: {{ $existingAbsenceAmount > 0 ? '#dc3545' : '#6c757d' }};">
                                        {{ $existingAbsenceAmount > 0 ? '₱' . number_format($existingAbsenceAmount, 2) : '—' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <input type="hidden"
                                        name="attendance[{{ $employee->id }}][dates_late]"
                                        class="dates-late-input"
                                        data-user-id="{{ $employee->id }}"
                                        value="{{ json_encode($existingLateDates) }}">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number"
                                            name="attendance[{{ $employee->id }}][minutes_late]"
                                            class="form-control form-control-sm text-center minutes-input"
                                            style="max-width: 70px; background-color: #f8f9fa;"
                                            value="{{ $existingMinutes > 0 ? $existingMinutes : '' }}"
                                            min="0"
                                            max="9999"
                                            placeholder="0"
                                            readonly
                                            data-base-salary="{{ $baseSalary }}"
                                            data-user-id="{{ $employee->id }}"
                                            data-row-index="{{ $employee->id }}">
                                        <button type="button"
                                            class="btn btn-sm btn-accent fw-bold shadow-sm text-nowrap mark-lates-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#latesCalendarModal"
                                            data-user-id="{{ $employee->id }}"
                                            data-user-name="{{ $employeeName }}"
                                            aria-label="Mark minutes late">
                                            <i class="bi bi-clock me-1"></i>Late
                                        </button>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="font-monospace fw-semibold late-deduction-display" id="late-deduction-{{ $employee->id }}"
                                        style="color: {{ $existingLateAmount > 0 ? '#dc3545' : '#6c757d' }};">
                                        {{ $existingLateAmount > 0 ? '₱' . number_format($existingLateAmount, 2) : '—' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    No active employees found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($employees->hasPages())
            <div class="border-top bg-white py-3 pagination-centered">
                {{ $employees->links('pagination::bootstrap-5') }}
            </div>
            @endif

            {{-- Summary Footer --}}
            <div class="card-footer bg-light border-top py-3">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="text-muted small fw-semibold">
                            <i class="bi bi-info-circle me-1"></i>
                            Absences: <code>Base Salary ÷ 22 × Days</code> &nbsp;|&nbsp; Lates: <code>Base Salary ÷ 22 ÷ 8 ÷ 60 × Minutes</code>
                        </span>
                    </div>
                    <div class="col text-end">
                        <span class="fw-bold" style="color: #1A3E6F;">
                            Absence Total: <span class="text-danger font-monospace" id="absenceGrandTotal">₱0.00</span>
                            &nbsp;|&nbsp;
                            Late Total: <span class="text-danger font-monospace" id="lateGrandTotal">₱0.00</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Smart Calendar Modal --}}
<div class="modal fade" id="attendanceCalendarModal" tabindex="-1" aria-labelledby="calendarEmployeeName" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-accent">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="calendarEmployeeName" style="color: #1A3E6F;">Select dates</h5>
                    <p class="text-muted small mb-0" id="calendarPeriodLabel">{{ $periodLabel }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-3">
                <div class="attendance-calendar-weekdays text-muted small fw-bold text-uppercase mb-2">
                    <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                </div>
                <div id="attendanceCalendarGrid" class="attendance-calendar-grid"></div>
                <div class="d-flex flex-wrap gap-3 small text-muted mt-3">
                    <span><span class="calendar-legend-swatch bg-white border"></span> Present</span>
                    <span><span class="calendar-legend-swatch bg-danger"></span> Full day</span>
                    <span><span class="calendar-legend-swatch bg-warning"></span> Half day</span>
                    <span><span class="calendar-legend-swatch" style="background: #e9ecef;"></span> Weekend / holiday</span>
                </div>
                <p class="fw-semibold mt-3 mb-0" style="color: #1A3E6F;">
                    Total Unexcused Absences: <span id="calendarSelectedTotal" class="text-danger">0</span> days
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-accent fw-bold shadow-sm" id="saveCalendarToEmployee">
                    <i class="bi bi-check-lg me-1"></i> Save to Employee
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Lates Calendar Modal --}}
<div class="modal fade" id="latesCalendarModal" tabindex="-1" aria-labelledby="latesCalendarEmployeeName" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-accent">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="latesCalendarEmployeeName" style="color: #1A3E6F;">Mark minutes late</h5>
                    <p class="text-muted small mb-0" id="latesCalendarPeriodLabel">{{ $periodLabel }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-3">
                <div class="attendance-calendar-weekdays text-muted small fw-bold text-uppercase mb-2">
                    <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                </div>
                <div id="latesCalendarGrid" class="attendance-calendar-grid"></div>
                <div class="d-flex flex-wrap gap-3 small text-muted mt-3">
                    <span><span class="calendar-legend-swatch bg-white border"></span> On time</span>
                    <span><span class="calendar-legend-swatch bg-warning"></span> Minutes late</span>
                    <span><span class="calendar-legend-swatch" style="background: #e9ecef;"></span> Weekend / holiday</span>
                </div>
                <p class="fw-semibold mt-3 mb-0" style="color: #1A3E6F;">
                    Total Minutes Late: <span id="latesSelectedTotal" class="text-danger">0</span> minutes
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-accent fw-bold shadow-sm" id="saveLatesToEmployee">
                    <i class="bi bi-check-lg me-1"></i> Save to Employee
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Minutes Late Input Modal --}}
<div class="modal fade" id="lateMinutesModal" tabindex="-1" aria-labelledby="lateMinutesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-accent">
                <h5 class="modal-title fw-bold" id="lateMinutesModalLabel" style="color: #1A3E6F;">Minutes Late</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <p class="text-muted small mb-3" id="lateMinutesDateLabel"></p>
                <label for="lateMinutesInput" class="form-label small fw-bold text-muted">Minutes</label>
                <input type="number" id="lateMinutesInput" class="form-control" min="0" max="9999" step="1" placeholder="e.g. 15" inputmode="numeric">
                <div class="invalid-feedback">Enter a whole number of minutes from 0 to 9999.</div>
                <p class="text-muted small mb-0 mt-2">Enter 0 to clear this date.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-danger btn-sm me-auto" id="clearLateMinutes">Clear</button>
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-accent fw-bold shadow-sm" id="applyLateMinutes">Apply</button>
            </div>
        </div>
    </div>
</div>

<style>
    #attendanceTable {
        --bs-table-border-color: #cbd5e1;
    }
    #attendanceTable th,
    #attendanceTable td {
        border: 1px solid #cbd5e1 !important;
    }
    .attendance-calendar-weekdays,
    .attendance-calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 0.4rem;
    }
    .attendance-calendar-weekdays span {
        text-align: center;
    }
    .attendance-calendar-day {
        min-height: 3.4rem;
        border: 1px solid #94a3b8;
        border-radius: 0.5rem;
        background: #fff;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.1rem;
        font-weight: 600;
        color: #1A3E6F;
        user-select: none;
        padding: 0.25rem;
    }
    .attendance-calendar-day.is-empty {
        border: none;
        background: transparent;
        cursor: default;
        min-height: 0;
    }
    .attendance-calendar-day.is-blocked {
        background: #e9ecef;
        color: #adb5bd;
        cursor: not-allowed;
        pointer-events: none;
    }
    .attendance-calendar-day.is-selected {
        background: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }
    .attendance-calendar-day.is-half,
    .attendance-calendar-day.is-late {
        background: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }
    .attendance-calendar-day:not(.is-blocked):not(.is-empty):hover {
        border-color: #facc15;
    }
    .attendance-calendar-day .calendar-day-note {
        font-size: 0.6rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        line-height: 1;
    }
    .calendar-legend-swatch {
        display: inline-block;
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 0.2rem;
        border: 1px solid #dee2e6;
        vertical-align: middle;
        margin-right: 0.3rem;
    }
    #lateMinutesModal {
        z-index: 1065;
    }
</style>

<script>
    const CALENDAR_PERIOD = {
        month: {{ (int) $periodMonth }},
        year: {{ (int) $periodYear }},
        label: @json($periodLabel)
    };
    const DECLARED_HOLIDAYS = @json($holidayMap);

    const calendarState = {
        userId: null,
        selectedAbsences: {}
    };

    const latesState = {
        userId: null,
        pendingDate: null,
        selectedLates: {}
    };

    function isoDate(year, month, day) {
        return year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');
    }

    function parseStoredAbsences(value) {
        try {
            const parsed = JSON.parse(value || '{}');
            const mapped = {};

            if (Array.isArray(parsed)) {
                parsed.forEach(function (date) {
                    if (typeof date === 'string') {
                        mapped[date] = 1;
                    }
                });
                return mapped;
            }

            if (parsed && typeof parsed === 'object') {
                Object.keys(parsed).forEach(function (date) {
                    const weight = parseFloat(parsed[date]);
                    if (weight === 1 || weight === 0.5) {
                        mapped[date] = weight;
                    }
                });
                return mapped;
            }
        } catch (error) {
            return {};
        }

        return {};
    }

    function sumSelectedAbsences(map) {
        return Object.keys(map).reduce(function (sum, date) {
            return sum + Number(map[date] || 0);
        }, 0);
    }

    function formatAbsenceDays(total) {
        return Number.isInteger(total) ? String(total) : total.toFixed(1);
    }

    function updateCalendarTotal() {
        const total = sumSelectedAbsences(calendarState.selectedAbsences);
        document.getElementById('calendarSelectedTotal').textContent = formatAbsenceDays(total);
    }

    function toggleCalendarDate(iso) {
        const current = calendarState.selectedAbsences[iso] || 0;

        if (current === 0) {
            calendarState.selectedAbsences[iso] = 1;
        } else if (current === 1) {
            calendarState.selectedAbsences[iso] = 0.5;
        } else {
            delete calendarState.selectedAbsences[iso];
        }

        renderAttendanceCalendar();
    }

    function renderAttendanceCalendar() {
        const grid = document.getElementById('attendanceCalendarGrid');
        grid.innerHTML = '';

        const first = new Date(CALENDAR_PERIOD.year, CALENDAR_PERIOD.month - 1, 1);
        const daysInMonth = new Date(CALENDAR_PERIOD.year, CALENDAR_PERIOD.month, 0).getDate();

        for (let i = 0; i < first.getDay(); i++) {
            const spacer = document.createElement('div');
            spacer.className = 'attendance-calendar-day is-empty';
            grid.appendChild(spacer);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(CALENDAR_PERIOD.year, CALENDAR_PERIOD.month - 1, day);
            const iso = isoDate(CALENDAR_PERIOD.year, CALENDAR_PERIOD.month, day);
            const weekday = date.getDay();
            const holidayName = DECLARED_HOLIDAYS[iso] || '';
            const isWeekend = weekday === 0 || weekday === 6;
            const isBlocked = isWeekend || Boolean(holidayName);

            const cell = document.createElement('div');
            cell.className = 'attendance-calendar-day';
            cell.dataset.date = iso;
            cell.dataset.state = '0';

            const number = document.createElement('span');
            number.textContent = String(day);
            cell.appendChild(number);

            if (isBlocked) {
                cell.classList.add('is-blocked');
                const note = document.createElement('small');
                note.className = 'calendar-day-note';
                note.textContent = holidayName ? 'Holiday' : 'Weekend';
                cell.appendChild(note);
                cell.title = holidayName || (weekday === 0 ? 'Sunday' : 'Saturday');
            } else {
                const weight = calendarState.selectedAbsences[iso] || 0;
                cell.dataset.state = String(weight);

                if (weight === 1) {
                    cell.classList.add('is-selected', 'bg-danger', 'text-white');
                    const icon = document.createElement('i');
                    icon.className = 'bi bi-x-lg';
                    cell.appendChild(icon);
                } else if (weight === 0.5) {
                    cell.classList.add('is-half', 'bg-warning', 'text-dark');
                    const half = document.createElement('small');
                    half.className = 'calendar-day-note';
                    half.textContent = '½';
                    cell.appendChild(half);
                }

                cell.addEventListener('click', function () {
                    toggleCalendarDate(iso);
                });
            }

            grid.appendChild(cell);
        }

        updateCalendarTotal();
    }

    function openCalendarForButton(button) {
        calendarState.userId = button.getAttribute('data-user-id');
        document.getElementById('calendarEmployeeName').textContent = button.getAttribute('data-user-name') || 'Employee';
        document.getElementById('calendarPeriodLabel').textContent = CALENDAR_PERIOD.label;

        const hidden = document.querySelector('.dates-absent-input[data-user-id="' + calendarState.userId + '"]');
        calendarState.selectedAbsences = hidden ? parseStoredAbsences(hidden.value) : {};
        renderAttendanceCalendar();
    }

    function saveCalendarToEmployee() {
        if (! calendarState.userId) {
            return;
        }

        const hidden = document.querySelector('.dates-absent-input[data-user-id="' + calendarState.userId + '"]');
        const daysInput = document.querySelector('.absent-input[data-user-id="' + calendarState.userId + '"]');
        const total = sumSelectedAbsences(calendarState.selectedAbsences);

        if (hidden) {
            hidden.value = JSON.stringify(calendarState.selectedAbsences);
        }

        if (daysInput) {
            daysInput.value = total > 0 ? formatAbsenceDays(total) : '';
            computeAbsenceDeduction(daysInput);
        }

        const modalEl = document.getElementById('attendanceCalendarModal');
        if (window.bootstrap && bootstrap.Modal.getInstance(modalEl)) {
            bootstrap.Modal.getInstance(modalEl).hide();
        }
    }

    /**
     * CSC formula for absences: base_salary / 22 * absent_days
     */
    function computeAbsenceDeduction(input) {
        const baseSalary = parseFloat(input.dataset.baseSalary) || 0;
        const days = parseFloat(input.value) || 0;
        const rowIndex = input.dataset.rowIndex;
        const display = document.getElementById('absence-deduction-' + rowIndex);

        if (days > 0 && baseSalary > 0) {
            const dailyRate = baseSalary / 22;
            const deduction = Math.round(days * dailyRate * 100) / 100;
            display.textContent = '₱' + deduction.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            display.style.color = '#dc3545';
        } else {
            display.textContent = '—';
            display.style.color = '#6c757d';
        }

        updateGrandTotals();
    }

    /**
     * CSC formula for lates: base_salary / 22 / 8 / 60 * minutes_late
     */
    function computeLateDeduction(input) {
        const baseSalary = parseFloat(input.dataset.baseSalary) || 0;
        const minutes = parseInt(input.value) || 0;
        const rowIndex = input.dataset.rowIndex;
        const display = document.getElementById('late-deduction-' + rowIndex);

        if (minutes > 0 && baseSalary > 0) {
            const minuteRate = baseSalary / 22 / 8 / 60;
            const deduction = Math.round(minutes * minuteRate * 100) / 100;
            display.textContent = '₱' + deduction.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            display.style.color = '#dc3545';
        } else {
            display.textContent = '—';
            display.style.color = '#6c757d';
        }

        updateGrandTotals();
    }

    function updateGrandTotals() {
        let absenceTotal = 0;
        let lateTotal = 0;

        document.querySelectorAll('.absent-input').forEach(input => {
            const baseSalary = parseFloat(input.dataset.baseSalary) || 0;
            const days = parseFloat(input.value) || 0;
            if (days > 0 && baseSalary > 0) {
                absenceTotal += Math.round((days * baseSalary / 22) * 100) / 100;
            }
        });

        document.querySelectorAll('.minutes-input').forEach(input => {
            const baseSalary = parseFloat(input.dataset.baseSalary) || 0;
            const minutes = parseInt(input.value) || 0;
            if (minutes > 0 && baseSalary > 0) {
                lateTotal += Math.round((minutes * baseSalary / 22 / 8 / 60) * 100) / 100;
            }
        });

        document.getElementById('absenceGrandTotal').textContent = '₱' + absenceTotal.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('lateGrandTotal').textContent = '₱' + lateTotal.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
        }
    });

    let searchTimer;
    searchInput.addEventListener('input', function () {
        const value = this.value.trim();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            const params = new URLSearchParams(window.location.search);
            const current = params.get('search') || '';
            if (value === current) {
                return;
            }
            params.set('month', '{{ $periodMonth }}');
            params.set('year', '{{ $periodYear }}');
            if (value === '') {
                params.delete('search');
            } else {
                params.set('search', value);
            }
            params.delete('page');
            window.location.search = params.toString();
        }, 400);
    });

    document.getElementById('attendanceCalendarModal').addEventListener('show.bs.modal', function (event) {
        if (event.relatedTarget) {
            openCalendarForButton(event.relatedTarget);
        }
    });

    document.getElementById('saveCalendarToEmployee').addEventListener('click', saveCalendarToEmployee);

    function parseStoredLates(value) {
        try {
            const parsed = JSON.parse(value || '{}');
            const mapped = {};

            if (parsed && typeof parsed === 'object' && ! Array.isArray(parsed)) {
                Object.keys(parsed).forEach(function (date) {
                    const minutes = parseInt(parsed[date], 10);
                    if (Number.isFinite(minutes) && minutes > 0) {
                        mapped[date] = minutes;
                    }
                });
            }

            return mapped;
        } catch (error) {
            return {};
        }
    }

    function sumSelectedLates(map) {
        return Object.keys(map).reduce(function (sum, date) {
            return sum + Number(map[date] || 0);
        }, 0);
    }

    function updateLatesTotal() {
        document.getElementById('latesSelectedTotal').textContent = String(sumSelectedLates(latesState.selectedLates));
    }

    function formatLateDateLabel(iso) {
        return new Date(iso + 'T00:00:00').toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function lateMinutesModalInstance() {
        const el = document.getElementById('lateMinutesModal');
        return window.bootstrap ? bootstrap.Modal.getOrCreateInstance(el) : null;
    }

    function openLateMinutesModal(iso) {
        latesState.pendingDate = iso;
        const input = document.getElementById('lateMinutesInput');
        document.getElementById('lateMinutesDateLabel').textContent = formatLateDateLabel(iso);
        input.classList.remove('is-invalid');
        input.value = latesState.selectedLates[iso] ? String(latesState.selectedLates[iso]) : '';

        const modal = lateMinutesModalInstance();
        if (modal) {
            modal.show();
        }
    }

    function applyLateMinutesFromModal(forceClear) {
        const input = document.getElementById('lateMinutesInput');
        const iso = latesState.pendingDate;

        if (! iso) {
            return;
        }

        const raw = forceClear ? '0' : input.value.trim();
        const minutes = parseInt(raw, 10);

        if (raw !== '' && raw !== '0' && (! Number.isFinite(minutes) || minutes < 0 || minutes > 9999)) {
            input.classList.add('is-invalid');
            return;
        }

        input.classList.remove('is-invalid');

        if (! Number.isFinite(minutes) || minutes <= 0) {
            delete latesState.selectedLates[iso];
        } else {
            latesState.selectedLates[iso] = minutes;
        }

        latesState.pendingDate = null;
        const modal = lateMinutesModalInstance();
        if (modal) {
            modal.hide();
        }
        renderLatesCalendar();
    }

    function renderLatesCalendar() {
        const grid = document.getElementById('latesCalendarGrid');
        grid.innerHTML = '';

        const first = new Date(CALENDAR_PERIOD.year, CALENDAR_PERIOD.month - 1, 1);
        const daysInMonth = new Date(CALENDAR_PERIOD.year, CALENDAR_PERIOD.month, 0).getDate();

        for (let i = 0; i < first.getDay(); i++) {
            const spacer = document.createElement('div');
            spacer.className = 'attendance-calendar-day is-empty';
            grid.appendChild(spacer);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(CALENDAR_PERIOD.year, CALENDAR_PERIOD.month - 1, day);
            const iso = isoDate(CALENDAR_PERIOD.year, CALENDAR_PERIOD.month, day);
            const weekday = date.getDay();
            const holidayName = DECLARED_HOLIDAYS[iso] || '';
            const isWeekend = weekday === 0 || weekday === 6;
            const isBlocked = isWeekend || Boolean(holidayName);

            const cell = document.createElement('div');
            cell.className = 'attendance-calendar-day';
            cell.dataset.date = iso;

            const number = document.createElement('span');
            number.textContent = String(day);
            cell.appendChild(number);

            if (isBlocked) {
                cell.classList.add('is-blocked');
                const note = document.createElement('small');
                note.className = 'calendar-day-note';
                note.textContent = holidayName ? 'Holiday' : 'Weekend';
                cell.appendChild(note);
                cell.title = holidayName || (weekday === 0 ? 'Sunday' : 'Saturday');
            } else {
                const minutes = latesState.selectedLates[iso] || 0;

                if (minutes > 0) {
                    cell.classList.add('is-late', 'bg-warning', 'text-dark');
                    const label = document.createElement('small');
                    label.className = 'calendar-day-note';
                    label.textContent = minutes + 'm';
                    cell.appendChild(label);
                }

                cell.addEventListener('click', function () {
                    openLateMinutesModal(iso);
                });
            }

            grid.appendChild(cell);
        }

        updateLatesTotal();
    }

    function openLatesCalendarForButton(button) {
        latesState.userId = button.getAttribute('data-user-id');
        document.getElementById('latesCalendarEmployeeName').textContent = button.getAttribute('data-user-name') || 'Employee';
        document.getElementById('latesCalendarPeriodLabel').textContent = CALENDAR_PERIOD.label;

        const hidden = document.querySelector('.dates-late-input[data-user-id="' + latesState.userId + '"]');
        latesState.selectedLates = hidden ? parseStoredLates(hidden.value) : {};
        renderLatesCalendar();
    }

    function saveLatesToEmployee() {
        if (! latesState.userId) {
            return;
        }

        const hidden = document.querySelector('.dates-late-input[data-user-id="' + latesState.userId + '"]');
        const minutesInput = document.querySelector('.minutes-input[data-user-id="' + latesState.userId + '"]');
        const total = sumSelectedLates(latesState.selectedLates);

        if (hidden) {
            hidden.value = JSON.stringify(latesState.selectedLates);
        }

        if (minutesInput) {
            minutesInput.value = total > 0 ? String(total) : '';
            computeLateDeduction(minutesInput);
        }

        const modalEl = document.getElementById('latesCalendarModal');
        if (window.bootstrap && bootstrap.Modal.getInstance(modalEl)) {
            bootstrap.Modal.getInstance(modalEl).hide();
        }
    }

    document.getElementById('latesCalendarModal').addEventListener('show.bs.modal', function (event) {
        if (event.relatedTarget) {
            openLatesCalendarForButton(event.relatedTarget);
        }
    });

    document.getElementById('saveLatesToEmployee').addEventListener('click', saveLatesToEmployee);

    document.getElementById('applyLateMinutes').addEventListener('click', function () {
        applyLateMinutesFromModal(false);
    });

    document.getElementById('clearLateMinutes').addEventListener('click', function () {
        applyLateMinutesFromModal(true);
    });

    document.getElementById('lateMinutesInput').addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            applyLateMinutesFromModal(false);
        }
    });

    document.getElementById('lateMinutesModal').addEventListener('shown.bs.modal', function () {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 1) {
            backdrops[backdrops.length - 1].style.zIndex = '1064';
        }
        const input = document.getElementById('lateMinutesInput');
        input.focus();
        input.select();
    });

    document.getElementById('latesCalendarModal').addEventListener('hidden.bs.modal', function () {
        const minutesModal = lateMinutesModalInstance();
        if (minutesModal) {
            minutesModal.hide();
        }
    });

    updateGrandTotals();
</script>
@endsection
