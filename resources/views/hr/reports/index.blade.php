@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-header-blue fw-bold m-0"><i class="bi bi-bar-chart-fill me-2 text-header-blue"></i> Institutional Reports</h4>
            <p class="text-muted small m-0">System-wide metrics and analytics overview.</p>
        </div>
        <button onclick="window.print()" class="btn btn-accent shadow-sm btn-sm fw-bold px-3 d-print-none">
            <i class="bi bi-printer-fill me-1"></i> Print Report
        </button>
    </div>

    <h6 class="text-accent fw-bold border-bottom pb-2 mb-3">1. Staffing Overview</h6>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body py-4 text-center d-flex flex-column justify-content-center">
                    <div class="display-4 fw-bolder text-accent mb-0">{{ $totalEmployees }}</div>
                    <p class="small text-muted text-uppercase fw-bold mb-0 mt-2" style="letter-spacing: 0.08em;">Registered Employees</p>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-dark m-0 small text-uppercase" style="letter-spacing: 0.04em;">
                        <i class="bi bi-people-fill text-accent me-2"></i>Employees by Position
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @forelse($employeesByPosition as $pos)
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center gap-2 border rounded-3 px-3 py-2 bg-light h-100">
                                    <span class="small fw-semibold text-dark">{{ $pos->position_name }}</span>
                                    <span class="badge bg-accent flex-shrink-0">{{ $pos->total }}</span>
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

    <h6 class="text-accent fw-bold border-bottom pb-2 mb-3">2. Leave Analytics</h6>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100 bg-warning bg-opacity-10">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="bi bi-hourglass-split fs-4 text-dark"></i>
                        </div>
                        <span class="text-dark small fw-bold text-uppercase">Pending</span>
                    </div>
                    <h2 class="fw-bolder text-dark mb-0">{{ $leaveStats['Pending'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 bg-success bg-opacity-10">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="bi bi-check-lg fs-3 text-white"></i>
                        </div>
                        <span class="text-success small fw-bold text-uppercase">Approved</span>
                    </div>
                    <h2 class="fw-bolder text-success mb-0">{{ $leaveStats['Approved'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 bg-danger bg-opacity-10">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="bi bi-x-lg fs-4 text-white"></i>
                        </div>
                        <span class="text-danger small fw-bold text-uppercase">Rejected</span>
                    </div>
                    <h2 class="fw-bolder text-danger mb-0">{{ $leaveStats['Denied'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <h6 class="text-accent fw-bold border-bottom pb-2 mb-3">3. System Engagement</h6>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <h6 class="fw-bold text-dark small text-uppercase mb-4">
                        <span class="bg-accent p-2 rounded-circle me-2"><i class="bi bi-calendar-star"></i></span> Event Engagement
                    </h6>

                    <div class="d-flex justify-content-between align-items-center p-3 mb-2 rounded-3 bg-light border">
                        <span class="text-muted fw-medium small">Total Events Hosted</span>
                        <span class="fw-bolder fs-5 text-dark">{{ $totalEvents }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-light border">
                        <span class="text-muted fw-medium small">Total Employee Registrations</span>
                        <span class="fw-bolder fs-5 text-dark">{{ $totalRegistrations }}</span>
                    </div>

                    @php
                        $attendanceRate = $totalRegistrations > 0 ? round(($totalAttended / $totalRegistrations) * 100) : 0;
                    @endphp
                    <div class="mt-4">
                        <div class="d-flex justify-content-between small fw-bold mb-2">
                            <span class="text-accent">Overall Attendance Rate</span>
                            <span class="text-dark">{{ $attendanceRate }}%</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 10px;">
                            <div class="progress-bar rounded-pill" role="progressbar" style="width: {{ $attendanceRate }}%; background-color: var(--accent-yellow);" aria-valuenow="{{ $attendanceRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <h6 class="fw-bold text-dark small text-uppercase mb-4">
                        <span class="bg-accent p-2 rounded-circle me-2"><i class="bi bi-megaphone"></i></span> Announcement Reach
                    </h6>

                    <div class="d-flex justify-content-between align-items-center p-3 mb-2 rounded-3 bg-light border">
                        <span class="text-muted fw-medium small">Total Broadcasts Sent</span>
                        <span class="fw-bolder fs-5 text-dark">{{ $totalAnnouncements }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-light border">
                        <span class="text-muted fw-medium small">Total Read Acknowledgments</span>
                        <span class="fw-bolder fs-5 text-dark">{{ $totalAcknowledgments }}</span>
                    </div>

                    @php
                        $maxPossibleReads = $totalAnnouncements * $totalEmployees;
                        $readRate = $maxPossibleReads > 0 ? round(($totalAcknowledgments / $maxPossibleReads) * 100) : 0;
                    @endphp
                    <div class="mt-4">
                        <div class="d-flex justify-content-between small fw-bold mb-2">
                            <span class="text-accent">Overall Read Receipt Rate</span>
                            <span class="text-dark">{{ $readRate }}%</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 10px;">
                            <div class="progress-bar rounded-pill" role="progressbar" style="width: {{ $readRate }}%; background-color: var(--accent-yellow);" aria-valuenow="{{ $readRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body { background-color: #fff !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        .navbar, .sidebar { display: none !important; }
        .container-fluid { padding: 0 !important; }
    }
</style>
@endsection
