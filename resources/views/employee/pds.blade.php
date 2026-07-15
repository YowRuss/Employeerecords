@extends('layouts.app')

@section('content')
<!-- CSS Assets -->
<link rel="stylesheet" href="{{ asset('build/assets/css/pds.css') }}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Global Loading Spinner -->
<div id="globalLoader">
    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
    <h5 class="mt-3 fw-bold text-muted">Processing Request...</h5>
</div>

<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="text-accent fw-bold m-0"><i class="bi bi-person-vcard me-2"></i> Personal Data Sheet</h4>
        <a href="{{ route('pds.print') }}" class="btn btn-outline-secondary shadow-sm fw-bold">
            <i class="bi bi-printer me-1"></i> Print / Export PDS
        </a>
    </div>

    @php
        $sections = [
            $personal_info, $children->count(), $education->count(), $eligibilities->count(), 
            $work_experiences->count(), $voluntary_works->count(), $learnings->count(), 
            $other_info->count(), $questionnaire
        ];
        $completedSections = count(array_filter($sections));
        $progress = round(($completedSections / 9) * 100);
    @endphp

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 border-top border-4 border-accent">
        <div class="card-body p-0">
            @php $activeTab = session('active_tab', 'personal'); @endphp

            <ul class="nav nav-tabs bg-light border-bottom pt-2 px-3 flex-nowrap overflow-auto" id="pdsTabs" role="tablist" style="font-size: 0.9rem; white-space: nowrap;">
                <li class="nav-item"><button class="nav-link fw-bold text-dark {{ $activeTab == 'personal' ? 'active border-bottom-0' : '' }}" data-bs-toggle="tab" data-bs-target="#personal" type="button">I. Personal</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark {{ $activeTab == 'family' ? 'active border-bottom-0' : '' }}" data-bs-toggle="tab" data-bs-target="#family" type="button">II. Family</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark {{ $activeTab == 'education' ? 'active border-bottom-0' : '' }}" data-bs-toggle="tab" data-bs-target="#education" type="button">III. Education</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark {{ $activeTab == 'eligibility' ? 'active border-bottom-0' : '' }}" data-bs-toggle="tab" data-bs-target="#eligibility" type="button">IV. Eligibility</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark {{ $activeTab == 'work' ? 'active border-bottom-0' : '' }}" data-bs-toggle="tab" data-bs-target="#work" type="button">V. Experience</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark {{ $activeTab == 'voluntary' ? 'active border-bottom-0' : '' }}" data-bs-toggle="tab" data-bs-target="#voluntary" type="button">VI. Voluntary</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark {{ $activeTab == 'learning' ? 'active border-bottom-0' : '' }}" data-bs-toggle="tab" data-bs-target="#learning" type="button">VII. L & D</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark {{ $activeTab == 'other' ? 'active border-bottom-0' : '' }}" data-bs-toggle="tab" data-bs-target="#other" type="button">VIII. Other Info</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark {{ $activeTab == 'page4' ? 'active border-bottom-0' : '' }}" data-bs-toggle="tab" data-bs-target="#page4" type="button">IX. Page 4 / Oath</button></li>
            </ul>

            <div class="tab-content bg-white" id="pdsTabContent">

                <!-- TAB 1: PERSONAL INFORMATION -->
                <div class="tab-pane fade p-3 p-md-4 {{ $activeTab == 'personal' ? 'show active' : '' }}" id="personal" role="tabpanel">
                    <form action="{{ route('pds.update_personal_info') }}" method="POST">
                        @csrf

                        <!-- Basic Information -->
                        <div class="pds-section-card">
                            <div class="pds-section-header">I. Personal Information</div>
                            <div class="pds-section-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Surname <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" class="form-control text-uppercase" value="{{ $personal_info->last_name ?? '' }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" class="form-control text-uppercase" value="{{ $personal_info->first_name ?? '' }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Extension <small class="text-muted">(Jr, Sr)</small></label>
                                        <input type="text" name="name_extension" class="form-control text-uppercase" value="{{ $personal_info->name_extension ?? '' }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Middle Name</label>
                                        <input type="text" name="middle_name" class="form-control text-uppercase" value="{{ $personal_info->middle_name ?? '' }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Date of Birth <span class="text-danger">*</span></label>
                                        <input type="date" name="date_of_birth" class="form-control" value="{{ $personal_info->date_of_birth ?? '' }}" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold">Place of Birth <span class="text-danger">*</span></label>
                                        <input type="text" name="place_of_birth" class="form-control text-uppercase" value="{{ $personal_info->place_of_birth ?? '' }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Sex <span class="text-danger">*</span></label>
                                        <div class="d-flex align-items-center mt-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="sex" id="sexMale" value="Male" {{ ($personal_info->sex ?? '') == 'Male' ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="sexMale">Male</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="sex" id="sexFemale" value="Female" {{ ($personal_info->sex ?? '') == 'Female' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="sexFemale">Female</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Civil Status <span class="text-danger">*</span></label>
                                        <select name="civil_status" class="form-select" required>
                                            <option value="" disabled selected>Select...</option>
                                            <option value="Single" {{ ($personal_info->civil_status ?? '') == 'Single' ? 'selected' : '' }}>Single</option>
                                            <option value="Married" {{ ($personal_info->civil_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
                                            <option value="Widowed" {{ ($personal_info->civil_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                            <option value="Separated" {{ ($personal_info->civil_status ?? '') == 'Separated' ? 'selected' : '' }}>Separated</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Height (m)</label>
                                        <input type="text" name="height" class="form-control" value="{{ $personal_info->height ?? '' }}" placeholder="1.70">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Weight (kg)</label>
                                        <input type="text" name="weight" class="form-control" value="{{ $personal_info->weight ?? '' }}" placeholder="65">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Blood Type</label>
                                        <input type="text" name="blood_type" class="form-control text-uppercase" value="{{ $personal_info->blood_type ?? '' }}" placeholder="O+">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Citizenship <span class="text-danger">*</span></label>
                                        <select name="citizenship" class="form-select" required>
                                            <option value="Filipino" selected>Filipino</option>
                                            <option value="Dual Citizenship">Dual Citizenship</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">If Dual, Country</label>
                                        <input type="text" name="citizenship_country" class="form-control text-uppercase" placeholder="Indicate Country">
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
                                        <label class="form-label fw-bold">GSIS ID NO.</label>
                                        <input type="text" name="gsis_no" class="form-control" value="{{ $personal_info->gsis_no ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">PAG-IBIG ID NO.</label>
                                        <input type="text" name="pagibig_no" class="form-control" value="{{ $personal_info->pagibig_no ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">PHILHEALTH NO.</label>
                                        <input type="text" name="philhealth_no" class="form-control" value="{{ $personal_info->philhealth_no ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">SSS NO.</label>
                                        <input type="text" name="sss_no" class="form-control" value="{{ $personal_info->sss_no ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">TIN NO.</label>
                                        <input type="text" name="tin_no" class="form-control" value="{{ $personal_info->tin_no ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">AGENCY EMPLOYEE NO.</label>
                                        <input type="text" name="agency_employee_no" class="form-control" value="{{ $personal_info->agency_employee_no ?? '' }}">
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
                                                <label class="form-label fw-bold">House/Block/Lot No.</label>
                                                <input type="text" name="res_house_no" class="form-control text-uppercase" value="{{ $personal_info->res_house_no ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Street</label>
                                                <input type="text" name="res_street" class="form-control text-uppercase" value="{{ $personal_info->res_street ?? '' }}">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">Subdivision/Village</label>
                                                <input type="text" name="res_subdivision" class="form-control text-uppercase" value="{{ $personal_info->res_subdivision ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Region <span class="text-danger">*</span></label>
                                                <select name="res_region_code" id="res_region" class="form-select select2-search" required>
                                                    <option value="" disabled selected>Search...</option>
                                                </select>
                                                <input type="hidden" name="res_region" id="res_region_text" value="{{ $personal_info->res_region ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Province <span class="text-danger">*</span></label>
                                                <select name="res_province_code" id="res_province" class="form-select select2-search" required>
                                                    <option value="" disabled selected>Search...</option>
                                                </select>
                                                <input type="hidden" name="res_province" id="res_province_text" value="{{ $personal_info->res_province ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">City/Municipality <span class="text-danger">*</span></label>
                                                <select name="res_city_code" id="res_city" class="form-select select2-search" required>
                                                    <option value="" disabled selected>Search...</option>
                                                </select>
                                                <input type="hidden" name="res_city" id="res_city_text" value="{{ $personal_info->res_city ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Barangay <span class="text-danger">*</span></label>
                                                <select name="res_barangay_code" id="res_barangay" class="form-select select2-search" required>
                                                    <option value="" disabled selected>Search...</option>
                                                </select>
                                                <input type="hidden" name="res_barangay" id="res_barangay_text" value="{{ $personal_info->res_barangay ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">ZIP Code</label>
                                                <input type="text" name="res_zip" class="form-control" value="{{ $personal_info->res_zip ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Permanent Address -->
                            <div class="col-lg-6">
                                <div class="pds-section-card h-100 mb-0">
                                    <div class="pds-section-header bg-light d-flex justify-content-between align-items-center">
                                        <span>18. Permanent Address</span>
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" id="sameAsResidential">
                                            <label class="form-check-label text-dark fw-normal text-capitalize" for="sameAsResidential" style="font-size: 0.8rem;">Same as Res</label>
                                        </div>
                                    </div>
                                    <div class="pds-section-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">House/Block/Lot No.</label>
                                                <input type="text" name="perm_house_no" id="perm_house_no" class="form-control text-uppercase" value="{{ $personal_info->perm_house_no ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Street</label>
                                                <input type="text" name="perm_street" id="perm_street" class="form-control text-uppercase" value="{{ $personal_info->perm_street ?? '' }}">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">Subdivision/Village</label>
                                                <input type="text" name="perm_subdivision" id="perm_subdivision" class="form-control text-uppercase" value="{{ $personal_info->perm_subdivision ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Region <span class="text-danger">*</span></label>
                                                <select name="perm_region_code" id="perm_region" class="form-select select2-search" required>
                                                    <option value="" disabled selected>Search...</option>
                                                </select>
                                                <input type="hidden" name="perm_region" id="perm_region_text" value="{{ $personal_info->perm_region ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Province <span class="text-danger">*</span></label>
                                                <select name="perm_province_code" id="perm_province" class="form-select select2-search" required>
                                                    <option value="" disabled selected>Search...</option>
                                                </select>
                                                <input type="hidden" name="perm_province" id="perm_province_text" value="{{ $personal_info->perm_province ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">City/Municipality <span class="text-danger">*</span></label>
                                                <select name="perm_city_code" id="perm_city" class="form-select select2-search" required>
                                                    <option value="" disabled selected>Search...</option>
                                                </select>
                                                <input type="hidden" name="perm_city" id="perm_city_text" value="{{ $personal_info->perm_city ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Barangay <span class="text-danger">*</span></label>
                                                <select name="perm_barangay_code" id="perm_barangay" class="form-select select2-search" required>
                                                    <option value="" disabled selected>Search...</option>
                                                </select>
                                                <input type="hidden" name="perm_barangay" id="perm_barangay_text" value="{{ $personal_info->perm_barangay ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">ZIP Code</label>
                                                <input type="text" name="perm_zip" id="perm_zip" class="form-control" value="{{ $personal_info->perm_zip ?? '' }}">
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
                                        <label class="form-label fw-bold">19. Telephone No.</label>
                                        <input type="text" name="telephone_no" class="form-control" value="{{ $personal_info->telephone_no ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">20. Mobile No.</label>
                                        <input type="text" name="mobile_no" class="form-control" value="{{ $personal_info->mobile_no ?? '' }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">21. E-Mail Address</label>
                                        <input type="email" name="email_address" class="form-control" value="{{ $personal_info->email_address ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-accent fw-bold px-4 py-2 shadow-sm">Save Personal Info <i class="bi bi-arrow-right ms-1"></i></button>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: FAMILY BACKGROUND -->
                <div class="tab-pane fade p-3 p-md-4 {{ $activeTab == 'family' ? 'show active' : '' }}" id="family" role="tabpanel">

                    <div class="row g-4">
                        <div class="col-lg-7">
                            <form action="{{ route('pds.update_family_background') }}" method="POST">
                                @csrf
                                <!-- Spouse -->
                                <div class="pds-section-card">
                                    <div class="pds-section-header">22. Spouse's Information</div>
                                    <div class="pds-section-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Surname</label>
                                                <input type="text" name="spouse_last_name" class="form-control text-uppercase" value="{{ $personal_info->spouse_last_name ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">First Name</label>
                                                <input type="text" name="spouse_first_name" class="form-control text-uppercase" value="{{ $personal_info->spouse_first_name ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Middle Name</label>
                                                <input type="text" name="spouse_middle_name" class="form-control text-uppercase" value="{{ $personal_info->spouse_middle_name ?? '' }}" placeholder="N/A">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Extension (Jr, Sr)</label>
                                                <input type="text" name="spouse_name_extension" class="form-control text-uppercase" value="{{ $personal_info->spouse_name_extension ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Occupation</label>
                                                <input type="text" name="spouse_occupation" class="form-control text-uppercase" value="{{ $personal_info->spouse_occupation ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Employer / Business Name</label>
                                                <input type="text" name="spouse_employer" class="form-control text-uppercase" value="{{ $personal_info->spouse_employer ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Business Address</label>
                                                <input type="text" name="spouse_business_address" class="form-control text-uppercase" value="{{ $personal_info->spouse_business_address ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Telephone No.</label>
                                                <input type="text" name="spouse_telephone" class="form-control" value="{{ $personal_info->spouse_telephone ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Parents -->
                                <div class="pds-section-card mb-4">
                                    <div class="pds-section-header">24. Father's Information</div>
                                    <div class="pds-section-body border-bottom">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label fw-bold">Surname</label><input type="text" name="father_last_name" class="form-control text-uppercase" value="{{ $personal_info->father_last_name ?? '' }}"></div>
                                            <div class="col-md-6"><label class="form-label fw-bold">First Name</label><input type="text" name="father_first_name" class="form-control text-uppercase" value="{{ $personal_info->father_first_name ?? '' }}"></div>
                                            <div class="col-md-6"><label class="form-label fw-bold">Middle Name</label><input type="text" name="father_middle_name" class="form-control text-uppercase" value="{{ $personal_info->father_middle_name ?? '' }}"></div>
                                            <div class="col-md-6"><label class="form-label fw-bold">Extension (Jr, Sr)</label><input type="text" name="father_name_extension" class="form-control text-uppercase" value="{{ $personal_info->father_name_extension ?? '' }}"></div>
                                        </div>
                                    </div>
                                    <div class="pds-section-header">25. Mother's Maiden Name</div>
                                    <div class="pds-section-body">
                                        <div class="row g-3">
                                            <div class="col-md-4"><label class="form-label fw-bold">Surname</label><input type="text" name="mother_maiden_last_name" class="form-control text-uppercase" value="{{ $personal_info->mother_maiden_last_name ?? '' }}"></div>
                                            <div class="col-md-4"><label class="form-label fw-bold">First Name</label><input type="text" name="mother_maiden_first_name" class="form-control text-uppercase" value="{{ $personal_info->mother_maiden_first_name ?? '' }}"></div>
                                            <div class="col-md-4"><label class="form-label fw-bold">Middle Name</label><input type="text" name="mother_maiden_middle_name" class="form-control text-uppercase" value="{{ $personal_info->mother_maiden_middle_name ?? '' }}"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-start mb-4 mb-lg-0">
                                    <button type="submit" class="btn btn-accent fw-bold px-4 shadow-sm">Save Spouse & Parents <i class="bi bi-save ms-1"></i></button>
                                </div>
                            </form>
                        </div>

                        <!-- Children -->
                        <div class="col-lg-5">
                            <div class="pds-section-card h-100 mb-0">
                                <div class="pds-section-header">23. Name of Children</div>
                                <div class="pds-section-body">
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-hover align-middle shadow-sm" style="font-size: 0.85rem;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-muted">FULL NAME</th>
                                                    <th class="text-muted">DATE OF BIRTH</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($children as $child)
                                                <tr>
                                                    <td class="fw-bold text-uppercase">{{ $child->child_name }}</td>
                                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($child->date_of_birth)->format('m/d/Y') }}</td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_children', 'id' => $child->id]) }}" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-3">No children recorded.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <form action="{{ route('pds.add_child') }}" method="POST" class="bg-light p-3 border rounded shadow-sm">
                                        <h6 class="fw-bold mb-3 small"><i class="bi bi-plus-circle me-1"></i> Add Child</h6>
                                        @csrf
                                        <div class="row g-2">
                                            <div class="col-12"><input type="text" name="child_name" class="form-control text-uppercase" placeholder="Full Name" required></div>
                                            <div class="col-12"><input type="date" name="child_dob" class="form-control" required></div>
                                            <div class="col-12"><button type="submit" class="btn btn-accent w-100 fw-bold">Add Child</button></div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: EDUCATION -->
                <div class="tab-pane fade p-3 p-md-4 {{ $activeTab == 'education' ? 'show active' : '' }}" id="education" role="tabpanel">
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
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-hover bg-white align-middle shadow-sm mb-0" style="font-size: 0.85rem;">
                                    <thead class="table-light text-muted text-center align-middle">
                                        <tr>
                                            <th rowspan="2">Name of School</th>
                                            <th rowspan="2">Degree / Course</th>
                                            <th colspan="2">Period</th>
                                            <th rowspan="2">Highest Level/Units</th>
                                            <th rowspan="2">Year Grad</th>
                                            <th rowspan="2">Honors</th>
                                            <th rowspan="2"></th>
                                        </tr>
                                        <tr>
                                            <th>From</th>
                                            <th>To</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($levelRecords as $edu)
                                        <tr>
                                            <td class="fw-bold">{{ $edu->school_name }}</td>
                                            <td>{{ $edu->degree_course ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $edu->period_from ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $edu->period_to ?? 'N/A' }}</td>
                                            <td>{{ $edu->highest_level_earned ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $edu->year_graduated ?? 'N/A' }}</td>
                                            <td>{{ $edu->scholarship_honors ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_education', 'id' => $edu->id]) }}"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            <form action="{{ route('pds.add_education') }}" method="POST">
                                @csrf
                                <input type="hidden" name="level" value="{{ $dbLevel }}">
                                <div class="row g-3 bg-white p-3 border rounded shadow-sm">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Name of School <span class="text-danger">*</span></label>
                                        <input type="text" name="school_name" class="form-control text-uppercase" placeholder="Write in full" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Basic Ed / Degree <span class="text-danger">*</span></label>
                                        <input type="text" name="degree" class="form-control text-uppercase" placeholder="e.g. BS IT" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Period (From)</label>
                                        <input type="text" name="period_from" class="form-control" placeholder="YYYY">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Period (To)</label>
                                        <input type="text" name="period_to" class="form-control" placeholder="YYYY">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Highest Lvl / Units Earned</label>
                                        <input type="text" name="highest_level" class="form-control text-uppercase" placeholder="If not graduated">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Year Graduated</label>
                                        <input type="text" name="year_graduated" class="form-control" placeholder="YYYY">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold">Scholarship/Academic Honors</label>
                                        <input type="text" name="honors" class="form-control text-uppercase" placeholder="e.g. Cum Laude">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-accent w-100 fw-bold shadow-sm">Add Record</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach

                    <!-- Signature Section (Reused across tabs) -->
                    @include('partials.pds_signature', ['tab' => 'education'])
                </div>

                <!-- TAB 4: ELIGIBILITY -->
                <div class="tab-pane fade p-3 p-md-4 {{ $activeTab == 'eligibility' ? 'show active' : '' }}" id="eligibility" role="tabpanel">
                    <div class="pds-section-card mb-4">
                        <div class="pds-section-header">IV. Civil Service Eligibility</div>
                        <div class="pds-section-body bg-light">
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-hover align-middle shadow-sm text-center bg-white" style="font-size: 0.85rem;">
                                    <thead class="table-light align-middle text-muted">
                                        <tr>
                                            <th rowspan="2">Eligibility / License</th>
                                            <th rowspan="2">Rating</th>
                                            <th rowspan="2">Date of Exam</th>
                                            <th rowspan="2">Place of Exam</th>
                                            <th colspan="2">License (if applicable)</th>
                                            <th rowspan="2"></th>
                                        </tr>
                                        <tr>
                                            <th>Number</th>
                                            <th>Valid Until</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($eligibilities as $elig)
                                        <tr>
                                            <td class="text-start fw-bold">{{ $elig->eligibility_name }}</td>
                                            <td>{{ $elig->rating ?? 'N/A' }}</td>
                                            <td>{{ $elig->exam_date ?? 'N/A' }}</td>
                                            <td>{{ $elig->exam_place ?? 'N/A' }}</td>
                                            <td>{{ $elig->license_number ?? 'N/A' }}</td>
                                            <td>{{ $elig->license_validity ?? 'N/A' }}</td>
                                            <td><button type="button" class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_eligibility', 'id' => $elig->id]) }}"><i class="bi bi-trash"></i></button></td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">No eligibility records found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <form action="{{ route('pds.add_eligibility') }}" method="POST" class="bg-white p-3 border rounded shadow-sm">
                                <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-1"></i> Add Eligibility Record</h6>
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Eligibility / License Name <span class="text-danger">*</span></label>
                                        <input type="text" name="eligibility_name" class="form-control text-uppercase" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Rating</label>
                                        <input type="text" name="rating" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Date of Examination</label>
                                        <input type="date" name="exam_date" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Place of Examination</label>
                                        <input type="text" name="exam_place" class="form-control text-uppercase">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">License Number</label>
                                        <input type="text" name="license_number" class="form-control text-uppercase">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">License Valid Until</label>
                                        <input type="date" name="license_validity" class="form-control">
                                    </div>
                                    <div class="col-md-5 d-flex align-items-end">
                                        <button type="submit" class="btn btn-accent w-100 fw-bold shadow-sm">Add Record</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: WORK EXPERIENCE -->
                <div class="tab-pane fade p-3 p-md-4 {{ $activeTab == 'work' ? 'show active' : '' }}" id="work" role="tabpanel">
                    <div class="pds-section-card mb-4">
                        <div class="pds-section-header d-flex justify-content-between">
                            <span>V. Work Experience</span>
                            <small class="text-muted fw-normal text-capitalize">Start from recent work</small>
                        </div>
                        <div class="pds-section-body bg-light">
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-hover align-middle shadow-sm text-center bg-white" style="font-size: 0.85rem;">
                                    <thead class="table-light align-middle text-muted">
                                        <tr>
                                            <th colspan="2">Inclusive Dates</th>
                                            <th rowspan="2">Position Title</th>
                                            <th rowspan="2">Department / Agency / Company</th>
                                            <th rowspan="2">Status of Appointment</th>
                                            <th rowspan="2">Gov't Service</th>
                                            <th rowspan="2"></th>
                                        </tr>
                                        <tr>
                                            <th>From</th>
                                            <th>To</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($work_experiences as $work)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($work->date_from)->format('m/d/Y') }}</td>
                                            <td>{{ $work->date_to == 'PRESENT' ? 'PRESENT' : \Carbon\Carbon::parse($work->date_to)->format('m/d/Y') }}</td>
                                            <td class="text-start fw-bold">{{ $work->position_title }}</td>
                                            <td class="text-start">{{ $work->agency_company }}</td>
                                            <td>{{ $work->status_appointment ?? 'N/A' }}</td>
                                            <td>{{ $work->govt_service ?? 'N/A' }}</td>
                                            <td><button type="button" class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_work_experience', 'id' => $work->id]) }}"><i class="bi bi-trash"></i></button></td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">No work experience records found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <form action="{{ route('pds.add_work_experience') }}" method="POST" class="bg-white p-3 border rounded shadow-sm">
                                <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-1"></i> Add Work Experience Record</h6>
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Date From <span class="text-danger">*</span></label>
                                        <input type="date" name="date_from" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Date To <span class="text-danger">*</span></label>
                                        <input type="text" name="date_to" class="form-control text-uppercase" placeholder="Date or 'PRESENT'" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Position Title <span class="text-danger">*</span></label>
                                        <input type="text" name="position_title" class="form-control text-uppercase" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Dept / Agency / Office / Company <span class="text-danger">*</span></label>
                                        <input type="text" name="agency_company" class="form-control text-uppercase" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Status of Appointment</label>
                                        <input type="text" name="status_appointment" class="form-control text-uppercase" placeholder="e.g. PERMANENT">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Gov't Service?</label>
                                        <select name="govt_service" class="form-select">
                                            <option value="" disabled selected>Select...</option>
                                            <option value="Y">Yes</option>
                                            <option value="N">No</option>
                                        </select>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-accent fw-bold px-4 shadow-sm">Add Record</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @include('partials.pds_signature', ['tab' => 'work'])
                </div>

                <!-- TAB 6: VOLUNTARY -->
                <div class="tab-pane fade p-3 p-md-4 {{ $activeTab == 'voluntary' ? 'show active' : '' }}" id="voluntary" role="tabpanel">
                    <div class="pds-section-card mb-4">
                        <div class="pds-section-header">VI. Voluntary Work or Involvement in Civic / NGO Organizations</div>
                        <div class="pds-section-body bg-light">
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-hover align-middle shadow-sm text-center bg-white" style="font-size: 0.85rem;">
                                    <thead class="table-light align-middle text-muted">
                                        <tr>
                                            <th rowspan="2">Name & Address of Organization</th>
                                            <th colspan="2">Inclusive Dates</th>
                                            <th rowspan="2">Hours</th>
                                            <th rowspan="2">Position / Nature of Work</th>
                                            <th rowspan="2"></th>
                                        </tr>
                                        <tr>
                                            <th>From</th>
                                            <th>To</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($voluntary_works as $vol)
                                        <tr>
                                            <td class="text-start fw-bold">{{ $vol->organization_name }}</td>
                                            <td>{{ \Carbon\Carbon::parse($vol->date_from)->format('m/d/Y') }}</td>
                                            <td>{{ $vol->date_to == 'PRESENT' ? 'PRESENT' : \Carbon\Carbon::parse($vol->date_to)->format('m/d/Y') }}</td>
                                            <td>{{ $vol->number_of_hours ?? 'N/A' }}</td>
                                            <td>{{ $vol->position_nature_of_work }}</td>
                                            <td><button type="button" class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_voluntary_work', 'id' => $vol->id]) }}"><i class="bi bi-trash"></i></button></td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No voluntary work records found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <form action="{{ route('pds.add_voluntary') }}" method="POST" class="bg-white p-3 border rounded shadow-sm">
                                <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-1"></i> Add Voluntary Work</h6>
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold">Name & Address of Organization <span class="text-danger">*</span></label>
                                        <input type="text" name="organization_name" class="form-control text-uppercase" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Date From <span class="text-danger">*</span></label>
                                        <input type="date" name="date_from" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Date To <span class="text-danger">*</span></label>
                                        <input type="text" name="date_to" class="form-control text-uppercase" placeholder="Date or PRESENT" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Hours</label>
                                        <input type="text" name="number_of_hours" class="form-control">
                                    </div>
                                    <div class="col-md-9">
                                        <label class="form-label fw-bold">Position / Nature of Work <span class="text-danger">*</span></label>
                                        <input type="text" name="position_nature_of_work" class="form-control text-uppercase" required>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-accent w-100 fw-bold shadow-sm">Add Record</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- TAB 7: LEARNING AND DEVELOPMENT -->
                <div class="tab-pane fade p-3 p-md-4 {{ $activeTab == 'learning' ? 'show active' : '' }}" id="learning" role="tabpanel">
                    <div class="pds-section-card mb-4">
                        <div class="pds-section-header d-flex justify-content-between align-items-center">
                            <span>VII. Learning and Development (L&D)</span>
                            <span class="badge bg-primary rounded-pill"><i class="bi bi-shield-check me-1"></i> Document Auditing Active</span>
                        </div>
                        <div class="pds-section-body bg-light">
                            <div class="table-responsive mb-0">
                                <table class="table table-bordered table-hover align-middle shadow-sm text-center bg-white mb-0" style="font-size: 0.85rem;">
                                    <thead class="table-light align-middle text-muted">
                                        <tr>
                                            <th rowspan="2" width="20%">Training Title</th><th colspan="2">Inclusive Dates</th><th rowspan="2" width="5%">Hours</th><th rowspan="2" width="10%">Type</th><th rowspan="2" width="15%">Conducted By</th><th colspan="2">Supporting Documents</th><th rowspan="2" width="5%"></th>
                                        </tr>
                                        <tr><th>From</th><th>To</th><th>Completion</th><th>Invitation</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse($learnings as $ld)
                                        <tr>
                                            <td class="text-start fw-bold text-uppercase">{{ $ld->training_title }}</td>
                                            <td>{{ \Carbon\Carbon::parse($ld->date_from)->format('m/d/Y') }}</td>
                                            <td>{{ $ld->date_to == 'PRESENT' ? 'PRESENT' : \Carbon\Carbon::parse($ld->date_to)->format('m/d/Y') }}</td>
                                            <td>{{ $ld->number_of_hours ?? 'N/A' }}</td><td class="text-uppercase">{{ $ld->ld_type ?? 'N/A' }}</td>
                                            <td class="text-start text-uppercase">{{ $ld->sponsored_by }}</td>
                                            
                                            <!-- Completion Doc -->
                                            <td>
                                                @if(!empty($ld->proof_of_completion))
                                                    <span class="badge bg-success shadow-sm p-1 w-100" data-bs-toggle="tooltip" title="Verified Document"><i class="bi bi-check-circle"></i> Uploaded</span>
                                                    <div class="mt-1 d-flex justify-content-center gap-2">
                                                        <a href="{{ route('pds.document', ['id' => $ld->id, 'column' => 'proof_of_completion']) }}" target="_blank" class="text-primary small" title="View"><i class="bi bi-eye-fill"></i></a>
                                                        <a href="{{ route('pds.document', ['id' => $ld->id, 'column' => 'proof_of_completion']) }}" download class="text-success small" title="Download"><i class="bi bi-download"></i></a>
                                                    </div>
                                                @else
                                                    <span class="badge bg-danger shadow-sm p-1 w-100" data-bs-toggle="tooltip" title="Required Document Missing"><i class="bi bi-x-circle"></i> Missing</span>
                                                @endif
                                            </td>

                                            <!-- Invitation Doc -->
                                            <td>
                                                @if(!empty($ld->proof_of_invitation))
                                                    <span class="badge bg-success shadow-sm p-1 w-100" data-bs-toggle="tooltip" title="Verified Document"><i class="bi bi-check-circle"></i> Uploaded</span>
                                                    <div class="mt-1 d-flex justify-content-center gap-2">
                                                        <a href="{{ route('pds.document', ['id' => $ld->id, 'column' => 'proof_of_invitation']) }}" target="_blank" class="text-primary small" title="View"><i class="bi bi-eye-fill"></i></a>
                                                        <a href="{{ route('pds.document', ['id' => $ld->id, 'column' => 'proof_of_invitation']) }}" download class="text-success small" title="Download"><i class="bi bi-download"></i></a>
                                                    </div>
                                                @else
                                                    <span class="badge bg-danger shadow-sm p-1 w-100" data-bs-toggle="tooltip" title="Required Document Missing"><i class="bi bi-x-circle"></i> Missing</span>
                                                @endif
                                            </td>

                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_learning_development', 'id' => $ld->id]) }}" data-record-name="L&D: {{ $ld->training_title }}"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="9" class="text-center text-muted py-4">No Learning & Development records found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Add New L&D Form Card -->
                    <div class="pds-section-card border-start border-4 border-accent mb-0">
                        <div class="pds-section-header bg-white border-bottom pb-3">
                            <h6 class="fw-bold mb-0 text-brand"><i class="bi bi-plus-circle-fill me-2"></i> Add New L&D / Training Record</h6>
                            <small class="text-muted text-capitalize fw-normal mt-1 d-block">All supporting documents must be uploaded to successfully save this record.</small>
                        </div>
                        <div class="pds-section-body bg-light p-4">
                            <form action="{{ route('pds.add_learning') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <h6 class="fw-bold text-muted mb-3 border-bottom pb-2">1. Training Information</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold">Title of L&D / Training <span class="text-danger">*</span></label>
                                        <input type="text" name="training_title" class="form-control text-uppercase" placeholder="Write in full" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Date From <span class="text-danger">*</span></label>
                                        <input type="date" name="date_from" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Date To <span class="text-danger">*</span></label>
                                        <input type="text" name="date_to" class="form-control text-uppercase" placeholder="Date or 'PRESENT'" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">Total Hours <span class="text-danger">*</span></label>
                                        <input type="number" name="number_of_hours" class="form-control" placeholder="e.g. 40" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Type of L&D <span class="text-danger">*</span></label>
                                        <select name="ld_type" class="form-select text-uppercase" required>
                                            <option value="" disabled selected>Select Type...</option>
                                            <option value="MANAGERIAL">Managerial</option>
                                            <option value="SUPERVISORY">Supervisory</option>
                                            <option value="TECHNICAL">Technical</option>
                                            <option value="FOUNDATIONAL">Foundational</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold">Conducted / Sponsored By <span class="text-danger">*</span></label>
                                        <input type="text" name="sponsored_by" class="form-control text-uppercase" placeholder="Write in full" required>
                                    </div>
                                </div>

                                <h6 class="fw-bold text-muted mb-3 border-bottom pb-2">2. Mandatory Supporting Documents</h6>
                                <div class="row g-4">

                                    <!-- Proof of Completion Upload -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark">Proof of Completion <span class="text-danger">*</span></label>
                                        <div class="file-upload-wrapper p-4 text-center border rounded-3 bg-white position-relative" id="drop-zone-completion">
                                            <i class="bi bi-award fs-1 text-accent mb-2"></i>
                                            <h6 class="fw-bold mb-1">Drag & Drop or Click to Upload</h6>
                                            <p class="small text-muted mb-3">Certificate of Completion/Participation</p>
                                            <input type="file" name="proof_of_completion" id="file-completion" class="form-control position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor: pointer;" accept=".pdf, image/jpeg, image/png, image/jpg" required>
                                            <div class="file-preview d-none" id="preview-completion">
                                                <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle me-1"></i> <span class="file-name"></span></span>
                                            </div>
                                        </div>
                                        <div class="small text-muted mt-2"><i class="bi bi-info-circle me-1"></i> Acceptable formats: PDF, JPG, PNG. Max size: 5MB.</div>
                                    </div>

                                    <!-- Proof of Invitation Upload -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark">Proof of Invitation <span class="text-danger">*</span></label>
                                        <div class="file-upload-wrapper p-4 text-center border rounded-3 bg-white position-relative" id="drop-zone-invitation">
                                            <i class="bi bi-envelope-paper fs-1 text-accent mb-2"></i>
                                            <h6 class="fw-bold mb-1">Drag & Drop or Click to Upload</h6>
                                            <p class="small text-muted mb-3">Invitation Letter, Memo, or Office Order</p>
                                            <input type="file" name="proof_of_invitation" id="file-invitation" class="form-control position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor: pointer;" accept=".pdf, image/jpeg, image/png, image/jpg" required>
                                            <div class="file-preview d-none" id="preview-invitation">
                                                <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle me-1"></i> <span class="file-name"></span></span>
                                            </div>
                                        </div>
                                        <div class="small text-muted mt-2"><i class="bi bi-info-circle me-1"></i> Acceptable formats: PDF, JPG, PNG. Max size: 5MB.</div>
                                    </div>
                                </div>

                                <div class="text-end mt-4 pt-3 border-top">
                                    <button type="submit" class="btn btn-accent px-5 py-2 fw-bold shadow-sm">
                                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Save Training & Documents
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- TAB 8: OTHER INFO -->
                <div class="tab-pane fade p-3 p-md-4 {{ $activeTab == 'other' ? 'show active' : '' }}" id="other" role="tabpanel">
                    <div class="pds-section-card mb-4">
                        <div class="pds-section-header">VIII. Other Information</div>
                        <div class="pds-section-body">
                            <div class="row g-4">
                                <!-- Skills -->
                                <div class="col-lg-4 border-end-lg">
                                    <h6 class="fw-bold small text-muted border-bottom pb-2">31. SPECIAL SKILLS / HOBBIES</h6>
                                    <ul class="list-group list-group-flush mb-3 shadow-sm border rounded">
                                        @php $skills = collect($other_info)->where('info_type', 'skill'); @endphp
                                        @forelse($skills as $item)
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3" style="font-size: 0.9rem;">
                                            {{ $item->details }}
                                            <button type="button" class="btn btn-link text-danger p-0 m-0" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_other_information', 'id' => $item->id]) }}"><i class="bi bi-x-circle"></i></button>
                                        </li>
                                        @empty
                                        <li class="list-group-item text-muted text-center py-3">No skills added.</li>
                                        @endforelse
                                    </ul>
                                    <form action="{{ route('pds.add_other_info') }}" method="POST" class="d-flex gap-2">
                                        @csrf
                                        <input type="hidden" name="info_type" value="skill">
                                        <input type="text" name="details" class="form-control text-uppercase" placeholder="Enter skill" required>
                                        <button type="submit" class="btn btn-accent fw-bold"><i class="bi bi-plus"></i></button>
                                    </form>
                                </div>

                                <!-- Recognitions -->
                                <div class="col-lg-4 border-end-lg">
                                    <h6 class="fw-bold small text-muted border-bottom pb-2">32. NON-ACADEMIC DISTINCTIONS</h6>
                                    <ul class="list-group list-group-flush mb-3 shadow-sm border rounded">
                                        @php $recognitions = collect($other_info)->where('info_type', 'recognition'); @endphp
                                        @forelse($recognitions as $item)
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3" style="font-size: 0.9rem;">
                                            {{ $item->details }}
                                            <button type="button" class="btn btn-link text-danger p-0 m-0" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_other_information', 'id' => $item->id]) }}"><i class="bi bi-x-circle"></i></button>
                                        </li>
                                        @empty
                                        <li class="list-group-item text-muted text-center py-3">No recognition added.</li>
                                        @endforelse
                                    </ul>
                                    <form action="{{ route('pds.add_other_info') }}" method="POST" class="d-flex gap-2">
                                        @csrf
                                        <input type="hidden" name="info_type" value="recognition">
                                        <input type="text" name="details" class="form-control text-uppercase" placeholder="Enter recognition" required>
                                        <button type="submit" class="btn btn-accent fw-bold"><i class="bi bi-plus"></i></button>
                                    </form>
                                </div>

                                <!-- Memberships -->
                                <div class="col-lg-4">
                                    <h6 class="fw-bold small text-muted border-bottom pb-2">33. MEMBERSHIP IN ASSOC/ORG</h6>
                                    <ul class="list-group list-group-flush mb-3 shadow-sm border rounded">
                                        @php $memberships = collect($other_info)->where('info_type', 'membership'); @endphp
                                        @forelse($memberships as $item)
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3" style="font-size: 0.9rem;">
                                            {{ $item->details }}
                                            <button type="button" class="btn btn-link text-danger p-0 m-0" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_other_information', 'id' => $item->id]) }}"><i class="bi bi-x-circle"></i></button>
                                        </li>
                                        @empty
                                        <li class="list-group-item text-muted text-center py-3">No memberships added.</li>
                                        @endforelse
                                    </ul>
                                    <form action="{{ route('pds.add_other_info') }}" method="POST" class="d-flex gap-2">
                                        @csrf
                                        <input type="hidden" name="info_type" value="membership">
                                        <input type="text" name="details" class="form-control text-uppercase" placeholder="Enter organization" required>
                                        <button type="submit" class="btn btn-accent fw-bold"><i class="bi bi-plus"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('partials.pds_signature', ['tab' => 'other'])
                </div>

                <!-- TAB 9: PAGE 4 / QUESTIONNAIRE & OATH -->
                <div class="tab-pane fade p-3 p-md-4 {{ $activeTab == 'page4' ? 'show active' : '' }}" id="page4" role="tabpanel">

                    <div class="pds-section-card mb-4">
                        <div class="pds-section-header">IX. Questionnaire (Items 34 - 40)</div>
                        <form action="{{ route('pds.update_questionnaire') }}" method="POST">
                            @csrf
                            <div class="list-group list-group-flush">

                                <div class="list-group-item p-4">
                                    <div class="fw-bold mb-3 text-dark">34. Are you related by consanguinity or affinity to the appointing or recommending authority, or to the chief of bureau or office...</div>
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-7 text-muted">a. within the third degree?</div>
                                        <div class="col-md-5">
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q34_a" value="YES" {{ ($questionnaire->q34_a ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label">YES</label></div>
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q34_a" value="NO" {{ ($questionnaire->q34_a ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label">NO</label></div>
                                        </div>
                                    </div>
                                    <div class="row align-items-start">
                                        <div class="col-md-7 text-muted">b. within the fourth degree (for Local Government Unit - Career Employees)?</div>
                                        <div class="col-md-5">
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q34_b" value="YES" {{ ($questionnaire->q34_b ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label">YES</label></div>
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q34_b" value="NO" {{ ($questionnaire->q34_b ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label">NO</label></div>
                                            <input type="text" name="q34_b_details" class="form-control mt-2" placeholder="If YES, give details" value="{{ $questionnaire->q34_b_details ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="list-group-item p-4 bg-light">
                                    <div class="fw-bold mb-3 text-dark">35. Offenses & Criminal Charges</div>
                                    <div class="row align-items-start mb-3">
                                        <div class="col-md-7 text-muted">a. Have you ever been found guilty of any administrative offense?</div>
                                        <div class="col-md-5">
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q35_a" value="YES" {{ ($questionnaire->q35_a ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label">YES</label></div>
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q35_a" value="NO" {{ ($questionnaire->q35_a ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label">NO</label></div>
                                            <input type="text" name="q35_a_details" class="form-control mt-2" placeholder="If YES, give details" value="{{ $questionnaire->q35_a_details ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="row align-items-start">
                                        <div class="col-md-7 text-muted">b. Have you been criminally charged before any court?</div>
                                        <div class="col-md-5">
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q35_b" value="YES" {{ ($questionnaire->q35_b ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label">YES</label></div>
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q35_b" value="NO" {{ ($questionnaire->q35_b ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label">NO</label></div>
                                            <div class="input-group mt-2">
                                                <span class="input-group-text bg-white">Date</span>
                                                <input type="date" name="q35_b_date" class="form-control" value="{{ $questionnaire->q35_b_date ?? '' }}">
                                            </div>
                                            <div class="input-group mt-2">
                                                <span class="input-group-text bg-white">Status</span>
                                                <input type="text" name="q35_b_status" class="form-control" placeholder="Status of Case" value="{{ $questionnaire->q35_b_status ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="list-group-item p-4">
                                    <div class="fw-bold mb-3 text-dark">36-39. Convictions, Separation, Elections, Immigration</div>
                                    <div class="row align-items-start mb-3">
                                        <div class="col-md-7 text-muted">36. Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal?</div>
                                        <div class="col-md-5">
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q36" value="YES" {{ ($questionnaire->q36 ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label">YES</label></div>
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q36" value="NO" {{ ($questionnaire->q36 ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label">NO</label></div>
                                            <input type="text" name="q36_details" class="form-control mt-2" placeholder="If YES, give details" value="{{ $questionnaire->q36_details ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="row align-items-start mb-3">
                                        <div class="col-md-7 text-muted">37. Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal...</div>
                                        <div class="col-md-5">
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q37" value="YES" {{ ($questionnaire->q37 ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label">YES</label></div>
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q37" value="NO" {{ ($questionnaire->q37 ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label">NO</label></div>
                                            <input type="text" name="q37_details" class="form-control mt-2" placeholder="If YES, give details" value="{{ $questionnaire->q37_details ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="row align-items-start mb-3">
                                        <div class="col-md-7 text-muted">38a. Have you ever been a candidate in a national or local election held within the last year?</div>
                                        <div class="col-md-5">
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q38_a" value="YES" {{ ($questionnaire->q38_a ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label">YES</label></div>
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q38_a" value="NO" {{ ($questionnaire->q38_a ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label">NO</label></div>
                                            <input type="text" name="q38_a_details" class="form-control mt-2" placeholder="If YES, give details" value="{{ $questionnaire->q38_a_details ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="row align-items-start mb-3">
                                        <div class="col-md-7 text-muted">38b. Have you resigned from the government service during the 3-month period before the last election to campaign?</div>
                                        <div class="col-md-5">
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q38_b" value="YES" {{ ($questionnaire->q38_b ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label">YES</label></div>
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q38_b" value="NO" {{ ($questionnaire->q38_b ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label">NO</label></div>
                                            <input type="text" name="q38_b_details" class="form-control mt-2" placeholder="If YES, give details" value="{{ $questionnaire->q38_b_details ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="row align-items-start">
                                        <div class="col-md-7 text-muted">39. Have you acquired the status of an immigrant or permanent resident of another country?</div>
                                        <div class="col-md-5">
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q39" value="YES" {{ ($questionnaire->q39 ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label">YES</label></div>
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q39" value="NO" {{ ($questionnaire->q39 ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label">NO</label></div>
                                            <input type="text" name="q39_details" class="form-control mt-2" placeholder="If YES, give details (country)" value="{{ $questionnaire->q39_details ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="list-group-item p-4 bg-light">
                                    <div class="fw-bold mb-3 text-dark">40. Indigenous, Disability, Solo Parent Status</div>
                                    <div class="row align-items-start mb-3">
                                        <div class="col-md-7 text-muted">a. Are you a member of any indigenous group?</div>
                                        <div class="col-md-5">
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q40_a" value="YES" {{ ($questionnaire->q40_a ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label">YES</label></div>
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q40_a" value="NO" {{ ($questionnaire->q40_a ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label">NO</label></div>
                                            <input type="text" name="q40_a_details" class="form-control mt-2" placeholder="If YES, please specify" value="{{ $questionnaire->q40_a_details ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="row align-items-start mb-3">
                                        <div class="col-md-7 text-muted">b. Are you a person with disability?</div>
                                        <div class="col-md-5">
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q40_b" value="YES" {{ ($questionnaire->q40_b ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label">YES</label></div>
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q40_b" value="NO" {{ ($questionnaire->q40_b ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label">NO</label></div>
                                            <input type="text" name="q40_b_details" class="form-control mt-2" placeholder="If YES, please specify ID No." value="{{ $questionnaire->q40_b_details ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="row align-items-start">
                                        <div class="col-md-7 text-muted">c. Are you a solo parent?</div>
                                        <div class="col-md-5">
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q40_c" value="YES" {{ ($questionnaire->q40_c ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label">YES</label></div>
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q40_c" value="NO" {{ ($questionnaire->q40_c ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label">NO</label></div>
                                            <input type="text" name="q40_c_details" class="form-control mt-2" placeholder="If YES, please specify ID No." value="{{ $questionnaire->q40_c_details ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="pds-section-body bg-white border-top text-end">
                                <button type="submit" class="btn btn-accent px-4 fw-bold shadow-sm">Save Questionnaire</button>
                            </div>
                        </form>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-lg-7">
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
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($references as $ref)
                                                <tr>
                                                    <td class="text-start fw-bold">{{ $ref->name }}</td>
                                                    <td class="text-start">{{ $ref->address }}</td>
                                                    <td>{{ $ref->contact_no }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_references', 'id' => $ref->id]) }}"><i class="bi bi-trash"></i></button>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-3">No references added.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="pds-section-card h-100 mb-0 border-start border-4 border-accent">
                                <div class="pds-section-header bg-white"><i class="bi bi-person-plus me-1"></i> Add Reference</div>
                                <div class="pds-section-body bg-light">
                                    <form action="{{ route('pds.add_reference') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Full Name</label>
                                            <input type="text" name="name" class="form-control text-uppercase" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Address</label>
                                            <input type="text" name="address" class="form-control text-uppercase" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Contact No.</label>
                                            <input type="text" name="contact_no" class="form-control" required>
                                        </div>
                                        <button type="submit" class="btn btn-accent w-100 fw-bold shadow-sm">Save Reference</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('pds.update_page4_details') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="pds-section-card h-100 mb-0">
                                    <div class="pds-section-header">Government Issued ID</div>
                                    <div class="pds-section-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">ID Type <span class="text-muted fw-normal">(Passport, GSIS, SSS, PRC, etc.)</span></label>
                                            <input type="text" name="gov_id_type" class="form-control text-uppercase" value="{{ $page4_details->gov_id_type ?? '' }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">ID/License/Passport No.</label>
                                            <input type="text" name="gov_id_no" class="form-control" value="{{ $page4_details->gov_id_no ?? '' }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Date/Place of Issuance</label>
                                            <input type="text" name="gov_id_issuance" class="form-control text-uppercase" value="{{ $page4_details->gov_id_issuance ?? '' }}">
                                        </div>
                                        <button type="submit" class="btn btn-accent w-100 fw-bold shadow-sm mt-2">Save ID Details</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="pds-section-card h-100 mb-0">
                                    <div class="pds-section-header">Required Attachments</div>
                                    <div class="pds-section-body">
                                        <div class="mb-4">
                                            <label class="form-label fw-bold d-block"><i class="bi bi-camera me-1"></i> Passport-sized Photo (4.5cm x 3.5cm)</label>
                                            @if(!empty($page4_details->passport_photo))
                                            <div class="mb-2 border p-1 rounded d-inline-block bg-white shadow-sm">
                                                <img src="data:image/jpeg;base64,{{ base64_encode($page4_details->passport_photo) }}" alt="Passport" style="width: 132px; height: 170px; object-fit: cover;">
                                            </div>
                                            @endif
                                            <input type="file" name="passport_photo" class="form-control" accept="image/jpeg, image/png">
                                        </div>
                                        <div>
                                            <label class="form-label fw-bold d-block"><i class="bi bi-fingerprint me-1"></i> Right Thumbmark</label>
                                            @if(!empty($page4_details->right_thumbmark))
                                            <div class="mb-2 border p-1 rounded d-inline-block bg-white shadow-sm">
                                                <img src="data:image/jpeg;base64,{{ base64_encode($page4_details->right_thumbmark) }}" alt="Thumbmark" style="max-height: 80px; max-width: 100px;">
                                            </div>
                                            @endif
                                            <input type="file" name="right_thumbmark" class="form-control" accept="image/jpeg, image/png">
                                            <button type="submit" class="btn btn-outline-secondary w-100 fw-bold shadow-sm mt-3">Upload Photos</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Oath Signature -->
                    @include('partials.pds_signature', ['tab' => 'page4'])
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="deleteConfirmModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-0 fs-5">Are you sure you want to delete this record?</p>
                <p class="text-muted small mt-1">This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center bg-light">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                <form id="universalDeleteForm" method="POST" action="">
                    @csrf
                    <button type="submit" class="btn btn-danger px-4 fw-bold shadow-sm"><i class="bi bi-trash me-1"></i> Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('build/assets/js/pds.js') }}"></script>


@endsection