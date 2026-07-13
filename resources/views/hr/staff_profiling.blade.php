@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-accent fw-bold m-0"><i class="bi bi-people-fill me-2"></i> Staff Profiling</h4>
        <a href="{{ route('employees.create') }}" class="btn btn-accent shadow-sm fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Add New Employee
        </a>
    </div>

    <div class="card shadow-sm border-0">
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
                                <a href="{{ route('hr.service_record.index', $emp->id) }}" class="btn btn-sm btn-outline-info" title="View Service Record">SR</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-4 text-muted">No staff found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection