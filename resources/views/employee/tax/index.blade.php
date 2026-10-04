@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <div>
            <h4 class="text-header-blue fw-bold m-0">
                <i class="bi bi-file-earmark-pdf-fill me-2 text-header-blue"></i> Tax Documents
            </h4>
            <p class="text-muted small mb-0 mt-1">Download your BIR Form 2316 certificates once they are released by HR.</p>
        </div>
    </div>

    <div class="card shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 py-3 px-4">
            <i class="bi bi-folder2-open text-muted"></i>
            <span class="fw-semibold" style="color: #1A3E6F;">BIR Form 2316</span>
        </div>
        <div class="card-body text-center py-5">
            <i class="bi bi-hourglass-split fs-1 text-muted"></i>
            <p class="text-muted mt-2 mb-0">Year-end tax certificates will appear here when HR finalizes them.</p>
        </div>
    </div>
</div>
@endsection
