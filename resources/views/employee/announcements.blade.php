@extends('layouts.app')

@section('content')
<!-- CSS Assets -->
<link rel="stylesheet" href="{{ asset('build/assets/css/pds.css') }}">

<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="text-accent fw-bold m-0"><i class="bi bi-megaphone-fill me-2"></i> My Announcements</h4>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 border-top border-4 border-accent">
        <div class="card-body p-3 p-md-4">
            <p class="text-muted small mb-4">View broadcast messages, emergency alerts, and school updates.</p>


    <div class="row g-4">
        @forelse($announcements as $announcement)
        <div class="col-12 col-lg-6">
            <div class="card h-100 border-0 shadow-sm {{ $announcement->has_acknowledged ? 'border-top border-4 border-success' : 'border-top border-4 border-warning' }}">
                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2.5 py-1.5 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                {{ $announcement->type }}
                            </span>
                            @if(isset($announcement->is_pinned) && $announcement->is_pinned)
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                    <i class="bi bi-pin-angle-fill me-1"></i> Pinned
                                </span>
                            @endif
                        </div>
                        <span class="text-muted small">
                            <i class="bi bi-calendar3 me-1"></i> {{ \Carbon\Carbon::parse($announcement->created_at)->format('M d, Y') }}
                        </span>
                    </div>

                    <h5 class="fw-bold text-dark mb-2">{{ $announcement->title }}</h5>
                    <p class="text-muted small mb-4 flex-grow-1" style="line-height: 1.6;">{{ $announcement->content }}</p>

                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            <i class="bi bi-clock me-1"></i> {{ \Carbon\Carbon::parse($announcement->created_at)->format('h:i A') }}
                        </span>

                        @if($announcement->has_acknowledged)
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem;">
                                <i class="bi bi-check-all me-1 fs-6"></i> Acknowledged
                            </span>
                        @else
                            <form action="{{ route('announcements.acknowledge', $announcement->id) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm fw-bold px-3 py-1.5 rounded-pill shadow-sm text-dark">
                                    <i class="bi bi-check2-circle me-1"></i> Acknowledge
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="card border-0 shadow-sm p-5">
                <i class="bi bi-inbox fs-1 text-warning opacity-75 d-block mb-3"></i>
                <h5 class="fw-bold text-dark mb-1">No Announcements Found</h5>
                <p class="text-muted small mb-0">There are no active or past announcement messages at this time.</p>
            </div>
        </div>
        @endforelse
    </div>
        </div>
    </div>
</div>
@endsection