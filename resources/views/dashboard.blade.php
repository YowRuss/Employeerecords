@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h3 class="text-accent fw-bold">Dashboard</h3>
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
<div class="row g-4">
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h5 class="text-accent fw-bold">Personal Data Sheet (PDS)</h5>
            <p class="text-muted small">Update your personal information, family background, and educational attainment.</p>
            <a href="{{ route('pds.edit') }}" class="btn btn-accent mt-auto w-100">Update PDS</a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h5 class="text-accent fw-bold">Statement of Assets, Liabilities, and Net Worth (SALN)</h5>
            <p class="text-muted small">Submit your annual SALN declaration required by the government.</p>
            <a href="{{ route('saln.index') }}" class="btn btn-accent mt-auto w-100">Submit SALN</a>
        </div>
    </div>
</div>

<!-- Upcoming Events Section (Only for Employees) -->
@if(isset($upcomingEvents) && $upcomingEvents->count() > 0)
    <div class="row mt-4 mb-2">
        <div class="col-12">
            <h5 class="text-accent fw-bold"><i class="bi bi-calendar-event-fill me-2"></i> Upcoming Events</h5>
        </div>
    </div>
    
    <div class="row g-4 mb-4">
        @foreach($upcomingEvents as $event)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm border-top border-4 border-success">
                <div class="card-body">
                    <span class="badge bg-success bg-opacity-10 text-success border mb-2">{{ $event->type }}</span>
                    <h5 class="fw-bold text-dark mb-3">{{ $event->title }}</h5>
                    
                    <div class="text-muted small mb-2">
                        <i class="bi bi-calendar me-2"></i> {{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}
                    </div>
                    <div class="text-muted small mb-2">
                        <i class="bi bi-clock me-2"></i> {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}
                    </div>
                    <div class="text-muted small mb-3">
                        <i class="bi bi-geo-alt-fill me-2"></i> {{ $event->venue }}
                    </div>

                    @if($event->description)
                        <p class="small text-muted border-top pt-2 mt-2">{{ Str::limit($event->description, 80) }}</p>
                    @endif

                    <div class="mt-auto pt-3">
                        @if($event->has_registered)
                            <button class="btn btn-light border text-success w-100 fw-bold disabled">
                                <i class="bi bi-check-circle-fill me-1"></i> You are Registered
                            </button>
                        @elseif($event->max_attendees && $event->current_attendees >= $event->max_attendees)
                            <button class="btn btn-light border text-danger w-100 fw-bold disabled">
                                <i class="bi bi-x-circle-fill me-1"></i> Event Full
                            </button>
                        @else
                            <form action="{{ route('events.register', $event->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 fw-bold shadow-sm">
                                    <i class="bi bi-calendar-plus me-1"></i> Register Now
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endif 
<!-- END EMPLOYEE DASHBOARD -->

<!-- HR DASHBOARD (Analytics Hub) -->
@if(session('role_id') == 2)
<div class="row g-4 mb-4">
    <!-- Card 1: Total Active Staff -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm rounded-4" style="border-left: 5px solid #1A3E6F !important;">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 50px; height: 50px; background-color: rgba(26, 62, 111, 0.1);">
                    <i class="bi bi-people fs-4" style="color: #1A3E6F;"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Total Active Staff</h6>
                    <h3 class="fw-bolder mb-0 text-dark">{{ $totalActiveStaff ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Teaching vs Non-Teaching -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm rounded-4" style="border-left: 5px solid #10B981 !important;">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 50px; height: 50px; background-color: rgba(16, 185, 129, 0.1);">
                    <i class="bi bi-person-badge fs-4" style="color: #10B981;"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Teaching / Non</h6>
                    <h3 class="fw-bolder mb-0 text-dark">{{ $teachingCount ?? 0 }} <span class="fs-5 text-muted fw-normal">/ {{ $nonTeachingCount ?? 0 }}</span></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Pending Leave Requests -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm rounded-4" style="border-left: 5px solid #F59E0B !important;">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 50px; height: 50px; background-color: rgba(245, 158, 11, 0.1);">
                    <i class="bi bi-calendar-check fs-4" style="color: #F59E0B;"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Pending Leaves</h6>
                    <h3 class="fw-bolder mb-0 text-dark">{{ $pendingLeaves ?? 0 }} <span class="fs-6 text-muted fw-normal">Pending</span></h3>
                </div>
                <a href="{{ route('hr.leave.index') }}" class="stretched-link"></a>
            </div>
        </div>
    </div>

    <!-- Card 4: Open Requisitions -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm rounded-4" style="border-left: 5px solid #EF4444 !important;">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 50px; height: 50px; background-color: rgba(239, 68, 68, 0.1);">
                    <i class="bi bi-clipboard-data fs-4" style="color: #EF4444;"></i>
                </div>
                <div>
                    <h6 class="text-muted small fw-bold text-uppercase mb-1">Open Requisitions</h6>
                    <h3 class="fw-bolder mb-0 text-dark">{{ $openRequisitions ?? 0 }} <span class="fs-6 text-muted fw-normal">Open</span></h3>
                </div>
                <a href="{{ route('requisitions.index') }}" class="stretched-link"></a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white pt-4 pb-3 border-bottom-0">
                <h5 class="text-accent fw-bold m-0"><i class="bi bi-bar-chart-line-fill me-2"></i> Staff Attendance Overview</h5>
            </div>
            <div class="card-body p-4">
                <div class="rounded d-flex align-items-center justify-content-center" style="height: 300px; background: #f8f9fa; border: 2px dashed #e2e8f0;">
                    <div class="text-center text-muted">
                        <i class="bi bi-graph-up fs-1 d-block mb-2 text-opacity-25"></i>
                        <p class="mb-0 fw-bold">Chart Integration Pending</p>
                        <small>Staff attendance graph will be displayed here.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <h5 class="text-accent fw-bold"><i class="bi bi-grid-fill me-2"></i> HR Management Modules</h5>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Announcements -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift" style="border-bottom: 4px solid #0d6efd !important;">
            <div class="card-body text-center p-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background-color: rgba(13, 110, 253, 0.1);">
                    <i class="bi bi-megaphone-fill fs-3 text-primary"></i>
                </div>
                <h6 class="fw-bold text-dark">Announcements</h6>
                <p class="text-muted small mb-4">Broadcast alerts, policies, and track read receipts across all staff.</p>
                <a href="{{ route('announcements.index') }}" class="btn btn-outline-primary btn-sm w-100 fw-bold rounded-pill">
                    Manage Broadcasts
                </a>
            </div>
        </div>
    </div>

    <!-- Event Organizer -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift" style="border-bottom: 4px solid #198754 !important;">
            <div class="card-body text-center p-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background-color: rgba(25, 135, 84, 0.1);">
                    <i class="bi bi-calendar-event-fill fs-3 text-success"></i>
                </div>
                <h6 class="fw-bold text-dark">Event Organizer</h6>
                <p class="text-muted small mb-4">Schedule meetings, track RSVPs, and monitor staff attendance.</p>
                <a href="{{ route('events.index') }}" class="btn btn-outline-success btn-sm w-100 fw-bold rounded-pill">
                    Manage Events
                </a>
            </div>
        </div>
    </div>

    <!-- System Reports -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift" style="border-bottom: 4px solid #0dcaf0 !important;">
            <div class="card-body text-center p-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background-color: rgba(13, 202, 240, 0.1);">
                    <i class="bi bi-bar-chart-fill fs-3 text-info"></i>
                </div>
                <h6 class="fw-bold text-dark">System Reports</h6>
                <p class="text-muted small mb-4">Generate comprehensive metrics on staff demographics and activities.</p>
                <a href="{{ route('hr.reports.index') }}" class="btn btn-outline-info btn-sm w-100 fw-bold rounded-pill">
                    View Reports
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
        </div>
    </div>
</div>
@endif



@endsection