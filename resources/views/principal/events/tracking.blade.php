@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-brand fw-bold m-0"><i class="bi bi-people-fill me-2"></i> Event Attendance</h4>
            <p class="text-muted small m-0">Manage registrations and mark attendance for this event.</p>
        </div>
        <a href="{{ route('events.index') }}" class="btn btn-light border shadow-sm btn-sm fw-bold text-muted px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Events
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success shadow-sm border-0 rounded-3">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    </div>
    @endif

    <div class="row g-4">
        <!-- Event Details -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 border-top border-4 border-success h-100">
                <div class="card-body">
                    <span class="badge bg-success bg-opacity-10 text-success border mb-2">{{ $event->type }}</span>
                    <h5 class="fw-bold text-dark">{{ $event->title }}</h5>
                    
                    <div class="mt-3 text-muted small">
                        <div class="mb-2"><i class="bi bi-calendar-event me-2"></i> {{ \Carbon\Carbon::parse($event->event_date)->format('l, F d, Y') }}</div>
                        <div class="mb-2"><i class="bi bi-clock me-2"></i> {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}</div>
                        <div class="mb-2"><i class="bi bi-geo-alt-fill me-2"></i> {{ $event->venue }}</div>
                        @if($event->max_attendees)
                        <div class="mb-2"><i class="bi bi-person-lines-fill me-2"></i> Capacity: {{ $event->max_attendees }} max</div>
                        @endif
                    </div>
                    
                    @if($event->description)
                    <div class="bg-light p-3 rounded-3 text-dark small my-3 border">
                        {{ $event->description }}
                    </div>
                    @endif

                    <hr>

                    <h6 class="fw-bold text-dark mb-2">Attendance Progress</h6>
                    @php
                        $totalRegistered = $registeredCount + $attendedCount;
                        $percentage = $totalRegistered > 0 ? round(($attendedCount / $totalRegistered) * 100) : 0;
                    @endphp
                    <div class="progress mb-2" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small fw-bold">
                        <span>{{ $attendedCount }} Attended</span>
                        <span>{{ $totalRegistered }} Registered</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendee List -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark m-0">Guest List</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th class="ps-4">Employee Name</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Mark Attendance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendees as $attendee)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">
                                        {{ $attendee->last_name }}, {{ $attendee->first_name }}
                                    </td>
                                    <td>
                                        @if($attendee->status == 'Attended')
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                                <i class="bi bi-check-circle-fill me-1"></i> Attended
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                                Registered
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('events.attendance', $attendee->id) }}" method="POST">
                                            @csrf
                                            @if($attendee->status == 'Attended')
                                                <button type="submit" class="btn btn-sm btn-outline-secondary fw-bold">
                                                    Undo
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-success fw-bold shadow-sm">
                                                    Mark Attended
                                                </button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-5">
                                        <i class="bi bi-person-x fs-2 d-block mb-2"></i>
                                        No employees have registered for this event yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection