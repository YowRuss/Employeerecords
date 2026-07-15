@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-accent fw-bold m-0"><i class="bi bi-folder2-open me-2"></i> Service Records Directory</h4>
    </div>

    @if(session('error'))
    <div class="alert alert-danger shadow-sm">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm border-0 border-top border-4 border-accent">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted">
                    <tr>
                        <th class="ps-4">Employee Name</th>
                        
                        <th width="15%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                    <tr>
                        <td class="ps-4 fw-bold text-uppercase">
                            <i class="bi bi-person-circle text-secondary me-2"></i>
                            {{ $emp->last_name }}, {{ $emp->first_name }} {{ $emp->middle_name }}
                        </td>
                        
                        <td class="text-muted">{{ $emp->username }}</td>
                        <td>
                            <!-- Links to the individual HR Service Record view we built earlier -->
                            <a href="{{ route('hr.service_record.index', $emp->id) }}" class="btn btn-sm btn-accent fw-bold shadow-sm w-100">
                                <i class="bi bi-folder2-open me-1"></i> Open Record
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">No employees found in the system.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection