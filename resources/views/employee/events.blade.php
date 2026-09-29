@extends('layouts.app')

@section('content')
<!-- CSS Assets -->
<link rel="stylesheet" href="{{ asset('build/assets/css/pds.css') }}">

<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="text-header-blue fw-bold m-0"><i class="bi bi-calendar-event-fill me-2 text-header-blue"></i> Upcoming Events</h4>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 border-top border-4 border-accent">
        <div class="card-body p-3 p-md-4">
            <p class="text-muted small mb-4">Register for meetings, training sessions, and school events.</p>

    <div class="row g-4">
        @forelse($events as $event)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm border-top border-4 border-success">
                <div class="card-body d-flex flex-column">
                    <div>
                        <span class="badge bg-success bg-opacity-10 text-success border mb-2">{{ $event->type }}</span>
                        <h5 class="fw-bold text-dark mb-3">{{ $event->title }}</h5>
                        
                        <div class="text-muted small mb-2"><i class="bi bi-calendar me-2"></i> {{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}</div>
                        <div class="text-muted small mb-2"><i class="bi bi-clock me-2"></i> {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}</div>
                        <div class="text-muted small mb-3"><i class="bi bi-geo-alt-fill me-2"></i> {{ $event->venue }}</div>

                        <!-- Adviser Display Badge -->
                        @if($event->adviser)
                        <div class="mb-3 small text-primary fw-bold bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded px-2 py-1 d-inline-block">
                            <i class="bi bi-person-badge-fill me-1"></i> Adviser: {{ $event->adviser->first_name }} {{ $event->adviser->last_name }}
                        </div>
                        @endif
                    </div>

                    @if($event->description)
                        <p class="small text-muted border-top pt-2 mt-2">{{ Str::limit($event->description, 80) }}</p>
                    @endif

                    <div class="mt-auto pt-3">
                        @if($event->has_registered)
                            <button class="btn btn-light border text-success w-100 fw-bold disabled">
                                <i class="bi bi-check-circle-fill me-1"></i> Registered
                            </button>
                        @elseif($event->max_attendees && $event->current_attendees >= $event->max_attendees)
                            <button class="btn btn-light border text-danger w-100 fw-bold disabled">
                                <i class="bi bi-x-circle-fill me-1"></i> Event Full
                            </button>
                        @else
                            <form action="{{ route('events.register', $event->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 fw-bold shadow-sm">
                                    <i class="bi bi-calendar-plus me-1"></i> Register Now
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-calendar-x fs-2 d-block mb-2"></i> No upcoming events available.
        </div>
        @endforelse
    </div>
        </div>
    </div>
</div>
@endsection