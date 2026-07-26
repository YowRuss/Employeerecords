@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-brand fw-bold m-0"><i class="bi bi-person-lines-fill me-2"></i> Applicant Tracking</h4>
            <p class="text-muted small m-0">Review incoming job applications and manage candidate statuses.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success shadow-sm border-0 rounded-3">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger shadow-sm border-0 rounded-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
    </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold text-dark m-0">Recent Applications</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th class="ps-4">Applicant Name</th>
                            <th>Position Applied For</th>
                            <th>Contact Info</th>
                            <th>Date Applied</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">
                                {{ $app->last_name }}, {{ $app->first_name }}
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                    {{ $app->position->position_name ?? 'Unknown Position' }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                <div><i class="bi bi-envelope me-1"></i> {{ $app->email }}</div>
                                <div><i class="bi bi-telephone me-1"></i> {{ $app->contact_number }}</div>
                            </td>
                            <td class="small text-muted">
                                {{ \Carbon\Carbon::parse($app->created_at)->format('M d, Y') }}
                            </td>
                            <td>
                                @if($app->status == 'Hired')
                                    <span class="badge bg-success">Hired</span>
                                @elseif($app->status == 'Rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @elseif($app->status == 'Interviewing')
                                    <span class="badge bg-info text-dark">Interviewing</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('hr.applications.resume', $app->id) }}" class="btn btn-sm btn-outline-primary fw-bold" title="Download Resume">
                                    <i class="bi bi-file-earmark-arrow-down-fill"></i> Resume
                                </a>
                                <button type="button" class="btn btn-sm btn-light border text-dark fw-bold ms-1" data-bs-toggle="modal" data-bs-target="#statusModal{{ $app->id }}">
                                    Update
                                </button>
                            </td>
                        </tr>

                        <!-- Update Status Modal -->
                        <div class="modal fade" id="statusModal{{ $app->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title fw-bold text-dark">Update Applicant Status</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('hr.applications.update', $app->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <p class="small text-muted mb-3">Update the hiring pipeline status for <strong>{{ $app->first_name }} {{ $app->last_name }}</strong>.</p>
                                            
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-dark">Current Status</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="Pending" {{ $app->status == 'Pending' ? 'selected' : '' }}>Pending Review</option>
                                                    <option value="Interviewing" {{ $app->status == 'Interviewing' ? 'selected' : '' }}>Interviewing</option>
                                                    <option value="Hired" {{ $app->status == 'Hired' ? 'selected' : '' }}>Hired / Offer Accepted</option>
                                                    <option value="Rejected" {{ $app->status == 'Rejected' ? 'selected' : '' }}>Rejected / Not Selected</option>
                                                </select>
                                            </div>

                                            @if($app->cover_letter)
                                            <hr>
                                            <label class="form-label small fw-bold text-dark">Applicant's Message / Cover Letter</label>
                                            <div class="p-3 bg-light rounded text-muted small border">
                                                {{ $app->cover_letter }}
                                            </div>
                                            @endif
                                        </div>
                                        <div class="modal-footer bg-light border-top-0">
                                            <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary fw-bold px-4">Save Status</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No applications received yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection