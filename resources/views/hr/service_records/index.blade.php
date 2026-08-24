@extends('layouts.app')

@section('content')
<style>
    @media (min-width: 768px) {
        .search-container {
            max-width: 300px;
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

    /* Tabs Styling */
    .service-tabs .nav-link {
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
    .service-tabs .nav-link:hover {
        opacity: 1;
        border-color: rgba(253, 224, 71, 0.5);
    }
    .service-tabs .nav-link.active {
        background-color: var(--accent-yellow, #FDE047);
        color: #1A3E6F;
        opacity: 1;
        border-color: var(--accent-yellow, #FDE047);
        border-radius: 8px 8px 0 0;
    }
</style>

<div class="container-fluid py-2">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="text-accent fw-bold m-0"><i class="bi bi-folder2-open me-2"></i> Service Records Directory</h4>
            <p class="text-muted small mt-1 mb-0">Manage and view employee service records across the organization.</p>
        </div>
        <!-- Optional: search bar placeholder -->
        <div class="input-group shadow-sm w-100 search-container" style="border-radius: 8px; overflow: hidden;">
            <span class="input-group-text bg-white border-end-0 border-light"><i class="bi bi-search text-muted"></i></span>
            <input type="text" class="form-control border-start-0 border-light ps-0 focus-ring" style="box-shadow: none;" placeholder="Search employees...">
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center mb-4 rounded-3">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>{{ session('error') }}</div>
    </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="m-0 fw-bold text-dark">Employee Directory</h6>
            </div>
            <!-- Tabbed Navigation -->
            <ul class="nav nav-tabs service-tabs border-bottom-0">
                <li class="nav-item">
                    <a class="nav-link {{ request('filter') === null || request('filter') === 'all' ? 'active' : '' }}" href="{{ route('hr.service_record.directory', ['filter' => 'all']) }}">
                        All Employees <span class="badge bg-white text-dark ms-1">{{ $allCount ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('filter') === 'teaching' ? 'active' : '' }}" href="{{ route('hr.service_record.directory', ['filter' => 'teaching']) }}">
                        Teaching Positions <span class="badge bg-white text-dark ms-1">{{ $teachingCount ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('filter') === 'non-teaching' ? 'active' : '' }}" href="{{ route('hr.service_record.directory', ['filter' => 'non-teaching']) }}">
                        Non-Teaching Positions <span class="badge bg-white text-dark ms-1">{{ $nonTeachingCount ?? 0 }}</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0 border-top-0">
                <thead class="bg-light" style="border-bottom: 2px solid #f1f5f9;">
                    <tr>
                        <th class="ps-4 text-muted small fw-bold text-uppercase py-3 border-0">Employee Details</th>
                        <th class="text-muted small fw-bold text-uppercase py-3 border-0 d-none d-md-table-cell">Username / ID</th>
                        <th width="15%" class="text-muted small fw-bold text-uppercase py-3 border-0 text-center pe-4">Action</th>
                    </tr>
                </thead>
                <tbody style="border-top: none;">
                    @forelse($employees as $emp)
                    <tr class="transition-all" style="cursor: pointer;">
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
                                        <i class="bi bi-person-badge me-1"></i> Active Employee
                                    </div>
                                    <!-- Mobile only username -->
                                    <div class="d-md-none mt-2">
                                        <span class="badge bg-light text-secondary border px-2 py-1 fw-medium" style="font-size: 0.85rem;">
                                            <i class="bi bi-envelope me-1"></i> {{ $emp->username }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        
                        <td class="border-light d-none d-md-table-cell">
                            <span class="badge bg-light text-secondary border px-2 py-1 fw-medium" style="font-size: 0.85rem;">
                                <i class="bi bi-envelope me-1"></i> {{ $emp->username }}
                            </span>
                        </td>
                        <td class="text-center px-4 border-light">
                            <a href="{{ route('hr.service_record.index', $emp->id) }}" class="btn btn-sm btn-outline-dark fw-bold rounded-pill shadow-sm px-2 px-md-3 w-100 d-flex justify-content-center align-items-center" style="transition: all 0.2s;">
                                <i class="bi bi-folder2-open me-md-2"></i> <span class="d-none d-md-inline">Open Record</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5 border-light">
                            <div class="text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                <p class="mb-0 fw-medium">No employees found in the system.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-2 border-top border-light">
            <div class="d-flex justify-content-center mt-4 mb-2 pagination-centered">
                {{ $employees->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection