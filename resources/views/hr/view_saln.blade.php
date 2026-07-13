@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-accent fw-bold m-0">Employee SALN Record</h4>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
        </div>

        <div class="card p-4">
            <h5 class="text-accent border-bottom pb-2 mb-4">{{ $employee->full_name }}</h5>
            <p class="text-muted small">SALN Declaration for the year: <strong>{{ $current_year }}</strong></p>
            
            @if($saln)
                <ul class="list-group list-group-flush mb-4">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted fw-bold">Total Assets:</span>
                        <span>₱ {{ number_format($saln->total_assets, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted fw-bold">Total Liabilities:</span>
                        <span>₱ {{ number_format($saln->total_liabilities, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 bg-light mt-3 p-2 border">
                        <span class="text-accent fw-bold">Computed Net Worth:</span>
                        <span class="fw-bold {{ $saln->net_worth < 0 ? 'text-danger' : 'text-success' }}">
                            ₱ {{ number_format($saln->net_worth, 2) }}
                        </span>
                    </li>
                </ul>
                <div class="text-end">
                    <small class="text-muted">Submitted on: {{ \Carbon\Carbon::parse($saln->created_at)->format('M d, Y') }}</small>
                </div>
            @else
                <div class="alert alert-warning text-center">
                    This employee has not submitted their SALN for the year {{ $current_year }} yet.
                </div>
            @endif
        </div>

    </div>
</div>
@endsection