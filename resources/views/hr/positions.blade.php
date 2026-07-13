@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="text-accent fw-bold mb-4"><i class="bi bi-gear-fill me-2"></i> Manage Positions</h4>

    @if(session('success'))
    <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="row">
        <!-- FORM TO ADD POSITION -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-top border-4 border-accent p-3">
                <h6 class="fw-bold mb-3">Add New Position</h6>
                <form action="{{ route('hr.positions.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Position Name</label>
                        <input type="text" name="position_name" class="form-control text-uppercase" placeholder="e.g. TEACHER III" required>
                    </div>
                    <button type="submit" class="btn btn-accent w-100 fw-bold">Save Position</button>
                </form>
            </div>
        </div>

        <!-- LIST OF POSITIONS -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted">
                            <tr><th>Position Name</th><th width="10%">Action</th></tr>
                        </thead>
                        <tbody>
                            @forelse($positions as $pos)
                            <tr>
                                <td class="fw-bold text-uppercase">{{ $pos->position_name }}</td>
                                <td>
                                    <form action="{{ route('hr.positions.destroy', $pos->id) }}" method="POST" onsubmit="return confirm('Delete this position?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger p-1"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center py-3 text-muted">No positions created yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection