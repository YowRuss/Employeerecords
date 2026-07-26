@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-brand fw-bold m-0"><i class="bi bi-bar-chart-fill me-2"></i> Institutional Reports</h4>
            <p class="text-muted small m-0">System-wide metrics and analytics overview.</p>
        </div>
        <button onclick="window.print()" class="btn btn-primary shadow-sm btn-sm fw-bold px-3 d-print-none">
            <i class="bi bi-printer-fill me-1"></i> Print Report
        </button>
    </div>

    <!-- 1. Demographics & Staffing -->
    <h6 class="text-accent fw-bold border-bottom pb-2 mb-3 mt-4">1. Staffing Overview</h6>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body py-4 text-center">
                    <h1 class="display-5 fw-bold mb-0">{{ $totalEmployees }}</h1>
                    <p class="small text-white-50 text-uppercase tracking-wide mb-0 mt-2">Registered Employees</p>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h6 class="fw-bold text-dark m-0 small text-uppercase">Employees by Position</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        @forelse($employeesByPosition as $pos)
                            <div class="col-md-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                    <span class="small text-muted">{{ $pos->position_name }}</span>
                                    <span class="badge bg-light text-dark border">{{ $pos->total }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-muted small">No position data available.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Leave Application Analytics -->
    <h6 class="text-accent fw-bold border-bottom pb-2 mb-3 mt-4">2. Leave Analytics</h6>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted small fw-bold text-uppercase mb-1">Pending Requests</h6>
                            <h3 class="fw-bold text-dark mb-0">{{ $leaveStats['Pending'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-hourglass-split fs-1 text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted small fw-bold text-uppercase mb-1">Approved Leaves</h6>
                            <h3 class="fw-bold text-dark mb-0">{{ $leaveStats['Approved'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-check-circle-fill fs-1 text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted small fw-bold text-uppercase mb-1">Denied Leaves</h6>
                            <h3 class="fw-bold text-dark mb-0">{{ $leaveStats['Denied'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-x-circle-fill fs-1 text-danger opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Engagement & Activities -->
    <h6 class="text-accent fw-bold border-bottom pb-2 mb-3 mt-4">3. System Engagement</h6>
    <div class="row g-3 mb-4">
        <!-- Events Column -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-dark small text-uppercase mb-3"><i class="bi bi-calendar-star text-success me-2"></i>Event Engagement</h6>
                    
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Total Events Hosted</span>
                        <span class="fw-bold">{{ $totalEvents }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Total Employee Registrations</span>
                        <span class="fw-bold">{{ $totalRegistrations }}</span>
                    </div>
                    
                    @php
                        $attendanceRate = $totalRegistrations > 0 ? round(($totalAttended / $totalRegistrations) * 100) : 0;
                    @endphp
                    <div class="mt-4">
                        <div class="d-flex justify-content-between small fw-bold mb-1">
                            <span class="text-success">Overall Attendance Rate</span>
                            <span>{{ $attendanceRate }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $attendanceRate }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Announcements Column -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-dark small text-uppercase mb-3"><i class="bi bi-megaphone text-primary me-2"></i>Announcement Reach</h6>
                    
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Total Broadcasts Sent</span>
                        <span class="fw-bold">{{ $totalAnnouncements }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Total Read Acknowledgments</span>
                        <span class="fw-bold">{{ $totalAcknowledgments }}</span>
                    </div>

                    @php
                        $maxPossibleReads = $totalAnnouncements * $totalEmployees;
                        $readRate = $maxPossibleReads > 0 ? round(($totalAcknowledgments / $maxPossibleReads) * 100) : 0;
                    @endphp
                    <div class="mt-4">
                        <div class="d-flex justify-content-between small fw-bold mb-1">
                            <span class="text-primary">Overall Read Receipt Rate</span>
                            <span>{{ $readRate }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $readRate }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling for the print button */
    @media print {
        body { background-color: #fff !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        .navbar, .sidebar { display: none !important; }
        .container-fluid { padding: 0 !important; }
    }
</style>
@endsection