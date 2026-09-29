@extends('layouts.app')

@section('content')
<style>
    /* System Tabs Styling (Yellow Active Theme) */
    .settings-tabs {
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .settings-tabs::-webkit-scrollbar { 
        display: none; 
    }
    .settings-tabs .nav-item { 
        flex-shrink: 0; 
    }
    .settings-tabs .nav-link {
        border: none;
        color: #1A3E6F;
        font-weight: 600;
        background: transparent;
        border-radius: 0;
        padding: 0.85rem 1.5rem;
        opacity: 0.75;
        transition: all 0.2s ease;
        border-bottom: 3px solid transparent;
        white-space: nowrap;
    }
    .settings-tabs .nav-link:hover {
        opacity: 1;
        border-color: rgba(253, 224, 71, 0.5);
    }
    .settings-tabs .nav-link.active {
        background-color: var(--accent-yellow, #FDE047);
        color: #1A3E6F;
        font-weight: 700;
        opacity: 1;
        border-color: #EAB308;
        border-radius: 8px 8px 0 0;
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
    .pagination-centered .d-none.flex-sm-fill.d-sm-flex {
        display: flex !important; /* Ensure desktop structure shows across all screens */
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
    .pagination-centered .page-link svg {
        width: 14px;
        height: 14px;
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
        color: #ffffff !important;
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

    .table th {
        font-size: 0.8rem;
        text-transform: uppercase;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.5px;
    }
</style>

@php
    $activeTab = session('active_tab', request('tab', 'teaching'));
    if (! session()->has('active_tab') && ! request()->filled('tab')) {
        $pageTabs = array_filter([
            'teaching' => request()->has('teaching_page'),
            'non-teaching' => request()->has('non_teaching_page'),
            'learning-areas' => request()->has('learning_areas_page'),
        ]);
        if (count($pageTabs) === 1) {
            $activeTab = array_key_first($pageTabs);
        }
    }
@endphp

<div class="container-fluid py-2">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
        <div>
            <h4 class="text-header-blue fw-bold m-0"><i class="bi bi-gear-fill me-2 text-header-blue"></i> Positions & Areas</h4>
            <p class="text-muted small mt-1 mb-0">Manage the system's position titles and learning areas.</p>
        </div>
    </div>

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

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-center mb-1">
            <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
            <strong>Please check the following:</strong>
        </div>
        <ul class="mb-0 small ps-4">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white pt-3 pb-0 border-bottom">
            <ul class="nav nav-tabs settings-tabs border-bottom-0" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'teaching' ? 'active' : '' }}" id="tab-teaching" data-bs-toggle="tab" data-bs-target="#pane-teaching" type="button" role="tab">
                        Teaching <span class="badge bg-white text-dark ms-1 border">{{ $teachingPositions->total() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'non-teaching' ? 'active' : '' }}" id="tab-non-teaching" data-bs-toggle="tab" data-bs-target="#pane-non-teaching" type="button" role="tab">
                        Non-Teaching <span class="badge bg-white text-dark ms-1 border">{{ $nonTeachingPositions->total() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'learning-areas' ? 'active' : '' }}" id="tab-learning-areas" data-bs-toggle="tab" data-bs-target="#pane-learning-areas" type="button" role="tab">
                        Learning Areas <span class="badge bg-white text-dark ms-1 border">{{ $learningAreas->total() }}</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="settingsTabsContent">
                
                <!-- Teaching Tab -->
                <div class="tab-pane fade {{ $activeTab === 'teaching' ? 'show active' : '' }}" id="pane-teaching" role="tabpanel">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-3">
                        <h6 class="text-dark fw-bold mb-0">Teaching Positions</h6>
                        <button type="button" class="btn btn-accent btn-sm fw-bold px-3 text-nowrap rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addTeachingModal">
                            <i class="bi bi-plus-lg me-1"></i> Add Position
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle bg-white m-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Position Name</th>
                                    <th>Salary Grade</th>
                                    <th class="text-center" style="width: 120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teachingPositions as $pos)
                                <tr>
                                    <td class="fw-bold text-uppercase ps-3 text-dark">{{ $pos->position_name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $pos->salary_grade ? 'SG ' . $pos->salary_grade : 'Not Set' }}</span></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary p-1 rounded-2 edit-position-btn" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editPositionModal"
                                                    data-id="{{ $pos->id }}" 
                                                    data-name="{{ $pos->position_name }}" 
                                                    data-category="{{ $pos->category instanceof \App\Enums\PositionCategory ? $pos->category->value : ($pos->category ?? \App\Enums\PositionCategory::Teaching->value) }}"
                                                    data-sg="{{ $pos->salary_grade }}"
                                                    title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('hr.positions.destroy', $pos->id) }}" method="POST" onsubmit="return confirm('Delete this position?');" class="m-0">
                                                @csrf
                                                <input type="hidden" name="active_tab" value="teaching">
                                                <button type="submit" class="btn btn-sm btn-outline-danger p-1 rounded-2" title="Delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-opacity-50"></i>
                                        No teaching positions created yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($teachingPositions->hasPages())
                    <div class="d-flex justify-content-center mt-4 mb-2 pagination-centered">
                        {{ $teachingPositions->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>

                <!-- Non-Teaching Tab -->
                <div class="tab-pane fade {{ $activeTab === 'non-teaching' ? 'show active' : '' }}" id="pane-non-teaching" role="tabpanel">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-3">
                        <h6 class="text-dark fw-bold mb-0">Non-Teaching Positions</h6>
                        <button type="button" class="btn btn-accent btn-sm fw-bold px-3 text-nowrap rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addNonTeachingModal">
                            <i class="bi bi-plus-lg me-1"></i> Add Position
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle bg-white m-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Position Name</th>
                                    <th>Salary Grade</th>
                                    <th class="text-center" style="width: 120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nonTeachingPositions as $pos)
                                <tr>
                                    <td class="fw-bold text-uppercase ps-3 text-dark">{{ $pos->position_name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $pos->salary_grade ? 'SG ' . $pos->salary_grade : 'Not Set' }}</span></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary p-1 rounded-2 edit-position-btn" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editPositionModal"
                                                    data-id="{{ $pos->id }}" 
                                                    data-name="{{ $pos->position_name }}" 
                                                    data-category="{{ $pos->category instanceof \App\Enums\PositionCategory ? $pos->category->value : ($pos->category ?? \App\Enums\PositionCategory::NonTeaching->value) }}"
                                                    data-sg="{{ $pos->salary_grade }}"
                                                    title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('hr.positions.destroy', $pos->id) }}" method="POST" onsubmit="return confirm('Delete this position?');" class="m-0">
                                                @csrf
                                                <input type="hidden" name="active_tab" value="non-teaching">
                                                <button type="submit" class="btn btn-sm btn-outline-danger p-1 rounded-2" title="Delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-opacity-50"></i>
                                        No non-teaching positions created yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($nonTeachingPositions->hasPages())
                    <div class="d-flex justify-content-center mt-4 mb-2 pagination-centered">
                        {{ $nonTeachingPositions->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
                
                <!-- Learning Areas Tab -->
                <div class="tab-pane fade {{ $activeTab === 'learning-areas' ? 'show active' : '' }}" id="pane-learning-areas" role="tabpanel">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-3">
                        <h6 class="text-dark fw-bold mb-0">Learning Areas</h6>
                        <button type="button" class="btn btn-accent btn-sm fw-bold px-3 text-nowrap rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addLearningAreaModal">
                            <i class="bi bi-plus-lg me-1"></i> Add Learning Area
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle bg-white m-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Learning Area Name</th>
                                    <th class="text-center" style="width: 120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($learningAreas as $area)
                                <tr>
                                    <td class="fw-bold text-uppercase ps-3 text-dark">{{ $area->name }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary p-1 rounded-2 edit-area-btn" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editLearningAreaModal"
                                                    data-id="{{ $area->id }}" 
                                                    data-name="{{ $area->name }}" 
                                                    title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('hr.learning_areas.destroy', $area->id) }}" method="POST" onsubmit="return confirm('Delete this learning area?');" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger p-1 rounded-2" title="Delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-opacity-50"></i>
                                        No learning areas created yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($learningAreas->hasPages())
                    <div class="d-flex justify-content-center mt-4 mb-2 pagination-centered">
                        {{ $learningAreas->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Add Teaching Modal -->
<div class="modal fade" id="addTeachingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-plus-circle text-accent me-2"></i> Add Teaching Position
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('hr.positions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="teaching">
                <input type="hidden" name="category" value="{{ \App\Enums\PositionCategory::Teaching->value }}">
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Position Name</label>
                        <input type="text" name="position_name" class="form-control text-uppercase" placeholder="e.g. TEACHER I" required style="border-radius: 8px;">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-muted">Salary Grade (SG)</label>
                        <input type="number" name="salary_grade" class="form-control" placeholder="e.g. 11" min="1" max="33" required style="border-radius: 8px;">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent rounded-3 px-4 fw-bold shadow-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Non-Teaching Modal -->
<div class="modal fade" id="addNonTeachingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-plus-circle text-accent me-2"></i> Add Non-Teaching Position
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('hr.positions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="non-teaching">
                <input type="hidden" name="category" value="{{ \App\Enums\PositionCategory::NonTeaching->value }}">
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Position Name</label>
                        <input type="text" name="position_name" class="form-control text-uppercase" placeholder="e.g. ADMINISTRATIVE ASSISTANT II" required style="border-radius: 8px;">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-muted">Salary Grade (SG)</label>
                        <input type="number" name="salary_grade" class="form-control" placeholder="e.g. 8" min="1" max="33" required style="border-radius: 8px;">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent rounded-3 px-4 fw-bold shadow-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Learning Area Modal -->
<div class="modal fade" id="addLearningAreaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-plus-circle text-accent me-2"></i> Add Learning Area
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('hr.learning_areas.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-muted">Learning Area Name</label>
                        <input type="text" name="name" class="form-control text-uppercase" placeholder="e.g. MATHEMATICS" required style="border-radius: 8px;">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent rounded-3 px-4 fw-bold shadow-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Position Modal -->
<div class="modal fade" id="editPositionModal" tabindex="-1" aria-labelledby="editPositionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="editPositionModalLabel">
                    <i class="bi bi-pencil-square text-accent me-2"></i> Edit Position
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPositionForm" method="POST">
                @csrf
                <input type="hidden" name="active_tab" id="edit_position_active_tab" value="teaching">
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label for="edit_position_name" class="form-label fw-bold small text-muted">Position Name</label>
                        <input type="text" name="position_name" id="edit_position_name" class="form-control text-uppercase" required style="border-radius: 8px;">
                    </div>
                    <div class="mb-2">
                        <label for="edit_position_category" class="form-label fw-bold small text-muted">Category</label>
                        <select name="category" id="edit_position_category" class="form-select" style="border-radius: 8px;">
                            <option value="{{ \App\Enums\PositionCategory::Teaching->value }}">Teaching</option>
                            <option value="{{ \App\Enums\PositionCategory::NonTeaching->value }}">Non-Teaching</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="edit_salary_grade" class="form-label fw-bold small text-muted">Salary Grade (SG)</label>
                        <input type="number" name="salary_grade" id="edit_salary_grade" class="form-control" placeholder="e.g. 15" min="1" max="33" style="border-radius: 8px;">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent rounded-3 px-4 fw-bold shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Learning Area Modal -->
<div class="modal fade" id="editLearningAreaModal" tabindex="-1" aria-labelledby="editLearningAreaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="editLearningAreaModalLabel">
                    <i class="bi bi-pencil-square text-accent me-2"></i> Edit Learning Area
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editLearningAreaForm" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-2">
                        <label for="edit_area_name" class="form-label fw-bold small text-muted">Learning Area Name</label>
                        <input type="text" name="name" id="edit_area_name" class="form-control text-uppercase" required style="border-radius: 8px;">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent rounded-3 px-4 fw-bold shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const updatePositionBaseUrl = "{{ url('/hr/positions/update') }}";
    const updateAreaBaseUrl = "{{ url('/hr/settings/learning-areas/update') }}";
    const TEACHING_VAL = '{{ \App\Enums\PositionCategory::Teaching->value }}';
    const NON_TEACHING_VAL = '{{ \App\Enums\PositionCategory::NonTeaching->value }}';

    // 1. Restore active tab from URL hash if present (safely guarded)
    try {
        const currentHash = window.location.hash;
        if (currentHash && currentHash.startsWith('#pane-') && currentHash.length > 6) {
            const targetTabButton = document.querySelector(`button[data-bs-target="${currentHash}"]`);
            if (targetTabButton && window.bootstrap && bootstrap.Tab) {
                const tabInstance = bootstrap.Tab.getOrCreateInstance(targetTabButton);
                tabInstance.show();
            }
        }
    } catch (err) {
        console.warn('Tab restore notice:', err);
    }

    // 2. Append active hash to pagination links
    function syncPaginationHash(hash) {
        if (!hash || !hash.startsWith('#pane-')) return;
        document.querySelectorAll('.pagination-centered a.page-link[href]').forEach(link => {
            const baseHref = link.href.split('#')[0];
            link.href = baseHref + hash;
        });
    }

    if (window.location.hash && window.location.hash.startsWith('#pane-')) {
        syncPaginationHash(window.location.hash);
    }

    // 3. Listen for tab switches and update URL hash + pagination links
    const tabButtons = document.querySelectorAll('#settingsTabs button[data-bs-toggle="tab"]');
    tabButtons.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function(e) {
            const target = e.target.getAttribute('data-bs-target');
            if (target && target.startsWith('#pane-')) {
                history.replaceState(null, null, window.location.pathname + window.location.search + target);
                syncPaginationHash(target);
            }
        });
    });

    // 4. Edit Position Modal handling (Element References)
    const editPositionForm = document.getElementById('editPositionForm');
    const editPositionNameInput = document.getElementById('edit_position_name');
    const editPositionCategorySelect = document.getElementById('edit_position_category');
    const editPositionSalaryGradeInput = document.getElementById('edit_salary_grade');
    const editPositionActiveTabInput = document.getElementById('edit_position_active_tab');

    function fillEditPositionModal(btn) {
        if (!btn) return;
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        const category = btn.getAttribute('data-category');
        const sg = btn.getAttribute('data-sg');

        if (editPositionForm && id) {
            editPositionForm.action = `${updatePositionBaseUrl}/${id}`;
        }
        if (editPositionNameInput) {
            editPositionNameInput.value = name || '';
        }
        if (editPositionCategorySelect && (category !== null && category !== undefined && category !== '')) {
            editPositionCategorySelect.value = String(category);
        }
        if (editPositionActiveTabInput) {
            const isNonTeaching = (editPositionCategorySelect && editPositionCategorySelect.value === NON_TEACHING_VAL) 
                || (String(category) === NON_TEACHING_VAL);
            editPositionActiveTabInput.value = isNonTeaching ? 'non-teaching' : 'teaching';
        }
        if (editPositionSalaryGradeInput) {
            editPositionSalaryGradeInput.value = (sg && sg !== 'null') ? sg : '';
        }
    }

    // Populate on button click (delegation)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.edit-position-btn');
        if (btn) {
            fillEditPositionModal(btn);
        }
    });

    // Also populate on Bootstrap modal show event
    const editPositionModalElement = document.getElementById('editPositionModal');
    if (editPositionModalElement) {
        editPositionModalElement.addEventListener('show.bs.modal', function(event) {
            if (event.relatedTarget) {
                fillEditPositionModal(event.relatedTarget);
            }
        });
    }

    if (editPositionCategorySelect) {
        editPositionCategorySelect.addEventListener('change', function() {
            if (editPositionActiveTabInput) {
                editPositionActiveTabInput.value = (this.value === NON_TEACHING_VAL) ? 'non-teaching' : 'teaching';
            }
        });
    }

    // 5. Edit Learning Area Modal handling
    const editAreaForm = document.getElementById('editLearningAreaForm');
    const editAreaNameInput = document.getElementById('edit_area_name');

    function fillEditAreaModal(btn) {
        if (!btn) return;
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');

        if (editAreaForm && id) {
            editAreaForm.action = `${updateAreaBaseUrl}/${id}`;
        }
        if (editAreaNameInput) {
            editAreaNameInput.value = name || '';
        }
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.edit-area-btn');
        if (btn) {
            fillEditAreaModal(btn);
        }
    });

    const editAreaModalElement = document.getElementById('editLearningAreaModal');
    if (editAreaModalElement) {
        editAreaModalElement.addEventListener('show.bs.modal', function(event) {
            if (event.relatedTarget) {
                fillEditAreaModal(event.relatedTarget);
            }
        });
    }
});
</script>
@endsection
