@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
        <div>
            <h4 class="text-header-blue fw-bold m-0"><i class="bi bi-megaphone-fill me-2 text-header-blue"></i> Manage Job Postings</h4>
            <p class="text-muted small mt-1 mb-0">Create and edit job openings shown on the public careers page.</p>
        </div>
        <button type="button" class="btn btn-accent shadow-sm rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#addPostingModal">
            <i class="bi bi-plus-circle me-1"></i> New Job Posting
        </button>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-dark">Active & Closed Postings</h6>
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">{{ count($postings) }} Records</span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle text-center mb-0 border-top-0" style="font-size: 0.9rem;">
                <thead class="bg-light" style="border-bottom: 2px solid #f1f5f9;">
                    <tr>
                        <th class="text-start ps-4 text-muted small fw-bold text-uppercase py-3 border-0">Position Details</th>
                        <th class="text-muted small fw-bold text-uppercase py-3 border-0 text-start d-none d-md-table-cell">Department & Type</th>
                        <th class="text-muted small fw-bold text-uppercase py-3 border-0 d-none d-md-table-cell">Location & Salary</th>
                        <th class="text-muted small fw-bold text-uppercase py-3 border-0 d-none d-md-table-cell">Status</th>
                        <th class="text-muted small fw-bold text-uppercase py-3 border-0 pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody style="border-top: none;">
                    @forelse($postings as $job)
                    <tr class="transition-all">
                        <!-- Position Details -->
                        <td class="text-start ps-4 py-3 border-light">
                            <div class="fw-bold text-dark text-uppercase" style="font-size: 0.95rem; letter-spacing: 0.2px;">{{ $job->position->position_name }}</div>
                            <div class="small text-muted text-wrap mt-1" style="max-width: 300px;">
                                <i class="bi bi-card-text me-1"></i> {{ Str::limit($job->description, 60) }}
                            </div>
                            
                            <!-- Mobile Only Details -->
                            <div class="d-md-none mt-3 p-2 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-accent bg-opacity-25 text-dark border border-warning border-opacity-50 rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                        {{ $job->employment_type }}
                                    </span>
                                    @if($job->is_active)
                                        <span class="badge bg-success px-2 py-1 rounded-pill" style="font-size: 0.7rem;"><i class="bi bi-globe"></i> Live</span>
                                    @else
                                        <span class="badge bg-secondary px-2 py-1 rounded-pill" style="font-size: 0.7rem;"><i class="bi bi-eye-slash-fill"></i> Closed</span>
                                    @endif
                                </div>
                                <div class="small fw-bold text-dark mb-1"><i class="bi bi-building me-1 text-muted"></i> {{ $job->department }}</div>
                                <div class="small text-muted mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $job->location }}</div>
                                <div class="small fw-bold text-dark"><i class="bi bi-cash-stack text-success me-1"></i> {{ $job->salary_info }}</div>
                            </div>
                        </td>
                        
                        <!-- Department & Type -->
                        <td class="border-light text-start d-none d-md-table-cell">
                            <div class="fw-bold text-dark"><i class="bi bi-building me-1 text-muted"></i> {{ $job->department }}</div>
                            <div class="mt-1">
                                <span class="badge bg-accent bg-opacity-25 text-dark border border-warning border-opacity-50 rounded-pill px-2 py-1">
                                    {{ $job->employment_type }}
                                </span>
                            </div>
                        </td>

                        <!-- Location & Salary -->
                        <td class="border-light text-muted d-none d-md-table-cell">
                            <div class="mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $job->location }}</div>
                            <div class="small fw-bold text-dark"><i class="bi bi-cash-stack text-success me-1"></i> {{ $job->salary_info }}</div>
                        </td>
                        
                        <!-- Status Badge -->
                        <td class="border-light d-none d-md-table-cell">
                            @if($job->is_active)
                                <span class="badge bg-success px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-globe me-1"></i> Live</span>
                            @else
                                <span class="badge bg-secondary px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-eye-slash-fill me-1"></i> Closed</span>
                            @endif
                        </td>
                        
                        <!-- Action Buttons -->
                        <td class="border-light text-end pe-4">
                            <div class="btn-group shadow-sm">
                                <button type="button" class="btn btn-sm btn-light border text-accent fw-bold" data-bs-toggle="modal" data-bs-target="#editModal{{ $job->id }}">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <form action="{{ route('hr.job_postings.destroy', $job->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this posting permanently?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-light border text-danger fw-bold rounded-end" style="border-left: 0 !important;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade text-start" id="editModal{{ $job->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                <div class="modal-header bg-light border-0 py-3">
                                    <h5 class="modal-title fw-bold text-dark m-0"><i class="bi bi-pencil-square me-2 text-accent"></i> Edit Job Posting</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('hr.job_postings.update', $job->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body p-4">
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Position</label>
                                                <select name="position_id" class="form-select p-2 bg-light border-0 focus-ring rounded-3" required>
                                                    @foreach($positions as $pos)
                                                        <option value="{{ $pos->id }}" {{ $job->position_id == $pos->id ? 'selected' : '' }}>{{ $pos->position_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Department</label>
                                                <input type="text" name="department" class="form-control p-2 bg-light border-0 focus-ring rounded-3" value="{{ $job->department }}" required>
                                            </div>
                                        </div>
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Employment Type</label>
                                                <select name="employment_type" class="form-select p-2 bg-light border-0 focus-ring rounded-3" required>
                                                    <option value="Full-Time" {{ $job->employment_type == 'Full-Time' ? 'selected' : '' }}>Full-Time</option>
                                                    <option value="Part-Time" {{ $job->employment_type == 'Part-Time' ? 'selected' : '' }}>Part-Time</option>
                                                    <option value="Contractual" {{ $job->employment_type == 'Contractual' ? 'selected' : '' }}>Contractual</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Location</label>
                                                <input type="text" name="location" class="form-control p-2 bg-light border-0 focus-ring rounded-3" value="{{ $job->location }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Salary Info</label>
                                                <input type="text" name="salary_info" class="form-control p-2 bg-light border-0 focus-ring rounded-3" value="{{ $job->salary_info }}" required>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Job Description / Requirements</label>
                                            <textarea name="description" class="form-control p-3 bg-light border-0 focus-ring rounded-3" rows="4" required>{{ $job->description }}</textarea>
                                        </div>
                                        <div class="p-3 bg-accent bg-opacity-10 border border-warning border-opacity-25 rounded-3 d-flex align-items-center">
                                            <div class="form-check form-switch m-0 d-flex align-items-center w-100">
                                                <input class="form-check-input mt-0 fs-5" type="checkbox" name="is_active" value="1" id="active{{ $job->id }}" {{ $job->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold text-dark ms-3 flex-grow-1" style="cursor: pointer;" for="active{{ $job->id }}">
                                                    Make visible on public website
                                                    <span class="d-block small text-muted fw-normal">Turning this on will immediately show the job posting on the careers page.</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-top-0 py-3">
                                        <button type="button" class="btn btn-light px-4 fw-bold rounded-pill text-muted" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-accent px-4 fw-bold shadow-sm rounded-pill">Update Posting</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5 border-light">
                            <div class="py-4">
                                <i class="bi bi-megaphone fs-1 d-block mb-3 opacity-50"></i>
                                <p class="mb-0 fw-medium">No job postings available.</p>
                                <p class="small">Click "New Job Posting" to create one.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-3 border-top border-light text-muted small d-flex justify-content-center">
            <span>Showing all recorded job postings.</span>
        </div>
    </div>
</div>

<!-- Add Posting Modal -->
<div class="modal fade text-start" id="addPostingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-accent text-dark border-0 py-3">
                <h5 class="modal-title fw-bold m-0"><i class="bi bi-plus-circle-fill me-2"></i> Create New Job Posting</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('hr.job_postings.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Position <span class="text-danger">*</span></label>
                            <select name="position_id" class="form-select p-2 bg-light border-0 focus-ring rounded-3" required>
                                <option value="" disabled selected>Select Position...</option>
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->id }}">{{ $pos->position_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Department <span class="text-danger">*</span></label>
                            <input type="text" name="department" class="form-control p-2 bg-light border-0 focus-ring rounded-3" placeholder="e.g., Teaching Faculty" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Employment Type <span class="text-danger">*</span></label>
                            <select name="employment_type" class="form-select p-2 bg-light border-0 focus-ring rounded-3" required>
                                <option value="Full-Time">Full-Time</option>
                                <option value="Part-Time">Part-Time</option>
                                <option value="Contractual">Contractual</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Location <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control p-2 bg-light border-0 focus-ring rounded-3" placeholder="e.g. Main Campus" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Salary Info <span class="text-danger">*</span></label>
                            <input type="text" name="salary_info" class="form-control p-2 bg-light border-0 focus-ring rounded-3" placeholder="e.g. Salary Grade 11" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Job Description / Requirements <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control p-3 bg-light border-0 focus-ring rounded-3" rows="4" placeholder="Enter the complete job description, qualifications, and requirements..." required></textarea>
                    </div>
                    <input type="hidden" name="is_active" value="1">
                    
                    <div class="alert alert-info border-0 shadow-sm rounded-3 mt-3 mb-0 d-flex align-items-center">
                        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                        <div class="small">By default, this job posting will be immediately visible on the public careers page once published. You can change its visibility later by editing it.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4 fw-bold rounded-pill text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent px-4 fw-bold shadow-sm rounded-pill">Publish Posting</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection