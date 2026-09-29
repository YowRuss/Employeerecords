@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-6 mb-3 mb-md-0">
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('hr.payroll.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1">
                    <i class="bi bi-arrow-left me-1"></i> Back to Payroll
                </a>
            </div>
            <h4 class="text-header-blue fw-bold mb-0">
                <i class="bi bi-sliders me-2 text-header-blue"></i> Deduction Categories & Types
            </h4>
            <p class="text-muted small mb-0">Manage loan categories and deduction types used across the payroll system.</p>
        </div>
        <div class="col-12 col-md-6 text-md-end d-flex flex-wrap justify-content-md-end gap-2">
            <button type="button" class="btn btn-accent fw-bold shadow-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-folder-plus me-1"></i> Add Category
            </button>
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

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-center mb-2">
            <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
            <strong>Please fix the following errors:</strong>
        </div>
        <ul class="mb-0 small ps-4">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4" style="border-left-color: #1A3E6F !important;">
                <span class="text-muted small fw-bold text-uppercase">Categories</span>
                <h4 class="fw-bold mb-0 mt-1" style="color: #1A3E6F;">{{ $categories->count() }}</h4>
                <span class="small text-muted">Total groups</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                <span class="text-muted small fw-bold text-uppercase">Active Categories</span>
                <h4 class="fw-bold mb-0 mt-1 text-success">{{ $categories->where('is_active', true)->count() }}</h4>
                <span class="small text-muted">Currently enabled</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4 border-info">
                <span class="text-muted small fw-bold text-uppercase">Deduction Types</span>
                <h4 class="fw-bold mb-0 mt-1 text-info">{{ $categories->sum(fn($c) => $c->types->count()) }}</h4>
                <span class="small text-muted">Total loan/deduction types</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm border-0 rounded-3 bg-white p-3 h-100 border-start border-4 border-warning">
                <span class="text-muted small fw-bold text-uppercase">With Excel Mapping</span>
                <h4 class="fw-bold mb-0 mt-1 text-warning">{{ $categories->sum(fn($c) => $c->types->whereNotNull('excel_column')->count()) }}</h4>
                <span class="small text-muted">Mapped to spreadsheet columns</span>
            </div>
        </div>
    </div>

    {{-- Accordion: Categories & Types --}}
    <div class="card shadow-sm border-0 rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0" style="color: #1A3E6F;">
                <i class="bi bi-list-nested me-2"></i> Categories & Deduction Types
            </h6>
            <p class="text-muted small mb-0">Expand each category to view and manage its deduction types.</p>
        </div>
        <div class="card-body p-3">
            @forelse($categories as $category)
            <div class="accordion mb-3" id="categoryAccordion{{ $category->id }}">
                <div class="accordion-item border rounded-3 overflow-hidden" style="border-color: {{ $category->is_active ? '#e2e8f0' : '#fecaca' }} !important;">
                    <h2 class="accordion-header" id="heading{{ $category->id }}">
                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} py-3 px-4 fw-bold" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapse{{ $category->id }}"
                                aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse{{ $category->id }}"
                                style="color: #1A3E6F; background-color: {{ $category->is_active ? '#f8fafc' : '#fff5f5' }}; font-size: 0.95rem;">
                            <span class="d-flex align-items-center gap-2 flex-grow-1">
                                <i class="bi bi-folder2-open me-1"></i>
                                {{ $category->name }}
                                <span class="badge rounded-pill {{ $category->is_active ? 'bg-success' : 'bg-danger' }} ms-2" style="font-size: 0.7rem;">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <span class="badge bg-light text-dark border ms-1" style="font-size: 0.7rem;">
                                    {{ $category->types->count() }} {{ Str::plural('type', $category->types->count()) }}
                                </span>
                                <span class="badge bg-light text-muted border ms-1" style="font-size: 0.65rem;">
                                    Sort: {{ $category->sort_order }}
                                </span>
                            </span>
                        </button>
                    </h2>
                    <div id="collapse{{ $category->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                         aria-labelledby="heading{{ $category->id }}">
                        <div class="accordion-body p-0">
                            {{-- Category Action Bar --}}
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-4 py-2 border-bottom" style="background-color: #f8fafc;">
                                <span class="small text-muted">
                                    <strong>Slug:</strong> <code>{{ $category->slug }}</code>
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-accent add-type-btn shadow-sm" data-bs-toggle="modal" data-bs-target="#addDeductionTypeModal" data-category-id="{{ $category->id }}" data-category-name="{{ $category->name }}"><i class="bi bi-plus-lg"></i> Add Type</button>
                                    <button type="button" class="btn btn-sm btn-outline-info px-2 py-1"
                                            data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}"
                                            title="Edit Category">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form action="{{ route('hr.settings.deductions.category.toggle', $category->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $category->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} px-2 py-1"
                                                title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi {{ $category->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('hr.settings.deductions.category.destroy', $category->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete category \'{{ $category->name }}\' and ALL its deduction types?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="Delete Category">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Types Table --}}
                            @if($category->types->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr style="font-size: 0.78rem; letter-spacing: 0.5px;">
                                            <th class="py-2 ps-4 text-uppercase text-secondary border-bottom" style="width: 40px;">#</th>
                                            <th class="py-2 text-uppercase text-secondary border-bottom">Name</th>
                                            <th class="py-2 text-uppercase text-secondary border-bottom" style="width: 140px;">Code</th>
                                            <th class="py-2 text-uppercase text-secondary border-bottom" style="width: 120px;">Excel Column</th>
                                            <th class="py-2 text-center text-uppercase text-secondary border-bottom" style="width: 90px;">Status</th>
                                            <th class="py-2 pe-4 text-center text-uppercase text-secondary border-bottom" style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($category->types as $typeIndex => $type)
                                        <tr>
                                            <td class="ps-4 text-muted small">{{ $typeIndex + 1 }}</td>
                                            <td class="fw-semibold" style="color: #1A3E6F;">{{ $type->name }}</td>
                                            <td><code class="text-muted">{{ $type->code }}</code></td>
                                            <td>
                                                @if($type->excel_column)
                                                    <span class="badge bg-light text-dark border font-monospace">{{ $type->excel_column }}</span>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('hr.settings.deductions.type.toggle', $type->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent" title="Toggle Active/Inactive">
                                                        @if($type->is_active)
                                                            <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1" style="cursor: pointer;">
                                                                <i class="bi bi-check-circle-fill me-1"></i>Active
                                                            </span>
                                                        @else
                                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1" style="cursor: pointer;">
                                                                <i class="bi bi-x-circle-fill me-1"></i>Inactive
                                                            </span>
                                                        @endif
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="text-center pe-4">
                                                <button type="button" class="btn btn-sm btn-outline-info px-2 py-1"
                                                        data-bs-toggle="modal" data-bs-target="#editTypeModal{{ $type->id }}"
                                                        title="Edit Type">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-type-btn" data-bs-toggle="modal" data-bs-target="#deleteDeductionTypeModal" data-id="{{ $type->id }}" data-name="{{ $type->name }}"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-4">
                                <i class="bi bi-inbox text-muted display-6 d-block mb-2 opacity-50"></i>
                                <p class="text-muted small mb-2">No deduction types in this category yet.</p>
                                <button type="button" class="btn btn-sm btn-accent add-type-btn shadow-sm" data-bs-toggle="modal" data-bs-target="#addDeductionTypeModal" data-category-id="{{ $category->id }}" data-category-name="{{ $category->name }}"><i class="bi bi-plus-lg"></i> Add Type</button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Edit Category Modal --}}
            <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <form action="{{ route('hr.settings.deductions.category.update', $category->id) }}" method="POST">
                            @csrf
                            <div class="modal-header border-bottom py-3" style="background-color: #f8fafc;">
                                <h6 class="modal-title fw-bold" style="color: #1A3E6F;">
                                    <i class="bi bi-pencil-square me-2"></i>Edit Category
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label for="editCatName{{ $category->id }}" class="form-label small fw-semibold text-secondary">Category Name</label>
                                    <input type="text" class="form-control" id="editCatName{{ $category->id }}" name="name" value="{{ $category->name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editCatSort{{ $category->id }}" class="form-label small fw-semibold text-secondary">Sort Order</label>
                                    <input type="number" class="form-control" id="editCatSort{{ $category->id }}" name="sort_order" value="{{ $category->sort_order }}" min="0">
                                </div>
                            </div>
                            <div class="modal-footer bg-light py-2 px-4 border-top">
                                <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-accent px-4 shadow-sm">
                                    <i class="bi bi-check2-circle me-1"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Edit Type Modals --}}
            @foreach($category->types as $type)
            <div class="modal fade" id="editTypeModal{{ $type->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <form action="{{ route('hr.settings.deductions.type.update', $type->id) }}" method="POST">
                            @csrf
                            <div class="modal-header border-bottom py-3" style="background-color: #f8fafc;">
                                <h6 class="modal-title fw-bold" style="color: #1A3E6F;">
                                    <i class="bi bi-pencil-square me-2"></i>Edit Deduction Type
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label for="editTypeName{{ $type->id }}" class="form-label small fw-semibold text-secondary">Name</label>
                                    <input type="text" class="form-control" id="editTypeName{{ $type->id }}" name="name" value="{{ $type->name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editTypeCode{{ $type->id }}" class="form-label small fw-semibold text-secondary">Code (snake_case)</label>
                                    <input type="text" class="form-control font-monospace" id="editTypeCode{{ $type->id }}" name="code" value="{{ $type->code }}" required>
                                    <div class="form-text">Unique identifier used in payroll data (e.g., <code>gsis_mpl</code>).</div>
                                </div>
                                <div class="mb-3">
                                    <label for="editTypeExcel{{ $type->id }}" class="form-label small fw-semibold text-secondary">Excel Column</label>
                                    <input type="text" class="form-control font-monospace" id="editTypeExcel{{ $type->id }}" name="excel_column" value="{{ $type->excel_column }}" placeholder="e.g. S, AD, AK">
                                    <div class="form-text">Spreadsheet column for the General Payroll export.</div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light py-2 px-4 border-top">
                                <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-accent px-4 shadow-sm">
                                    <i class="bi bi-check2-circle me-1"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach

            @empty
            <div class="text-center py-5">
                <i class="bi bi-folder-x text-muted display-4 d-block mb-3 opacity-50"></i>
                <h6 class="fw-bold text-secondary mb-1">No Deduction Categories</h6>
                <p class="text-muted small mb-3">Get started by creating your first deduction category.</p>
                <button type="button" class="btn btn-sm btn-accent shadow-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-folder-plus me-1"></i> Add Category
                </button>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ============================= --}}
{{-- Add Category Modal --}}
{{-- ============================= --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('hr.settings.deductions.category.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3" style="background-color: #f8fafc;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: rgba(26, 62, 111, 0.1); color: #1A3E6F;">
                            <i class="bi bi-folder-plus fs-5"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0" id="addCategoryModalLabel" style="color: #1A3E6F;">Add Deduction Category</h6>
                            <div class="text-muted small">Create a new grouping for deduction types.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="newCatName" class="form-label small fw-semibold text-secondary">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="newCatName" name="name" placeholder="e.g. GSIS Loans" required>
                    </div>
                    <div class="mb-3">
                        <label for="newCatSort" class="form-label small fw-semibold text-secondary">Sort Order</label>
                        <input type="number" class="form-control" id="newCatSort" name="sort_order" placeholder="Auto-assigned if blank" min="0">
                        <div class="form-text">Lower numbers appear first in the payroll modal.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-accent px-4 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================= --}}
{{-- Add Deduction Type Modal --}}
{{-- ============================= --}}
<div class="modal fade" id="addDeductionTypeModal" tabindex="-1" aria-labelledby="addDeductionTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('hr.settings.deductions.type.store') }}" method="POST">
                @csrf
                <input type="hidden" name="category_id" id="modal_category_id">

                <div class="modal-header border-bottom py-3" style="background-color: #f8fafc;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: rgba(26, 62, 111, 0.1); color: #1A3E6F;">
                            <i class="bi bi-plus-circle fs-5"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0" id="addDeductionTypeModalLabel" style="color: #1A3E6F;">Add Deduction Type</h6>
                            <div class="text-muted small">Define a new loan or deduction under this category.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold">Adding under Category</label>
                        <input type="text" id="modal_category_name" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="deduction_name" class="form-label small fw-semibold text-secondary">Name <span class="text-danger">*</span></label>
                        <input type="text" id="deduction_name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="deduction_code" class="form-label small fw-semibold text-secondary">Code <span class="text-danger">*</span></label>
                        <input type="text" id="deduction_code" name="code" class="form-control bg-light" readonly>
                        <div class="form-text">System-generated unique identifier used in payroll JSON data.</div>
                    </div>
                    <div class="mb-3">
                        <label for="newTypeExcel" class="form-label small fw-semibold text-secondary">Excel Column</label>
                        <input type="text" class="form-control font-monospace" id="newTypeExcel" name="excel_column" placeholder="e.g. S, AD, AK">
                        <div class="form-text">Spreadsheet column letter(s) for General Payroll export. Leave blank if not mapped.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-accent px-4 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Create Deduction Type
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================= --}}
{{-- Delete Deduction Type Modal --}}
{{-- ============================= --}}
<div class="modal fade" id="deleteDeductionTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3" style="background-color: #fff5f5;">
                <h6 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Delete Deduction Type
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="bi bi-trash text-danger display-4 d-block mb-3 opacity-75"></i>
                <p class="mb-0">Are you sure you want to delete <strong id="delete_type_name"></strong>? This action cannot be undone.</p>
            </div>
            <div class="modal-footer bg-light py-2 px-4 border-top">
                <form id="deleteTypeForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger px-4 shadow-sm">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // 1. Bootstrap native event: Fires exactly when the modal is about to open
    var addModal = document.getElementById('addDeductionTypeModal');
    if (addModal) {
        addModal.addEventListener('show.bs.modal', function (event) {
            // Get the button that triggered the modal
            var button = event.relatedTarget; 
            
            // Extract data attributes from that specific button
            var categoryId = button.getAttribute('data-category-id');
            var categoryName = button.getAttribute('data-category-name');
            
            // Inject the values into the modal's inputs
            document.getElementById('modal_category_id').value = categoryId;
            document.getElementById('modal_category_name').value = categoryName;
        });

        // Clear inputs when modal closes
        addModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('modal_category_id').value = '';
            document.getElementById('modal_category_name').value = '';
            document.getElementById('deduction_name').value = '';
            document.getElementById('deduction_code').value = '';
        });
    }

    // 2. Real-time auto-slug formatting for the Code field
    var nameInput = document.getElementById('deduction_name');
    var codeInput = document.getElementById('deduction_code');
    
    if (nameInput && codeInput) {
        nameInput.addEventListener('input', function() {
            var slug = this.value.toLowerCase()
                .replace(/[^a-z0-9]+/g, '_') // Replace spaces/special chars with underscores
                .replace(/^_+|_+$/g, '');    // Trim leading/trailing underscores
            
            codeInput.value = slug;
        });
    }

    // 3. Delete Type Modal Logic
    var deleteModal = document.getElementById('deleteDeductionTypeModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var typeId = button.getAttribute('data-id');
            var typeName = button.getAttribute('data-name');
            
            document.getElementById('delete_type_name').textContent = typeName;
            document.getElementById('deleteTypeForm').setAttribute('action', '/hr/settings/deductions/types/' + typeId);
        });
    }
});
</script>
@endsection
