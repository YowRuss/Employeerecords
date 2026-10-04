@extends('layouts.app')

@section('content')
<!-- CSS Assets -->
<link rel="stylesheet" href="{{ asset('build/assets/css/pds.css') }}">

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-header-blue fw-bold m-0"><i class="bi bi-file-earmark-person me-2 text-header-blue"></i> Employee PDS Review</h4>
            <p class="text-muted small m-0">Viewing official records for: <strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong></p>
        </div>
        <div>
            <a href="{{ route('hr.view_profile', $employee->id) }}" class="btn btn-outline-secondary shadow-sm fw-bold me-2"><i class="bi bi-arrow-left me-1"></i> Back to Profile</a>
            <a href="{{ route('pds.export', $employee->id) }}" class="btn btn-accent shadow-sm fw-bold">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Official PDS (Excel)
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 border-top border-4 border-accent">
        <div class="card-body p-0">
            @if(!$personal_info)
                <div class="alert alert-warning text-center m-5">
                    <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
                    This employee has not submitted their Personal Data Sheet yet.
                </div>
            @else
                <ul class="nav nav-tabs bg-light border-bottom pt-2 px-3 flex-nowrap overflow-auto" id="pdsTabs" role="tablist" style="font-size: 0.9rem; white-space: nowrap;">
                    <li class="nav-item"><button class="nav-link fw-bold text-dark active border-bottom-0" data-bs-toggle="tab" data-bs-target="#personal" type="button">I. Personal</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#family" type="button">II. Family</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#education" type="button">III. Education</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#eligibility" type="button">IV. Eligibility</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#work" type="button">V. Work Exp</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#voluntary" type="button">VI. Voluntary</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#learning" type="button">VII. L&D</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#other" type="button">VIII. Other Info</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#page4" type="button">IX. Questionnaire</button></li>
                </ul>

                <div class="tab-content bg-white" id="pdsTabContent">
                    <!-- TAB 1: PERSONAL INFORMATION -->
                    <div class="tab-pane fade p-3 p-md-4 show active" id="personal" role="tabpanel">
                        <!-- Basic Information -->
                        <div class="pds-section-card">
                            <div class="pds-section-header">I. Personal Information</div>
                            <div class="pds-section-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-muted small mb-0">Surname</label>
                                        <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->last_name }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-muted small mb-0">First Name</label>
                                        <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->first_name }}</div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold text-muted small mb-0">Extension</label>
                                        <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->name_extension ?: 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-muted small mb-0">Middle Name</label>
                                        <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->middle_name ?: 'N/A' }}</div>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-muted small mb-0">Date of Birth</label>
                                        <div class="fw-bold border-bottom pb-1">{{ \Carbon\Carbon::parse($personal_info->date_of_birth)->format('F d, Y') }}</div>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold text-muted small mb-0">Place of Birth</label>
                                        <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->place_of_birth }}</div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold text-muted small mb-0">Sex</label>
                                        <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->sex === 1 ? 'MALE' : ($personal_info->sex === 0 ? 'FEMALE' : 'N/A') }}</div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold text-muted small mb-0">Civil Status</label>
                                        <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->civil_status }}</div>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label fw-bold text-muted small mb-0">Height (m)</label>
                                        <div class="fw-bold border-bottom pb-1">{{ $personal_info->height ?: 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold text-muted small mb-0">Weight (kg)</label>
                                        <div class="fw-bold border-bottom pb-1">{{ $personal_info->weight ?: 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold text-muted small mb-0">Blood Type</label>
                                        <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->blood_type ?: 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-muted small mb-0">Citizenship</label>
                                        <div class="fw-bold text-uppercase border-bottom pb-1">
                                            @if($personal_info->citizenship === 1)
                                                Dual Citizenship {{ $personal_info->country ? ' - ' . $personal_info->country->name : '' }}
                                            @elseif($personal_info->citizenship === 0)
                                                Filipino
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-muted small mb-0">If Dual, Country</label>
                                        <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->country->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ID Numbers -->
                        <div class="pds-section-card">
                            <div class="pds-section-header">Government Identification Numbers</div>
                            <div class="pds-section-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-muted small mb-0">GSIS ID NO.</label>
                                        <div class="fw-bold border-bottom pb-1">{{ $personal_info->gsis_no ?: 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-muted small mb-0">PAG-IBIG ID NO.</label>
                                        <div class="fw-bold border-bottom pb-1">{{ $personal_info->pagibig_no ?: 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-muted small mb-0">PHILHEALTH NO.</label>
                                        <div class="fw-bold border-bottom pb-1">{{ $personal_info->philhealth_no ?: 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-muted small mb-0">PSN NO.</label>
                                        <div class="fw-bold border-bottom pb-1">{{ $personal_info->psn_no ?: 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-muted small mb-0">TIN NO.</label>
                                        <div class="fw-bold border-bottom pb-1">{{ $personal_info->tin_no ?: 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-muted small mb-0">AGENCY EMPLOYEE NO.</label>
                                        <div class="fw-bold border-bottom pb-1">{{ $personal_info->agency_employee_no ?: 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <!-- Residential Address -->
                            <div class="col-lg-6">
                                <div class="pds-section-card h-100 mb-0">
                                    <div class="pds-section-header bg-light">17. Residential Address</div>
                                    <div class="pds-section-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">House/Block/Lot No.</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->res_house_no ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Street</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->res_street ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold text-muted small mb-0">Subdivision/Village</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->res_subdivision ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Region</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->region->region_name ?? $personal_info->res_region ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Province</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->province->province_name ?? $personal_info->res_province ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">City/Municipality</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->city->city_name ?? $personal_info->res_city ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Barangay</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->barangay->brgy_name ?? $personal_info->res_barangay ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">ZIP Code</label>
                                                <div class="fw-bold border-bottom pb-1">{{ $personal_info->res_zip ?: 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Permanent Address -->
                            <div class="col-lg-6">
                                <div class="pds-section-card h-100 mb-0">
                                    <div class="pds-section-header bg-light">18. Permanent Address</div>
                                    <div class="pds-section-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">House/Block/Lot No.</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->perm_house_no ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Street</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->perm_street ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold text-muted small mb-0">Subdivision/Village</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->perm_subdivision ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Region</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->permRegion->region_name ?? $personal_info->perm_region ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Province</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->permProvince->province_name ?? $personal_info->perm_province ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">City/Municipality</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->permCity->city_name ?? $personal_info->perm_city ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Barangay</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->permBarangay->brgy_name ?? $personal_info->perm_barangay ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">ZIP Code</label>
                                                <div class="fw-bold border-bottom pb-1">{{ $personal_info->perm_zip ?: 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Details -->
                        <div class="pds-section-card mb-4">
                            <div class="pds-section-body py-3">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-muted small mb-0">19. Telephone No.</label>
                                        <div class="fw-bold border-bottom pb-1">{{ $personal_info->telephone_no ?: 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-muted small mb-0">20. Mobile No.</label>
                                        <div class="fw-bold border-bottom pb-1">{{ $personal_info->mobile_no ?: 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-muted small mb-0">21. E-Mail Address</label>
                                        <div class="fw-bold border-bottom pb-1">{{ $personal_info->email_address ?: 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: FAMILY BACKGROUND -->
                    <div class="tab-pane fade p-3 p-md-4" id="family" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-lg-7">
                                <!-- Spouse -->
                                <div class="pds-section-card">
                                    <div class="pds-section-header">22. Spouse's Information</div>
                                    <div class="pds-section-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Surname</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->spouse_last_name ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">First Name</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->spouse_first_name ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Middle Name</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->spouse_middle_name ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Extension (Jr, Sr)</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->spouse_name_extension ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Occupation</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->spouse_occupation ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Employer / Business Name</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->spouse_employer ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Business Address</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->spouse_business_address ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Telephone No.</label>
                                                <div class="fw-bold border-bottom pb-1">{{ $personal_info->spouse_telephone ?: 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Parents -->
                                <div class="pds-section-card mb-4">
                                    <div class="pds-section-header">24. Father's Information</div>
                                    <div class="pds-section-body border-bottom">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Surname</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->father_last_name ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">First Name</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->father_first_name ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Middle Name</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->father_middle_name ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted small mb-0">Extension (Jr, Sr)</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->father_name_extension ?: 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pds-section-header">25. Mother's Maiden Name</div>
                                    <div class="pds-section-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold text-muted small mb-0">Surname</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->mother_maiden_last_name ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold text-muted small mb-0">First Name</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->mother_maiden_first_name ?: 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold text-muted small mb-0">Middle Name</label>
                                                <div class="fw-bold text-uppercase border-bottom pb-1">{{ $personal_info->mother_maiden_middle_name ?: 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Children -->
                            <div class="col-lg-5">
                                <div class="pds-section-card h-100 mb-0">
                                    <div class="pds-section-header">23. Name of Children</div>
                                    <div class="pds-section-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover align-middle shadow-sm mb-0" style="font-size: 0.85rem;">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="text-muted">FULL NAME</th>
                                                        <th class="text-muted text-center">DATE OF BIRTH</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($children as $child)
                                                    <tr>
                                                        <td class="fw-bold text-uppercase">{{ $child->child_name }}</td>
                                                        <td class="text-nowrap text-center">{{ \Carbon\Carbon::parse($child->date_of_birth)->format('m/d/Y') }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted py-3">No children recorded.</td>
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

                    <!-- TAB 3: EDUCATION -->
                    <div class="tab-pane fade p-3 p-md-4" id="education" role="tabpanel">
                        @php
                        $eduLevels = [
                            'Elementary' => 'Elementary',
                            'Secondary' => 'Secondary',
                            'Vocational/Trade' => 'Vocational / Trade Course',
                            'College' => 'College',
                            'Graduate Studies' => 'Graduate Studies'
                        ];
                        @endphp

                        @foreach($eduLevels as $dbLevel => $displayLevel)
                        <div class="pds-section-card mb-4 border-start border-4 border-accent">
                            <div class="pds-section-header bg-white border-bottom pb-2">
                                <i class="bi bi-mortarboard-fill me-2 text-accent"></i> {{ $displayLevel }}
                            </div>
                            <div class="pds-section-body bg-light">
                                @php
                                $levelRecords = collect($education)->where('level', $dbLevel);
                                @endphp

                                @if($levelRecords->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover bg-white align-middle shadow-sm mb-0" style="font-size: 0.85rem;">
                                        <thead class="table-light text-muted text-center align-middle">
                                            <tr>
                                                <th rowspan="2">Name of School</th>
                                                <th rowspan="2">Degree / Course</th>
                                                <th colspan="2">Period</th>
                                                <th rowspan="2">Highest Level/Units</th>
                                                <th rowspan="2">Year Grad</th>
                                                <th rowspan="2">Honors</th>
                                            </tr>
                                            <tr>
                                                <th>From</th>
                                                <th>To</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($levelRecords as $edu)
                                            <tr>
                                                <td class="fw-bold text-uppercase">{{ $edu->school_name }}</td>
                                                <td class="text-uppercase">{{ $edu->degree_course ?: 'N/A' }}</td>
                                                <td class="text-center">{{ $edu->period_from ?: 'N/A' }}</td>
                                                <td class="text-center">{{ $edu->period_to ?: 'N/A' }}</td>
                                                <td class="text-uppercase">{{ $edu->highest_level_earned ?: 'N/A' }}</td>
                                                <td class="text-center">{{ $edu->year_graduated ?: 'N/A' }}</td>
                                                <td class="text-uppercase">{{ $edu->scholarship_honors ?: 'N/A' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                    <div class="text-center text-muted py-3 border bg-white rounded">
                                        No records found for this educational level.
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- TAB 4: ELIGIBILITY -->
                    <div class="tab-pane fade p-3 p-md-4" id="eligibility" role="tabpanel">
                        <div class="pds-section-card mb-4">
                            <div class="pds-section-header">IV. Civil Service Eligibility</div>
                            <div class="pds-section-body bg-light">
                                <div class="table-responsive mb-0">
                                    <table class="table table-bordered table-hover align-middle shadow-sm text-center bg-white mb-0" style="font-size: 0.85rem;">
                                        <thead class="table-light align-middle text-muted">
                                            <tr>
                                                <th rowspan="2">Eligibility / License</th>
                                                <th rowspan="2">Rating</th>
                                                <th rowspan="2">Date of Exam</th>
                                                <th rowspan="2">Place of Exam</th>
                                                <th colspan="2">License (if applicable)</th>
                                            </tr>
                                            <tr>
                                                <th>Number</th>
                                                <th>Valid Until</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($eligibilities as $elig)
                                            <tr>
                                                <td class="text-start fw-bold text-uppercase">{{ $elig->eligibility_name }}</td>
                                                <td>{{ $elig->rating ?? 'N/A' }}</td>
                                                <td>{{ $elig->exam_date ? \Carbon\Carbon::parse($elig->exam_date)->format('m/d/Y') : 'N/A' }}</td>
                                                <td class="text-uppercase">{{ $elig->exam_place ?? 'N/A' }}</td>
                                                <td class="text-uppercase">{{ $elig->license_number ?? 'N/A' }}</td>
                                                <td>{{ $elig->license_validity ? \Carbon\Carbon::parse($elig->license_validity)->format('m/d/Y') : 'N/A' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No eligibility records found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: WORK EXPERIENCE -->
                    <div class="tab-pane fade p-3 p-md-4" id="work" role="tabpanel">
                        <div class="pds-section-card mb-4">
                            <div class="pds-section-header">V. Work Experience</div>
                            <div class="pds-section-body bg-light">
                                <div class="table-responsive mb-0">
                                    <table class="table table-bordered table-hover align-middle shadow-sm text-center bg-white mb-0" style="font-size: 0.85rem;">
                                        <thead class="table-light align-middle text-muted">
                                            <tr>
                                                <th colspan="2">Inclusive Dates</th>
                                                <th rowspan="2">Position Title</th>
                                                <th rowspan="2">Department / Agency / Company</th>
                                                <th rowspan="2">Status of Appointment</th>
                                                <th rowspan="2">Gov't Service</th>
                                            </tr>
                                            <tr>
                                                <th>From</th>
                                                <th>To</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($work_experiences as $work)
                                            <tr>
                                                <td>{{ $work->date_from ? \Carbon\Carbon::parse($work->date_from)->format('m/d/Y') : 'N/A' }}</td>
                                                <td>{{ $work->date_to == 'PRESENT' ? 'PRESENT' : ($work->date_to ? \Carbon\Carbon::parse($work->date_to)->format('m/d/Y') : 'N/A') }}</td>
                                                <td class="text-start fw-bold text-uppercase">{{ $work->position_title }}</td>
                                                <td class="text-start text-uppercase">{{ $work->agency_company }}</td>
                                                <td class="text-uppercase">{{ $work->status_appointment ?? 'N/A' }}</td>
                                                <td class="text-uppercase">{{ $work->govt_service ?? 'N/A' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No work experience records found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 6: VOLUNTARY -->
                    <div class="tab-pane fade p-3 p-md-4" id="voluntary" role="tabpanel">
                        <div class="pds-section-card mb-4">
                            <div class="pds-section-header">VI. Voluntary Work or Involvement in Civic / NGO Organizations</div>
                            <div class="pds-section-body bg-light">
                                <div class="table-responsive mb-0">
                                    <table class="table table-bordered table-hover align-middle shadow-sm text-center bg-white mb-0" style="font-size: 0.85rem;">
                                        <thead class="table-light align-middle text-muted">
                                            <tr>
                                                <th rowspan="2">Name & Address of Organization</th>
                                                <th colspan="2">Inclusive Dates</th>
                                                <th rowspan="2">Hours</th>
                                                <th rowspan="2">Position / Nature of Work</th>
                                            </tr>
                                            <tr>
                                                <th>From</th>
                                                <th>To</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($voluntary_works as $vol)
                                            <tr>
                                                <td class="text-start fw-bold text-uppercase">{{ $vol->organization_name }}</td>
                                                <td>{{ $vol->date_from ? \Carbon\Carbon::parse($vol->date_from)->format('m/d/Y') : 'N/A' }}</td>
                                                <td>{{ $vol->date_to == 'PRESENT' ? 'PRESENT' : ($vol->date_to ? \Carbon\Carbon::parse($vol->date_to)->format('m/d/Y') : 'N/A') }}</td>
                                                <td>{{ $vol->number_of_hours ?? 'N/A' }}</td>
                                                <td class="text-uppercase">{{ $vol->position_nature_of_work }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No voluntary work records found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 7: LEARNING AND DEVELOPMENT -->
                    <div class="tab-pane fade p-3 p-md-4" id="learning" role="tabpanel">
                        <div class="pds-section-card mb-4">
                            <div class="pds-section-header">VII. Learning and Development (L&D) Interventions/Training Programs Attended</div>
                            <div class="pds-section-body bg-light">
                                <div class="table-responsive mb-0">
                                    <table class="table table-bordered table-hover align-middle shadow-sm text-center bg-white mb-0" style="font-size: 0.85rem;">
                                        <thead class="table-light align-middle text-muted">
                                            <tr>
                                                <th rowspan="2" width="20%">Training Title</th>
                                                <th colspan="2">Inclusive Dates</th>
                                                <th rowspan="2" width="5%">Hours</th>
                                                <th rowspan="2" width="10%">Type</th>
                                                <th rowspan="2" width="15%">Conducted By</th>
                                                <th colspan="2">Supporting Documents</th>
                                            </tr>
                                            <tr><th>From</th><th>To</th><th>Completion</th><th>Invitation</th></tr>
                                        </thead>
                                        <tbody>
                                            @forelse($learnings as $ld)
                                            <tr>
                                                <td class="text-start fw-bold text-uppercase">{{ $ld->training_title }}</td>
                                                <td>{{ $ld->date_from ? \Carbon\Carbon::parse($ld->date_from)->format('m/d/Y') : 'N/A' }}</td>
                                                <td>{{ $ld->date_to == 'PRESENT' ? 'PRESENT' : ($ld->date_to ? \Carbon\Carbon::parse($ld->date_to)->format('m/d/Y') : 'N/A') }}</td>
                                                <td>{{ $ld->number_of_hours ?? 'N/A' }}</td>
                                                <td class="text-uppercase">{{ $ld->ld_type ?? 'N/A' }}</td>
                                                <td class="text-start text-uppercase">{{ $ld->sponsored_by }}</td>
                                                <td>
                                                    @if(!empty($ld->proof_of_completion))
                                                        <a href="{{ route('pds.document', ['id' => $ld->id, 'column' => 'proof_of_completion']) }}" target="_blank" class="btn btn-sm btn-outline-primary p-1 px-2" title="View"><i class="bi bi-eye-fill"></i> View</a>
                                                    @else
                                                        <span class="badge bg-danger shadow-sm p-1 w-100"><i class="bi bi-x-circle"></i> Missing</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!empty($ld->proof_of_invitation))
                                                        <a href="{{ route('pds.document', ['id' => $ld->id, 'column' => 'proof_of_invitation']) }}" target="_blank" class="btn btn-sm btn-outline-primary p-1 px-2" title="View"><i class="bi bi-eye-fill"></i> View</a>
                                                    @else
                                                        <span class="badge bg-danger shadow-sm p-1 w-100"><i class="bi bi-x-circle"></i> Missing</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="8" class="text-center text-muted py-4">No Learning & Development records found.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 8: OTHER INFO -->
                    <div class="tab-pane fade p-3 p-md-4" id="other" role="tabpanel">
                        <div class="pds-section-card mb-4">
                            <div class="pds-section-header">VIII. Other Information</div>
                            <div class="pds-section-body">
                                <div class="row g-4">
                                    <!-- Skills -->
                                    <div class="col-lg-4 border-end-lg">
                                        <h6 class="fw-bold small text-muted border-bottom pb-2">31. SPECIAL SKILLS / HOBBIES</h6>
                                        <ul class="list-group list-group-flush mb-0 shadow-sm border rounded">
                                            @php $skills = collect($other_info)->where('info_type', 'skill'); @endphp
                                            @forelse($skills as $item)
                                            <li class="list-group-item py-2 px-3 text-uppercase" style="font-size: 0.9rem;">{{ $item->details }}</li>
                                            @empty
                                            <li class="list-group-item text-muted text-center py-3">No skills added.</li>
                                            @endforelse
                                        </ul>
                                    </div>

                                    <!-- Recognitions -->
                                    <div class="col-lg-4 border-end-lg">
                                        <h6 class="fw-bold small text-muted border-bottom pb-2">32. NON-ACADEMIC DISTINCTIONS</h6>
                                        <ul class="list-group list-group-flush mb-0 shadow-sm border rounded">
                                            @php $recognitions = collect($other_info)->where('info_type', 'recognition'); @endphp
                                            @forelse($recognitions as $item)
                                            <li class="list-group-item py-2 px-3 text-uppercase" style="font-size: 0.9rem;">{{ $item->details }}</li>
                                            @empty
                                            <li class="list-group-item text-muted text-center py-3">No recognition added.</li>
                                            @endforelse
                                        </ul>
                                    </div>

                                    <!-- Memberships -->
                                    <div class="col-lg-4">
                                        <h6 class="fw-bold small text-muted border-bottom pb-2">33. MEMBERSHIP IN ASSOC/ORG</h6>
                                        <ul class="list-group list-group-flush mb-0 shadow-sm border rounded">
                                            @php $memberships = collect($other_info)->where('info_type', 'membership'); @endphp
                                            @forelse($memberships as $item)
                                            <li class="list-group-item py-2 px-3 text-uppercase" style="font-size: 0.9rem;">{{ $item->details }}</li>
                                            @empty
                                            <li class="list-group-item text-muted text-center py-3">No memberships added.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 9: QUESTIONNAIRE -->
                    <div class="tab-pane fade p-3 p-md-4" id="page4" role="tabpanel">
                        <div class="pds-section-card mb-4">
                            <div class="pds-section-header">IX. Questionnaire (Items 34 - 40)</div>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item p-4">
                                    <div class="fw-bold mb-3 text-dark">34. Are you related by consanguinity or affinity to the appointing or recommending authority, or to the chief of bureau or office...</div>
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-7 text-muted">a. within the third degree?</div>
                                        <div class="col-md-5 fw-bold text-uppercase border-bottom pb-1">{{ $questionnaire->q34_a ?? 'N/A' }}</div>
                                    </div>
                                    <div class="row align-items-start">
                                        <div class="col-md-7 text-muted">b. within the fourth degree (for Local Government Unit - Career Employees)?</div>
                                        <div class="col-md-5">
                                            <div class="fw-bold text-uppercase border-bottom pb-1 mb-1">{{ $questionnaire->q34_b ?? 'N/A' }}</div>
                                            <div class="text-uppercase small text-muted">{{ $questionnaire->q34_b_details ?? '' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="list-group-item p-4 bg-light">
                                    <div class="fw-bold mb-3 text-dark">35. Offenses & Criminal Charges</div>
                                    <div class="row align-items-start mb-3">
                                        <div class="col-md-7 text-muted">a. Have you ever been found guilty of any administrative offense?</div>
                                        <div class="col-md-5">
                                            <div class="fw-bold text-uppercase border-bottom pb-1 mb-1">{{ $questionnaire->q35_a ?? 'N/A' }}</div>
                                            <div class="text-uppercase small text-muted">{{ $questionnaire->q35_a_details ?? '' }}</div>
                                        </div>
                                    </div>
                                    <div class="row align-items-start">
                                        <div class="col-md-7 text-muted">b. Have you been criminally charged before any court?</div>
                                        <div class="col-md-5">
                                            <div class="fw-bold text-uppercase border-bottom pb-1 mb-1">{{ $questionnaire->q35_b ?? 'N/A' }}</div>
                                            @if(($questionnaire->q35_b ?? '') == 'YES')
                                            <div class="small text-muted mt-1">Date: {{ $questionnaire->q35_b_date ?? 'N/A' }}</div>
                                            <div class="small text-muted text-uppercase">Status: {{ $questionnaire->q35_b_status ?? 'N/A' }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="list-group-item p-4">
                                    <div class="fw-bold mb-3 text-dark">36-39. Convictions, Separation, Elections, Immigration</div>
                                    <div class="row align-items-start mb-3">
                                        <div class="col-md-7 text-muted">36. Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal?</div>
                                        <div class="col-md-5">
                                            <div class="fw-bold text-uppercase border-bottom pb-1 mb-1">{{ $questionnaire->q36 ?? 'N/A' }}</div>
                                            <div class="text-uppercase small text-muted">{{ $questionnaire->q36_details ?? '' }}</div>
                                        </div>
                                    </div>
                                    <div class="row align-items-start mb-3">
                                        <div class="col-md-7 text-muted">37. Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal...</div>
                                        <div class="col-md-5">
                                            <div class="fw-bold text-uppercase border-bottom pb-1 mb-1">{{ $questionnaire->q37 ?? 'N/A' }}</div>
                                            <div class="text-uppercase small text-muted">{{ $questionnaire->q37_details ?? '' }}</div>
                                        </div>
                                    </div>
                                    <div class="row align-items-start mb-3">
                                        <div class="col-md-7 text-muted">38a. Have you ever been a candidate in a national or local election held within the last year?</div>
                                        <div class="col-md-5">
                                            <div class="fw-bold text-uppercase border-bottom pb-1 mb-1">{{ $questionnaire->q38_a ?? 'N/A' }}</div>
                                            <div class="text-uppercase small text-muted">{{ $questionnaire->q38_a_details ?? '' }}</div>
                                        </div>
                                    </div>
                                    <div class="row align-items-start mb-3">
                                        <div class="col-md-7 text-muted">38b. Have you resigned from the government service during the 3-month period before the last election to campaign?</div>
                                        <div class="col-md-5">
                                            <div class="fw-bold text-uppercase border-bottom pb-1 mb-1">{{ $questionnaire->q38_b ?? 'N/A' }}</div>
                                            <div class="text-uppercase small text-muted">{{ $questionnaire->q38_b_details ?? '' }}</div>
                                        </div>
                                    </div>
                                    <div class="row align-items-start">
                                        <div class="col-md-7 text-muted">39. Have you acquired the status of an immigrant or permanent resident of another country?</div>
                                        <div class="col-md-5">
                                            <div class="fw-bold text-uppercase border-bottom pb-1 mb-1">{{ $questionnaire->q39 ?? 'N/A' }}</div>
                                            <div class="text-uppercase small text-muted">{{ $questionnaire->q39_details ?? '' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="list-group-item p-4 bg-light">
                                    <div class="fw-bold mb-3 text-dark">40. Indigenous, Disability, Solo Parent Status</div>
                                    <div class="row align-items-start mb-3">
                                        <div class="col-md-7 text-muted">a. Are you a member of any indigenous group?</div>
                                        <div class="col-md-5">
                                            <div class="fw-bold text-uppercase border-bottom pb-1 mb-1">{{ $questionnaire->q40_a ?? 'N/A' }}</div>
                                            <div class="text-uppercase small text-muted">{{ $questionnaire->q40_a_details ?? '' }}</div>
                                        </div>
                                    </div>
                                    <div class="row align-items-start mb-3">
                                        <div class="col-md-7 text-muted">b. Are you a person with disability?</div>
                                        <div class="col-md-5">
                                            <div class="fw-bold text-uppercase border-bottom pb-1 mb-1">{{ $questionnaire->q40_b ?? 'N/A' }}</div>
                                            <div class="text-uppercase small text-muted">{{ $questionnaire->q40_b_details ?? '' }}</div>
                                        </div>
                                    </div>
                                    <div class="row align-items-start">
                                        <div class="col-md-7 text-muted">c. Are you a solo parent?</div>
                                        <div class="col-md-5">
                                            <div class="fw-bold text-uppercase border-bottom pb-1 mb-1">{{ $questionnaire->q40_c ?? 'N/A' }}</div>
                                            <div class="text-uppercase small text-muted">{{ $questionnaire->q40_c_details ?? '' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-lg-6">
                                <div class="pds-section-card h-100 mb-0">
                                    <div class="pds-section-header">41. References</div>
                                    <div class="pds-section-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover align-middle shadow-sm text-center" style="font-size: 0.85rem;">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>NAME</th>
                                                        <th>ADDRESS</th>
                                                        <th>CONTACT NO.</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($references as $ref)
                                                    <tr>
                                                        <td class="text-start fw-bold text-uppercase">{{ $ref->name }}</td>
                                                        <td class="text-start text-uppercase">{{ $ref->address }}</td>
                                                        <td>{{ $ref->contact_no }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-3">No references added.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="pds-section-card h-100 mb-0">
                                    <div class="pds-section-header">Government Issued ID</div>
                                    <div class="pds-section-body">
                                        <div class="row">
                                            <div class="col-md-4 text-muted small fw-bold">ID Type</div>
                                            <div class="col-md-8 fw-bold text-uppercase border-bottom pb-1 mb-2">{{ $page4_details->gov_id_type ?? 'N/A' }}</div>
                                            
                                            <div class="col-md-4 text-muted small fw-bold">ID Number</div>
                                            <div class="col-md-8 fw-bold border-bottom pb-1 mb-2">{{ $page4_details->gov_id_no ?? 'N/A' }}</div>
                                            
                                            <div class="col-md-4 text-muted small fw-bold">Date/Place Issuance</div>
                                            <div class="col-md-8 fw-bold text-uppercase border-bottom pb-1">{{ $page4_details->gov_id_issuance ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attachments Row -->
                        <div class="pds-section-card mb-0">
                            <div class="pds-section-header">Required Attachments</div>
                            <div class="pds-section-body">
                                <div class="row">
                                    <div class="col-md-6 text-center">
                                        <label class="form-label fw-bold d-block"><i class="bi bi-camera me-1"></i> Passport-sized Photo</label>
                                        @if(!empty($page4_details->passport_photo))
                                        <div class="mb-2 border p-1 rounded d-inline-block bg-white shadow-sm">
                                            <img src="data:image/jpeg;base64,{{ base64_encode($page4_details->passport_photo) }}" alt="Passport" class="zoomable-img" style="width: 132px; height: 170px; object-fit: cover; cursor: zoom-in;">
                                        </div>
                                        @else
                                        <div class="text-muted border rounded bg-light d-inline-flex align-items-center justify-content-center" style="width: 132px; height: 170px;">No Photo</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6 text-center">
                                        <label class="form-label fw-bold d-block"><i class="bi bi-fingerprint me-1"></i> Right Thumbmark</label>
                                        @if(!empty($page4_details->right_thumbmark))
                                        <div class="mb-2 border p-1 rounded d-inline-block bg-white shadow-sm">
                                            <img src="data:image/jpeg;base64,{{ base64_encode($page4_details->right_thumbmark) }}" alt="Thumbmark" class="zoomable-img" style="max-height: 120px; max-width: 150px; cursor: zoom-in;">
                                        </div>
                                        @else
                                        <div class="text-muted border rounded bg-light d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 120px;">No Thumbmark</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            @endif
        </div>

        @if($personal_info)
        <div class="card-footer bg-light border-top border-4 border-accent p-4">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <span class="d-block small fw-bold text-muted mb-2">Employee E-Signature</span>
                    @if(!empty($personal_info->e_signature))
                    <div class="bg-white p-2 border rounded d-inline-block shadow-sm">
                        <img src="data:image/jpeg;base64,{{ base64_encode($personal_info->e_signature) }}" alt="E-Signature" class="zoomable-img" style="max-height: 70px; max-width: 200px; object-fit: contain; cursor: zoom-in;">
                    </div>
                    @else
                    <div class="bg-white p-3 border rounded text-muted small d-inline-block fst-italic">No signature uploaded.</div>
                    @endif
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <span class="d-block small fw-bold text-muted mb-1">Date Accomplished</span>
                    <div class="fw-bold fs-5">{{ $personal_info->signature_date ? \Carbon\Carbon::parse($personal_info->signature_date)->format('F d, Y') : 'N/A' }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Image Zoom Modal -->
<div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body text-center position-relative p-0">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close" style="z-index: 1050; filter: invert(1); background-color: rgba(255,255,255,0.8); padding: 10px; border-radius: 50%;"></button>
                <img src="" id="zoomedImage" class="img-fluid rounded shadow-lg bg-white p-2" alt="Zoomed Image" style="max-height: 85vh; cursor: zoom-out;" data-bs-dismiss="modal">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const zoomableImages = document.querySelectorAll('.zoomable-img');
    const zoomedImage = document.getElementById('zoomedImage');
    
    // Check if modal exists to avoid errors
    const modalEl = document.getElementById('imageZoomModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const zoomModal = new bootstrap.Modal(modalEl);
        
        zoomableImages.forEach(img => {
            img.addEventListener('click', function() {
                zoomedImage.src = this.src;
                zoomModal.show();
            });
        });
    }
});
</script>

@endsection