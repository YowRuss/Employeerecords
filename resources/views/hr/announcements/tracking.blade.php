@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-brand fw-bold m-0"><i class="bi bi-eye-fill me-2"></i> Announcement Tracking</h4>
            <p class="text-muted small m-0">Monitor employee acknowledgments for this broadcast.</p>
        </div>
        <a href="{{ route('announcements.index') }}" class="btn btn-light border shadow-sm btn-sm fw-bold text-muted px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="row g-4">
        <!-- Announcement Details -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 border-top border-4 border-primary h-100">
                <div class="card-body">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border mb-2">{{ $announcement->type }}</span>
                    <h5 class="fw-bold text-dark">{{ $announcement->title }}</h5>
                    <p class="text-muted small mb-4">Published: {{ \Carbon\Carbon::parse($announcement->created_at)->format('M d, Y h:i A') }}</p>
                    
                    <div class="bg-light p-3 rounded-3 text-dark small mb-4 border">
                        {{ $announcement->content }}
                    </div>

                    <h6 class="fw-bold text-dark mb-2">Read Receipt Progress</h6>
                    @php
                        $percentage = $totalEmployees > 0 ? round(($acknowledgedCount / $totalEmployees) * 100) : 0;
                    @endphp
                    <div class="progress mb-2" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small fw-bold">
                        <span>{{ $acknowledgedCount }} Read</span>
                        <span>{{ $totalEmployees }} Total Employees</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acknowledgment List -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark m-0">Acknowledgment Ledger</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th class="ps-4">Employee Name</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($acknowledgedUsers as $user)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">
                                        {{ $user->last_name }}, {{ $user->first_name }}
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i class="bi bi-check2-all"></i> Read & Acknowledged</span>
                                    </td>
                                    <td class="text-end pe-4 text-muted small">
                                        {{ \Carbon\Carbon::parse($user->acknowledged_at)->format('M d, Y h:i A') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-5">
                                        <i class="bi bi-clock-history fs-2 d-block mb-2"></i>
                                        No employees have acknowledged this announcement yet.
                                    </td>
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
@endsection