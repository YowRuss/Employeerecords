@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <div>
            <h4 class="text-header-blue fw-bold m-0">
                <i class="bi bi-clock-history me-2 text-header-blue"></i> My Attendance
            </h4>
            <p class="text-muted small mb-0 mt-1">Click a payroll period to see the exact days you were marked absent or late.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4" id="period-list">
            <div class="card shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 py-3 px-4">
                    <i class="bi bi-calendar3" style="color: #1A3E6F;"></i>
                    <span class="fw-semibold" style="color: #1A3E6F;">Payroll Periods</span>
                    <span class="badge bg-accent ms-auto">{{ $records->count() }} {{ Str::plural('period', $records->count()) }}</span>
                </div>
                <div class="card-body p-0">
                    @if($records->isEmpty())
                        <div class="text-center py-5 px-3">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2 mb-0">No attendance deductions recorded yet.</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush" id="attendancePeriodList">
                            @foreach($records as $record)
                                @php
                                    $absences = $record->calendarAbsences();
                                    $lates = $record->calendarLates();
                                @endphp
                                <button type="button"
                                    class="list-group-item list-group-item-action attendance-period-item py-3 px-4"
                                    data-absences='@json($absences)'
                                    data-lates='@json($lates)'
                                    data-month="{{ $record->period_month }}"
                                    data-year="{{ $record->period_year }}">
                                    <div class="fw-semibold mb-1" style="color: #1A3E6F;">{{ $record->period_label }}</div>
                                    <div class="small text-muted">
                                        Absent: {{ number_format((float) $record->unexcused_absences, 1) }} {{ Str::plural('day', (float) $record->unexcused_absences) }}
                                        · Late: {{ (int) $record->minutes_late }} {{ Str::plural('minute', (int) $record->minutes_late) }}
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8" id="calendar-view">
            <div class="card shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <span class="fw-semibold d-block" id="employeeCalendarTitle" style="color: #1A3E6F;">Attendance Calendar</span>
                        <span class="text-muted small" id="employeeCalendarSubtitle">Select a payroll period</span>
                    </div>
                    <i class="bi bi-calendar2-week text-muted fs-4"></i>
                </div>
                <div class="card-body px-4 py-4">
                    <div class="attendance-calendar-weekdays text-muted small fw-bold text-uppercase mb-2">
                        <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                    </div>
                    <div id="employeeAttendanceCalendarGrid" class="attendance-calendar-grid">
                        <div class="text-muted text-center py-5" style="grid-column: 1 / -1;">
                            Choose a period on the left to view your calendar.
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-3 small text-muted mt-4">
                        <span><span class="calendar-legend-swatch bg-danger"></span> Full Day Absent</span>
                        <span><span class="calendar-legend-swatch bg-warning"></span> Half Day Absent (½)</span>
                        <span><span class="calendar-legend-swatch" style="background: #fd7e14;"></span> Minutes Late</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .attendance-period-item.active {
        background-color: #fde047;
        border-left: 3px solid #facc15;
        color: #1e293b;
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
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        background: #fff;
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
        min-height: 0;
    }
    .attendance-calendar-day.is-weekend {
        background: #f8f9fa;
        color: #adb5bd;
    }
    .attendance-calendar-day.is-full {
        background: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }
    .attendance-calendar-day.is-half {
        background: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }
    .attendance-calendar-day.is-late {
        background: #fd7e14;
        border-color: #fd7e14;
        color: #fff;
    }
    .attendance-calendar-day.has-late-border {
        box-shadow: inset 0 0 0 2px #fd7e14;
    }
    .attendance-calendar-day .calendar-day-note {
        font-size: 0.6rem;
        font-weight: 700;
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
</style>

<script>
    function isoDate(year, month, day) {
        return year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');
    }

    function parseJsonAttribute(value, fallback) {
        try {
            const parsed = JSON.parse(value || '');
            if (parsed && typeof parsed === 'object' && ! Array.isArray(parsed)) {
                return parsed;
            }

            if (Array.isArray(parsed)) {
                const mapped = {};
                parsed.forEach(function (date) {
                    if (typeof date === 'string') {
                        mapped[date] = 1;
                    }
                });
                return mapped;
            }
        } catch (error) {
            return fallback;
        }

        return fallback;
    }

    function monthLabel(year, month) {
        return new Date(year, month - 1, 1).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    }

    function renderEmployeeCalendar(year, month, absences, lates) {
        const grid = document.getElementById('employeeAttendanceCalendarGrid');
        const title = document.getElementById('employeeCalendarTitle');
        const subtitle = document.getElementById('employeeCalendarSubtitle');

        title.textContent = monthLabel(year, month);
        subtitle.textContent = 'Days marked absent or late this period';
        grid.innerHTML = '';

        const first = new Date(year, month - 1, 1);
        const daysInMonth = new Date(year, month, 0).getDate();

        for (let i = 0; i < first.getDay(); i++) {
            const spacer = document.createElement('div');
            spacer.className = 'attendance-calendar-day is-empty';
            grid.appendChild(spacer);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month - 1, day);
            const iso = isoDate(year, month, day);
            const weekday = date.getDay();
            const absenceValue = Number(absences[iso] || 0);
            const lateMinutes = parseInt(lates[iso], 10) || 0;

            const cell = document.createElement('div');
            cell.className = 'attendance-calendar-day';
            cell.dataset.date = iso;

            const number = document.createElement('span');
            number.textContent = String(day);
            cell.appendChild(number);

            if (absenceValue === 1) {
                cell.classList.add('is-full', 'bg-danger', 'text-white');
            } else if (absenceValue === 0.5) {
                cell.classList.add('is-half', 'bg-warning', 'text-dark');
                const half = document.createElement('small');
                half.className = 'calendar-day-note';
                half.textContent = '½';
                cell.appendChild(half);
            } else if (weekday === 0 || weekday === 6) {
                cell.classList.add('is-weekend');
            }

            if (lateMinutes > 0) {
                if (absenceValue === 0) {
                    cell.classList.add('is-late');
                    cell.classList.remove('is-weekend');
                } else {
                    cell.classList.add('has-late-border');
                }

                const lateLabel = document.createElement('small');
                lateLabel.className = 'calendar-day-note';
                lateLabel.textContent = lateMinutes + 'm';
                cell.appendChild(lateLabel);
            }

            grid.appendChild(cell);
        }
    }

    function selectPeriodItem(item) {
        document.querySelectorAll('.attendance-period-item').forEach(function (el) {
            el.classList.remove('active');
        });
        item.classList.add('active');

        const month = parseInt(item.getAttribute('data-month'), 10);
        const year = parseInt(item.getAttribute('data-year'), 10);
        const absences = parseJsonAttribute(item.getAttribute('data-absences'), {});
        const lates = parseJsonAttribute(item.getAttribute('data-lates'), {});

        if (! month || ! year) {
            return;
        }

        renderEmployeeCalendar(year, month, absences, lates);
    }

    document.querySelectorAll('.attendance-period-item').forEach(function (item) {
        item.addEventListener('click', function () {
            selectPeriodItem(item);
        });
    });

    const firstPeriod = document.querySelector('.attendance-period-item');
    if (firstPeriod) {
        selectPeriodItem(firstPeriod);
    }
</script>
@endsection
