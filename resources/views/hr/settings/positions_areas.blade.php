@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h3 class="text-accent fw-bold"><i class="bi bi-gear-fill me-2"></i> Positions & Areas</h3>
        <p class="text-muted">Manage the system's position titles and learning areas.</p>
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

<div class="row g-4">
    <div class="col-12">
        <div class="card p-4 shadow-sm border-0 rounded-4">
            
            <style>
                .settings-tabs .nav-link {
                    border: none;
                    color: #1A3E6F;
                    font-weight: 600;
                    background: transparent;
                    border-radius: 0;
                    padding: 0.75rem 1.25rem;
                    opacity: 0.7;
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
                    opacity: 1;
                    border-color: var(--accent-yellow, #FDE047);
                    border-radius: 8px 8px 0 0;
                }
                .action-col { width: 100px; }
            </style>

            <ul class="nav nav-tabs settings-tabs border-bottom-0 flex-nowrap overflow-auto" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-teaching" data-bs-toggle="tab" data-bs-target="#pane-teaching" type="button" role="tab">
                        Teaching <span class="badge bg-white text-dark ms-1 border">{{ count($teachingPositions) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-non-teaching" data-bs-toggle="tab" data-bs-target="#pane-non-teaching" type="button" role="tab">
                        Non-Teaching <span class="badge bg-white text-dark ms-1 border">{{ count($nonTeachingPositions) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-learning-areas" data-bs-toggle="tab" data-bs-target="#pane-learning-areas" type="button" role="tab">
                        Learning Areas <span class="badge bg-white text-dark ms-1 border">{{ count($learningAreas) }}</span>
                    </button>
                </li>
            </ul>
            
            <div class="tab-content border-top border-light pt-4" id="settingsTabsContent">
                
                <!-- Teaching Tab -->
                <div class="tab-pane fade show active" id="pane-teaching" role="tabpanel">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-3">
                        <h6 class="text-accent fw-bold mb-0">Teaching Positions</h6>
                        <form action="{{ route('hr.positions.store') }}" method="POST" class="d-flex w-100 w-sm-auto gap-2">
                            @csrf
                            <input type="hidden" name="category" value="Teaching">
                            <input type="text" name="position_name" class="form-control form-control-sm text-uppercase" placeholder="New Teaching Position" required>
                            <button type="submit" class="btn btn-accent btn-sm fw-bold px-3 text-nowrap"><i class="bi bi-plus-lg me-1"></i> Add</button>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle bg-white m-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Position Name</th>
                                    <th class="text-center action-col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teachingPositions as $pos)
                                <tr>
                                    <td class="fw-bold text-uppercase ps-3">{{ $pos->position_name }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center">
                                            <button class="btn btn-sm btn-outline-primary p-1 me-1" title="Edit"><i class="bi bi-pencil"></i></button>
                                            <form action="{{ route('hr.positions.destroy', $pos->id) }}" method="POST" onsubmit="return confirm('Delete this position?');" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-4 d-block mb-2 text-opacity-50"></i>
                                        No teaching positions created yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Non-Teaching Tab -->
                <div class="tab-pane fade" id="pane-non-teaching" role="tabpanel">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-3">
                        <h6 class="text-accent fw-bold mb-0">Non-Teaching Positions</h6>
                        <form action="{{ route('hr.positions.store') }}" method="POST" class="d-flex w-100 w-sm-auto gap-2">
                            @csrf
                            <input type="hidden" name="category" value="Non-Teaching">
                            <input type="text" name="position_name" class="form-control form-control-sm text-uppercase" placeholder="New Non-Teaching Position" required>
                            <button type="submit" class="btn btn-accent btn-sm fw-bold px-3 text-nowrap"><i class="bi bi-plus-lg me-1"></i> Add</button>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle bg-white m-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Position Name</th>
                                    <th class="text-center action-col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nonTeachingPositions as $pos)
                                <tr>
                                    <td class="fw-bold text-uppercase ps-3">{{ $pos->position_name }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center">
                                            <button class="btn btn-sm btn-outline-primary p-1 me-1" title="Edit"><i class="bi bi-pencil"></i></button>
                                            <form action="{{ route('hr.positions.destroy', $pos->id) }}" method="POST" onsubmit="return confirm('Delete this position?');" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-4 d-block mb-2 text-opacity-50"></i>
                                        No non-teaching positions created yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Learning Areas Tab -->
                <div class="tab-pane fade" id="pane-learning-areas" role="tabpanel">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-3">
                        <h6 class="text-accent fw-bold mb-0">Learning Areas</h6>
                        <form action="{{ route('hr.learning_areas.store') }}" method="POST" class="d-flex w-100 w-sm-auto gap-2">
                            @csrf
                            <input type="text" name="name" class="form-control form-control-sm text-uppercase" placeholder="New Learning Area" required>
                            <button type="submit" class="btn btn-accent btn-sm fw-bold px-3 text-nowrap"><i class="bi bi-plus-lg me-1"></i> Add</button>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle bg-white m-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Learning Area Name</th>
                                    <th class="text-center action-col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($learningAreas as $area)
                                <tr>
                                    <td class="fw-bold text-uppercase ps-3">{{ $area->name }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center">
                                            <button class="btn btn-sm btn-outline-primary p-1 me-1" title="Edit"><i class="bi bi-pencil"></i></button>
                                            <form action="{{ route('hr.learning_areas.destroy', $area->id) }}" method="POST" onsubmit="return confirm('Delete this learning area?');" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-4 d-block mb-2 text-opacity-50"></i>
                                        No learning areas created yet.
                                    </td>
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
@endsection
