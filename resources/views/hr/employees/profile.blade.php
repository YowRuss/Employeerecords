@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-header-blue m-0">Employee Profile</h4>
        <a href="{{ route('hr.staff_profiling') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Directory
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    
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
                            <span class="d-inline-flex align-items-center fw-semibold text-dark" style="font-size: 1.1rem;">
                                {{ $employee->position->position_name ?? 'No Position Assigned' }}
                                <button type="button" class="btn btn-sm btn-light border shadow-sm rounded-circle text-primary ms-2" data-bs-toggle="modal" data-bs-target="#editPositionModal" title="Update Employee Position" style="width: 24px; height: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-pencil-square" style="font-size: 0.7rem;"></i>
                                </button>
                            </span>
                            <br>
                            <div id="learningAreaWrapper" style="{{ (isset($employee->position) && $employee->position->category === \App\Enums\PositionCategory::Teaching) ? '' : 'display: none;' }}">
                            <span class="d-inline-flex align-items-center mt-1 text-dark" style="font-size: 0.9rem;">
                                <span>Area of Specialization: <span class="fw-bold">{{ $employee->learningArea->name ?? 'Not Set' }}</span></span>
                                <button type="button" class="btn btn-sm btn-light border shadow-sm rounded-circle text-primary ms-2" data-bs-toggle="modal" data-bs-target="#editLearningAreaModal" title="Edit Area of Specialization" style="width: 24px; height: 24px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-pencil-square" style="font-size: 0.7rem;"></i>
                                </button>
                            </span>
                            </div>
                        </p>
                        <div class="d-grid gap-2">
                            <a href="{{ route('hr.view_pds', $employee->id) }}" class="btn btn-warning fw-bold text-dark rounded-pill shadow-sm">
                                <i class="bi bi-person-vcard me-1"></i> View PDS
                            </a>
                            <a href="{{ route('hr.view_saln', $employee->id) }}" class="btn btn-outline-warning fw-bold text-dark rounded-pill">
                                <i class="bi bi-cash-coin me-1"></i> View SALN
                            </a>
                            <a href="{{ route('hr.service_record.show', $employee->id) }}" class="btn btn-outline-secondary rounded-pill">
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
                    @php $profileTab = session('active_tab', 'account'); @endphp
                    <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $profileTab === 'account' ? 'active' : '' }} fw-semibold" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab" aria-controls="account" aria-selected="{{ $profileTab === 'account' ? 'true' : 'false' }}">
                                Account Details
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $profileTab === 'contact' ? 'active' : '' }} fw-semibold" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="{{ $profileTab === 'contact' ? 'true' : 'false' }}">
                                Emergency Contact
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $profileTab === 'credits' ? 'active' : '' }} fw-semibold" id="credits-tab" data-bs-toggle="tab" data-bs-target="#credits" type="button" role="tab" aria-controls="credits" aria-selected="{{ $profileTab === 'credits' ? 'true' : 'false' }}">
                                Service Credits &amp; CTO
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="profileTabsContent">
                        <!-- Account Details Tab -->
                        <div class="tab-pane fade {{ $profileTab === 'account' ? 'show active' : '' }}" id="account" role="tabpanel" aria-labelledby="account-tab">
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
                        <div class="tab-pane fade {{ $profileTab === 'contact' ? 'show active' : '' }}" id="contact" role="tabpanel" aria-labelledby="contact-tab">
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

                        <div class="tab-pane fade {{ $profileTab === 'credits' ? 'show active' : '' }}" id="credits" role="tabpanel" aria-labelledby="credits-tab">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                                <div class="rounded-4 px-4 py-3 text-white" style="background: #1A3E6F; min-width: 220px;">
                                    <div class="small text-uppercase" style="letter-spacing: 0.04em; opacity: 0.8;">Current Balance</div>
                                    <div class="fs-2 fw-bolder mb-0">{{ number_format($employee->available_credits, 1) }} <span class="fs-6 fw-semibold">days</span></div>
                                </div>
                                <button type="button" class="btn fw-bold rounded-pill px-4" style="background: #ffc107; color: #1A3E6F;" data-bs-toggle="modal" data-bs-target="#grantCreditsModal">
                                    <i class="bi bi-plus-circle me-1"></i> Grant Credits
                                </button>
                            </div>

                            <input type="search" id="serviceCreditSearch" class="form-control form-control-sm mb-3" placeholder="Search ledger..." style="max-width: 280px;">

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="serviceCreditTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="small text-muted text-uppercase">Date</th>
                                            <th class="small text-muted text-uppercase">Description</th>
                                            <th class="small text-muted text-uppercase">Type</th>
                                            <th class="small text-muted text-uppercase text-end">Days</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employee->serviceCredits as $credit)
                                        <tr data-search="{{ strtolower($credit->description.' '.$credit->type) }}">
                                            <td>{{ $credit->transaction_date?->format('M d, Y') }}</td>
                                            <td>{{ $credit->description }}</td>
                                            <td>
                                                @if($credit->type === 'earned')
                                                    <span class="badge rounded-pill text-bg-success">Earned</span>
                                                @else
                                                    <span class="badge rounded-pill text-bg-warning">Used</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-semibold">{{ $credit->type === 'earned' ? '+' : '-' }}{{ number_format((float) $credit->days, 1) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No service credit transactions yet.</td>
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
    </div>
</div>

<div class="modal fade" id="grantCreditsModal" tabindex="-1" aria-labelledby="grantCreditsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0" style="background: #ffc107;">
                <h5 class="modal-title fw-bold" id="grantCreditsModalLabel" style="color: #1A3E6F;">Grant Service Credits</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('hr.service_credits.store', $employee->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="transaction_date">Transaction Date</label>
                        <input type="date" class="form-control" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="credit_description">Description</label>
                        <input type="text" class="form-control" id="credit_description" name="description" value="{{ old('description') }}" placeholder="Brigada Eskwela" maxlength="255" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" for="credit_days">Days</label>
                        <input type="number" class="form-control" id="credit_days" name="days" value="{{ old('days', '1.0') }}" min="0.5" max="30" step="0.5" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn fw-bold" style="background: #ffc107; color: #1A3E6F;">Save Credits</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('serviceCreditSearch')?.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#serviceCreditTable tbody tr[data-search]').forEach(function (row) {
            row.hidden = !row.dataset.search.includes(query);
        });
    });
</script>

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
                <div class="modal-body p-4" style="min-height: 250px;">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase tracking-wider" style="color: #1A3E6F;">Learning Area <span class="text-danger">*</span></label>
                        <select id="selectLearningArea" name="learning_area_id" class="form-select p-3 bg-light border-0 focus-ring" required style="border-radius: 8px;">
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

<!-- Edit Position Modal -->
<style>
    .btn-outline-theme {
        color: #1A3E6F;
        border-color: #1A3E6F;
    }
    .btn-outline-theme:hover {
        background-color: rgba(26, 62, 111, 0.1);
        color: #1A3E6F;
    }
    .btn-check:checked + .btn-outline-theme {
        background-color: #1A3E6F;
        color: #ffffff;
        border-color: #1A3E6F;
    }
    .btn-check:focus + .btn-outline-theme {
        box-shadow: 0 0 0 0.25rem rgba(26, 62, 111, 0.25);
    }
</style>
<div class="modal fade text-start" id="editPositionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-white border-bottom py-3" style="border-bottom: 2px solid var(--accent-yellow) !important;">
                <h5 class="modal-title fw-bold m-0" style="color: #1A3E6F;"><i class="bi bi-briefcase me-2 text-warning"></i> Update Employee Position</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('hr.update_position', $employee->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4" style="min-height: 300px;">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase tracking-wider" style="color: #1A3E6F;">Category</label>
                        <div class="btn-group w-100 mt-2 shadow-sm" role="group">
                            <input type="radio" class="btn-check" name="position_category" id="catTeaching" value="{{ \App\Enums\PositionCategory::Teaching->value }}" autocomplete="off" {{ ($employee->position->category ?? null) === \App\Enums\PositionCategory::Teaching ? 'checked' : '' }}>
                            <label class="btn btn-outline-theme fw-bold py-2" for="catTeaching"><i class="bi bi-book me-1"></i> Teaching</label>

                            <input type="radio" class="btn-check" name="position_category" id="catNonTeaching" value="{{ \App\Enums\PositionCategory::NonTeaching->value }}" autocomplete="off" {{ ($employee->position->category ?? null) === \App\Enums\PositionCategory::NonTeaching ? 'checked' : '' }}>
                            <label class="btn btn-outline-theme fw-bold py-2" for="catNonTeaching"><i class="bi bi-briefcase me-1"></i> Non-Teaching</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase tracking-wider" style="color: #1A3E6F;">Position <span class="text-danger">*</span></label>
                        <select id="positionSelect" name="position_id" class="form-select p-3 bg-light border-0" required style="border-radius: 8px;">
                            <option value="" disabled>Select a Position</option>
                            @foreach($positions as $position)
                                <option value="{{ $position->id }}" data-category="{{ $position->category->value ?? $position->category }}" {{ $employee->position_id == $position->id ? 'selected' : '' }}>
                                    {{ $position->position_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase tracking-wider" style="color: #1A3E6F;">Step Increment (1-8)</label>
                        <select name="step_increment" class="form-select p-3 bg-light border-0" required style="border-radius: 8px;">
                            @for ($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}" {{ ($employee->step_increment ?? 1) == $i ? 'selected' : '' }}>Step {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div id="modalLearningAreaWrapper" class="mb-4" style="{{ ($employee->position->category ?? null) === \App\Enums\PositionCategory::Teaching ? '' : 'display: none;' }}">
                        <label class="form-label small fw-bold text-uppercase tracking-wider" style="color: #1A3E6F;">Learning Area</label>
                        <select id="modalSelectLearningArea" name="learning_area_id" class="form-select p-3 bg-light border-0" style="border-radius: 8px;">
                            <option value="">Select a Learning Area</option>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const TEACHING_VALUE = '{{ \App\Enums\PositionCategory::Teaching->value }}';
    const NON_TEACHING_VALUE = '{{ \App\Enums\PositionCategory::NonTeaching->value }}';
    const categoryRadios = document.querySelectorAll('input[name="position_category"]');
    const positionSelect = document.getElementById('positionSelect');
    const learningAreaWrapper = document.getElementById('learningAreaWrapper');
    const modalLearningAreaWrapper = document.getElementById('modalLearningAreaWrapper');
    const modalLearningAreaSelect = document.getElementById('modalSelectLearningArea');
    const positionOptions = positionSelect ? positionSelect.querySelectorAll('option[data-category]') : [];

    function syncEmployeeForm() {
        const selectedRadio = document.querySelector('input[name="position_category"]:checked');
        if (!selectedRadio || !positionSelect) return;

        const selectedCategory = selectedRadio.value;
        const isTeaching = selectedCategory === TEACHING_VALUE;

        // Learning Area Toggle: show for Teaching, hide for Non-Teaching
        if (learningAreaWrapper) {
            learningAreaWrapper.style.display = isTeaching ? '' : 'none';
        }
        if (modalLearningAreaWrapper) {
            modalLearningAreaWrapper.style.display = isTeaching ? '' : 'none';
            if (!isTeaching && modalLearningAreaSelect) {
                modalLearningAreaSelect.value = '';
            }
        }

        // Position Filtering: show/enable matching options, hide/disable non-matching
        let currentPositionStillValid = false;
        positionOptions.forEach(function(option) {
            if (option.getAttribute('data-category') === selectedCategory) {
                option.style.display = '';
                option.disabled = false;
                if (option.selected) {
                    currentPositionStillValid = true;
                }
            } else {
                option.style.display = 'none';
                option.disabled = true;
            }
        });

        // Reset position if current selection is now hidden/disabled
        if (!currentPositionStillValid) {
            positionSelect.value = '';
        }

        // Trigger Select2 update if present
        if (typeof $ !== 'undefined' && $.fn.select2 && $(positionSelect).hasClass('select2-hidden-accessible')) {
            $(positionSelect).trigger('change');
        }
    }

    // Attach event listeners to category radio buttons
    categoryRadios.forEach(function(radio) {
        radio.addEventListener('change', syncEmployeeForm);
    });

    // Initialize Select2 if available
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#positionSelect').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#editPositionModal')
        });

        $('#selectLearningArea').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#editLearningAreaModal')
        });

        $('#modalSelectLearningArea').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#editPositionModal')
        });
    }

    // Run on initial page load to set correct state
    syncEmployeeForm();
});
</script>
@endsection