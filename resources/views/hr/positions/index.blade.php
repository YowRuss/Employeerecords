@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="text-header-blue fw-bold mb-4"><i class="bi bi-gear-fill me-2 text-header-blue"></i> Manage Positions</h4>

    @if(session('success'))
    <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @endif

    <style>
        .position-tabs .nav-link {
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
        .position-tabs .nav-link:hover {
            opacity: 1;
            border-color: rgba(253, 224, 71, 0.5);
        }
        .position-tabs .nav-link.active {
            background-color: var(--accent-yellow, #FDE047);
            color: #1A3E6F;
            opacity: 1;
            border-color: var(--accent-yellow, #FDE047);
            border-radius: 8px 8px 0 0;
        }
    </style>

    <div class="row">
        <!-- FORM TO ADD POSITION -->
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card shadow-sm border-0 border-top border-4 border-accent p-3">
                <h6 class="fw-bold mb-3" style="color: #1A3E6F;">Add New Position</h6>
                <form action="{{ route('hr.positions.store') }}" method="POST">
                    @csrf
                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="small fw-bold text-muted text-uppercase tracking-wider">Position Name</label>
                            <input type="text" name="position_name" class="form-control text-uppercase" placeholder="e.g. TEACHER III" required>
                        </div>
                        <div class="col-4">
                            <label class="small fw-bold text-muted text-uppercase tracking-wider text-nowrap">Salary Grade</label>
                            <input type="number" name="salary_grade" class="form-control" placeholder="SG (1-33)" min="1" max="33" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="small fw-bold text-muted text-uppercase tracking-wider">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="" disabled selected>Select Category</option>
                            @foreach(\App\Enums\PositionCategory::cases() as $category)
                            <option value="{{ $category->value }}">{{ $category->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn text-white w-100 fw-bold" style="background-color: #1A3E6F;">Save Position</button>
                </form>
            </div>
        </div>

        <!-- LIST OF POSITIONS -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
                    <!-- Tabbed Navigation -->
                    <ul class="nav nav-tabs position-tabs border-bottom-0" id="positionTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="teaching-tab" data-bs-toggle="tab" data-bs-target="#teaching" type="button" role="tab" aria-controls="teaching" aria-selected="true">
                                Teaching Positions <span class="badge bg-white text-dark ms-1 border">{{ count($teachingPositions) }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="non-teaching-tab" data-bs-toggle="tab" data-bs-target="#non-teaching" type="button" role="tab" aria-controls="non-teaching" aria-selected="false">
                                Non-Teaching Positions <span class="badge bg-white text-dark ms-1 border">{{ count($nonTeachingPositions) }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0 border-top border-light">
                    <div class="tab-content" id="positionTabsContent">
                        <!-- Teaching Tab -->
                        <div class="tab-pane fade show active" id="teaching" role="tabpanel" aria-labelledby="teaching-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 border-top-0">
                                    <thead class="bg-light" style="border-bottom: 2px solid #f1f5f9;">
                                        <tr>
                                            <th class="ps-4 text-muted small fw-bold text-uppercase py-3 border-0">ID</th>
                                            <th class="text-muted small fw-bold text-uppercase py-3 border-0">Position Name</th>
                                            <th class="text-muted small fw-bold text-uppercase py-3 border-0">Salary Grade</th>
                                            <th class="text-muted small fw-bold text-uppercase py-3 border-0">Created At</th>
                                            <th class="text-end pe-4 text-muted small fw-bold text-uppercase py-3 border-0">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody style="border-top: none;">
                                        @forelse($teachingPositions as $pos)
                                        <tr>
                                            <td class="ps-4 py-3 text-muted border-light">{{ $pos->id }}</td>
                                            <td class="fw-bold text-uppercase py-3 border-light">{{ $pos->position_name }}</td>
                                            <td class="py-3 border-light"><span class="badge bg-light text-dark border">{{ $pos->salary_grade ? 'SG ' . $pos->salary_grade : 'Not Set' }}</span></td>
                                            <td class="text-muted small py-3 border-light">{{ \Carbon\Carbon::parse($pos->created_at)->format('M d, Y') }}</td>
                                            <td class="text-end pe-4 py-3 border-light">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary p-1 shadow-sm rounded-circle" style="width: 32px; height: 32px;" title="Edit Position" data-bs-toggle="modal" data-bs-target="#editPositionModal{{ $pos->id }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('hr.positions.destroy', $pos->id) }}" method="POST" onsubmit="return confirm('Delete this position?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger p-1 shadow-sm rounded-circle" style="width: 32px; height: 32px;" title="Delete Position"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </div>

                                                <!-- Edit Position Modal -->
                                                <div class="modal fade text-start" id="editPositionModal{{ $pos->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content border-0 shadow">
                                                            <div class="modal-header bg-light">
                                                                <h5 class="modal-title fw-bold" style="color: #1A3E6F;"><i class="bi bi-pencil-square me-2"></i>Edit Position</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="{{ route('hr.positions.update', $pos->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="active_tab" value="teaching">
                                                                <div class="modal-body p-4">
                                                                    <div class="mb-3">
                                                                        <label class="small fw-bold text-muted text-uppercase tracking-wider">Position Name</label>
                                                                        <input type="text" name="position_name" class="form-control text-uppercase" value="{{ $pos->position_name }}" required>
                                                                    </div>
                                                                    <div class="row g-2 mb-3">
                                                                        <div class="col-8">
                                                                            <label class="small fw-bold text-muted text-uppercase tracking-wider">Category</label>
                                                                            <select name="category" class="form-select">
                                                                                @foreach(\App\Enums\PositionCategory::cases() as $category)
                                                                                <option value="{{ $category->value }}" {{ $pos->category === $category || $pos->category === $category->value ? 'selected' : '' }}>{{ $category->label() }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <label class="small fw-bold text-muted text-uppercase tracking-wider">Salary Grade</label>
                                                                            <input type="number" name="salary_grade" class="form-control" min="1" max="33" value="{{ $pos->salary_grade }}" placeholder="SG">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer bg-light border-top-0 py-3">
                                                                    <button type="button" class="btn btn-secondary px-4 fw-bold rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn text-white px-4 fw-bold rounded-pill" style="background-color: #1A3E6F;">Save Changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center py-4 text-muted border-light">No teaching positions found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Non-Teaching Tab -->
                        <div class="tab-pane fade" id="non-teaching" role="tabpanel" aria-labelledby="non-teaching-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 border-top-0">
                                    <thead class="bg-light" style="border-bottom: 2px solid #f1f5f9;">
                                        <tr>
                                            <th class="ps-4 text-muted small fw-bold text-uppercase py-3 border-0">ID</th>
                                            <th class="text-muted small fw-bold text-uppercase py-3 border-0">Position Name</th>
                                            <th class="text-muted small fw-bold text-uppercase py-3 border-0">Salary Grade</th>
                                            <th class="text-muted small fw-bold text-uppercase py-3 border-0">Created At</th>
                                            <th class="text-end pe-4 text-muted small fw-bold text-uppercase py-3 border-0">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody style="border-top: none;">
                                        @forelse($nonTeachingPositions as $pos)
                                        <tr>
                                            <td class="ps-4 py-3 text-muted border-light">{{ $pos->id }}</td>
                                            <td class="fw-bold text-uppercase py-3 border-light">{{ $pos->position_name }}</td>
                                            <td class="py-3 border-light"><span class="badge bg-light text-dark border">{{ $pos->salary_grade ? 'SG ' . $pos->salary_grade : 'Not Set' }}</span></td>
                                            <td class="text-muted small py-3 border-light">{{ \Carbon\Carbon::parse($pos->created_at)->format('M d, Y') }}</td>
                                            <td class="text-end pe-4 py-3 border-light">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary p-1 shadow-sm rounded-circle" style="width: 32px; height: 32px;" title="Edit Position" data-bs-toggle="modal" data-bs-target="#editPositionModal{{ $pos->id }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('hr.positions.destroy', $pos->id) }}" method="POST" onsubmit="return confirm('Delete this position?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger p-1 shadow-sm rounded-circle" style="width: 32px; height: 32px;" title="Delete Position"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                </div>

                                                <!-- Edit Position Modal -->
                                                <div class="modal fade text-start" id="editPositionModal{{ $pos->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content border-0 shadow">
                                                            <div class="modal-header bg-light">
                                                                <h5 class="modal-title fw-bold" style="color: #1A3E6F;"><i class="bi bi-pencil-square me-2"></i>Edit Position</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="{{ route('hr.positions.update', $pos->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="active_tab" value="non-teaching">
                                                                <div class="modal-body p-4">
                                                                    <div class="mb-3">
                                                                        <label class="small fw-bold text-muted text-uppercase tracking-wider">Position Name</label>
                                                                        <input type="text" name="position_name" class="form-control text-uppercase" value="{{ $pos->position_name }}" required>
                                                                    </div>
                                                                    <div class="row g-2 mb-3">
                                                                        <div class="col-8">
                                                                            <label class="small fw-bold text-muted text-uppercase tracking-wider">Category</label>
                                                                            <select name="category" class="form-select">
                                                                                @foreach(\App\Enums\PositionCategory::cases() as $category)
                                                                                <option value="{{ $category->value }}" {{ $pos->category === $category || $pos->category === $category->value ? 'selected' : '' }}>{{ $category->label() }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-4">
                                                                            <label class="small fw-bold text-muted text-uppercase tracking-wider">Salary Grade</label>
                                                                            <input type="number" name="salary_grade" class="form-control" min="1" max="33" value="{{ $pos->salary_grade }}" placeholder="SG">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer bg-light border-top-0 py-3">
                                                                    <button type="button" class="btn btn-secondary px-4 fw-bold rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn text-white px-4 fw-bold rounded-pill" style="background-color: #1A3E6F;">Save Changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center py-4 text-muted border-light">No non-teaching positions found.</td></tr>
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
@endsection