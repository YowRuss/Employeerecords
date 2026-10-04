@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h3 class="text-header-blue fw-bold">Dashboard</h3>
        <p class="text-muted">Role:
            @if(session('role_id') == 1) Employee
            @elseif(session('role_id') == 2) HR Officer
            @elseif(session('role_id') == 3) Administrator
            @elseif(session('role_id') == 4) Principal
            @endif
        </p>
    </div>
</div>

<!-- Announcements Section (Visible to all roles) -->
@if(isset($activeAnnouncements) && $activeAnnouncements->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            @foreach($activeAnnouncements as $announcement)
                @if(!$announcement->has_acknowledged)
                    <div class="alert {{ $announcement->is_pinned ? 'alert-danger border-danger' : 'alert-primary border-primary' }} shadow-sm border-2 border-bottom-0 border-end-0 border-top-0 rounded-3 mb-3 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold mb-1">
                                @if($announcement->is_pinned) <i class="bi bi-pin-angle-fill me-1"></i> @endif
                                {{ $announcement->title }} 
                                <span class="badge bg-white text-dark border ms-2" style="font-size: 0.7rem;">{{ $announcement->type }}</span>
                            </div>
                            <div class="small opacity-75 mb-0">{{ $announcement->content }}</div>
                        </div>
                        <div class="ms-3">
                            <form action="{{ route('announcements.acknowledge', $announcement->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-light fw-bold shadow-sm text-primary text-nowrap">
                                    <i class="bi bi-check2-circle me-1"></i> Acknowledge
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- EMPLOYEE DASHBOARD -->
@if(session('role_id') == 1)
<div class="row g-4 mb-4">
    <!-- Recent Announcements Widget (Left Column) -->
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm border-0 h-100 rounded-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="text-warning fw-bold mb-0"><i class="bi bi-megaphone-fill me-2"></i> Recent Announcements</h5>
                <a href="{{ route('employee.announcements') }}" class="btn btn-sm btn-outline-warning text-dark fw-bold rounded-pill px-3" style="font-size: 0.75rem;">
                    View All
                </a>
            </div>
            <div class="card-body p-4 d-flex flex-column">
                @if(isset($announcements) && $announcements->count() > 0)
                    <div class="d-flex flex-column gap-3 flex-grow-1">
                        @foreach($announcements as $announcement)
                            <div class="p-3 rounded-3 bg-light border border-light-subtle">
                                <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                    <h6 class="fw-bold text-dark mb-0 flex-grow-1">{{ $announcement->title }}</h6>
                                    @if($announcement->type)
                                        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 rounded-pill flex-shrink-0" style="font-size: 0.7rem;">
                                            {{ $announcement->type }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-muted small mb-2">
                                    <i class="bi bi-clock me-1"></i> {{ \Carbon\Carbon::parse($announcement->created_at)->format('M d, Y h:i A') }}
                                </div>
                                <p class="text-muted small mb-0" style="line-height: 1.5;">
                                    {{ Str::limit($announcement->content ?? $announcement->body, 120) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 my-auto">
                        <i class="bi bi-inbox text-warning fs-1 d-block mb-3"></i>
                        <h6 class="fw-bold text-dark mb-1">No Announcements Found</h6>
                        <p class="text-muted small mb-0">There are no active announcement messages at this time.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Upcoming Events Widget (Right Column) -->
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm border-0 h-100 rounded-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="text-warning fw-bold mb-0"><i class="bi bi-calendar-event-fill me-2"></i> Upcoming Events</h5>
                <a href="{{ route('employee.events') }}" class="btn btn-sm btn-outline-warning text-dark fw-bold rounded-pill px-3" style="font-size: 0.75rem;">
                    View All
                </a>
            </div>
            <div class="card-body p-4 d-flex flex-column">
                @if(isset($events) && $events->count() > 0)
                    <div class="d-flex flex-column gap-3 flex-grow-1">
                        @foreach($events as $event)
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light border border-light-subtle">
                                <!-- Calendar Date Block -->
                                <div class="rounded-3 text-white text-center p-2 me-3 flex-shrink-0 d-flex flex-column justify-content-center shadow-sm" 
                                     style="width: 56px; height: 56px; background-color: #1A3E6F;">
                                    <span class="text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px; line-height: 1;">
                                        {{ \Carbon\Carbon::parse($event->event_date)->format('M') }}
                                    </span>
                                    <span class="fw-bolder fs-5" style="line-height: 1.1;">
                                        {{ \Carbon\Carbon::parse($event->event_date)->format('d') }}
                                    </span>
                                </div>
                                <!-- Event Details -->
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h6 class="fw-bold text-dark mb-0 text-truncate">{{ $event->title }}</h6>
                                        @if($event->type)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill" style="font-size: 0.65rem;">
                                                {{ $event->type }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-muted small d-flex flex-wrap align-items-center gap-3">
                                        @if($event->event_time)
                                            <span><i class="bi bi-clock me-1"></i> {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}</span>
                                        @endif
                                        @if($event->venue)
                                            <span class="text-truncate"><i class="bi bi-geo-alt-fill me-1"></i> {{ $event->venue }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 my-auto">
                        <i class="bi bi-calendar-x text-warning fs-1 d-block mb-3"></i>
                        <h6 class="fw-bold text-dark mb-1">No Upcoming Events</h6>
                        <p class="text-muted small mb-0">No upcoming events scheduled at this time.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
<!-- END EMPLOYEE DASHBOARD -->

<!-- HR DASHBOARD (Analytics Hub) -->
@if(session('role_id') == 2)
@php
    $teachingShare = $teachingShare ?? 0;
    $nonTeachingShare = 100 - $teachingShare;
@endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-xl">
        <div class="card hr-stat-card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="d-flex h-100">
                <div class="flex-shrink-0" style="width: 6px; background: #1A3E6F;"></div>
                <div class="card-body py-3 px-3">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Active Staff</span>
                        <span class="hr-stat-icon" style="background: rgba(26, 62, 111, 0.1); color: #1A3E6F;"><i class="bi bi-people"></i></span>
                    </div>
                    <h3 class="fw-bolder mb-0 text-dark">{{ $totalActiveStaff ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl">
        <div class="card hr-stat-card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="d-flex h-100">
                <div class="flex-shrink-0" style="width: 6px; background: #1A3E6F;"></div>
                <div class="card-body py-3 px-3">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Teaching</span>
                        <span class="hr-stat-icon" style="background: rgba(26, 62, 111, 0.1); color: #1A3E6F;"><i class="bi bi-person-workspace"></i></span>
                    </div>
                    <h3 class="fw-bolder mb-0 text-dark">{{ $teachingCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl">
        <div class="card hr-stat-card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="d-flex h-100">
                <div class="flex-shrink-0" style="width: 6px; background: #10B981;"></div>
                <div class="card-body py-3 px-3">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Non-Teaching</span>
                        <span class="hr-stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10B981;"><i class="bi bi-person-badge"></i></span>
                    </div>
                    <h3 class="fw-bolder mb-0 text-dark">{{ $nonTeachingCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl">
        <a href="{{ route('hr.leave.index') }}" class="text-decoration-none">
            <div class="card hr-stat-card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="d-flex h-100">
                    <div class="flex-shrink-0" style="width: 6px; background: #F59E0B;"></div>
                    <div class="card-body py-3 px-3">
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Pending Leaves</span>
                            <span class="hr-stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #d97706;"><i class="bi bi-calendar-check"></i></span>
                        </div>
                        <h3 class="fw-bolder mb-0 text-dark">{{ $pendingLeaves ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-6 col-xl">
        <a href="{{ route('requisitions.index') }}" class="text-decoration-none">
            <div class="card hr-stat-card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="d-flex h-100">
                    <div class="flex-shrink-0" style="width: 6px; background: #EF4444;"></div>
                    <div class="card-body py-3 px-3">
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Open Requisitions</span>
                            <span class="hr-stat-icon" style="background: rgba(239, 68, 68, 0.12); color: #EF4444;"><i class="bi bi-clipboard-data"></i></span>
                        </div>
                        <h3 class="fw-bolder mb-0 text-dark">{{ $openRequisitions ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white pt-4 pb-0 border-bottom-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="fw-bold m-0" style="color: #1A3E6F;"><i class="bi bi-bar-chart-line-fill me-2"></i> Staff Attendance Overview</h5>
                <span class="badge rounded-pill" style="background: rgba(26, 62, 111, 0.08); color: #1A3E6F;">{{ $attendancePeriodLabel ?? now()->format('F Y') }}</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small fw-semibold mb-1">
                                <span style="color: #1A3E6F;">Teaching</span>
                                <span>{{ $teachingCount ?? 0 }} <span class="text-muted fw-normal">({{ $teachingShare }}%)</span></span>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 999px; background: #e2e8f0;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $teachingShare }}%; background: #1A3E6F;" aria-valuenow="{{ $teachingShare }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between small fw-semibold mb-1">
                                <span class="text-success">Non-Teaching</span>
                                <span>{{ $nonTeachingCount ?? 0 }} <span class="text-muted fw-normal">({{ $nonTeachingShare }}%)</span></span>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 999px; background: #e2e8f0;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $nonTeachingShare }}%;" aria-valuenow="{{ $nonTeachingShare }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="rounded-3 p-3 h-100" style="background: #f8fafc;">
                                    <div class="text-muted small">With deductions</div>
                                    <div class="fw-bolder fs-4" style="color: #1A3E6F;">{{ $attendanceRecords ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="rounded-3 p-3 h-100" style="background: #fef2f2;">
                                    <div class="text-muted small">Absence days</div>
                                    <div class="fw-bolder fs-4 text-danger">{{ number_format($monthAbsenceDays ?? 0, 1) }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="rounded-3 p-3 h-100" style="background: #fffbeb;">
                                    <div class="text-muted small">Minutes late</div>
                                    <div class="fw-bolder fs-4" style="color: #d97706;">{{ number_format($monthLateMinutes ?? 0) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <h5 class="text-header-blue fw-bold"><i class="bi bi-grid-fill me-2"></i> HR Management Modules</h5>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Announcements -->
    <div class="col-md-6 col-lg-6">
        <div class="card hr-module-card h-100 border-0 shadow-sm rounded-4 hover-lift">
            <div class="card-body text-center p-4">
                <div class="hr-module-icon rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                    <i class="bi bi-megaphone-fill fs-3"></i>
                </div>
                <h6 class="fw-bold text-dark">Announcements</h6>
                <p class="text-muted small mb-4">Broadcast alerts, policies, and track read receipts across all staff.</p>
                <a href="{{ route('announcements.index') }}" class="btn hr-module-btn btn-sm w-100 fw-bold rounded-pill">
                    Manage Broadcasts
                </a>
            </div>
        </div>
    </div>

    <!-- Event Organizer -->
    <div class="col-md-6 col-lg-6">
        <div class="card hr-module-card h-100 border-0 shadow-sm rounded-4 hover-lift">
            <div class="card-body text-center p-4">
                <div class="hr-module-icon rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                    <i class="bi bi-calendar-event-fill fs-3"></i>
                </div>
                <h6 class="fw-bold text-dark">Event Organizer</h6>
                <p class="text-muted small mb-4">Schedule meetings, track RSVPs, and monitor staff attendance.</p>
                <a href="{{ route('events.index') }}" class="btn hr-module-btn btn-sm w-100 fw-bold rounded-pill">
                    Manage Events
                </a>
            </div>
        </div>
    </div>

</div>
@endif

<style>
    .hover-lift {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }
    .hr-stat-card {
        border-top: 0 !important;
    }
    .hr-stat-icon {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .hr-stat-card .text-uppercase {
        letter-spacing: 0.03em;
        line-height: 1.2;
        font-size: 0.68rem;
        white-space: nowrap;
    }
    .hr-module-card {
        border-top: 0 !important;
        border-bottom: 4px solid var(--accent-yellow, #ffc107) !important;
    }
    .hr-module-icon {
        width: 60px;
        height: 60px;
        background-color: var(--accent-yellow, #ffc107);
        color: #1A3E6F;
        border: 1px solid #eab308;
    }
    .hr-module-btn {
        color: #1A3E6F;
        background-color: var(--accent-yellow, #ffc107);
        border: 1px solid #eab308;
    }
    .hr-module-btn:hover {
        color: #0f172a;
        background-color: #facc15;
        border-color: #ca8a04;
    }
</style>

<!-- ADMIN DASHBOARD -->
@if(session('role_id') == 3)
<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-4 h-100">
            <h5 class="text-accent fw-bold mb-4">Create New Position</h5>

            <form action="{{ route('positions.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Position Title</label>
                    <input type="text" name="position_name" class="form-control" placeholder="e.g., Master Teacher I" required>
                </div>
                <button type="submit" class="btn btn-accent w-100">Save Position</button>
            </form>

            <hr class="my-4">
            <h6 class="text-accent fw-bold mb-3">Current Positions</h6>
            <ul class="list-group list-group-flush small">
                @if(isset($positions))
                @foreach($positions as $pos)
                <li class="list-group-item px-0 border-bottom">{{ $pos->position_name }}</li>
                @endforeach
                @endif
            </ul>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card p-4 h-100">
            <h5 class="text-accent fw-bold mb-4">Registered Employees</h5>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle bg-white">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($employees) && $employees->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center text-muted">No employees found.</td>
                        </tr>
                        @elseif(isset($employees))
                        @foreach($employees as $emp)
                        <tr>
                            <td>{{ $emp->id }}</td>
                            <td class="fw-bold">{{ $emp->full_name }}</td>
                            <td>{{ $emp->username }}</td>
                            <td>{{ \Carbon\Carbon::parse($emp->created_at)->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            @if(isset($employees) && $employees->hasPages())
            <div class="pagination-centered mt-3">
                {{ $employees->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endif



@endsection