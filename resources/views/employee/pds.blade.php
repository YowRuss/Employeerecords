@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="text-accent fw-bold m-0"><i class="bi bi-person-vcard me-2"></i> Personal Data Sheet</h4>
        <a href="{{ route('pds.print') }}" class="btn btn-outline-secondary btn-sm shadow-sm fw-bold">
            <i class="bi bi-printer me-1"></i> Print / Export PDS
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 border-top border-4 border-accent">
        <div class="card-body p-0">
            @php $activeTab = session('active_tab', 'personal'); @endphp

            <ul class="nav nav-tabs bg-light border-bottom pt-2 px-2 flex-nowrap overflow-auto" id="pdsTabs" role="tablist" style="font-size: 0.85rem; white-space: nowrap;">
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

            <div class="tab-content p-3 p-md-4" id="pdsTabContent">

                <div class="tab-pane fade {{ $activeTab == 'personal' ? 'show active' : '' }}" id="personal" role="tabpanel">
                    <form action="{{ route('pds.update_personal_info') }}" method="POST">
                        @csrf

                        <div class="bg-secondary text-white fw-bold p-2 mb-3 rounded-1">
                            I. PERSONAL INFORMATION
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-2 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">2. SURNAME</div>
                            <div class="col-12 col-md-10"><input type="text" name="last_name" class="form-control text-uppercase" value="{{ $personal_info->last_name ?? '' }}" required></div>

                            <div class="col-12 col-md-2 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">FIRST NAME</div>
                            <div class="col-12 col-md-5"><input type="text" name="first_name" class="form-control text-uppercase" value="{{ $personal_info->first_name ?? '' }}" required></div>

                            <div class="col-12 col-md-3 bg-light border p-2 fw-bold text-muted small d-flex align-items-center justify-content-md-center">NAME EXTENSION (JR., SR)</div>
                            <div class="col-12 col-md-2"><input type="text" name="name_extension" class="form-control text-uppercase" value="{{ $personal_info->name_extension ?? '' }}" placeholder="e.g. JR., SR."></div>

                            <div class="col-12 col-md-2 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">MIDDLE NAME</div>
                            <div class="col-12 col-md-10"><input type="text" name="middle_name" class="form-control text-uppercase" value="{{ $personal_info->middle_name ?? '' }}" placeholder="If none, type N/A"></div>
                        </div>

                        <hr class="my-4 text-muted">

                        <div class="row g-3 mb-4">
                            <div class="col-lg-6 border-end-lg pe-lg-4">
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5 bg-light border p-2 fw-bold text-muted small">3. DATE OF BIRTH</div>
                                    <div class="col-12 col-sm-7"><input type="date" name="date_of_birth" class="form-control" value="{{ $personal_info->date_of_birth ?? '' }}" required></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5 bg-light border p-2 fw-bold text-muted small">4. PLACE OF BIRTH</div>
                                    <div class="col-12 col-sm-7"><input type="text" name="place_of_birth" class="form-control text-uppercase" value="{{ $personal_info->place_of_birth ?? '' }}" required></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5 bg-light border p-2 fw-bold text-muted small">5. SEX</div>
                                    <div class="col-12 col-sm-7 d-flex align-items-center p-2 p-sm-0 border border-sm-0">
                                        <div class="form-check form-check-inline me-3">
                                            <input class="form-check-input" type="radio" name="sex" id="sexMale" value="Male" {{ ($personal_info->sex ?? '') == 'Male' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="sexMale">Male</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="sex" id="sexFemale" value="Female" {{ ($personal_info->sex ?? '') == 'Female' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sexFemale">Female</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5 bg-light border p-2 fw-bold text-muted small">6. CIVIL STATUS</div>
                                    <div class="col-12 col-sm-7">
                                        <select name="civil_status" class="form-select" required>
                                            <option value="" disabled selected>Select Status...</option>
                                            <option value="Single" {{ ($personal_info->civil_status ?? '') == 'Single' ? 'selected' : '' }}>Single</option>
                                            <option value="Married" {{ ($personal_info->civil_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
                                            <option value="Widowed" {{ ($personal_info->civil_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                            <option value="Separated" {{ ($personal_info->civil_status ?? '') == 'Separated' ? 'selected' : '' }}>Separated</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5 bg-light border p-2 fw-bold text-muted small">7. HEIGHT (m)</div>
                                    <div class="col-12 col-sm-7"><input type="text" name="height" class="form-control" value="{{ $personal_info->height ?? '' }}" placeholder="e.g. 1.70"></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5 bg-light border p-2 fw-bold text-muted small">8. WEIGHT (kg)</div>
                                    <div class="col-12 col-sm-7"><input type="text" name="weight" class="form-control" value="{{ $personal_info->weight ?? '' }}" placeholder="e.g. 65"></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5 bg-light border p-2 fw-bold text-muted small">9. BLOOD TYPE</div>
                                    <div class="col-12 col-sm-7"><input type="text" name="blood_type" class="form-control text-uppercase" value="{{ $personal_info->blood_type ?? '' }}" placeholder="e.g. O+"></div>
                                </div>
                            </div>

                            <div class="col-lg-6 ps-lg-4 mt-4 mt-lg-0">
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5 bg-light border p-2 fw-bold text-muted small">16. CITIZENSHIP</div>
                                    <div class="col-12 col-sm-7">
                                        <select name="citizenship" class="form-select mb-2" required>
                                            <option value="Filipino" selected>Filipino</option>
                                            <option value="Dual Citizenship">Dual Citizenship</option>
                                        </select>
                                        <div class="small text-muted mb-1">If Dual, indicate country:</div>
                                        <input type="text" name="citizenship_country" class="form-control form-control-sm text-uppercase" placeholder="Country">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5 bg-light border p-2 fw-bold text-muted small">10. GSIS ID NO.</div>
                                    <div class="col-12 col-sm-7"><input type="text" name="gsis_no" class="form-control" value="{{ $personal_info->gsis_no ?? '' }}"></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5 bg-light border p-2 fw-bold text-muted small">11. PAG-IBIG ID NO.</div>
                                    <div class="col-12 col-sm-7"><input type="text" name="pagibig_no" class="form-control" value="{{ $personal_info->pagibig_no ?? '' }}"></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5 bg-light border p-2 fw-bold text-muted small">12. PHILHEALTH NO.</div>
                                    <div class="col-12 col-sm-7"><input type="text" name="philhealth_no" class="form-control" value="{{ $personal_info->philhealth_no ?? '' }}"></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5 bg-light border p-2 fw-bold text-muted small">13. SSS NO.</div>
                                    <div class="col-12 col-sm-7"><input type="text" name="sss_no" class="form-control" value="{{ $personal_info->sss_no ?? '' }}"></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5 bg-light border p-2 fw-bold text-muted small">14. TIN NO.</div>
                                    <div class="col-12 col-sm-7"><input type="text" name="tin_no" class="form-control" value="{{ $personal_info->tin_no ?? '' }}"></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5 bg-light border p-2 fw-bold text-muted small">15. AGENCY EMPLOYEE NO.</div>
                                    <div class="col-12 col-sm-7"><input type="text" name="agency_employee_no" class="form-control" value="{{ $personal_info->agency_employee_no ?? '' }}"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-12 col-md-2 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">
                                17. RESIDENTIAL ADDRESS
                            </div>
                            <div class="col-12 col-md-10">
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-6"><input type="text" name="res_house_no" class="form-control text-uppercase" placeholder="House/Block/Lot No." value="{{ $personal_info->res_house_no ?? '' }}"></div>
                                    <div class="col-12 col-sm-6"><input type="text" name="res_street" class="form-control text-uppercase" placeholder="Street" value="{{ $personal_info->res_street ?? '' }}"></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-6"><input type="text" name="res_subdivision" class="form-control text-uppercase" placeholder="Subdivision/Village" value="{{ $personal_info->res_subdivision ?? '' }}"></div>
                                    <div class="col-12 col-sm-6"><input type="text" name="res_barangay" class="form-control text-uppercase" placeholder="Barangay" value="{{ $personal_info->res_barangay ?? '' }}"></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5"><input type="text" name="res_city" class="form-control text-uppercase" placeholder="City/Municipality" value="{{ $personal_info->res_city ?? '' }}"></div>
                                    <div class="col-12 col-sm-5"><input type="text" name="res_province" class="form-control text-uppercase" placeholder="Province" value="{{ $personal_info->res_province ?? '' }}"></div>
                                    <div class="col-12 col-sm-2"><input type="text" name="res_zip" class="form-control" placeholder="ZIP CODE" value="{{ $personal_info->res_zip ?? '' }}"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-12 col-md-2 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">
                                18. PERMANENT ADDRESS
                            </div>
                            <div class="col-12 col-md-10">
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-6"><input type="text" name="perm_house_no" class="form-control text-uppercase" placeholder="House/Block/Lot No." value="{{ $personal_info->perm_house_no ?? '' }}"></div>
                                    <div class="col-12 col-sm-6"><input type="text" name="perm_street" class="form-control text-uppercase" placeholder="Street" value="{{ $personal_info->perm_street ?? '' }}"></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-6"><input type="text" name="perm_subdivision" class="form-control text-uppercase" placeholder="Subdivision/Village" value="{{ $personal_info->perm_subdivision ?? '' }}"></div>
                                    <div class="col-12 col-sm-6"><input type="text" name="perm_barangay" class="form-control text-uppercase" placeholder="Barangay" value="{{ $personal_info->perm_barangay ?? '' }}"></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm-5"><input type="text" name="perm_city" class="form-control text-uppercase" placeholder="City/Municipality" value="{{ $personal_info->perm_city ?? '' }}"></div>
                                    <div class="col-12 col-sm-5"><input type="text" name="perm_province" class="form-control text-uppercase" placeholder="Province" value="{{ $personal_info->perm_province ?? '' }}"></div>
                                    <div class="col-12 col-sm-2"><input type="text" name="perm_zip" class="form-control" placeholder="ZIP CODE" value="{{ $personal_info->perm_zip ?? '' }}"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="bg-light border p-2 fw-bold text-muted small mb-1 d-flex align-items-center">19. TELEPHONE NO.</div>
                                <input type="text" name="telephone_no" class="form-control" value="{{ $personal_info->telephone_no ?? '' }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="bg-light border p-2 fw-bold text-muted small mb-1 d-flex align-items-center">20. MOBILE NO.</div>
                                <input type="text" name="mobile_no" class="form-control" value="{{ $personal_info->mobile_no ?? '' }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="bg-light border p-2 fw-bold text-muted small mb-1 d-flex align-items-center">21. E-MAIL ADDRESS (if any)</div>
                                <input type="email" name="email_address" class="form-control" value="{{ $personal_info->email_address ?? '' }}">
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-accent fw-bold px-4 w-100 w-sm-auto">Save Personal Info & Continue <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade {{ $activeTab == 'family' ? 'show active' : '' }}" id="family" role="tabpanel">
                    <div class="bg-secondary text-white fw-bold p-2 mb-3 rounded-1">
                        II. FAMILY BACKGROUND
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-7 border-end-lg pe-lg-4">
                            <form action="{{ route('pds.update_family_background') }}" method="POST">
                                @csrf
                                <div class="row g-2 mb-3">
                                    <div class="col-12 col-sm-4 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">22. SPOUSE'S SURNAME</div>
                                    <div class="col-12 col-sm-8"><input type="text" name="spouse_last_name" class="form-control text-uppercase" value="{{ $personal_info->spouse_last_name ?? '' }}"></div>

                                    <div class="col-12 col-sm-4 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">FIRST NAME</div>
                                    <div class="col-12 col-sm-8 col-md-5"><input type="text" name="spouse_first_name" class="form-control text-uppercase" value="{{ $personal_info->spouse_first_name ?? '' }}"></div>
                                    <div class="col-12 col-sm-6 col-md-1 bg-light border p-2 fw-bold text-muted small d-flex align-items-center justify-content-center text-center" style="font-size: 0.65rem;">EXT</div>
                                    <div class="col-12 col-sm-6 col-md-2"><input type="text" name="spouse_name_extension" class="form-control text-uppercase" placeholder="JR."></div>

                                    <div class="col-12 col-sm-4 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">MIDDLE NAME</div>
                                    <div class="col-12 col-sm-8"><input type="text" name="spouse_middle_name" class="form-control text-uppercase" value="{{ $personal_info->spouse_middle_name ?? '' }}" placeholder="If none, type N/A"></div>

                                    <div class="col-12 col-sm-4 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">OCCUPATION</div>
                                    <div class="col-12 col-sm-8"><input type="text" name="spouse_occupation" class="form-control text-uppercase" value="{{ $personal_info->spouse_occupation ?? '' }}"></div>

                                    <div class="col-12 col-sm-4 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">EMPLOYER / BUS. NAME</div>
                                    <div class="col-12 col-sm-8"><input type="text" name="spouse_employer" class="form-control text-uppercase" value="{{ $personal_info->spouse_employer ?? '' }}"></div>

                                    <div class="col-12 col-sm-4 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">BUSINESS ADDRESS</div>
                                    <div class="col-12 col-sm-8"><input type="text" name="spouse_business_address" class="form-control text-uppercase" value="{{ $personal_info->spouse_business_address ?? '' }}"></div>

                                    <div class="col-12 col-sm-4 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">TELEPHONE NO.</div>
                                    <div class="col-12 col-sm-8"><input type="text" name="spouse_telephone" class="form-control" value="{{ $personal_info->spouse_telephone ?? '' }}"></div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-12 col-sm-4 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">24. FATHER'S SURNAME</div>
                                    <div class="col-12 col-sm-8"><input type="text" name="father_last_name" class="form-control text-uppercase" value="{{ $personal_info->father_last_name ?? '' }}"></div>

                                    <div class="col-12 col-sm-4 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">FIRST NAME</div>
                                    <div class="col-12 col-sm-8 col-md-5"><input type="text" name="father_first_name" class="form-control text-uppercase" value="{{ $personal_info->father_first_name ?? '' }}"></div>
                                    <div class="col-12 col-sm-6 col-md-1 bg-light border p-2 fw-bold text-muted small d-flex align-items-center justify-content-center text-center" style="font-size: 0.65rem;">EXT</div>
                                    <div class="col-12 col-sm-6 col-md-2"><input type="text" name="father_name_extension" class="form-control text-uppercase" value="{{ $personal_info->father_name_extension ?? '' }}"></div>

                                    <div class="col-12 col-sm-4 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">MIDDLE NAME</div>
                                    <div class="col-12 col-sm-8"><input type="text" name="father_middle_name" class="form-control text-uppercase" value="{{ $personal_info->father_middle_name ?? '' }}"></div>
                                </div>

                                <div class="row g-2 mb-4">
                                    <div class="col-12 bg-light border p-2 fw-bold text-muted small">25. MOTHER'S MAIDEN NAME</div>

                                    <div class="col-12 col-sm-4 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">SURNAME</div>
                                    <div class="col-12 col-sm-8"><input type="text" name="mother_maiden_last_name" class="form-control text-uppercase" value="{{ $personal_info->mother_maiden_last_name ?? '' }}"></div>

                                    <div class="col-12 col-sm-4 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">FIRST NAME</div>
                                    <div class="col-12 col-sm-8"><input type="text" name="mother_maiden_first_name" class="form-control text-uppercase" value="{{ $personal_info->mother_maiden_first_name ?? '' }}"></div>

                                    <div class="col-12 col-sm-4 bg-light border p-2 fw-bold text-muted small d-flex align-items-center">MIDDLE NAME</div>
                                    <div class="col-12 col-sm-8"><input type="text" name="mother_maiden_middle_name" class="form-control text-uppercase" value="{{ $personal_info->mother_maiden_middle_name ?? '' }}"></div>
                                </div>

                                <div class="text-start mb-4 mb-lg-0">
                                    <button type="submit" class="btn btn-accent fw-bold px-4 w-100 w-sm-auto">Save Spouse & Parents <i class="bi bi-save"></i></button>
                                </div>
                            </form>
                        </div>

                        <div class="col-lg-5">
                            <div class="d-flex justify-content-between align-items-center bg-light border p-2 mb-2 flex-wrap">
                                <span class="fw-bold text-muted small">23. NAME of CHILDREN</span>
                                <span class="fw-bold text-muted small">DATE OF BIRTH</span>
                            </div>

                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-hover align-middle shadow-sm" style="font-size: 0.85rem;">
                                    <tbody>
                                        @forelse($children as $child)
                                        <tr>
                                            <td class="fw-bold text-uppercase">{{ $child->child_name }}</td>
                                            <td width="120" class="text-nowrap">{{ \Carbon\Carbon::parse($child->date_of_birth)->format('m/d/Y') }}</td>
                                            <td width="50" class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_children', 'id' => $child->id]) }}" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">
                                                <i class="bi bi-info-circle d-block mb-1"></i> No children recorded.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="card bg-light border-0 shadow-sm border-start border-4 border-accent">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-3 small"><i class="bi bi-plus-circle me-1"></i> Add Child</h6>
                                    <form action="{{ route('pds.add_child') }}" method="POST">
                                        @csrf
                                        <div class="mb-2">
                                            <input type="text" name="child_name" class="form-control form-control-sm text-uppercase" placeholder="Full Name" required>
                                        </div>
                                        <div class="mb-2">
                                            <input type="date" name="child_dob" class="form-control form-control-sm" required>
                                        </div>
                                        <button type="submit" class="btn btn-accent btn-sm w-100 fw-bold">Add Child</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab == 'education' ? 'show active' : '' }}" id="education" role="tabpanel">
                    <div class="bg-secondary text-white fw-bold p-2 mb-4 rounded-1">
                        III. EDUCATIONAL BACKGROUND
                    </div>

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
                    <div class="card bg-light border-0 shadow-sm border-start border-4 border-accent mb-4">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-3 text-uppercase text-brand border-bottom pb-2">
                                <i class="bi bi-mortarboard-fill me-2"></i> {{ $displayLevel }}
                            </h6>

                            @php
                            $levelRecords = collect($education)->where('level', $dbLevel);
                            @endphp

                            @if($levelRecords->count() > 0)
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered table-sm table-hover bg-white align-middle shadow-sm mb-0" style="font-size: 0.85rem;">
                                    <thead class="table-light text-muted text-center align-middle">
                                        <tr>
                                            <th width="20%" rowspan="2">Name of School</th>
                                            <th width="20%" rowspan="2">Degree / Course</th>
                                            <th colspan="2">Period of Attendance</th>
                                            <th width="15%" rowspan="2">Highest Level/Units</th>
                                            <th width="8%" rowspan="2">Year Grad</th>
                                            <th width="12%" rowspan="2">Honors</th>
                                            <th width="5%" rowspan="2"></th>
                                        </tr>
                                        <tr>
                                            <th width="10%">From</th>
                                            <th width="10%">To</th>
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
                                                <button type="button" class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_education', 'id' => $edu->id]) }}" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            <form action="{{ route('pds.add_education') }}" method="POST" class="mt-2">
                                @csrf
                                <input type="hidden" name="level" value="{{ $dbLevel }}">

                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small fw-bold text-muted mb-1">Name of School <span class="text-danger">*</span></label>
                                        <input type="text" name="school_name" class="form-control form-control-sm text-uppercase" placeholder="Write in full" required>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small fw-bold text-muted mb-1">Basic Ed / Degree <span class="text-danger">*</span></label>
                                        <input type="text" name="degree" class="form-control form-control-sm text-uppercase" placeholder="e.g. BS IT" required>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Period (From)</label>
                                        <input type="text" name="period_from" class="form-control form-control-sm" placeholder="YYYY">
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Period (To)</label>
                                        <input type="text" name="period_to" class="form-control form-control-sm" placeholder="YYYY">
                                    </div>
                                </div>

                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small fw-bold text-muted mb-1">Highest Lvl / Units Earned</label>
                                        <input type="text" name="highest_level" class="form-control form-control-sm text-uppercase" placeholder="If not graduated">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Year Graduated</label>
                                        <input type="text" name="year_graduated" class="form-control form-control-sm" placeholder="YYYY">
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <label class="form-label small fw-bold text-muted mb-1">Scholarship/Academic Honors</label>
                                        <input type="text" name="honors" class="form-control form-control-sm text-uppercase" placeholder="e.g. Cum Laude">
                                    </div>
                                    <div class="col-12 col-md-1">
                                        <button type="submit" class="btn btn-accent btn-sm w-100 fw-bold shadow-sm" title="Add {{ $dbLevel }} Record"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach

                    <hr class="text-muted opacity-25 my-5">
                    <div class="card shadow-sm border-0 border-start border-4 border-accent mb-2 bg-light">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3 text-uppercase"><i class="bi bi-pen-fill me-2"></i> Page 1 Authorization / Signature</h6>
                            <form action="{{ route('pds.signature') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="active_tab" value="education">
                                <div class="row align-items-center">
                                    <div class="col-md-4 text-center border-end-md mb-3 mb-md-0">
                                        <span class="d-block small fw-bold text-muted mb-2">Current E-Signature</span>
                                        @if(!empty($personal_info->e_signature))
                                        <div class="bg-white p-2 border rounded d-inline-block shadow-sm">
                                            <img src="data:image/jpeg;base64,{{ base64_encode($personal_info->e_signature) }}" alt="E-Signature" style="max-height: 70px; max-width: 200px; object-fit: contain;">
                                        </div>
                                        <div class="text-success small fw-bold mt-1"><i class="bi bi-check-circle-fill"></i> Uploaded</div>
                                        @else
                                        <div class="bg-white p-3 border rounded text-muted small d-inline-block fst-italic">No signature uploaded yet.</div>
                                        @endif
                                    </div>
                                    <div class="col-md-8 ps-md-4">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label small fw-bold text-muted">Upload / Replace Signature</label>
                                                <input type="file" name="e_signature" class="form-control form-control-sm bg-white" accept="image/png, image/jpeg" {{ empty($personal_info->e_signature) ? 'required' : '' }}>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-muted">Date Accomplished</label>
                                                <input type="date" name="signature_date" class="form-control form-control-sm bg-white" value="{{ $personal_info->signature_date ?? date('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-md-6 d-flex align-items-end">
                                                <button type="submit" class="btn btn-accent btn-sm w-100 fw-bold shadow-sm py-2">
                                                    <i class="bi bi-save me-1"></i> Save Page 1 Signature
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab == 'eligibility' ? 'show active' : '' }}" id="eligibility" role="tabpanel">
                    <div class="bg-secondary text-white fw-bold p-2 mb-4 rounded-1">
                        IV. CIVIL SERVICE ELIGIBILITY
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm table-hover align-middle shadow-sm text-center" style="font-size: 0.85rem;">
                            <thead class="table-light align-middle text-muted">
                                <tr>
                                    <th width="30%" rowspan="2">Eligibility / License</th>
                                    <th width="10%" rowspan="2">Rating</th>
                                    <th width="15%" rowspan="2">Date of Exam</th>
                                    <th width="20%" rowspan="2">Place of Exam</th>
                                    <th colspan="2">License (if applicable)</th>
                                    <th width="5%" rowspan="2"></th>
                                </tr>
                                <tr>
                                    <th width="10%">Number</th>
                                    <th width="10%">Valid Until</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($eligibilities) && $eligibilities->count() > 0)
                                @foreach($eligibilities as $elig)
                                <tr>
                                    <td class="text-start fw-bold">{{ $elig->eligibility_name }}</td>
                                    <td>{{ $elig->rating ?? 'N/A' }}</td>
                                    <td>{{ $elig->exam_date ?? 'N/A' }}</td>
                                    <td>{{ $elig->exam_place ?? 'N/A' }}</td>
                                    <td>{{ $elig->license_number ?? 'N/A' }}</td>
                                    <td>{{ $elig->license_validity ?? 'N/A' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_eligibility', 'id' => $elig->id]) }}" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No eligibility records found.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="card bg-light border-0 shadow-sm border-start border-4 border-accent mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-1"></i> Add Eligibility Record</h6>
                            <form action="{{ route('pds.add_eligibility') }}" method="POST">
                                @csrf
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small fw-bold text-muted mb-1">Eligibility / License Name <span class="text-danger">*</span></label>
                                        <input type="text" name="eligibility_name" class="form-control form-control-sm text-uppercase" placeholder="e.g. CAREER SERVICE PROFESSIONAL" required>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Rating</label>
                                        <input type="text" name="rating" class="form-control form-control-sm" placeholder="If applicable">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Date of Examination</label>
                                        <input type="date" name="exam_date" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Place of Examination</label>
                                        <input type="text" name="exam_place" class="form-control form-control-sm text-uppercase" placeholder="City / Province">
                                    </div>
                                </div>
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small fw-bold text-muted mb-1">License Number</label>
                                        <input type="text" name="license_number" class="form-control form-control-sm text-uppercase" placeholder="If applicable">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1">License Valid Until</label>
                                        <input type="date" name="license_validity" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12 col-md-3 offset-md-2 mt-3 mt-md-0">
                                        <button type="submit" class="btn btn-accent btn-sm w-100 fw-bold shadow-sm">Add Record</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab == 'work' ? 'show active' : '' }}" id="work" role="tabpanel">
                    <div class="bg-secondary text-white fw-bold p-2 mb-4 rounded-1">
                        V. WORK EXPERIENCE
                    </div>
                    <p class="small text-muted fst-italic mb-3">Include private employment. Start from your recent work.</p>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm table-hover align-middle shadow-sm text-center" style="font-size: 0.85rem;">
                            <thead class="table-light align-middle text-muted">
                                <tr>
                                    <th colspan="2">Inclusive Dates</th>
                                    <th rowspan="2" width="25%">Position Title</th>
                                    <th rowspan="2" width="25%">Department / Agency / Company</th>
                                    <th rowspan="2" width="15%">Status of Appointment</th>
                                    <th rowspan="2" width="10%">Gov't Service (Y/N)</th>
                                    <th rowspan="2" width="5%"></th>
                                </tr>
                                <tr>
                                    <th width="10%">From</th>
                                    <th width="10%">To</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($work_experiences) && $work_experiences->count() > 0)
                                @foreach($work_experiences as $work)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($work->date_from)->format('m/d/Y') }}</td>
                                    <td>{{ $work->date_to == 'PRESENT' ? 'PRESENT' : \Carbon\Carbon::parse($work->date_to)->format('m/d/Y') }}</td>
                                    <td class="text-start fw-bold">{{ $work->position_title }}</td>
                                    <td class="text-start">{{ $work->agency_company }}</td>
                                    <td>{{ $work->status_appointment ?? 'N/A' }}</td>
                                    <td>{{ $work->govt_service ?? 'N/A' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_work_experience', 'id' => $work->id]) }}" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No work experience records found.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="card bg-light border-0 shadow-sm border-start border-4 border-accent mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-1"></i> Add Work Experience Record</h6>
                            <form action="{{ route('pds.add_work_experience') }}" method="POST">
                                @csrf
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Date From <span class="text-danger">*</span></label>
                                        <input type="date" name="date_from" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Date To <span class="text-danger">*</span></label>
                                        <input type="text" name="date_to" class="form-control form-control-sm text-uppercase" placeholder="YYYY-MM-DD or 'PRESENT'" required>
                                        <div class="form-text" style="font-size: 0.65rem;">Type "PRESENT" if currently employed here.</div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold text-muted mb-1">Position Title <span class="text-danger">*</span></label>
                                        <input type="text" name="position_title" class="form-control form-control-sm text-uppercase" placeholder="Write in full" required>
                                    </div>
                                </div>
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-md-5">
                                        <label class="form-label small fw-bold text-muted mb-1">Dept / Agency / Office / Company <span class="text-danger">*</span></label>
                                        <input type="text" name="agency_company" class="form-control form-control-sm text-uppercase" placeholder="Write in full" required>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Status of Appointment</label>
                                        <input type="text" name="status_appointment" class="form-control form-control-sm text-uppercase" placeholder="e.g. PERMANENT, CONTRACTUAL">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Gov't Service?</label>
                                        <select name="govt_service" class="form-select form-select-sm">
                                            <option value="" disabled selected>Select...</option>
                                            <option value="Y">Yes (Y)</option>
                                            <option value="N">No (N)</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2 mt-3 mt-md-0">
                                        <button type="submit" class="btn btn-accent btn-sm w-100 fw-bold shadow-sm">Add Record</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25 my-5">
                    <div class="card shadow-sm border-0 border-start border-4 border-accent mb-2 bg-light">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3 text-uppercase"><i class="bi bi-pen-fill me-2"></i> Page 2 Authorization / Signature</h6>
                            <form action="{{ route('pds.signature') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="active_tab" value="work">
                                <div class="row align-items-center">
                                    <div class="col-md-4 text-center border-end-md mb-3 mb-md-0">
                                        <span class="d-block small fw-bold text-muted mb-2">Current E-Signature</span>
                                        @if(!empty($personal_info->e_signature))
                                        <div class="bg-white p-2 border rounded d-inline-block shadow-sm">
                                            <img src="data:image/jpeg;base64,{{ base64_encode($personal_info->e_signature) }}" alt="E-Signature" style="max-height: 70px; max-width: 200px; object-fit: contain;">
                                        </div>
                                        <div class="text-success small fw-bold mt-1"><i class="bi bi-check-circle-fill"></i> Uploaded</div>
                                        @else
                                        <div class="bg-white p-3 border rounded text-muted small d-inline-block fst-italic">No signature uploaded yet.</div>
                                        @endif
                                    </div>
                                    <div class="col-md-8 ps-md-4">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label small fw-bold text-muted">Upload / Replace Signature</label>
                                                <input type="file" name="e_signature" class="form-control form-control-sm bg-white" accept="image/png, image/jpeg" {{ empty($personal_info->e_signature) ? 'required' : '' }}>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-muted">Date Accomplished</label>
                                                <input type="date" name="signature_date" class="form-control form-control-sm bg-white" value="{{ $personal_info->signature_date ?? date('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-md-6 d-flex align-items-end">
                                                <button type="submit" class="btn btn-accent btn-sm w-100 fw-bold shadow-sm py-2">
                                                    <i class="bi bi-save me-1"></i> Save Page 2 Signature
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab == 'voluntary' ? 'show active' : '' }}" id="voluntary" role="tabpanel">
                    <div class="bg-secondary text-white fw-bold p-2 mb-4 rounded-1 text-uppercase">
                        VI. Voluntary Work or Involvement in Civic/Non-Government/People/Voluntary Org
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm table-hover align-middle shadow-sm text-center" style="font-size: 0.85rem;">
                            <thead class="table-light align-middle text-muted">
                                <tr>
                                    <th rowspan="2" width="30%">Name & Address of Organization</th>
                                    <th colspan="2">Inclusive Dates</th>
                                    <th rowspan="2" width="10%">Number of Hours</th>
                                    <th rowspan="2" width="30%">Position / Nature of Work</th>
                                    <th rowspan="2" width="5%"></th>
                                </tr>
                                <tr>
                                    <th width="12%">From</th>
                                    <th width="12%">To</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($voluntary_works) && $voluntary_works->count() > 0)
                                @foreach($voluntary_works as $vol)
                                <tr>
                                    <td class="text-start fw-bold">{{ $vol->organization_name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($vol->date_from)->format('m/d/Y') }}</td>
                                    <td>{{ $vol->date_to == 'PRESENT' ? 'PRESENT' : \Carbon\Carbon::parse($vol->date_to)->format('m/d/Y') }}</td>
                                    <td>{{ $vol->number_of_hours ?? 'N/A' }}</td>
                                    <td>{{ $vol->position_nature_of_work }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_voluntary_work', 'id' => $vol->id]) }}" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No voluntary work records found.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="card bg-light border-0 shadow-sm border-start border-4 border-accent mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-1"></i> Add Voluntary Work</h6>
                            <form action="{{ route('pds.add_voluntary') }}" method="POST">
                                @csrf
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-md-5">
                                        <label class="form-label small fw-bold text-muted mb-1">Name & Address of Organization <span class="text-danger">*</span></label>
                                        <input type="text" name="organization_name" class="form-control form-control-sm text-uppercase" required>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Date From <span class="text-danger">*</span></label>
                                        <input type="date" name="date_from" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Date To <span class="text-danger">*</span></label>
                                        <input type="text" name="date_to" class="form-control form-control-sm text-uppercase" placeholder="Date or PRESENT" required>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Number of Hours</label>
                                        <input type="text" name="number_of_hours" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="row g-2 mt-1 align-items-end">
                                    <div class="col-12 col-md-9">
                                        <label class="form-label small fw-bold text-muted mb-1">Position / Nature of Work <span class="text-danger">*</span></label>
                                        <input type="text" name="position_nature_of_work" class="form-control form-control-sm text-uppercase" required>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <button type="submit" class="btn btn-accent btn-sm w-100 fw-bold shadow-sm">Add Record</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab == 'learning' ? 'show active' : '' }}" id="learning" role="tabpanel">
                    <div class="bg-secondary text-white fw-bold p-2 mb-4 rounded-1 text-uppercase">
                        VII. Learning and Development (L&D) Interventions/Training Programs Attended
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm table-hover align-middle shadow-sm text-center" style="font-size: 0.85rem;">
                            <thead class="table-light align-middle text-muted">
                                <tr>
                                    <th rowspan="2" width="30%">Title of L&D Interventions/Training</th>
                                    <th colspan="2">Inclusive Dates</th>
                                    <th rowspan="2" width="8%">Hours</th>
                                    <th rowspan="2" width="15%">Type of L&D</th>
                                    <th rowspan="2" width="20%">Conducted / Sponsored By</th>
                                    <th rowspan="2" width="5%"></th>
                                </tr>
                                <tr>
                                    <th width="11%">From</th>
                                    <th width="11%">To</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($learnings) && $learnings->count() > 0)
                                @foreach($learnings as $ld)
                                <tr>
                                    <td class="text-start fw-bold">{{ $ld->training_title }}</td>
                                    <td>{{ \Carbon\Carbon::parse($ld->date_from)->format('m/d/Y') }}</td>
                                    <td>{{ $ld->date_to == 'PRESENT' ? 'PRESENT' : \Carbon\Carbon::parse($ld->date_to)->format('m/d/Y') }}</td>
                                    <td>{{ $ld->number_of_hours ?? 'N/A' }}</td>
                                    <td>{{ $ld->ld_type ?? 'N/A' }}</td>
                                    <td class="text-start">{{ $ld->sponsored_by }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_learning_development', 'id' => $ld->id]) }}" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No L&D records found.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="card bg-light border-0 shadow-sm border-start border-4 border-accent mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-1"></i> Add L&D / Training</h6>
                            <form action="{{ route('pds.add_learning') }}" method="POST">
                                @csrf
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-md-5">
                                        <label class="form-label small fw-bold text-muted mb-1">Title of L&D / Training <span class="text-danger">*</span></label>
                                        <input type="text" name="training_title" class="form-control form-control-sm text-uppercase" required>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Date From <span class="text-danger">*</span></label>
                                        <input type="date" name="date_from" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Date To <span class="text-danger">*</span></label>
                                        <input type="text" name="date_to" class="form-control form-control-sm text-uppercase" placeholder="Date or PRESENT" required>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Number of Hours</label>
                                        <input type="text" name="number_of_hours" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="row g-2 mt-1 align-items-end">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small fw-bold text-muted mb-1">Type of L&D (Managerial, etc)</label>
                                        <input type="text" name="ld_type" class="form-control form-control-sm text-uppercase">
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <label class="form-label small fw-bold text-muted mb-1">Conducted / Sponsored By</label>
                                        <input type="text" name="sponsored_by" class="form-control form-control-sm text-uppercase">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <button type="submit" class="btn btn-accent btn-sm w-100 fw-bold shadow-sm">Add Training</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab == 'other' ? 'show active' : '' }}" id="other" role="tabpanel">
                    <div class="bg-secondary text-white fw-bold p-2 mb-4 rounded-1 text-uppercase">
                        VIII. OTHER INFORMATION
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-lg-4">
                            <h6 class="fw-bold small text-muted border-bottom pb-2">31. SPECIAL SKILLS / HOBBIES</h6>
                            <ul class="list-group list-group-flush mb-3 shadow-sm border rounded">
                                @php $skills = collect($other_info)->where('info_type', 'skill'); @endphp
                                @forelse($skills as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2" style="font-size: 0.85rem;">
                                    {{ $item->details }}
                                    <button type="button" class="btn btn-link text-danger p-0 m-0" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_other_information', 'id' => $item->id]) }}">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </li>
                                @empty
                                <li class="list-group-item text-muted text-center py-2" style="font-size: 0.85rem;">No skills added.</li>
                                @endforelse
                            </ul>
                            <form action="{{ route('pds.add_other_info') }}" method="POST" class="d-flex gap-2">
                                @csrf
                                <input type="hidden" name="info_type" value="skill">
                                <input type="text" name="details" class="form-control form-control-sm text-uppercase" placeholder="Enter skill" required>
                                <button type="submit" class="btn btn-accent btn-sm fw-bold"><i class="bi bi-plus"></i></button>
                            </form>
                        </div>

                        <div class="col-lg-4">
                            <h6 class="fw-bold small text-muted border-bottom pb-2">32. NON-ACADEMIC DISTINCTIONS</h6>
                            <ul class="list-group list-group-flush mb-3 shadow-sm border rounded">
                                @php $recognitions = collect($other_info)->where('info_type', 'recognition'); @endphp
                                @forelse($recognitions as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2" style="font-size: 0.85rem;">
                                    {{ $item->details }}
                                    <button type="button" class="btn btn-link text-danger p-0 m-0" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_other_information', 'id' => $item->id]) }}">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </li>
                                @empty
                                <li class="list-group-item text-muted text-center py-2" style="font-size: 0.85rem;">No recognition added.</li>
                                @endforelse
                            </ul>
                            <form action="{{ route('pds.add_other_info') }}" method="POST" class="d-flex gap-2">
                                @csrf
                                <input type="hidden" name="info_type" value="recognition">
                                <input type="text" name="details" class="form-control form-control-sm text-uppercase" placeholder="Enter recognition" required>
                                <button type="submit" class="btn btn-accent btn-sm fw-bold"><i class="bi bi-plus"></i></button>
                            </form>
                        </div>

                        <div class="col-lg-4">
                            <h6 class="fw-bold small text-muted border-bottom pb-2">33. MEMBERSHIP IN ASSOC/ORG</h6>
                            <ul class="list-group list-group-flush mb-3 shadow-sm border rounded">
                                @php $memberships = collect($other_info)->where('info_type', 'membership'); @endphp
                                @forelse($memberships as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2" style="font-size: 0.85rem;">
                                    {{ $item->details }}
                                    <button type="button" class="btn btn-link text-danger p-0 m-0" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_other_information', 'id' => $item->id]) }}">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </li>
                                @empty
                                <li class="list-group-item text-muted text-center py-2" style="font-size: 0.85rem;">No memberships added.</li>
                                @endforelse
                            </ul>
                            <form action="{{ route('pds.add_other_info') }}" method="POST" class="d-flex gap-2">
                                @csrf
                                <input type="hidden" name="info_type" value="membership">
                                <input type="text" name="details" class="form-control form-control-sm text-uppercase" placeholder="Enter organization" required>
                                <button type="submit" class="btn btn-accent btn-sm fw-bold"><i class="bi bi-plus"></i></button>
                            </form>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25 my-5">
                    <div class="card shadow-sm border-0 border-start border-4 border-accent mb-2 bg-light">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3 text-uppercase"><i class="bi bi-pen-fill me-2"></i> Page 3 Authorization / Signature</h6>
                            <form action="{{ route('pds.signature') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="active_tab" value="other">
                                <div class="row align-items-center">
                                    <div class="col-md-4 text-center border-end-md mb-3 mb-md-0">
                                        <span class="d-block small fw-bold text-muted mb-2">Current E-Signature</span>
                                        @if(!empty($personal_info->e_signature))
                                        <div class="bg-white p-2 border rounded d-inline-block shadow-sm">
                                            <img src="data:image/jpeg;base64,{{ base64_encode($personal_info->e_signature) }}" alt="E-Signature" style="max-height: 70px; max-width: 200px; object-fit: contain;">
                                        </div>
                                        <div class="text-success small fw-bold mt-1"><i class="bi bi-check-circle-fill"></i> Uploaded</div>
                                        @else
                                        <div class="bg-white p-3 border rounded text-muted small d-inline-block fst-italic">No signature uploaded yet.</div>
                                        @endif
                                    </div>
                                    <div class="col-md-8 ps-md-4">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label small fw-bold text-muted">Upload / Replace Signature</label>
                                                <input type="file" name="e_signature" class="form-control form-control-sm bg-white" accept="image/png, image/jpeg" {{ empty($personal_info->e_signature) ? 'required' : '' }}>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-muted">Date Accomplished</label>
                                                <input type="date" name="signature_date" class="form-control form-control-sm bg-white" value="{{ $personal_info->signature_date ?? date('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-md-6 d-flex align-items-end">
                                                <button type="submit" class="btn btn-accent btn-sm w-100 fw-bold shadow-sm py-2">
                                                    <i class="bi bi-save me-1"></i> Save Page 3 Signature
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab == 'page4' ? 'show active' : '' }}" id="page4" role="tabpanel">

                    <div class="bg-secondary text-white fw-bold p-2 mb-3 rounded-1">
                        IX. QUESTIONNAIRE (Items 34 - 40)
                    </div>

                    <form action="{{ route('pds.update_questionnaire') }}" method="POST" class="mb-5">
                        @csrf
                        <div class="list-group shadow-sm border mb-3">
                            <div class="list-group-item bg-light fw-bold small text-muted">34. Are you related by consanguinity or affinity to the appointing or recommending authority, or to the chief of bureau or office...</div>
                            <div class="list-group-item">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-7 small">a. within the third degree?</div>
                                    <div class="col-md-5">
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q34_a" value="YES" {{ ($questionnaire->q34_a ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label small">YES</label></div>
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q34_a" value="NO" {{ ($questionnaire->q34_a ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label small">NO</label></div>
                                    </div>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-md-7 small">b. within the fourth degree (for Local Government Unit - Career Employees)?</div>
                                    <div class="col-md-5">
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q34_b" value="YES" {{ ($questionnaire->q34_b ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label small">YES</label></div>
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q34_b" value="NO" {{ ($questionnaire->q34_b ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label small">NO</label></div>
                                        <input type="text" name="q34_b_details" class="form-control form-control-sm mt-1" placeholder="If YES, give details" value="{{ $questionnaire->q34_b_details ?? '' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="list-group-item bg-light fw-bold small text-muted">35. Offenses & Criminal Charges</div>
                            <div class="list-group-item">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-7 small">a. Have you ever been found guilty of any administrative offense?</div>
                                    <div class="col-md-5">
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q35_a" value="YES" {{ ($questionnaire->q35_a ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label small">YES</label></div>
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q35_a" value="NO" {{ ($questionnaire->q35_a ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label small">NO</label></div>
                                        <input type="text" name="q35_a_details" class="form-control form-control-sm mt-1" placeholder="If YES, give details" value="{{ $questionnaire->q35_a_details ?? '' }}">
                                    </div>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-md-7 small">b. Have you been criminally charged before any court?</div>
                                    <div class="col-md-5">
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q35_b" value="YES" {{ ($questionnaire->q35_b ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label small">YES</label></div>
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q35_b" value="NO" {{ ($questionnaire->q35_b ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label small">NO</label></div>
                                        <div class="input-group input-group-sm mt-1">
                                            <span class="input-group-text">Date</span>
                                            <input type="text" name="q35_b_date" class="form-control" placeholder="Date Filed" value="{{ $questionnaire->q35_b_date ?? '' }}">
                                        </div>
                                        <div class="input-group input-group-sm mt-1">
                                            <span class="input-group-text">Status</span>
                                            <input type="text" name="q35_b_status" class="form-control" placeholder="Status of Case" value="{{ $questionnaire->q35_b_status ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="list-group-item bg-light fw-bold small text-muted">36-39. Convictions, Separation, Elections, Immigration</div>
                            <div class="list-group-item">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-7 small">36. Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal?</div>
                                    <div class="col-md-5">
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q36" value="YES" {{ ($questionnaire->q36 ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label small">YES</label></div>
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q36" value="NO" {{ ($questionnaire->q36 ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label small">NO</label></div>
                                        <input type="text" name="q36_details" class="form-control form-control-sm mt-1" placeholder="If YES, give details" value="{{ $questionnaire->q36_details ?? '' }}">
                                    </div>
                                </div>
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-7 small">37. Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal...</div>
                                    <div class="col-md-5">
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q37" value="YES" {{ ($questionnaire->q37 ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label small">YES</label></div>
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q37" value="NO" {{ ($questionnaire->q37 ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label small">NO</label></div>
                                        <input type="text" name="q37_details" class="form-control form-control-sm mt-1" placeholder="If YES, give details" value="{{ $questionnaire->q37_details ?? '' }}">
                                    </div>
                                </div>
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-7 small">38a. Have you ever been a candidate in a national or local election held within the last year?</div>
                                    <div class="col-md-5">
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q38_a" value="YES" {{ ($questionnaire->q38_a ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label small">YES</label></div>
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q38_a" value="NO" {{ ($questionnaire->q38_a ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label small">NO</label></div>
                                        <input type="text" name="q38_a_details" class="form-control form-control-sm mt-1" placeholder="If YES, give details" value="{{ $questionnaire->q38_a_details ?? '' }}">
                                    </div>
                                </div>
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-7 small">38b. Have you resigned from the government service during the 3-month period before the last election to campaign?</div>
                                    <div class="col-md-5">
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q38_b" value="YES" {{ ($questionnaire->q38_b ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label small">YES</label></div>
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q38_b" value="NO" {{ ($questionnaire->q38_b ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label small">NO</label></div>
                                        <input type="text" name="q38_b_details" class="form-control form-control-sm mt-1" placeholder="If YES, give details" value="{{ $questionnaire->q38_b_details ?? '' }}">
                                    </div>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-md-7 small">39. Have you acquired the status of an immigrant or permanent resident of another country?</div>
                                    <div class="col-md-5">
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q39" value="YES" {{ ($questionnaire->q39 ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label small">YES</label></div>
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q39" value="NO" {{ ($questionnaire->q39 ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label small">NO</label></div>
                                        <input type="text" name="q39_details" class="form-control form-control-sm mt-1" placeholder="If YES, give details (country)" value="{{ $questionnaire->q39_details ?? '' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="list-group-item bg-light fw-bold small text-muted">40. Indigenous, Disability, Solo Parent Status</div>
                            <div class="list-group-item">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-7 small">a. Are you a member of any indigenous group?</div>
                                    <div class="col-md-5">
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q40_a" value="YES" {{ ($questionnaire->q40_a ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label small">YES</label></div>
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q40_a" value="NO" {{ ($questionnaire->q40_a ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label small">NO</label></div>
                                        <input type="text" name="q40_a_details" class="form-control form-control-sm mt-1" placeholder="If YES, please specify" value="{{ $questionnaire->q40_a_details ?? '' }}">
                                    </div>
                                </div>
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-7 small">b. Are you a person with disability?</div>
                                    <div class="col-md-5">
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q40_b" value="YES" {{ ($questionnaire->q40_b ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label small">YES</label></div>
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q40_b" value="NO" {{ ($questionnaire->q40_b ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label small">NO</label></div>
                                        <input type="text" name="q40_b_details" class="form-control form-control-sm mt-1" placeholder="If YES, please specify ID No." value="{{ $questionnaire->q40_b_details ?? '' }}">
                                    </div>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-md-7 small">c. Are you a solo parent?</div>
                                    <div class="col-md-5">
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q40_c" value="YES" {{ ($questionnaire->q40_c ?? '') == 'YES' ? 'checked' : '' }}> <label class="form-check-label small">YES</label></div>
                                        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="q40_c" value="NO" {{ ($questionnaire->q40_c ?? '') == 'NO' ? 'checked' : '' }}> <label class="form-check-label small">NO</label></div>
                                        <input type="text" name="q40_c_details" class="form-control form-control-sm mt-1" placeholder="If YES, please specify ID No." value="{{ $questionnaire->q40_c_details ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-end"><button type="submit" class="btn btn-accent px-4 fw-bold shadow-sm">Save Questionnaire</button></div>
                    </form>

                    <div class="bg-secondary text-white fw-bold p-2 mb-3 rounded-1">
                        41. REFERENCES <span class="fw-normal fst-italic small">(Person not related by consanguinity or affinity to applicant)</span>
                    </div>
                    <div class="row g-4 mb-5">
                        <div class="col-lg-7">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle shadow-sm text-center" style="font-size: 0.85rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>NAME</th>
                                            <th>OFFICE / RESIDENTIAL ADDRESS</th>
                                            <th>CONTACT NO.</th>
                                            <th width="10%"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($references as $ref)
                                        <tr>
                                            <td class="text-start fw-bold">{{ $ref->name }}</td>
                                            <td class="text-start">{{ $ref->address }}</td>
                                            <td>{{ $ref->contact_no }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('pds.delete_record', ['table' => 'pds_references', 'id' => $ref->id]) }}" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
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
                        <div class="col-lg-5">
                            <div class="card bg-light border-0 shadow-sm border-start border-4 border-accent">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-3 small"><i class="bi bi-plus-circle me-1"></i> Add Reference</h6>
                                    <form action="{{ route('pds.add_reference') }}" method="POST">
                                        @csrf
                                        <input type="text" name="name" class="form-control form-control-sm mb-2 text-uppercase" placeholder="Full Name" required>
                                        <input type="text" name="address" class="form-control form-control-sm mb-2 text-uppercase" placeholder="Address" required>
                                        <input type="text" name="contact_no" class="form-control form-control-sm mb-2" placeholder="Contact No / Email" required>
                                        <button type="submit" class="btn btn-accent btn-sm w-100 fw-bold shadow-sm">Save Reference</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-secondary text-white fw-bold p-2 mb-3 rounded-1">
                        42. OATH, GOVERNMENT ID, AND ATTACHMENTS
                    </div>

                    <form action="{{ route('pds.update_page4_details') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header bg-light fw-bold text-muted small">Government Issued ID</div>
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <label class="small fw-bold text-muted">ID Type (Passport, GSIS, SSS, PRC, etc.)</label>
                                            <input type="text" name="gov_id_type" class="form-control form-control-sm text-uppercase" value="{{ $page4_details->gov_id_type ?? '' }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="small fw-bold text-muted">ID/License/Passport No.</label>
                                            <input type="text" name="gov_id_no" class="form-control form-control-sm" value="{{ $page4_details->gov_id_no ?? '' }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="small fw-bold text-muted">Date/Place of Issuance</label>
                                            <input type="text" name="gov_id_issuance" class="form-control form-control-sm text-uppercase" value="{{ $page4_details->gov_id_issuance ?? '' }}">
                                        </div>
                                        <button type="submit" class="btn btn-accent btn-sm w-100 fw-bold shadow-sm mt-2">Save ID Details & Photos</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header bg-light fw-bold text-muted small">Required Attachments (Page 4)</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="small fw-bold text-muted"><i class="bi bi-camera me-1"></i> Passport-sized Photo (4.5cm x 3.5cm)</label>

                                            @if(!empty($page4_details->passport_photo))
                                            <div class="mb-2 border p-1 rounded d-inline-block bg-white shadow-sm">
                                                <img src="data:image/jpeg;base64,{{ base64_encode($page4_details->passport_photo) }}" alt="Passport" style="width: 132px; height: 170px; object-fit: cover;">
                                            </div>
                                            @endif

                                            <input type="file" name="passport_photo" class="form-control form-control-sm" accept="image/jpeg, image/png">
                                        </div>
                                        <div>
                                            <label class="small fw-bold text-muted"><i class="bi bi-fingerprint me-1"></i> Right Thumbmark</label>

                                            @if(!empty($page4_details->right_thumbmark))
                                            <div class="mb-2 border p-1 rounded d-inline-block bg-white shadow-sm">
                                                <img src="data:image/jpeg;base64,{{ base64_encode($page4_details->right_thumbmark) }}" alt="Thumbmark" style="max-height: 80px; max-width: 100px;">
                                            </div>
                                            @endif

                                            <input type="file" name="right_thumbmark" class="form-control form-control-sm" accept="image/jpeg, image/png">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <hr class="text-muted opacity-25 my-5">
                    <div class="card shadow-sm border-0 border-start border-4 border-accent mb-2 bg-light">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3 text-uppercase"><i class="bi bi-pen-fill me-2"></i> Page 4 Final Oath Signature</h6>
                            <form action="{{ route('pds.signature') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="active_tab" value="page4">
                                <div class="row align-items-center">
                                    <div class="col-md-4 text-center border-end-md mb-3 mb-md-0">
                                        <span class="d-block small fw-bold text-muted mb-2">Current E-Signature</span>
                                        @if(!empty($personal_info->e_signature))
                                        <div class="bg-white p-2 border rounded d-inline-block shadow-sm">
                                            <img src="data:image/jpeg;base64,{{ base64_encode($personal_info->e_signature) }}" alt="E-Signature" style="max-height: 70px; max-width: 200px; object-fit: contain;">
                                        </div>
                                        <div class="text-success small fw-bold mt-1"><i class="bi bi-check-circle-fill"></i> Uploaded</div>
                                        @else
                                        <div class="bg-white p-3 border rounded text-muted small d-inline-block fst-italic">No signature uploaded yet.</div>
                                        @endif
                                    </div>
                                    <div class="col-md-8 ps-md-4">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label small fw-bold text-muted">Upload / Replace Signature</label>
                                                <input type="file" name="e_signature" class="form-control form-control-sm bg-white" accept="image/png, image/jpeg" {{ empty($personal_info->e_signature) ? 'required' : '' }}>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-muted">Date Accomplished</label>
                                                <input type="date" name="signature_date" class="form-control form-control-sm bg-white" value="{{ $personal_info->signature_date ?? date('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-md-6 d-flex align-items-end">
                                                <button type="submit" class="btn btn-accent btn-sm w-100 fw-bold shadow-sm py-2">
                                                    <i class="bi bi-save me-1"></i> Save Final Signature
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

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

@endsection