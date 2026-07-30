@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-accent m-0">Employee Profile</h4>
        <a href="{{ route('hr.staff_profiling') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Directory
        </a>
    </div>
    
    <div class="row">
        <div class="col-xl-4 col-lg-5 mb-4">
            <!-- Profile Card -->
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100">
                <div class="bg-accent" style="height: 120px; background: linear-gradient(135deg, #fdd10dff 0%, #ecf00dff 100%); opacity: 0.85;"></div>
                <div class="card-body text-center position-relative pb-4">
                    <div class="position-absolute top-0 start-50 translate-middle-x" style="margin-top: -60px;">
                        @if($employee->profile_image)
                            <img src="data:{{ $employee->image_type }};base64,{{ base64_encode($employee->profile_image) }}" 
                                 class="rounded-circle shadow-lg bg-white p-1" 
                                 style="width: 130px; height: 130px; object-fit: cover; border: 3px solid #fff;" 
                                 alt="Profile">
                        @else
                            <div class="rounded-circle bg-white text-warning d-flex align-items-center justify-content-center fw-bold shadow-lg" 
                                 style="width: 130px; height: 130px; font-size: 3.5rem; border: 4px solid #fff;">
                                {{ strtoupper(substr($employee->first_name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    
                    <div style="margin-top: 75px;">
                        <div class="d-flex justify-content-center align-items-center mb-1">
                            <h5 class="fw-bold m-0 me-2">{{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }} {{ $employee->suffix }}</h5>
                            <button type="button" class="btn btn-sm btn-light border rounded-circle text-warning" data-bs-toggle="modal" data-bs-target="#editNameModal" title="Edit Official Name" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-pencil-fill" style="font-size: 0.8rem;"></i>
                            </button>
                        </div>
                        <p class="text-muted mb-3">
                            <span class="d-block fw-semibold">{{ $serviceRecordPosition->position_name ?? 'No Position Assigned' }}</span>
                            @if(isset($serviceRecordPosition) && $serviceRecordPosition->category === 'Teaching')
                            <span class="d-inline-flex align-items-center mt-1 text-dark" style="font-size: 0.9rem;">
                                <span>Area of Specialization: <span class="fw-bold">{{ $employee->learningArea->name ?? 'Not Set' }}</span></span>
                                <button type="button" class="btn btn-sm btn-light border shadow-sm rounded-circle text-primary ms-2" data-bs-toggle="modal" data-bs-target="#editLearningAreaModal" title="Edit Area of Specialization" style="width: 24px; height: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-pencil-square" style="font-size: 0.7rem;"></i>
                                </button>
                            </span>
                            @endif
                        </p>
                        <div class="d-grid gap-2">
                            <a href="{{ route('hr.view_pds', $employee->id) }}" class="btn btn-warning fw-bold text-dark rounded-pill shadow-sm">
                                <i class="bi bi-person-vcard me-1"></i> View PDS
                            </a>
                            <a href="{{ route('hr.view_saln', $employee->id) }}" class="btn btn-outline-warning fw-bold text-dark rounded-pill">
                                <i class="bi bi-cash-coin me-1"></i> View SALN
                            </a>
                            <a href="{{ route('hr.service_record.index', $employee->id) }}" class="btn btn-outline-secondary rounded-pill">
                                <i class="bi bi-card-list me-1"></i> Service Record
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-8 col-lg-7 mb-4">
            <!-- Details Card -->
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-info-circle text-accent me-2"></i> Information Overview</h5>
                </div>
                <div class="card-body p-4">
                    <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab" aria-controls="account" aria-selected="true">
                                Account Details
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">
                                Emergency Contact
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="profileTabsContent">
                        <!-- Account Details Tab -->
                        <div class="tab-pane fade show active" id="account" role="tabpanel" aria-labelledby="account-tab">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 h-100">
                                        <small class="text-muted d-block mb-1">Username</small>
                                        <div class="fw-medium text-dark"><i class="bi bi-person me-2 text-warning"></i>{{ $employee->username }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 h-100">
                                        <small class="text-muted d-block mb-1">System Role</small>
                                        <div class="fw-medium text-dark">
                                            <i class="bi bi-shield-check me-2 text-success"></i>
                                            @if($employee->role_id == 1) Employee
                                            @elseif($employee->role_id == 2) HR Staff
                                            @elseif($employee->role_id == 3) Administrator
                                            @elseif($employee->role_id == 4) Head / Approver
                                            @else Unknown Role
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 h-100">
                                        <small class="text-muted d-block mb-1">Account Created</small>
                                        <div class="fw-medium text-dark"><i class="bi bi-calendar-plus me-2 text-info"></i>{{ \Carbon\Carbon::parse($employee->created_at)->format('F d, Y') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 h-100">
                                        <small class="text-muted d-block mb-1">Last Updated</small>
                                        <div class="fw-medium text-dark"><i class="bi bi-clock-history me-2 text-warning"></i>{{ \Carbon\Carbon::parse($employee->updated_at)->format('F d, Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Emergency Contact Tab -->
                        <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                            @if($employee->emergency_contact_person)
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 border-danger-subtle bg-danger-subtle bg-opacity-10 h-100">
                                        <small class="text-danger d-block mb-1">Contact Person</small>
                                        <div class="fw-medium text-dark"><i class="bi bi-person-heart me-2 text-danger"></i>{{ $employee->emergency_contact_person }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 border-danger-subtle bg-danger-subtle bg-opacity-10 h-100">
                                        <small class="text-danger d-block mb-1">Contact Number</small>
                                        <div class="fw-medium text-dark"><i class="bi bi-telephone-fill me-2 text-danger"></i>{{ $employee->emergency_contact_number }}</div>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="text-center py-5 text-muted bg-light rounded-3">
                                <i class="bi bi-exclamation-circle text-warning fs-1 d-block mb-3"></i>
                                <h5>No Contact Information</h5>
                                <p class="mb-0">This employee hasn't provided emergency contact details yet.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Official Name Modal -->
<div class="modal fade text-start" id="editNameModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-warning text-dark border-0 py-3">
                <h5 class="modal-title fw-bold m-0"><i class="bi bi-person-lines-fill me-2"></i> Edit Official Name</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('hr.update_official_name', $employee->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning py-2 small mb-3 border-0 shadow-sm" style="background-color: #fff3cd; border-left: 4px solid #ffc107 !important;">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> <strong class="text-dark">Warning:</strong> <span class="text-muted">This will permanently change the employee's official name across the system.</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control text-uppercase p-3 bg-light border-0 focus-ring" value="{{ $employee->first_name }}" required style="border-radius: 8px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control text-uppercase p-3 bg-light border-0 focus-ring" value="{{ $employee->last_name }}" required style="border-radius: 8px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control text-uppercase p-3 bg-light border-0 focus-ring" value="{{ $employee->middle_name }}" style="border-radius: 8px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Suffix <span class="text-muted text-lowercase fw-normal">(Jr, Sr)</span></label>
                            <input type="text" name="suffix" class="form-control text-uppercase p-3 bg-light border-0 focus-ring" value="{{ $employee->suffix }}" style="border-radius: 8px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4 fw-bold rounded-pill text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark px-4 fw-bold shadow-sm rounded-pill">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Learning Area Modal -->
<div class="modal fade text-start" id="editLearningAreaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-white border-bottom py-3" style="border-bottom: 2px solid var(--accent-yellow) !important;">
                <h5 class="modal-title fw-bold m-0" style="color: #1A3E6F;"><i class="bi bi-book-half me-2 text-warning"></i> Edit Area of Specialization</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('hr.update_learning_area', $employee->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase tracking-wider" style="color: #1A3E6F;">Learning Area <span class="text-danger">*</span></label>
                        <select name="learning_area_id" class="form-select p-3 bg-light border-0 focus-ring" required style="border-radius: 8px;">
                            <option value="" disabled {{ !$employee->learning_area_id ? 'selected' : '' }}>Select a Learning Area</option>
                            @foreach($learningAreas as $area)
                                <option value="{{ $area->id }}" {{ $employee->learning_area_id == $area->id ? 'selected' : '' }}>
                                    {{ $area->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4 fw-bold rounded-pill text-muted border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white px-4 fw-bold shadow-sm rounded-pill" style="background-color: #1A3E6F;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection