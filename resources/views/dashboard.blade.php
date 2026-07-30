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

<!-- HR DASHBOARD -->
@if(session('role_id') == 2)
<div class="row g-4">
    <div class="col-12">
        <div class="card p-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-accent fw-bold mb-0">Manage Positions</h5>
                <form action="{{ route('hr.positions.store') }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="text" name="position_name" class="form-control form-control-sm text-uppercase" placeholder="New Position Name" required>
                    <select name="category" class="form-select form-select-sm" required>
                        <option value="" disabled selected>Select Category</option>
                        <option value="Teaching">Teaching</option>
                        <option value="Non-Teaching">Non-Teaching</option>
                    </select>
                    <button type="submit" class="btn btn-accent btn-sm fw-bold">Add Position</button>
                </form>
            </div>

            <style>
                .position-tabs .nav-link {
                    border: none;
                    color: #1A3E6F;
                    font-weight: 600;
                    background: transparent;
                    border-radius: 0;
                    padding: 0.75rem 1.25rem;
                    opacity: 0.7;
                    border-bottom: 3px solid transparent;
                }
                .position-tabs .nav-link:hover {
                    opacity: 1;
                    border-color: rgba(253, 224, 71, 0.5);
                }
                .position-tabs .nav-link.active {
                    background-color: var(--accent-yellow, #FDE047);
                    color: #1A3E6F;
                    opacity: 1;
                    border-color: var(--accent-yellow, #FDE047);
                    border-radius: 8px 8px 0 0;
                }
            </style>

            <ul class="nav nav-tabs position-tabs border-bottom-0" id="dashboardPositionTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="dash-teaching-tab" data-bs-toggle="tab" data-bs-target="#dash-teaching" type="button" role="tab" aria-controls="dash-teaching" aria-selected="true">
                        Teaching Positions <span class="badge bg-white text-dark ms-1 border">{{ count($teachingPositions) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="dash-non-teaching-tab" data-bs-toggle="tab" data-bs-target="#dash-non-teaching" type="button" role="tab" aria-controls="dash-non-teaching" aria-selected="false">
                        Non-Teaching Positions <span class="badge bg-white text-dark ms-1 border">{{ count($nonTeachingPositions) }}</span>
                    </button>
                </li>
            </ul>
            
            <div class="tab-content border-top border-light pt-3" id="dashboardPositionTabsContent">
                <!-- Teaching Tab -->
                <div class="tab-pane fade show active" id="dash-teaching" role="tabpanel" aria-labelledby="dash-teaching-tab">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle bg-white">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Position Name</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teachingPositions as $pos)
                                <tr>
                                    <td>{{ $pos->id }}</td>
                                    <td class="fw-bold text-uppercase">{{ $pos->position_name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($pos->created_at)->format('M d, Y') }}</td>
                                    <td>
                                        <form action="{{ route('hr.positions.destroy', $pos->id) }}" method="POST" onsubmit="return confirm('Delete this position?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No teaching positions created yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Non-Teaching Tab -->
                <div class="tab-pane fade" id="dash-non-teaching" role="tabpanel" aria-labelledby="dash-non-teaching-tab">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle bg-white">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Position Name</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nonTeachingPositions as $pos)
                                <tr>
                                    <td>{{ $pos->id }}</td>
                                    <td class="fw-bold text-uppercase">{{ $pos->position_name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($pos->created_at)->format('M d, Y') }}</td>
                                    <td>
                                        <form action="{{ route('hr.positions.destroy', $pos->id) }}" method="POST" onsubmit="return confirm('Delete this position?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No non-teaching positions created yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

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

<!-- PRINCIPAL DASHBOARD -->
@if(session('role_id') == 4)
<div class="row mb-4">
    <div class="col-12">
        <h4 class="fw-bold text-dark mb-1">Welcome, Principal</h4>
        <p class="text-muted small">Select a module below to manage school operations and staff requests.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Power 1: Announcements -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm border-bottom border-4 border-primary hover-lift">
            <div class="card-body text-center p-4">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-megaphone-fill text-primary fs-3"></i>
                </div>
                <h6 class="fw-bold text-dark">Announcements</h6>
                <p class="text-muted small mb-4">Broadcast alerts, policies, and track read receipts across all staff.</p>
                <a href="{{ route('announcements.index') }}" class="btn btn-outline-primary btn-sm w-100 fw-bold rounded-pill">
                    Manage Broadcasts
                </a>
            </div>
        </div>
    </div>

    <!-- Power 2: Event Organizer -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm border-bottom border-4 border-success hover-lift">
            <div class="card-body text-center p-4">
                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-calendar-star-fill text-success fs-3"></i>
                </div>
                <h6 class="fw-bold text-dark">Event Organizer</h6>
                <p class="text-muted small mb-4">Schedule meetings, track RSVPs, and monitor staff attendance.</p>
                <a href="{{ route('events.index') }}" class="btn btn-outline-success btn-sm w-100 fw-bold rounded-pill">
                    Manage Events
                </a>
            </div>
        </div>
    </div>

    <!-- Power 3: Leave Approvals (Final Authority) -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm border-bottom border-4 border-warning hover-lift">
            <div class="card-body text-center p-4">
                <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-check-circle-fill text-warning fs-3"></i>
                </div>
                <h6 class="fw-bold text-dark">Leave Approvals</h6>
                <p class="text-muted small mb-4">Review, approve, or deny all staff leave applications.</p>
                <a href="{{ route('principal.leaves.index') }}" class="btn btn-outline-warning btn-sm w-100 fw-bold rounded-pill">
                    Manage Leave Requests
                </a>
            </div>
        </div>
    </div>

    <!-- Institutional Reports -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm border-bottom border-4 border-info hover-lift">
            <div class="card-body text-center p-4">
                <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-bar-chart-fill text-info fs-3"></i>
                </div>
                <h6 class="fw-bold text-dark">System Reports</h6>
                <p class="text-muted small mb-4">Generate comprehensive metrics on staff demographics and activities.</p>
                <a class="btn btn-outline-info btn-sm w-100 fw-bold rounded-pill" href="{{ route('principal.reports.index') }}">
                    View Reports
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }
</style>
@endif

@endsection