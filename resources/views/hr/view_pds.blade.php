@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0" style="color: #1A3E6F;"><i class="bi bi-file-earmark-person me-2"></i> Employee PDS Review</h4>
            <p class="text-muted small m-0">Viewing official records for: <strong>{{ $employee->full_name }}</strong></p>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm me-2"><i class="bi bi-arrow-left"></i> Back to List</a>
            <button class="btn btn-sm text-white" style="background-color: #1A3E6F;" onclick="window.print()"><i class="bi bi-printer"></i> Print Official PDS</button>
        </div>
    </div>

    <!-- PDS Document Container -->
    <div class="card shadow-sm border-0 border-top border-4" style="border-top-color: #1A3E6F !important;">
        <div class="card-body p-4 bg-white">
            
            @if(!$personal_info)
                <div class="alert alert-warning text-center my-5">
                    <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
                    This employee has not submitted their Personal Data Sheet yet.
                </div>
            @else

                <!-- SECTION 1: PERSONAL INFO -->
                <div class="text-white fw-bold p-2 mb-3 rounded-1 small" style="background-color: #1A3E6F;">
                    I. PERSONAL INFORMATION
                </div>

                <div class="row g-0 border mb-4 text-uppercase" style="font-size: 0.85rem;">
                    <div class="col-md-2 bg-light border-end border-bottom p-2 fw-bold text-muted">2. SURNAME</div>
                    <div class="col-md-10 border-bottom p-2 fw-bold">{{ $personal_info->last_name }}</div>
                    
                    <div class="col-md-2 bg-light border-end border-bottom p-2 fw-bold text-muted">FIRST NAME</div>
                    <div class="col-md-6 border-end border-bottom p-2 fw-bold">{{ $personal_info->first_name }}</div>
                    <div class="col-md-2 bg-light border-end border-bottom p-2 fw-bold text-muted text-center" style="font-size: 0.7rem;">NAME EXTENSION</div>
                    <div class="col-md-2 border-bottom p-2 fw-bold">{{ $personal_info->name_extension ?? 'N/A' }}</div>

                    <div class="col-md-2 bg-light border-end p-2 fw-bold text-muted">MIDDLE NAME</div>
                    <div class="col-md-10 p-2 fw-bold">{{ $personal_info->middle_name ?? 'N/A' }}</div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-bordered m-0" style="font-size: 0.85rem;">
                            <tbody>
                                <tr><td class="bg-light text-muted fw-bold w-50">3. DATE OF BIRTH</td><td class="fw-bold">{{ \Carbon\Carbon::parse($personal_info->date_of_birth)->format('F d, Y') }}</td></tr>
                                <tr><td class="bg-light text-muted fw-bold">4. PLACE OF BIRTH</td><td class="fw-bold text-uppercase">{{ $personal_info->place_of_birth }}</td></tr>
                                <tr><td class="bg-light text-muted fw-bold">5. SEX</td><td class="fw-bold text-uppercase">{{ $personal_info->sex }}</td></tr>
                                <tr><td class="bg-light text-muted fw-bold">6. CIVIL STATUS</td><td class="fw-bold text-uppercase">{{ $personal_info->civil_status }}</td></tr>
                                <tr><td class="bg-light text-muted fw-bold">7. HEIGHT (m)</td><td class="fw-bold">{{ $personal_info->height ?? 'N/A' }}</td></tr>
                                <tr><td class="bg-light text-muted fw-bold">8. WEIGHT (kg)</td><td class="fw-bold">{{ $personal_info->weight ?? 'N/A' }}</td></tr>
                                <tr><td class="bg-light text-muted fw-bold">9. BLOOD TYPE</td><td class="fw-bold text-uppercase">{{ $personal_info->blood_type ?? 'N/A' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-bordered m-0" style="font-size: 0.85rem;">
                            <tbody>
                                <tr><td class="bg-light text-muted fw-bold w-50">16. CITIZENSHIP</td><td class="fw-bold text-uppercase">{{ $personal_info->citizenship ?? 'FILIPINO' }}</td></tr>
                                <tr><td class="bg-light text-muted fw-bold">10. GSIS ID NO.</td><td class="fw-bold">{{ $personal_info->gsis_no ?? 'N/A' }}</td></tr>
                                <tr><td class="bg-light text-muted fw-bold">11. PAG-IBIG ID NO.</td><td class="fw-bold">{{ $personal_info->pagibig_no ?? 'N/A' }}</td></tr>
                                <tr><td class="bg-light text-muted fw-bold">12. PHILHEALTH NO.</td><td class="fw-bold">{{ $personal_info->philhealth_no ?? 'N/A' }}</td></tr>
                                <tr><td class="bg-light text-muted fw-bold">13. SSS NO.</td><td class="fw-bold">{{ $personal_info->sss_no ?? 'N/A' }}</td></tr>
                                <tr><td class="bg-light text-muted fw-bold">14. TIN NO.</td><td class="fw-bold">{{ $personal_info->tin_no ?? 'N/A' }}</td></tr>
                                <tr><td class="bg-light text-muted fw-bold">15. AGENCY EMP. NO.</td><td class="fw-bold">{{ $personal_info->agency_employee_no ?? 'N/A' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SECTION 3: EDUCATION -->
                <div class="text-white fw-bold p-2 mb-3 rounded-1 small mt-5" style="background-color: #1A3E6F;">
                    III. EDUCATIONAL BACKGROUND
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" style="font-size: 0.85rem;">
                        <thead class="bg-light text-center text-muted">
                            <tr>
                                <th class="py-2">LEVEL</th>
                                <th class="py-2">NAME OF SCHOOL</th>
                                <th class="py-2">BASIC EDUCATION/DEGREE/COURSE</th>
                                <th class="py-2">YEAR GRADUATED</th>
                                <th class="py-2">SCHOLARSHIP / HONORS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($education as $edu)
                            <tr>
                                <td class="fw-bold text-center text-uppercase">{{ $edu->level }}</td>
                                <td class="text-uppercase">{{ $edu->school_name }}</td>
                                <td class="text-uppercase">{{ $edu->degree_course ?? 'N/A' }}</td>
                                <td class="text-center">{{ $edu->year_graduated ?? 'N/A' }}</td>
                                <td class="text-center text-uppercase">{{ $edu->scholarship_honors ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No education records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            @endif
        </div>
    </div>
</div>
@endsection