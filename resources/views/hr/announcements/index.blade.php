@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-brand fw-bold m-0"><i class="bi bi-megaphone-fill me-2"></i> Announcement Manager</h4>
            <p class="text-muted small m-0">Broadcast messages, emergency alerts, and updates to all employees.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success shadow-sm border-0 rounded-3">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    </div>
    @endif

    <div class="row g-4">
        <!-- Create Announcement Form -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 border-top border-4 border-primary h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h6 class="fw-bold text-dark m-0">Create Announcement</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('announcements.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Title / Subject <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Weather Holiday">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Announcement Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="" disabled selected>Select category...</option>
                                <option value="Policy Update">Policy Update</option>
                                <option value="Emergency Alert">Emergency Alert</option>
                                <option value="Reminder">Reminder</option>
                                <option value="Strategic Message">Strategic Message</option>
                                <option value="General Information">General Information</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Message Content <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="5" required placeholder="Type the full announcement here..."></textarea>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Schedule (Optional)</label>
                                <input type="datetime-local" name="scheduled_at" class="form-control text-muted" style="font-size: 0.85rem;">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Expires (Optional)</label>
                                <input type="datetime-local" name="expires_at" class="form-control text-muted" style="font-size: 0.85rem;">
                            </div>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_pinned" id="is_pinned" value="1">
                            <label class="form-check-label small fw-bold text-dark" for="is_pinned">
                                <i class="bi bi-pin-angle-fill text-danger"></i> Pin to top of dashboard
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                            <i class="bi bi-send-fill me-1"></i> Publish Announcement
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Active Announcements List -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark m-0">Recent Announcements</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th class="ps-4">Title & Type</th>
                                    <th>Status</th>
                                    <th>Date Created</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($announcements as $announcement)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">
                                            @if($announcement->is_pinned)
                                                <i class="bi bi-pin-angle-fill text-danger me-1" title="Pinned"></i>
                                            @endif
                                            {{ $announcement->title }}
                                        </div>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border mt-1">{{ $announcement->type }}</span>
                                    </td>
                                    <td>
                                        @if($announcement->expires_at && $announcement->expires_at < now())
                                            <span class="badge bg-danger">Expired</span>
                                        @elseif($announcement->scheduled_at && $announcement->scheduled_at > now())
                                            <span class="badge bg-warning text-dark">Scheduled</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        {{ \Carbon\Carbon::parse($announcement->created_at)->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('announcements.track', $announcement->id) }}" class="btn btn-sm btn-light border text-primary fw-bold" title="Track Readers">
                                            <i class="bi bi-eye-fill"></i> Track
                                        </a>
                                        <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this announcement?');">
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
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No announcements published yet.
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