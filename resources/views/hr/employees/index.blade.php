@extends('layouts.app')

@section('content')
<style>
    @media (min-width: 768px) {
        .search-container {
            max-width: 350px;
        }
    }

    /* System Design Pagination Styling */
    .pagination-centered nav {
        width: 100%;
        display: flex;
        justify-content: center;
    }
    .pagination-centered .d-flex.justify-content-between.flex-fill.d-sm-none {
        display: none !important; /* Hides mobile summary row */
    }
    .pagination-centered .d-none.flex-sm-fill.d-sm-flex.align-items-sm-center.justify-content-sm-between > div:first-child {
        display: none !important; /* Hides the 'Showing 1 to 10...' text block */
    }
    .pagination-centered .d-none.flex-sm-fill.d-sm-flex.align-items-sm-center.justify-content-sm-between > div:last-child {
        width: 100%;
        display: flex;
        justify-content: center;
    }
    .pagination-centered .pagination {
        display: flex;
        gap: 6px;
        margin: 0;
        padding: 0;
    }
    .pagination-centered .page-item .page-link {
        color: #1A3E6F;
        font-weight: 600;
        font-size: 0.875rem;
        min-width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px !important;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease-in-out;
        text-decoration: none;
    }
    .pagination-centered .page-item:not(.active):not(.disabled) .page-link:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
    }
    .pagination-centered .page-item.active .page-link {
        background: linear-gradient(135deg, #1A3E6F, #0f2746);
        border-color: #1A3E6F;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(26, 62, 111, 0.25);
    }
    .pagination-centered .page-item.disabled .page-link {
        background-color: #f8fafc;
        border-color: #f1f5f9;
        color: #94a3b8;
        opacity: 0.6;
        cursor: not-allowed;
    }
    .pagination-centered .page-link:focus {
        box-shadow: 0 0 0 3px rgba(26, 62, 111, 0.2);
    }
</style>

<div class="container-fluid py-2">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
        <div>
            <h4 class="text-accent fw-bold m-0"><i class="bi bi-people-fill me-2"></i> Staff Profiling</h4>
            <p class="text-muted small mt-1 mb-0">Manage and oversee all employee profiles and records.</p>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Employee Statistics -->
    <div class="row g-3 mb-3">
        <!-- Gender Statistics 
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 h-100 overflow-hidden" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 36px; height: 36px;">
                            <i class="bi bi-gender-ambiguous fs-5"></i>
                        </div>
                        <h6 class="fw-bold m-0 text-dark">Gender Distribution</h6>
                    </div>
                    <div class="row g-2 text-center mt-1">
                        <div class="col-6">
                            <div class="p-2 bg-white rounded-3 shadow-sm border border-light">
                                <h4 class="fw-bold text-primary mb-0">{{ $maleCount }}</h4>
                                <div class="text-muted small fw-medium text-uppercase" style="font-size: 0.75rem;"><i class="bi bi-gender-male me-1"></i> Male</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-white rounded-3 shadow-sm border border-light">
                                <h4 class="fw-bold text-danger mb-0">{{ $femaleCount }}</h4>
                                <div class="text-muted small fw-medium text-uppercase" style="font-size: 0.75rem;"><i class="bi bi-gender-female me-1"></i> Female</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        -->

        <!-- Position Statistics 
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 h-100 overflow-hidden" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 36px; height: 36px;">
                            <i class="bi bi-briefcase-fill fs-5"></i>
                        </div>
                        <h6 class="fw-bold m-0 text-dark">Position Categories</h6>
                    </div>
                    <div class="row g-2 text-center mt-1">
                        <div class="col-6">
                            <div class="p-2 bg-white rounded-3 shadow-sm border border-light">
                                <h4 class="fw-bold text-success mb-0">{{ $positionStats['Teaching'] ?? 0 }}</h4>
                                <div class="text-muted small fw-medium text-uppercase" style="font-size: 0.75rem;"><i class="bi bi-book-half me-1"></i> Teaching</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-white rounded-3 shadow-sm border border-light">
                                <h4 class="fw-bold text-warning mb-0">{{ $positionStats['Non-Teaching'] ?? 0 }}</h4>
                                <div class="text-muted small fw-medium text-uppercase" style="font-size: 0.75rem;"><i class="bi bi-buildings me-1"></i> Non-Teaching</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    -->

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
    </style>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                <div class="d-flex align-items-center">
                    <h6 class="m-0 fw-bold text-dark me-3">Staff Directory</h6>
                </div>
                <form method="GET" action="{{ route('hr.staff_profiling') }}" class="search-container w-100">
                    <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 border-light"><i class="bi bi-search text-muted"></i></span>
                        <input
                            type="text"
                            name="search"
                            value="{{ $search ?? '' }}"
                            class="form-control border-start-0 border-light ps-0 focus-ring"
                            style="box-shadow: none;"
                            placeholder="Search by name, username, or position..."
                        >
                        @if(!empty($search))
                        <a href="{{ route('hr.staff_profiling') }}" class="btn btn-light border-light text-danger">
                            <i class="bi bi-x-circle-fill"></i>
                        </a>
                        @else
                        <button type="submit" class="btn btn-light border-light text-muted fw-bold px-3">
                            Search
                        </button>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabbed Navigation -->
            <ul class="nav nav-tabs staff-tabs border-bottom-0" id="staffTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-employees-tab" data-bs-toggle="tab" data-bs-target="#all-employees" type="button" role="tab" aria-controls="all-employees" aria-selected="true">
                        All Employees <span class="badge bg-white text-dark ms-1">{{ count($allEmployees) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="teaching-tab" data-bs-toggle="tab" data-bs-target="#teaching" type="button" role="tab" aria-controls="teaching" aria-selected="false">
                        Teaching Positions <span class="badge bg-white text-dark ms-1">{{ count($teachingStaff) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="non-teaching-tab" data-bs-toggle="tab" data-bs-target="#non-teaching" type="button" role="tab" aria-controls="non-teaching" aria-selected="false">
                        Non-Teaching Positions <span class="badge bg-white text-dark ms-1">{{ count($nonTeachingStaff) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-danger" id="incomplete-tab" data-bs-toggle="tab" data-bs-target="#incomplete" type="button" role="tab" aria-controls="incomplete" aria-selected="false">
                        Incomplete PDS <span class="badge bg-danger ms-1">{{ count($incompletePds) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-muted" id="inactive-tab" data-bs-toggle="tab" data-bs-target="#inactive" type="button" role="tab" aria-controls="inactive" aria-selected="false">
                        Inactive / Separated <span class="badge bg-secondary ms-1">{{ count($inactiveEmployees) }}</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-0 border-top border-light">
            <div class="tab-content" id="staffTabsContent">
                <!-- All Employees Tab -->
                <div class="tab-pane fade show active" id="all-employees" role="tabpanel" aria-labelledby="all-employees-tab">
                    @include('hr.employees.partials.staff_table', ['employees' => $allEmployees, 'showContact' => true, 'showReminder' => false])
                </div>

                <!-- Teaching Tab -->
                <div class="tab-pane fade" id="teaching" role="tabpanel" aria-labelledby="teaching-tab">
                    
                    <!-- Sub-tabs for Teaching & Filter -->
                    <div class="px-3 pt-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <ul class="nav nav-pills" id="teaching-subtabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded-pill fw-bold px-4" id="teaching-all-tab" data-bs-toggle="pill" data-bs-target="#teaching-all" type="button" role="tab" aria-controls="teaching-all" aria-selected="true" style="transition: all 0.2s;">
                                    All Teaching
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill fw-bold px-4 mx-2" id="teaching-male-tab" data-bs-toggle="pill" data-bs-target="#teaching-male" type="button" role="tab" aria-controls="teaching-male" aria-selected="false" style="transition: all 0.2s;">
                                    Male <span class="badge bg-secondary ms-1">{{ $maleCount }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill fw-bold px-4" id="teaching-female-tab" data-bs-toggle="pill" data-bs-target="#teaching-female" type="button" role="tab" aria-controls="teaching-female" aria-selected="false" style="transition: all 0.2s;">
                                    Female <span class="badge bg-secondary ms-1">{{ $femaleCount }}</span>
                                </button>
                            </li>
                        </ul>
                        
                        <!-- Learning Area Filter -->
                        <div class="d-flex align-items-center mt-3 mt-md-0">
                            <label for="teaching-learning-area-filter" class="form-label mb-0 me-2 fw-bold text-nowrap" style="color: #1A3E6F;">Filter by Learning Area:</label>
                            <select id="teaching-learning-area-filter" class="form-select shadow-sm" style="background-color: white; color: #1A3E6F; border-color: #1A3E6F; min-width: 200px;">
                                <option value="">All Learning Areas</option>
                                @foreach($learningAreas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="tab-content mt-3" id="teaching-subtabs-content">
                        <!-- All Teaching -->
                        <div class="tab-pane fade show active" id="teaching-all" role="tabpanel" aria-labelledby="teaching-all-tab">
                            @include('hr.employees.partials.staff_table', ['employees' => $teachingStaff, 'showContact' => true, 'showReminder' => false])
                        </div>
                        
                        <!-- Male Teaching -->
                        <div class="tab-pane fade" id="teaching-male" role="tabpanel" aria-labelledby="teaching-male-tab">
                            @include('hr.employees.partials.staff_table', ['employees' => $maleEmployees, 'showContact' => true, 'showReminder' => false])
                        </div>
                        
                        <!-- Female Teaching -->
                        <div class="tab-pane fade" id="teaching-female" role="tabpanel" aria-labelledby="teaching-female-tab">
                            @include('hr.employees.partials.staff_table', ['employees' => $femaleEmployees, 'showContact' => true, 'showReminder' => false])
                        </div>
                    </div>
                </div>
                
                <!-- Non-Teaching Tab -->
                <div class="tab-pane fade" id="non-teaching" role="tabpanel" aria-labelledby="non-teaching-tab">
                    
                    <!-- Sub-tabs for Non-Teaching -->
                    <div class="px-3 pt-3">
                        <ul class="nav nav-pills" id="non-teaching-subtabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded-pill fw-bold px-4" id="non-teaching-all-tab" data-bs-toggle="pill" data-bs-target="#non-teaching-all" type="button" role="tab" aria-controls="non-teaching-all" aria-selected="true" style="transition: all 0.2s;">
                                    All Non-Teaching
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill fw-bold px-4 mx-2" id="non-teaching-male-tab" data-bs-toggle="pill" data-bs-target="#non-teaching-male" type="button" role="tab" aria-controls="non-teaching-male" aria-selected="false" style="transition: all 0.2s;">
                                    Male <span class="badge bg-secondary ms-1">{{ $nonTeachingMaleCount }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill fw-bold px-4" id="non-teaching-female-tab" data-bs-toggle="pill" data-bs-target="#non-teaching-female" type="button" role="tab" aria-controls="non-teaching-female" aria-selected="false" style="transition: all 0.2s;">
                                    Female <span class="badge bg-secondary ms-1">{{ $nonTeachingFemaleCount }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="tab-content mt-3" id="non-teaching-subtabs-content">
                        <!-- All Non-Teaching -->
                        <div class="tab-pane fade show active" id="non-teaching-all" role="tabpanel" aria-labelledby="non-teaching-all-tab">
                            @include('hr.employees.partials.staff_table', ['employees' => $nonTeachingStaff, 'showContact' => true, 'showReminder' => false])
                        </div>
                        
                        <!-- Male Non-Teaching -->
                        <div class="tab-pane fade" id="non-teaching-male" role="tabpanel" aria-labelledby="non-teaching-male-tab">
                            @include('hr.employees.partials.staff_table', ['employees' => $nonTeachingMaleEmployees, 'showContact' => true, 'showReminder' => false])
                        </div>
                        
                        <!-- Female Non-Teaching -->
                        <div class="tab-pane fade" id="non-teaching-female" role="tabpanel" aria-labelledby="non-teaching-female-tab">
                            @include('hr.employees.partials.staff_table', ['employees' => $nonTeachingFemaleEmployees, 'showContact' => true, 'showReminder' => false])
                        </div>
                    </div>
                </div>
                
                <!-- Incomplete PDS Tab -->
                <div class="tab-pane fade" id="incomplete" role="tabpanel" aria-labelledby="incomplete-tab">
                    @include('hr.employees.partials.staff_table', ['employees' => $incompletePds, 'showContact' => false, 'showReminder' => true])
                </div>
                
                <!-- Inactive / Separated Tab -->
                <div class="tab-pane fade" id="inactive" role="tabpanel" aria-labelledby="inactive-tab">
                    @include('hr.employees.partials.staff_table', ['employees' => $inactiveEmployees, 'showContact' => true, 'showReminder' => false])
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-0 py-2">
            <div class="d-flex justify-content-center mt-4 mb-2 pagination-centered">
                {{ $employees->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Restore active tab from URL hash (e.g., #teaching, #non-teaching)
    const currentHash = window.location.hash;
    if (currentHash) {
        const targetTabButton = document.querySelector(`button[data-bs-target="${currentHash}"]`);
        if (targetTabButton) {
            const tabInstance = bootstrap.Tab.getOrCreateInstance(targetTabButton);
            tabInstance.show();
        }
    }

    // Function to append active hash to pagination links so page changes preserve the tab
    function syncPaginationHash(hash) {
        if (!hash) return;
        document.querySelectorAll('.pagination-centered .page-link').forEach(link => {
            const baseHref = link.href.split('#')[0];
            link.href = baseHref + hash;
        });
    }

    // Sync initial pagination links if a hash is present
    if (window.location.hash) {
        syncPaginationHash(window.location.hash);
    }

    // 2. Listen for tab switches and update URL hash + pagination links
    const tabButtons = document.querySelectorAll('#staffTabs button[data-bs-toggle="tab"]');
    tabButtons.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function(e) {
            const target = e.target.getAttribute('data-bs-target');
            if (target && target.startsWith('#')) {
                history.replaceState(null, null, window.location.pathname + window.location.search + target);
                syncPaginationHash(target);
            }
        });
    });

    // 3. Learning Area Filter
    const filterSelect = document.getElementById('teaching-learning-area-filter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            const selectedAreaId = this.value;
            
            // Only filter rows inside the teaching tab pane
            const teachingPanes = document.querySelectorAll('#teaching .tab-pane');
            
            teachingPanes.forEach(pane => {
                const rows = pane.querySelectorAll('tbody tr.employee-row');
                rows.forEach(row => {
                    if (selectedAreaId === '') {
                        row.style.display = '';
                    } else {
                        const rowAreaId = row.getAttribute('data-learning-area');
                        if (rowAreaId === selectedAreaId) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        });
    }
});
</script>
@endsection