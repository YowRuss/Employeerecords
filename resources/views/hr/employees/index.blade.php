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

    /* Sex Sub-Tabs (Rectangular) */
    .sex-pills .nav-link,
    .gender-pills .nav-link {
        border-radius: 6px;
        padding: 0.38rem 1.1rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #1A3E6F;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    .sex-pills .nav-link:hover,
    .gender-pills .nav-link:hover {
        background-color: #e2e8f0;
        color: #1A3E6F;
    }
    .sex-pills .nav-link.active,
    .gender-pills .nav-link.active {
        background-color: var(--accent-yellow, #FDE047) !important;
        color: #1A3E6F !important;
        border-color: var(--accent-yellow, #FDE047) !important;
        font-weight: 700;
        box-shadow: 0 2px 5px rgba(253, 224, 71, 0.45);
    }

    /* Select2 Bootstrap 5 Theme for Staff Profiling */
    .select2-container--bootstrap-5 .select2-selection {
        border-color: #dee2e6;
        font-size: 0.875rem;
        border-radius: 0.375rem;
        min-height: calc(1.5em + 0.5rem + 2px);
        padding: 0.25rem 0.5rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .select2-container--bootstrap-5.select2-container--open .select2-selection {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .select2-container--bootstrap-5 .select2-dropdown {
        border-color: #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        font-size: 0.875rem;
        z-index: 1056;
    }
    .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
        background-color: #1A3E6F;
        color: #ffffff;
    }
    .select2-container--bootstrap-5 .select2-results__option[aria-disabled="true"] {
        display: none !important;
    }
</style>

<div class="container-fluid py-2">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
        <div>
            <h4 class="text-header-blue fw-bold m-0"><i class="bi bi-people-fill me-2 text-header-blue"></i> Staff Profiling</h4>
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

    <!-- Employee Statistics (Commented out but structurally repaired) -->
    <!--
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            ...
        </div>
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

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <form method="GET" action="{{ route('hr.staff_profiling') }}" id="staff-filter-form">
        <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                <div class="d-flex align-items-center">
                    <h6 class="m-0 fw-bold text-dark me-3">Staff Directory</h6>
                </div>
                <div class="search-container w-100">
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
                </div>
            </div>

            <!-- Tabbed Navigation -->
            <ul class="nav nav-tabs staff-tabs border-bottom-0" id="staffTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ !in_array(request('category'), ['teaching', 'non-teaching', 'unassigned', 'incomplete', 'inactive']) ? 'active' : '' }}" id="all-employees-tab" data-bs-toggle="tab" data-bs-target="#all-employees" type="button" role="tab" aria-controls="all-employees" aria-selected="{{ !in_array(request('category'), ['teaching', 'non-teaching', 'unassigned', 'incomplete', 'inactive']) ? 'true' : 'false' }}">
                        All Employees <span class="badge bg-white text-dark ms-1">{{ $totalActiveCount }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ request('category') == 'teaching' ? 'active' : '' }}" id="teaching-tab" data-bs-toggle="tab" data-bs-target="#teaching" type="button" role="tab" aria-controls="teaching" aria-selected="{{ request('category') == 'teaching' ? 'true' : 'false' }}">
                        Teaching Positions <span class="badge bg-white text-dark ms-1">{{ $teachingCount }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ request('category') == 'non-teaching' ? 'active' : '' }}" id="non-teaching-tab" data-bs-toggle="tab" data-bs-target="#non-teaching" type="button" role="tab" aria-controls="non-teaching" aria-selected="{{ request('category') == 'non-teaching' ? 'true' : 'false' }}">
                        Non-Teaching Positions <span class="badge bg-white text-dark ms-1">{{ $nonTeachingCount }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-warning {{ request('category') == 'unassigned' ? 'active' : '' }}" id="unassigned-tab" data-bs-toggle="tab" data-bs-target="#unassigned" type="button" role="tab" aria-controls="unassigned" aria-selected="{{ request('category') == 'unassigned' ? 'true' : 'false' }}">
                        Unassigned Positions <span class="badge bg-warning text-dark ms-1">{{ $unassignedCount }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-danger {{ request('category') == 'incomplete' ? 'active' : '' }}" id="incomplete-tab" data-bs-toggle="tab" data-bs-target="#incomplete" type="button" role="tab" aria-controls="incomplete" aria-selected="{{ request('category') == 'incomplete' ? 'true' : 'false' }}">
                        Incomplete PDS <span class="badge bg-danger ms-1">{{ $incompleteCount }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-muted {{ request('category') == 'inactive' ? 'active' : '' }}" id="inactive-tab" data-bs-toggle="tab" data-bs-target="#inactive" type="button" role="tab" aria-controls="inactive" aria-selected="{{ request('category') == 'inactive' ? 'true' : 'false' }}">
                        Inactive / Separated <span class="badge bg-secondary ms-1">{{ $inactiveCount }}</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-0 border-top border-light">
            <input type="hidden" name="category" id="active_category" value="{{ request('category', '') }}">
            <input type="hidden" name="sex" id="sex_filter_input" value="{{ request('sex') }}">
            
            <!-- Global Filters: Sex, Position & Learning Area -->
            <div class="p-3 bg-light border-bottom d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                
                <!-- Sex Sub-Tabs (Rectangular) -->
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <ul class="nav nav-pills sex-pills gap-1" id="sexFilterTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button type="button" 
                                    class="nav-link {{ request()->has('sex') && request('sex') !== '' ? '' : 'active' }}" 
                                    onclick="document.getElementById('sex_filter_input').value = ''; document.getElementById('staff-filter-form').submit();">
                                All
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" 
                                    class="nav-link {{ request('sex') === '1' ? 'active' : '' }}" 
                                    onclick="document.getElementById('sex_filter_input').value = '1'; document.getElementById('staff-filter-form').submit();">
                                Male
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" 
                                    class="nav-link {{ request('sex') === '0' ? 'active' : '' }}" 
                                    onclick="document.getElementById('sex_filter_input').value = '0'; document.getElementById('staff-filter-form').submit();">
                                Female
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Position & Learning Area Filters -->
                <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-3">
                    <div class="d-flex align-items-center">
                        <label for="positionSelect" class="form-label mb-0 me-2 fw-semibold small text-nowrap text-secondary">
                            <i class="bi bi-briefcase me-1 text-primary"></i> Filter by Position:
                        </label>
                        <div style="min-width: 220px; max-width: 320px;">
                            <select name="position_id" id="positionSelect" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()" style="width: 100%;">
                                <option value="">All Positions</option>
                                
                                <!-- Always render both optgroups, JavaScript will dynamically hide/disable them based on the active tab -->
                                <optgroup label="Teaching Positions" id="optgroup-teaching">
                                    @foreach($filterPositions->where('category', \App\Enums\PositionCategory::Teaching) as $pos)
                                        <option value="{{ $pos->id }}" {{ request('position_id') == $pos->id ? 'selected' : '' }}>{{ $pos->position_name }}</option>
                                    @endforeach
                                </optgroup>
                                
                                <optgroup label="Non-Teaching Positions" id="optgroup-non-teaching">
                                    @foreach($filterPositions->where('category', \App\Enums\PositionCategory::NonTeaching) as $pos)
                                        <option value="{{ $pos->id }}" {{ request('position_id') == $pos->id ? 'selected' : '' }}>{{ $pos->position_name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    
                    <div id="learningAreaFilterWrapper" class="align-items-center {{ request('category') == 'teaching' ? 'd-flex' : 'd-none' }}">
                        <label for="teaching-learning-area-filter" class="form-label mb-0 me-2 fw-semibold small text-nowrap text-secondary">
                            <i class="bi bi-book me-1 text-primary"></i> Filter by Learning Area:
                        </label>
                        <div style="min-width: 220px; max-width: 320px;">
                            <select name="learning_area_id" id="teaching-learning-area-filter" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()" style="width: 100%;">
                                <option value="">All Learning Areas</option>
                                @foreach($learningAreas as $area)
                                    <option value="{{ $area->id }}" {{ request('learning_area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-3 pb-3">
                @include('hr.employees.partials.staff_table', ['employees' => $employees, 'showContact' => request('category') !== 'incomplete', 'showReminder' => request('category') === 'incomplete'])
            </div>
        </div>

        <div class="card-footer bg-white border-0 py-2">
            <div class="d-flex justify-content-center mt-4 mb-2 pagination-centered">
                {{ $employees->links('pagination::bootstrap-5') }}
            </div>
        </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let select2PositionReady = false;
    let select2LearningAreaReady = false;
    
    // Initialize Select2 with Bootstrap 5 Theme
    function initFiltersSelect2() {
        if (typeof $ !== 'undefined' && $.fn.select2) {
            if (!$('#positionSelect').hasClass('select2-hidden-accessible')) {
                $('#positionSelect').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'All Positions',
                    allowClear: false
                });
                select2PositionReady = true;

                $('#positionSelect').on('select2:select', function() {
                    document.getElementById('staff-filter-form').submit();
                });

                $('#positionSelect').on('change', function() {
                    if (select2PositionReady) {
                        document.getElementById('staff-filter-form').submit();
                    }
                });
            }

            if (!$('#teaching-learning-area-filter').hasClass('select2-hidden-accessible')) {
                $('#teaching-learning-area-filter').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'All Learning Areas',
                    allowClear: false
                });
                select2LearningAreaReady = true;

                $('#teaching-learning-area-filter').on('select2:select', function() {
                    document.getElementById('staff-filter-form').submit();
                });

                $('#teaching-learning-area-filter').on('change', function() {
                    if (select2LearningAreaReady) {
                        document.getElementById('staff-filter-form').submit();
                    }
                });
            }
        }
    }
    
    // Core Dynamic Form Sync Logic
    function syncEmployeeForm() {
        const category = document.getElementById('active_category').value;
        const teachingGroup = document.getElementById('optgroup-teaching');
        const nonTeachingGroup = document.getElementById('optgroup-non-teaching');
        const learningAreaWrapper = document.getElementById('learningAreaFilterWrapper');
        const positionSelect = document.getElementById('positionSelect');
        const learningAreaSelect = document.getElementById('teaching-learning-area-filter');

        // Reset visibility and disabled states based on the active tab
        if (category === 'teaching') {
            if (teachingGroup) { teachingGroup.style.display = ''; teachingGroup.disabled = false; }
            if (nonTeachingGroup) { nonTeachingGroup.style.display = 'none'; nonTeachingGroup.disabled = true; }
            if (learningAreaWrapper) { 
                learningAreaWrapper.classList.remove('d-none');
                learningAreaWrapper.classList.add('d-flex');
            }
            
        } else if (category === 'non-teaching') {
            if (teachingGroup) { teachingGroup.style.display = 'none'; teachingGroup.disabled = true; }
            if (nonTeachingGroup) { nonTeachingGroup.style.display = ''; nonTeachingGroup.disabled = false; }
            if (learningAreaWrapper) { 
                learningAreaWrapper.classList.remove('d-flex');
                learningAreaWrapper.classList.add('d-none'); 
                if (learningAreaSelect) {
                    learningAreaSelect.value = '';
                    if (typeof $ !== 'undefined' && $.fn.select2 && $(learningAreaSelect).hasClass('select2-hidden-accessible')) {
                        $(learningAreaSelect).val('').trigger('change.select2');
                    }
                }
            }
            
        } else {
            // "All Employees" or other tabs - Show everything for positions, hide Learning Area
            if (teachingGroup) { teachingGroup.style.display = ''; teachingGroup.disabled = false; }
            if (nonTeachingGroup) { nonTeachingGroup.style.display = ''; nonTeachingGroup.disabled = false; }
            if (learningAreaWrapper) { 
                learningAreaWrapper.classList.remove('d-flex');
                learningAreaWrapper.classList.add('d-none'); 
                if (learningAreaSelect) {
                    learningAreaSelect.value = '';
                    if (typeof $ !== 'undefined' && $.fn.select2 && $(learningAreaSelect).hasClass('select2-hidden-accessible')) {
                        $(learningAreaSelect).val('').trigger('change.select2');
                    }
                }
            }
        }

        // If the current selected Position belongs to a group we just hid, reset the select box to "All Positions"
        const selectedOption = positionSelect.options[positionSelect.selectedIndex];
        if (selectedOption && selectedOption.parentNode.disabled) {
            positionSelect.value = '';
            if (typeof $ !== 'undefined' && $.fn.select2 && $(positionSelect).hasClass('select2-hidden-accessible')) {
                $(positionSelect).val('').trigger('change.select2');
            }
        }
    }

    // Execute immediately on page load to set the correct visual state
    syncEmployeeForm();
    initFiltersSelect2();
    window.addEventListener('load', initFiltersSelect2);

    // Tab Change Listener
    const tabButtons = document.querySelectorAll('#staffTabs button[data-bs-toggle="tab"]');
    tabButtons.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function(e) {
            const target = e.target.getAttribute('data-bs-target');
            if (target && target.startsWith('#')) {
                const category = target.replace('#', '');
                
                // Update hidden input
                document.getElementById('active_category').value = category;
                
                // Sync the form visual state BEFORE submitting
                syncEmployeeForm();

                // Submit the form to fetch backend data
                document.getElementById('staff-filter-form').submit();
            }
        });
    });

    // Function to append active hash to pagination links
    function syncPaginationHash(hash) {
        if (!hash) return;
        document.querySelectorAll('.pagination-centered .page-link').forEach(link => {
            const baseHref = link.href.split('#')[0];
            link.href = baseHref + hash;
        });
    }

    if (window.location.hash) {
        syncPaginationHash(window.location.hash);
    }
});
</script>
@endsection