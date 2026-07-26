@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-accent fw-bold m-0"><i class="bi bi-inbox-fill me-2"></i> Helpdesk Inbox</h4>
            <p class="text-muted small m-0">Manage employee requests, PDS updates, and inquiries.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="list-group list-group-flush rounded-3">
                @forelse($conversations as $emp)
                    <a href="{{ route('hr.chat.show', $emp->id) }}" class="list-group-item list-group-item-action p-4 border-bottom {{ $emp->unread_count > 0 ? 'bg-warning bg-opacity-10' : '' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1 {{ $emp->unread_count > 0 ? 'text-dark' : 'text-muted' }}">
                                    {{ $emp->last_name }}, {{ $emp->first_name }}
                                </h6>
                                <p class="mb-0 small text-truncate" style="max-width: 500px;">
                                    @if($emp->latest_message->sender_id != $emp->id)
                                        <i class="bi bi-reply-fill text-muted me-1"></i>
                                    @endif
                                    {{ $emp->latest_message->message }}
                                </p>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted mb-2">
                                    {{ \Carbon\Carbon::parse($emp->latest_message->created_at)->diffForHumans() }}
                                </div>
                                @if($emp->unread_count > 0)
                                    <span class="badge bg-danger rounded-pill">{{ $emp->unread_count }} New</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check2-circle fs-1 d-block mb-2 opacity-50"></i>
                        <p>Inbox is zero! No pending employee messages.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection