@extends('layouts.app')

@section('content')
<style>
    @media (min-width: 768px) {
        .search-container {
            max-width: 350px;
        }
    }
</style>

<div class="container-fluid py-2">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
        <div>
            <h4 class="text-accent fw-bold m-0"><i class="bi bi-people-fill me-2"></i> Staff Profiling</h4>
            <p class="text-muted small mt-1 mb-0">Manage and oversee all employee profiles and records.</p>
        </div>
        <a href="{{ route('employees.create') }}" class="btn btn-accent shadow-sm fw-bold rounded-pill px-4">
            <i class="bi bi-person-plus-fill me-1"></i> Add New Employee
        </a>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom border-light d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
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

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0 border-top-0" style="font-size: 0.95rem;">
                <thead class="bg-light" style="border-bottom: 2px solid #f1f5f9;">
                    <tr>
                        <th class="ps-4 text-muted small fw-bold text-uppercase py-3 border-0">Employee Details</th>
                        <th class="text-muted small fw-bold text-uppercase py-3 border-0 d-none d-md-table-cell">Position / Role</th>
                        <th class="text-muted small fw-bold text-uppercase py-3 border-0 d-none d-lg-table-cell">Username</th>
                        <th class="text-muted small fw-bold text-uppercase py-3 border-0 text-end pe-4">Quick Actions</th>
                    </tr>
                </thead>
                <tbody style="border-top: none;">
                    @forelse($employees as $emp)
                    <tr class="transition-all" style="cursor: pointer;" onclick="window.location='{{ route('hr.view_profile', $emp->id) }}';">
                        <td class="ps-4 py-3 border-light">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-accent text-dark d-flex align-items-center justify-content-center fw-bold me-3 shadow-sm flex-shrink-0" style="width: 45px; height: 45px; font-size: 1.1rem; opacity: 0.9;">
                                    {{ strtoupper(substr($emp->first_name, 0, 1)) }}{{ strtoupper(substr($emp->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark text-uppercase mb-1 text-break" style="font-size: 0.95rem; letter-spacing: 0.2px;">
                                        {{ $emp->last_name }}, {{ $emp->first_name }} {{ $emp->middle_name }}
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-upc-scan me-1"></i> ID: {{ str_pad($emp->id, 4, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <!-- Mobile only details -->
                                    <div class="d-md-none mt-2">
                                        <span class="badge bg-light text-dark border shadow-sm px-2 py-1 mb-1 text-uppercase" style="font-size: 0.75rem;">
                                            <i class="bi bi-briefcase-fill me-1 text-muted"></i> {{ $emp->position_name ?? 'Not Assigned' }}
                                        </span><br>
                                        <span class="badge bg-light text-secondary border px-2 py-1 fw-medium" style="font-size: 0.75rem;">
                                            <i class="bi bi-person-badge me-1"></i> {{ $emp->username }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 border-light d-none d-md-table-cell">
                            <span class="badge bg-light text-dark border shadow-sm px-3 py-2 text-uppercase">
                                <i class="bi bi-briefcase-fill me-1 text-muted"></i> {{ $emp->position_name ?? 'Not Assigned' }}
                            </span>
                        </td>
                        <td class="text-muted py-3 border-light d-none d-lg-table-cell">
                            <span class="badge bg-light text-secondary border px-2 py-1 fw-medium" style="font-size: 0.85rem;">
                                <i class="bi bi-person-badge text-secondary me-1"></i> {{ $emp->username }}
                            </span>
                        </td>
                        <td class="text-end pe-4 py-3 border-light" onclick="event.stopPropagation();">
                            <div class="btn-group shadow-sm rounded-pill bg-white">
                                <a href="{{ route('hr.view_profile', $emp->id) }}" class="btn btn-sm btn-light border-end" data-bs-toggle="tooltip" title="View Profile" style="padding: 0.4rem 0.8rem;">
                                    <i class="bi bi-person-lines-fill text-info"></i>
                                </a>
                                <a href="{{ route('hr.view_pds', $emp->id) }}" class="btn btn-sm btn-light border-end" data-bs-toggle="tooltip" title="View PDS" style="padding: 0.4rem 0.8rem;">
                                    <i class="bi bi-file-earmark-person-fill text-primary"></i>
                                </a>
                                <a href="{{ route('hr.view_saln', $emp->id) }}" class="btn btn-sm btn-light" data-bs-toggle="tooltip" title="View SALN" style="padding: 0.4rem 0.8rem;">
                                    <i class="bi bi-file-earmark-bar-graph-fill text-success"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 border-light">
                            <div class="py-4">
                                <i class="bi bi-search fs-1 text-muted opacity-50 mb-3 d-block"></i>
                                @if(!empty($search))
                                    <h6 class="fw-bold text-dark">No staff matched "{{ $search }}".</h6>
                                    <p class="mb-0 text-muted">Try searching for a different name, username, or position.</p>
                                @else
                                    <h6 class="fw-bold text-dark">No staff found.</h6>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
        <div class="card-footer bg-white border-top border-light py-3">
            {{ $employees->links() }}
        </div>
        @else
        <div class="card-footer bg-white py-3 border-top border-light text-muted small d-flex justify-content-center">
            <span>Showing all employee profiles.</span>
        </div>
        @endif
    </div>
</div>
@endsection