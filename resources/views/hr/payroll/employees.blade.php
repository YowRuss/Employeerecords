@extends('layouts.app')

@section('content')
<style>
    .staff-tabs .nav-link {
        border: none;
        color: #1A3E6F;
        font-weight: 600;
        background: transparent;
        border-radius: 0;
        padding: 1rem 1.5rem;
        opacity: 0.7;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
    }
    .staff-tabs .nav-link:hover {
        opacity: 1;
        border-color: rgba(253, 224, 71, 0.5);
    }
    .staff-tabs .nav-link.active {
        background-color: var(--accent-yellow, #FDE047);
        color: #1A3E6F;
        opacity: 1;
        border-color: var(--accent-yellow, #FDE047);
        border-radius: 8px 8px 0 0;
    }
    .sex-pills .nav-link {
        border-radius: 6px;
        padding: 0.38rem 1.1rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #1A3E6F;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease-in-out;
    }
    .sex-pills .nav-link:hover {
        background-color: #e2e8f0;
        color: #1A3E6F;
    }
    .sex-pills .nav-link.active {
        background-color: var(--accent-yellow, #FDE047) !important;
        color: #1A3E6F !important;
        border-color: var(--accent-yellow, #FDE047) !important;
        font-weight: 700;
        box-shadow: 0 2px 5px rgba(253, 224, 71, 0.45);
    }
    @media (max-width: 991.98px) {
        .staff-tabs .nav-link {
            padding: 0.65rem 0.85rem;
        }
        .payroll-profile-table .mobile-action {
            width: 56px;
            min-width: 56px;
            vertical-align: top;
        }
    }
    @media (min-width: 992px) {
        .payroll-profile-table .mobile-action {
            min-width: 170px;
        }
    }
</style>
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-7 mb-3 mb-md-0">
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('hr.payroll.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1">
                    <i class="bi bi-arrow-left me-1"></i> Back to Periods
                </a>
                <span class="badge rounded-pill bg-light text-dark border px-3 py-1 fw-semibold" style="font-size: 0.8rem;">
                    DepEd HR Payroll
                </span>
            </div>
            <h4 class="text-header-blue fw-bold mb-0">
                <i class="bi bi-person-badge-fill me-2 text-header-blue"></i> Employee Payroll Profiles
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Monitor employee compensation, track Notice of Salary Increment (NOSI) eligibility, and manage step advancements.
            </p>
        </div>
        <div class="col-12 col-md-5 text-md-end d-flex flex-wrap justify-content-md-end gap-2">
            <a href="{{ route('payroll.salary_settings') }}" class="btn btn-outline-secondary fw-semibold shadow-sm px-3 py-2">
                <i class="bi bi-sliders me-1"></i> Salary Matrix
            </a>
            <a href="{{ route('hr.payroll.index') }}" class="btn btn-accent fw-bold shadow-sm px-3 py-2">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Payroll Periods
            </a>
        </div>
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm rounded-3 bg-white p-3 h-100">
                <span class="text-muted small fw-bold text-uppercase">Total Employees</span>
                <h4 class="fw-bold mb-0 mt-1" style="color: #1A3E6F;">{{ $counts['total'] }}</h4>
                <span class="small text-muted">Active payroll accounts</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">NOSI Eligible Now</span>
                        <h4 class="fw-bold mb-0 mt-1 text-success">{{ $counts['eligible'] }}</h4>
                        <span class="small text-muted">Due for 3-yr increment</span>
                    </div>
                    @if($counts['eligible'] > 0)
                        <span class="badge bg-success rounded-pill px-2 py-1 small">Action Due</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-info">
                <span class="text-muted small fw-bold text-uppercase">Teaching Personnel</span>
                <h4 class="fw-bold mb-0 mt-1 text-info">{{ $counts['teaching'] }}</h4>
                <span class="small text-muted">Teachers & Master Teachers</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-secondary">
                <span class="text-muted small fw-bold text-uppercase">Non-Teaching Staff</span>
                <h4 class="fw-bold mb-0 mt-1" style="color: #1A3E6F;">{{ $counts['non_teaching'] }}</h4>
                <span class="small text-muted">Administrative & Support</span>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    @php
        $profileFilters = array_filter([
            'search' => $currentSearch,
            'category' => $currentCategory,
            'eligibility' => $currentEligibility,
            'sex' => $currentSex,
            'learning_area_id' => $currentCategory === 'teaching' && $currentLearningAreaId ? $currentLearningAreaId : null,
        ], fn ($value) => $value !== null && $value !== '');

        $filtersExcept = function (array $drop) use ($profileFilters) {
            return array_diff_key($profileFilters, array_flip($drop));
        };
    @endphp
    <div class="card shadow-sm rounded-3 bg-white">
        <form method="GET" action="{{ route('hr.payroll.employees') }}" class="m-0" id="payroll-profile-filter">
            <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                    <h6 class="m-0 fw-bold text-dark">Payroll Directory</h6>
                    <div class="search-container w-100" style="max-width: 420px;">
                        <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                            <span class="input-group-text bg-white border-end-0 border-light"><i class="bi bi-search text-muted"></i></span>
                            <input type="text"
                                   name="search"
                                   value="{{ $currentSearch }}"
                                   class="form-control border-start-0 border-light ps-0"
                                   style="box-shadow: none;"
                                   placeholder="Name, ID, or position">
                            <input type="hidden" name="category" value="{{ $currentCategory }}">
                            <input type="hidden" name="eligibility" value="{{ $currentEligibility }}">
                            <input type="hidden" name="sex" value="{{ $currentSex }}">
                            @if($currentSearch !== '')
                                <a href="{{ route('hr.payroll.employees', $filtersExcept(['search'])) }}" class="btn btn-light border-light text-danger">
                                    <i class="bi bi-x-circle-fill"></i>
                                </a>
                            @else
                                <button type="submit" class="btn btn-light border-light text-muted fw-bold px-3">Search</button>
                            @endif
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs staff-tabs border-bottom-0" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $currentCategory === 'all' ? 'active' : '' }}"
                           href="{{ route('hr.payroll.employees', array_merge($filtersExcept(['category', 'learning_area_id']), ['category' => 'all'])) }}">
                            All Employees <span class="badge bg-white text-dark ms-1">{{ $counts['total'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $currentCategory === 'teaching' ? 'active' : '' }}"
                           href="{{ route('hr.payroll.employees', array_merge($filtersExcept(['category']), ['category' => 'teaching'])) }}">
                            Teaching Positions <span class="badge bg-white text-dark ms-1">{{ $counts['teaching'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $currentCategory === 'non-teaching' ? 'active' : '' }}"
                           href="{{ route('hr.payroll.employees', array_merge($filtersExcept(['category', 'learning_area_id']), ['category' => 'non-teaching'])) }}">
                            Non-Teaching Positions <span class="badge bg-white text-dark ms-1">{{ $counts['non_teaching'] }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="p-3 bg-light border-bottom border-top d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <ul class="nav nav-pills sex-pills gap-1 mb-0">
                        <li class="nav-item">
                            <a class="nav-link {{ $currentSex === '' ? 'active' : '' }}"
                               href="{{ route('hr.payroll.employees', $filtersExcept(['sex'])) }}">
                                All
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentSex === '1' ? 'active' : '' }}"
                               href="{{ route('hr.payroll.employees', array_merge($profileFilters, ['sex' => '1'])) }}">
                                Male
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentSex === '0' ? 'active' : '' }}"
                               href="{{ route('hr.payroll.employees', array_merge($profileFilters, ['sex' => '0'])) }}">
                                Female
                            </a>
                        </li>
                    </ul>
                    <ul class="nav nav-pills sex-pills gap-1 mb-0">
                        <li class="nav-item">
                            <a class="nav-link {{ $currentEligibility === 'eligible' ? '' : 'active' }}"
                               href="{{ route('hr.payroll.employees', array_merge($filtersExcept(['eligibility']), ['eligibility' => 'all'])) }}">
                                All Status
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentEligibility === 'eligible' ? 'active' : '' }}"
                               href="{{ route('hr.payroll.employees', array_merge($profileFilters, ['eligibility' => 'eligible'])) }}">
                                NOSI Due <span class="badge bg-white text-dark ms-1">{{ $counts['eligible'] }}</span>
                            </a>
                        </li>
                    </ul>
                </div>

                @if($currentCategory === 'teaching')
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 w-100">
                        <label for="teaching-learning-area-filter" class="form-label mb-0 me-sm-2 fw-semibold small text-secondary">
                            <i class="bi bi-book me-1 text-header-blue"></i> Filter by Learning Area:
                        </label>
                        <div class="flex-grow-1" style="min-width: 0; max-width: 320px;">
                            <select name="learning_area_id" id="teaching-learning-area-filter" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()">
                                <option value="">All Learning Areas</option>
                                @foreach($learningAreas as $area)
                                    <option value="{{ $area->id }}" {{ (int) $currentLearningAreaId === (int) $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
            </div>
        </form>

        {{-- Table Content --}}
        <div class="card-body p-0">
            @if($employees->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-people text-muted" style="font-size: 3rem; opacity: 0.4;"></i>
                    <h5 class="fw-bold mt-3 text-secondary">No Employees Found</h5>
                    <p class="text-muted small mb-0">Try changing your search keywords or filter criteria.</p>
                    <a href="{{ route('hr.payroll.employees') }}" class="btn btn-sm btn-outline-secondary mt-3">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset Filters
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 payroll-profile-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-muted small fw-bold text-uppercase py-3 ps-3 ps-md-4 border-0">Employee</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 border-0 d-none d-lg-table-cell">Position & Category</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 border-0 d-none d-lg-table-cell">Date Hired / Status</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 border-0 d-none d-lg-table-cell">Salary Grade & Step</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 border-0 d-none d-lg-table-cell">Monthly Basic</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 border-0 d-none d-lg-table-cell">NOSI Eligibility</th>
                                <th class="text-muted small fw-bold text-uppercase py-3 border-0 text-end pe-3 pe-md-4 mobile-action">
                                    <span class="d-none d-lg-inline">Action</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $employee)
                            @php
                                $fullName = ($employee->last_name ?? 'User') . ', ' . ($employee->first_name ?? '');
                                if (!empty($employee->middle_name)) {
                                    $fullName .= ' ' . strtoupper(substr((string) $employee->middle_name, 0, 1)) . '.';
                                }
                                if (!empty($employee->suffix)) {
                                    $fullName .= ' ' . $employee->suffix;
                                }

                                $pds = $employee->pdsPersonalInfo;
                                $employeeNo = $pds->employee_no ?? 'EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT);
                                $positionName = $employee->position ? $employee->position->position_name : 'No Position Assigned';
                                $isTeaching = $employee->isTeaching();
                                $salaryGrade = $employee->position ? $employee->position->salary_grade : null;
                                $currentStep = $employee->step_increment ?: 1;

                                // Rate lookups
                                $currentRate = 0.00;
                                if ($salaryGrade) {
                                    $sgRecord = $salaryMatrix->get($salaryGrade . '_' . $currentStep);
                                    $currentRate = $sgRecord ? (float)$sgRecord->amount : (float)$employee->base_salary;
                                }

                                $nextStepRate = null;
                                if ($salaryGrade && $currentStep < 8) {
                                    $nextSgRecord = $salaryMatrix->get($salaryGrade . '_' . ($currentStep + 1));
                                    $nextStepRate = $nextSgRecord ? (float)$nextSgRecord->amount : null;
                                }

                                $dateHiredRaw = $employee->date_hired;
                                $dateHiredFormatted = $dateHiredRaw
                                    ? \Carbon\Carbon::parse($dateHiredRaw)->format('M d, Y')
                                    : 'Not Recorded';

                                $yearsInService = $dateHiredRaw
                                    ? number_format(\Carbon\Carbon::parse($dateHiredRaw)->diffInDays(now()) / 365.25, 1)
                                    : null;

                                $employmentStatus = $employee->employment_status;
                                $eligibilityDateRaw = $employee->next_eligibility_date;
                                $eligibilityDateFormatted = $eligibilityDateRaw
                                    ? \Carbon\Carbon::parse($eligibilityDateRaw)->format('M d, Y')
                                    : 'N/A';

                                $isEligible = false;
                                if ($salaryGrade && $currentStep < 8 && $eligibilityDateRaw) {
                                    $isEligible = \Carbon\Carbon::parse($eligibilityDateRaw)->lte(now());
                                }
                                $isMaxStep = $currentStep >= 8;

                                $initials = strtoupper(substr((string) ($employee->first_name ?: $employee->name ?: 'U'), 0, 1)) . strtoupper(substr((string) ($employee->last_name ?: ''), 0, 1));
                            @endphp
                            <tr>
                                {{-- Employee Info --}}
                                <td class="ps-3 ps-md-4 py-3">
                                    <div class="d-flex align-items-start gap-2 gap-md-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-accent fw-bold shadow-sm flex-shrink-0"
                                             style="width: 40px; height: 40px; font-size: 0.95rem;">
                                            {{ $initials }}
                                        </div>
                                        <div class="min-w-0">
                                            <a href="javascript:void(0)"
                                               data-bs-toggle="modal"
                                               data-bs-target="#profileModal{{ $employee->id }}"
                                               class="fw-bold text-decoration-none d-block text-break"
                                               style="color: #1A3E6F;">
                                                {{ $fullName }}
                                            </a>
                                            <div class="text-muted small d-flex align-items-center flex-wrap gap-2">
                                                <span class="text-nowrap"><i class="bi bi-hash text-secondary"></i>{{ $employeeNo }}</span>
                                                <span class="d-none d-xl-inline">&bull;</span>
                                                <span class="d-none d-xl-inline text-break">{{ $employee->email }}</span>
                                            </div>
                                            <div class="d-lg-none mt-2">
                                                <div class="fw-semibold text-dark small text-break">{{ $positionName }}</div>
                                                <div class="d-flex align-items-center flex-wrap gap-1 mt-1">
                                                    @if($isTeaching)
                                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-0" style="font-size: 0.7rem;">Teaching</span>
                                                        @if($employee->learningArea)
                                                            <span class="text-muted small">{{ $employee->learningArea->name }}</span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2 py-0" style="font-size: 0.7rem;">Non-Teaching</span>
                                                    @endif
                                                    <span class="badge {{ $employmentStatus === 'Permanent' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : ($employmentStatus === 'Unassigned' ? 'bg-light text-secondary border' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle') }} px-2 py-0" style="font-size: 0.7rem;">
                                                        {{ $employmentStatus }}
                                                    </span>
                                                </div>
                                                <div class="d-flex flex-wrap align-items-center gap-1 mt-2">
                                                    @if($salaryGrade)
                                                        <span class="badge bg-light text-dark border fw-bold px-2 py-1">SG-{{ $salaryGrade }}</span>
                                                        <span class="badge {{ $isMaxStep ? 'bg-success text-white' : 'bg-primary-subtle text-primary-emphasis border border-primary-subtle' }} px-2 py-1">Step {{ $currentStep }}{{ $isMaxStep ? ' (MAX)' : '' }}</span>
                                                    @endif
                                                    <span class="fw-bold font-monospace small" style="color: #1A3E6F;">{{ number_format($currentRate, 2) }}</span>
                                                </div>
                                                <div class="mt-2">
                                                    @if($isMaxStep)
                                                        <span class="badge bg-light text-secondary border px-2 py-1">Max Step Reached</span>
                                                    @elseif($isEligible)
                                                        <span class="badge bg-success text-white px-2 py-1">Eligible Now</span>
                                                    @else
                                                        <span class="badge bg-light text-dark border px-2 py-1">NOSI {{ $eligibilityDateFormatted }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Position & Category --}}
                                <td class="py-3 d-none d-lg-table-cell">
                                    <div class="fw-semibold text-dark">{{ $positionName }}</div>
                                    <div class="d-flex align-items-center gap-1 mt-1">
                                        @if($isTeaching)
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-0" style="font-size: 0.7rem;">
                                                <i class="bi bi-book-half me-1"></i>Teaching
                                            </span>
                                            @if($employee->learningArea)
                                                <span class="text-muted small" style="font-size: 0.72rem;">{{ $employee->learningArea->name }}</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2 py-0" style="font-size: 0.7rem;">
                                                <i class="bi bi-buildings me-1"></i>Non-Teaching
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Date Hired / Status --}}
                                <td class="py-3 d-none d-lg-table-cell">
                                    <div class="fw-medium text-dark">{{ $dateHiredFormatted }}</div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge {{ $employmentStatus === 'Permanent' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : ($employmentStatus === 'Unassigned' ? 'bg-light text-secondary border' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle') }} px-2 py-0" style="font-size: 0.7rem;">
                                            {{ $employmentStatus }}
                                        </span>
                                        @if($yearsInService !== null)
                                            <span class="text-muted small" style="font-size: 0.72rem;">{{ $yearsInService }} yrs</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Grade & Step --}}
                                <td class="py-3 d-none d-lg-table-cell">
                                    @if($salaryGrade)
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge bg-light text-dark border fw-bold px-2 py-1">
                                                SG-{{ $salaryGrade }}
                                            </span>
                                            <span class="badge {{ $isMaxStep ? 'bg-success text-white' : 'bg-primary-subtle text-primary-emphasis border border-primary-subtle' }} px-2 py-1">
                                                Step {{ $currentStep }} {{ $isMaxStep ? '(MAX)' : '' }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary-subtle text-muted">Not Configured</span>
                                    @endif
                                </td>

                                {{-- Monthly Basic --}}
                                <td class="py-3 font-monospace fw-bold d-none d-lg-table-cell" style="color: #1A3E6F;">
                                    {{ number_format($currentRate, 2) }}
                                </td>

                                {{-- NOSI Eligibility --}}
                                <td class="py-3 d-none d-lg-table-cell">
                                    @if($isMaxStep)
                                        <span class="badge bg-light text-secondary border px-2 py-1">
                                            <i class="bi bi-shield-check me-1 text-success"></i>Max Step Reached
                                        </span>
                                    @elseif($isEligible)
                                        <div>
                                            <span class="badge bg-success text-white px-2 py-1 shadow-sm">
                                                <i class="bi bi-check-circle-fill me-1"></i>Eligible Now
                                            </span>
                                            <div class="text-muted small mt-1" style="font-size: 0.72rem;">Due: {{ $eligibilityDateFormatted }}</div>
                                        </div>
                                    @else
                                        <div>
                                            <span class="badge bg-light text-dark border px-2 py-1">
                                                <i class="bi bi-clock me-1 text-muted"></i>{{ $eligibilityDateFormatted }}
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td class="py-3 pe-3 pe-md-4 text-end">
                                    <button type="button"
                                            class="btn btn-sm btn-accent shadow-sm text-nowrap px-2 px-lg-3"
                                            data-bs-toggle="modal"
                                            data-bs-target="#profileModal{{ $employee->id }}"
                                            aria-label="Profile and NOSI">
                                        <i class="bi bi-person-vcard"></i>
                                        <span class="d-none d-lg-inline ms-1">Profile &amp; NOSI</span>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Card Footer --}}
        <div class="card-footer bg-white border-top py-3 d-flex flex-column align-items-center gap-2">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 w-100">
                <span class="text-muted small">
                    Showing <strong>{{ $employees->firstItem() ?? 0 }}</strong>–<strong>{{ $employees->lastItem() ?? 0 }}</strong> of <strong>{{ $employees->total() }}</strong> employees
                </span>
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1 text-secondary"></i> Step increment advances by 1 step every 3 years of continuous satisfactory service.
                </div>
            </div>
            @if($employees->hasPages())
            <div class="pagination-centered w-100">
                {{ $employees->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Employee Payroll Profile Modals --}}
@foreach($employees as $employee)
@php
    $modalFullName = ($employee->last_name ?? 'User') . ', ' . ($employee->first_name ?? '');
    if (!empty($employee->middle_name)) {
        $modalFullName .= ' ' . strtoupper(substr((string) $employee->middle_name, 0, 1)) . '.';
    }
    if (!empty($employee->suffix)) {
        $modalFullName .= ' ' . $employee->suffix;
    }

    $modalPds = $employee->pdsPersonalInfo;
    $modalEmployeeId = $modalPds->employee_no ?? 'EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT);
    $modalPositionName = $employee->position ? $employee->position->position_name : 'Unassigned';
    $modalSalaryGrade = $employee->position ? $employee->position->salary_grade : null;
    $modalCurrentStep = $employee->step_increment ?: 1;

    $modalCurrentRate = 0.00;
    if ($modalSalaryGrade) {
        $sgRec = $salaryMatrix->get($modalSalaryGrade . '_' . $modalCurrentStep);
        $modalCurrentRate = $sgRec ? (float)$sgRec->amount : (float)$employee->base_salary;
    }

    $modalNextStepRate = null;
    if ($modalSalaryGrade && $modalCurrentStep < 8) {
        $nextSgRec = $salaryMatrix->get($modalSalaryGrade . '_' . ($modalCurrentStep + 1));
        $modalNextStepRate = $nextSgRec ? (float)$nextSgRec->amount : null;
    }

    $modalDateHired = $employee->date_hired
        ? \Carbon\Carbon::parse($employee->date_hired)->format('M d, Y')
        : 'N/A';
    $modalYearsInService = $employee->date_hired
        ? number_format(\Carbon\Carbon::parse($employee->date_hired)->diffInDays(now()) / 365.25, 1)
        : null;

    $modalEmploymentStatus = $employee->employment_status;
    $modalLastIncrementDate = $employee->last_increment_date
        ? \Carbon\Carbon::parse($employee->last_increment_date)->format('M d, Y')
        : 'N/A';
    $modalNextEligibilityDate = $employee->next_eligibility_date
        ? \Carbon\Carbon::parse($employee->next_eligibility_date)->format('M d, Y')
        : 'N/A';

    $modalIncrementLogs = $employee->stepIncrementLogs->sortByDesc('effective_date');
    $modalIsAtMaxStep = $modalCurrentStep >= 8;

    $modalIsEligible = false;
    if ($modalSalaryGrade && $modalCurrentStep < 8 && $employee->next_eligibility_date) {
        $modalIsEligible = \Carbon\Carbon::parse($employee->next_eligibility_date)->lte(now());
    }
@endphp
<div class="modal fade text-start" id="profileModal{{ $employee->id }}" tabindex="-1" aria-labelledby="profileModalLabel{{ $employee->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            {{-- Modal Header --}}
            <div class="modal-header border-0 pb-0 bg-accent align-items-start">
                <div class="d-flex align-items-start gap-3 py-2 min-w-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-white" style="width: 48px; height: 48px;">
                        <i class="bi bi-person-badge fs-4 text-header-blue"></i>
                    </div>
                    <div class="min-w-0">
                        <h6 class="modal-title fw-bold mb-0 text-break" id="profileModalLabel{{ $employee->id }}">
                            {{ $modalFullName }}
                        </h6>
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                            <span class="badge bg-white text-dark" style="font-size: 0.72rem;">
                                <i class="bi bi-hash me-1"></i>{{ $modalEmployeeId }}
                            </span>
                            <span class="badge bg-white text-dark" style="font-size: 0.72rem;">
                                <i class="bi bi-briefcase me-1"></i>{{ $modalPositionName }}
                            </span>
                            @if($employee->isTeaching())
                                <span class="badge bg-info text-dark" style="font-size: 0.72rem;">Teaching</span>
                            @else
                                <span class="badge bg-secondary text-white" style="font-size: 0.72rem;">Non-Teaching</span>
                            @endif
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3 p-md-4">
                {{-- Employment Info Card (derived from service_records) --}}
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 h-100" style="background-color: #f0f4f8;">
                            <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">
                                <i class="bi bi-calendar-event me-1"></i>Date Hired & Service Length
                            </div>
                            <div class="fw-bold" style="color: #1A3E6F;">{{ $modalDateHired }}</div>
                            @if($modalYearsInService !== null)
                                <div class="text-muted small mt-1">
                                    <strong>{{ $modalYearsInService }}</strong> continuous years in government service
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 h-100" style="background-color: #f0f4f8;">
                            <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">
                                <i class="bi bi-shield-check me-1"></i>Employment Status
                            </div>
                            <div class="fw-bold" style="color: #1A3E6F;">
                                <span class="badge {{ $modalEmploymentStatus === 'Permanent' ? 'bg-success' : ($modalEmploymentStatus === 'Unassigned' ? 'bg-secondary' : 'bg-info text-dark') }} px-2 py-1">
                                    {{ $modalEmploymentStatus }}
                                </span>
                            </div>
                            <div class="text-muted small mt-1">
                                Dynamically verified from Service Records
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Salary Details Card --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-2">
                        <h6 class="fw-bold mb-0 small" style="color: #1A3E6F;">
                            <i class="bi bi-cash-stack me-2"></i>Compensation & Salary Details
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-borderless mb-0 small">
                            <tbody>
                                <tr class="border-bottom">
                                    <td class="text-muted py-2 ps-3" style="width: 50%;">Current Salary Grade</td>
                                    <td class="fw-bold text-end pe-3 py-2" style="color: #1A3E6F;">
                                        {{ $modalSalaryGrade ? 'SG-' . $modalSalaryGrade : 'Not Set' }}
                                    </td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-muted py-2 ps-3">Current Step Increment</td>
                                    <td class="fw-bold text-end pe-3 py-2" style="color: #1A3E6F;">
                                        Step {{ $modalCurrentStep }}
                                        @if($modalIsAtMaxStep)
                                            <span class="badge bg-success ms-1" style="font-size: 0.65rem;">MAX STEP</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-muted py-2 ps-3">Monthly Basic Rate</td>
                                    <td class="fw-bold text-end pe-3 py-2 font-monospace" style="color: #1A3E6F;">
                                        {{ number_format($modalCurrentRate, 2) }}
                                    </td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-muted py-2 ps-3">Next Step Rate (Step {{ $modalCurrentStep + 1 }})</td>
                                    <td class="fw-bold text-end pe-3 py-2 font-monospace {{ $modalNextStepRate ? 'text-success' : 'text-muted' }}">
                                        {{ $modalNextStepRate ? number_format($modalNextStepRate, 2) : ($modalIsAtMaxStep ? 'At Maximum Step' : 'N/A') }}
                                    </td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-muted py-2 ps-3">Last Increment Date</td>
                                    <td class="fw-bold text-end pe-3 py-2" style="color: #1A3E6F;">{{ $modalLastIncrementDate }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2 ps-3">Next NOSI Eligibility Date</td>
                                    <td class="fw-bold text-end pe-3 py-2">
                                        @if($modalIsEligible)
                                            <span class="text-success">
                                                <i class="bi bi-check-circle-fill me-1"></i>{{ $modalNextEligibilityDate }}
                                                <span class="badge bg-success ms-1" style="font-size: 0.65rem;">ELIGIBLE NOW</span>
                                            </span>
                                        @else
                                            <span style="color: #1A3E6F;">{{ $modalNextEligibilityDate }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Step Increment Action (HR/Admin only) --}}
                @if(in_array((int)session('role_id'), [2, 3]))
                <div class="card shadow-sm mb-4 {{ $modalIsAtMaxStep ? 'opacity-75' : '' }}">
                    <div class="card-header bg-white border-bottom py-2">
                        <h6 class="fw-bold mb-0 small" style="color: #1A3E6F;">
                            <i class="bi bi-arrow-up-circle me-2"></i>Process Step Increment (NOSI)
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($modalIsAtMaxStep)
                            <div class="alert alert-success py-2 px-3 mb-0 small d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                This employee is already at the maximum salary step (Step 8). No further increments are available.
                            </div>
                        @else
                            <form action="{{ route('hr.payroll.step_increment.process', $employee->id) }}" method="POST" class="d-flex flex-column flex-sm-row align-items-sm-end gap-3">
                                @csrf
                                <div class="flex-grow-1">
                                    <label for="effective_date_{{ $employee->id }}" class="form-label small fw-semibold text-muted mb-1">
                                        Effective Date
                                    </label>
                                    <input type="date"
                                           class="form-control form-control-sm"
                                           id="effective_date_{{ $employee->id }}"
                                           name="effective_date"
                                           value="{{ now()->toDateString() }}">
                                </div>
                                <div class="flex-shrink-0">
                                    <button type="submit" class="btn btn-sm btn-accent px-4 shadow-sm w-100 w-sm-auto"
                                            onclick="return confirm('Process step increment from Step {{ $modalCurrentStep }} to Step {{ $modalCurrentStep + 1 }} for {{ $modalFullName }}?')">
                                        <i class="bi bi-arrow-up-circle me-1"></i> Process Increment
                                    </button>
                                </div>
                            </form>
                            <div class="mt-2 small text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Advances from <strong>Step {{ $modalCurrentStep }}</strong> ({{ number_format($modalCurrentRate, 2) }}) to <strong>Step {{ $modalCurrentStep + 1 }}</strong>
                                @if($modalNextStepRate)
                                    ({{ number_format($modalNextStepRate, 2) }})
                                @endif
                                and records an audit log entry with today's date.
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Increment History / Audit Trail --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 small" style="color: #1A3E6F;">
                            <i class="bi bi-clock-history me-2"></i>Increment History / Audit Trail
                        </h6>
                        @if($modalIncrementLogs->count() > 0)
                            <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">
                                {{ $modalIncrementLogs->count() }} {{ Str::plural('record', $modalIncrementLogs->count()) }}
                            </span>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        @if($modalIncrementLogs->isEmpty())
                            <div class="text-center py-4 text-muted small">
                                <i class="bi bi-inbox d-block mb-2" style="font-size: 1.5rem; opacity: 0.4;"></i>
                                No increment history recorded yet.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0 small align-middle">
                                    <thead class="bg-light text-secondary" style="font-size: 0.72rem;">
                                        <tr>
                                            <th class="py-2 ps-3">Effective Date</th>
                                            <th class="py-2 text-center">Step Change</th>
                                            <th class="py-2 text-end">Old Rate</th>
                                            <th class="py-2 text-end">New Rate</th>
                                            <th class="py-2 pe-3">Approved By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($modalIncrementLogs as $log)
                                        <tr>
                                            <td class="ps-3 py-2">
                                                {{ $log->effective_date ? \Carbon\Carbon::parse($log->effective_date)->format('M d, Y') : '—' }}
                                            </td>
                                            <td class="text-center py-2">
                                                <span class="badge bg-light text-dark border font-monospace">
                                                    {{ $log->old_step }} <i class="bi bi-arrow-right mx-1 text-muted"></i> {{ $log->new_step }}
                                                </span>
                                            </td>
                                            <td class="text-end py-2 font-monospace text-muted">
                                                {{ number_format($log->old_rate, 2) }}
                                            </td>
                                            <td class="text-end py-2 font-monospace fw-bold text-success">
                                                {{ number_format($log->new_rate, 2) }}
                                            </td>
                                            <td class="pe-3 py-2">
                                                <span class="badge bg-light text-secondary border">
                                                    {{ $log->approver ? $log->approver->name ?? ($log->approver->first_name . ' ' . $log->approver->last_name) : 'System' }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light py-2 px-4 border-top">
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
