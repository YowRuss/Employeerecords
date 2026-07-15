@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="text-accent fw-bold m-0"><i class="bi bi-people-fill me-2"></i> Staff Profiling</h4>
        <a href="{{ route('employees.create') }}" class="btn btn-accent shadow-sm fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Add New Employee
        </a>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <form method="GET" action="{{ route('hr.staff_profiling') }}" class="d-flex gap-2">
                <input
                    type="text"
                    name="search"
                    value="{{ $search ?? '' }}"
                    class="form-control form-control-sm"
                    placeholder="Search by name, username, or position..."
                >
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-search"></i>
                </button>
                @if(!empty($search))
                <a href="{{ route('hr.staff_profiling') }}" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-x-circle"></i>
                </a>
                @endif
            </form>
        </div>

        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Full Name</th>
                        <th>Position</th>
                        <th>Username</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                    <tr>
                        <td class="ps-4 fw-bold text-uppercase">{{ $emp->last_name }}, {{ $emp->first_name }} {{ $emp->middle_name }}</td>
                        <td>{{ $emp->position_name ?? 'Not Assigned' }}</td>
                        <td>{{ $emp->username }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('hr.view_pds', $emp->id) }}" class="btn btn-sm btn-outline-primary" title="View PDS">PDS</a>
                                <a href="{{ route('hr.view_saln', $emp->id) }}" class="btn btn-sm btn-outline-success" title="View SALN">SALN</a>
                                <a href="{{ route('hr.view_profile', $emp->id) }}" class="btn btn-sm btn-outline-info" title="View Profile">View Profile</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            @if(!empty($search))
                                No staff matched "{{ $search }}".
                            @else
                                No staff found.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
        <div class="card-footer bg-white">
            {{ $employees->links() }}
        </div>
        @endif
    </div>
</div>
@endsection