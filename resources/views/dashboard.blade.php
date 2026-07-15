@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h3 class="text-accent fw-bold">Dashboard</h3>
        <p class="text-muted">Role:
            @if(session('role_id') == 1) Employee
            @elseif(session('role_id') == 2) HR Officer
            @elseif(session('role_id') == 3) Administrator
            @elseif(session('role_id') == 4) Principal
            @endif
        </p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- EMPLOYEE DASHBOARD -->
@if(session('role_id') == 1)
<div class="row g-4">
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h5 class="text-accent fw-bold">Personal Data Sheet (PDS)</h5>
            <p class="text-muted small">Update your personal information, family background, and educational attainment.</p>
            <a href="{{ route('pds.edit') }}" class="btn btn-accent mt-auto w-100">Update PDS</a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h5 class="text-accent fw-bold">Statement of Assets, Liabilities, and Net Worth (SALN)</h5>
            <p class="text-muted small">Submit your annual SALN declaration required by the government.</p>
            <a href="{{ route('saln.index') }}" class="btn btn-accent mt-auto w-100">Submit SALN</a>
        </div>
    </div>
</div>
@endif

<!-- HR DASHBOARD -->
@if(session('role_id') == 2)
<div class="row g-4">
    <div class="col-12">
        <div class="card p-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-accent fw-bold mb-0">Manage Positions</h5>
                <!-- Form to add new position -->
                <form action="{{ route('hr.positions.store') }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="text" name="position_name" class="form-control form-control-sm text-uppercase" placeholder="New Position Name" required>
                    <button type="submit" class="btn btn-accent btn-sm fw-bold">Add Position</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle bg-white">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Position Name</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Make sure your DashboardController passes $positions to the view --}}
                        @forelse($positions as $pos)
                        <tr>
                            <td>{{ $pos->id }}</td>
                            <td class="fw-bold text-uppercase">{{ $pos->position_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($pos->created_at)->format('M d, Y') }}</td>
                            <td>
                                <form action="{{ route('hr.positions.destroy', $pos->id) }}" method="POST" onsubmit="return confirm('Delete this position?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No positions created yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

<!-- PRINCIPAL DASHBOARD -->
@if(session('role_id') == 4)
<div class="alert alert-info bg-white border-info text-dark shadow-sm">
    <h5 class="text-accent fw-bold">Principal's Overview</h5>
    <p class="mb-0">Welcome to the administrative portal. System modules for leave monitoring approvals and institutional reports will appear here.</p>
</div>
@endif

<!-- ADMIN DASHBOARD -->
@if(session('role_id') == 3)
<div class="row g-4">

    <div class="col-md-4">
        <div class="card p-4 h-100">
            <h5 class="text-accent fw-bold mb-4">Create New Position</h5>

            <form action="{{ route('positions.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Position Title</label>
                    <input type="text" name="position_name" class="form-control" placeholder="e.g., Master Teacher I" required>
                </div>
                <button type="submit" class="btn btn-accent w-100">Save Position</button>
            </form>

            <hr class="my-4">
            <h6 class="text-accent fw-bold mb-3">Current Positions</h6>
            <ul class="list-group list-group-flush small">
                @if(isset($positions))
                @foreach($positions as $pos)
                <li class="list-group-item px-0 border-bottom">{{ $pos->position_name }}</li>
                @endforeach
                @endif
            </ul>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card p-4 h-100">
            <h5 class="text-accent fw-bold mb-4">Registered Employees</h5>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle bg-white">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($employees) && $employees->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center text-muted">No employees found.</td>
                        </tr>
                        @elseif(isset($employees))
                        @foreach($employees as $emp)
                        <tr>
                            <td>{{ $emp->id }}</td>
                            <td class="fw-bold">{{ $emp->full_name }}</td>
                            <td>{{ $emp->username }}</td>
                            <td>{{ \Carbon\Carbon::parse($emp->created_at)->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

@endsection