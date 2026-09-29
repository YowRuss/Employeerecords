@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-header-blue fw-bold m-0"><i class="bi bi-gear-fill me-2 text-header-blue"></i> Announcement Settings</h4>
            <p class="text-muted small m-0">Manage announcement categories and types.</p>
        </div>
        <a href="{{ route('announcements.index') }}" class="btn btn-light border shadow-sm btn-sm fw-bold text-muted px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Announcements
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success shadow-sm border-0 rounded-3">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger shadow-sm border-0 rounded-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
    </div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger shadow-sm border-0 rounded-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row g-4">
        <!-- Add Category Form -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h6 class="fw-bold text-dark m-0">Add New Category</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('announcements.settings.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Health Update">
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Badge Color</label>
                            <select name="badge_color" class="form-select">
                                <option value="primary" class="text-primary fw-bold">Blue (Primary)</option>
                                <option value="secondary" class="text-secondary fw-bold" selected>Gray (Secondary)</option>
                                <option value="success" class="text-success fw-bold">Green (Success)</option>
                                <option value="warning" class="text-warning fw-bold">Yellow (Warning)</option>
                                <option value="danger" class="text-danger fw-bold">Red (Danger)</option>
                                <option value="info" class="text-info fw-bold">Light Blue (Info)</option>
                                <option value="dark" class="text-dark fw-bold">Black (Dark)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-accent w-100 fw-bold shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Add Category
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Categories List -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark m-0">Announcement Categories</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th class="ps-4">Category Name</th>
                                    <th>Badge Preview</th>
                                    <th>Usage Count</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($announcementTypes as $type)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $type->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $type->badge_color }} bg-opacity-10 text-{{ $type->badge_color }} border">{{ $type->name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $type->announcements_count }} announcements</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <!-- Edit Button (Triggers Modal) -->
                                        <button type="button" class="btn btn-sm btn-light border text-accent fw-bold me-1" data-bs-toggle="modal" data-bs-target="#editModal{{ $type->id }}" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <!-- Delete Form -->
                                        <form action="{{ route('announcements.settings.destroy', $type->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete" {{ $type->announcements_count > 0 ? 'disabled' : '' }}>
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>

                                        <!-- Edit Modal -->
                                        <div class="modal fade text-start" id="editModal{{ $type->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $type->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel{{ $type->id }}">Edit Category</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('announcements.settings.update', $type->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold text-muted">Category Name</label>
                                                                <input type="text" name="name" class="form-control" value="{{ $type->name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold text-muted">Badge Color</label>
                                                                <select name="badge_color" class="form-select">
                                                                    <option value="primary" {{ $type->badge_color == 'primary' ? 'selected' : '' }}>Blue (Primary)</option>
                                                                    <option value="secondary" {{ $type->badge_color == 'secondary' ? 'selected' : '' }}>Gray (Secondary)</option>
                                                                    <option value="success" {{ $type->badge_color == 'success' ? 'selected' : '' }}>Green (Success)</option>
                                                                    <option value="warning" {{ $type->badge_color == 'warning' ? 'selected' : '' }}>Yellow (Warning)</option>
                                                                    <option value="danger" {{ $type->badge_color == 'danger' ? 'selected' : '' }}>Red (Danger)</option>
                                                                    <option value="info" {{ $type->badge_color == 'info' ? 'selected' : '' }}>Light Blue (Info)</option>
                                                                    <option value="dark" {{ $type->badge_color == 'dark' ? 'selected' : '' }}>Black (Dark)</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0">
                                                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-accent fw-bold shadow-sm">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i class="bi bi-tags fs-2 d-block mb-2"></i>
                                        No announcement categories found.
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
