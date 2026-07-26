@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-brand fw-bold m-0"><i class="bi bi-calendar-star-fill me-2"></i> Event Organizer</h4>
            <p class="text-muted small m-0">Schedule meetings, training sessions, and track employee attendance.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-light border shadow-sm btn-sm fw-bold text-muted px-3">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success shadow-sm border-0 rounded-3">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    </div>
    @endif

    <div class="row g-4">
        <!-- Create Event Form -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 border-top border-4 border-success h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h6 class="fw-bold text-dark m-0">Create New Event</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('events.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Event Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Quarterly Town Hall">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Event Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="" disabled selected>Select category...</option>
                                <option value="Company Meeting">Company Meeting</option>
                                <option value="Training Session">Training Session</option>
                                <option value="Team Building">Team Building</option>
                                <option value="Celebration">Celebration</option>
                                <option value="Client Event">Client Event</option>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Date <span class="text-danger">*</span></label>
                                <input type="date" name="event_date" class="form-control text-muted" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Time <span class="text-danger">*</span></label>
                                <input type="time" name="event_time" class="form-control text-muted" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Venue / Location <span class="text-danger">*</span></label>
                            <input type="text" name="venue" class="form-control" required placeholder="e.g. Main Conference Room">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Additional details about the event..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Max Attendees (Optional)</label>
                            <input type="number" name="max_attendees" class="form-control" min="1" placeholder="Leave blank for unlimited">
                            <div class="form-text small">Cap the number of employees who can register.</div>
                        </div>

                        <!-- Adviser Assignment Dropdown -->
                        <div class="mb-3 border-top pt-3 mt-3">
                            <label class="form-label small fw-bold text-brand">Assign Adviser (Optional)</label>
                            <select name="adviser_id" class="form-select">
                                <option value="">-- No Adviser Needed --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('adviser_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->last_name }}, {{ $emp->first_name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small">Select an official staff adviser for this event.</div>
                        </div>

                        <!-- Conflict Override Checkbox (Appears only if redirected back with an error) -->
                        @if(session('error') && str_contains(session('error'), 'Conflict'))
                        <div class="form-check mb-4 border p-2 rounded bg-danger bg-opacity-10">
                            <input class="form-check-input border-danger" type="checkbox" name="override_conflict" value="1" id="overrideConflict">
                            <label class="form-check-label text-danger small fw-bold" for="overrideConflict">
                                Acknowledge schedule conflict and assign anyway
                            </label>
                        </div>
                        @endif

                        <button type="submit" class="btn btn-success w-100 fw-bold shadow-sm">
                            <i class="bi bi-calendar-plus-fill me-1"></i> Publish Event
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Active Events List -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark m-0">Scheduled Events</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th class="ps-4">Event Details</th>
                                    <th>Schedule & Venue</th>
                                    <th>Capacity</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $event)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $event->title }}</div>
                                        <span class="badge bg-success bg-opacity-10 text-success border mt-1">{{ $event->type }}</span>
                                    </td>
                                    
                                    <!-- Updated Schedule & Venue column to include Adviser -->
                                    <td>
                                        <div class="text-dark fw-semibold small">
                                            <i class="bi bi-calendar-event me-1 text-muted"></i> {{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}
                                        </div>
                                        <div class="text-muted small">
                                            <i class="bi bi-clock me-1"></i> {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}
                                        </div>
                                        <div class="text-muted small mt-1">
                                            <i class="bi bi-geo-alt-fill me-1"></i> {{ $event->venue }}
                                        </div>
                                        @if($event->adviser)
                                        <div class="mt-2 small text-primary fw-bold bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded px-2 py-1 d-inline-block">
                                            <i class="bi bi-person-badge-fill me-1"></i> Adviser: {{ $event->adviser->first_name }} {{ $event->adviser->last_name }}
                                        </div>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $event->max_attendees ? $event->max_attendees . ' Max' : 'Unlimited' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('events.track', $event->id) }}" class="btn btn-sm btn-light border text-success fw-bold" title="Manage Attendees">
                                            <i class="bi bi-people-fill"></i> Attendees
                                        </a>
                                        <form action="{{ route('events.destroy', $event->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Cancel and delete this event?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                                        No events scheduled yet.
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