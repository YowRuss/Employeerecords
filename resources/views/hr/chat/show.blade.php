@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="{{ route('hr.chat.inbox') }}" class="btn btn-sm btn-light border mb-2 shadow-sm fw-bold">
                        <i class="bi bi-arrow-left me-1"></i> Back to Inbox
                    </a>
                    <h4 class="text-header-blue fw-bold m-0"><i class="bi bi-person-circle me-2 text-header-blue"></i> {{ $employee->first_name }} {{ $employee->last_name }}</h4>
                    <p class="text-muted small m-0">Employee Helpdesk Thread</p>
                </div>
            </div>

            <!-- Chat Box -->
            <div class="card shadow-sm border-0 d-flex flex-column" style="height: 65vh;">
                
                <!-- Messages Area -->
                <div class="card-body overflow-auto bg-light p-4" id="chatContainer">
                    @forelse($messages as $msg)
                        @if($msg->sender_id == session('user_id'))
                            <!-- HR Message (Right Side) -->
                            <div class="d-flex justify-content-end mb-3">
                                <div class="bg-accent text-dark p-3 rounded-4 shadow-sm" style="max-width: 75%; border-bottom-right-radius: 4px !important;">
                                    @include('partials.helpdesk_attachment')
                                    <div class="text-black-50 text-end" style="font-size: 0.7rem;">
                                        {{ \Carbon\Carbon::parse($msg->created_at)->format('M d, h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Employee Message (Left Side) -->
                            <div class="d-flex justify-content-start mb-3">
                                <div class="bg-white text-dark border p-3 rounded-4 shadow-sm" style="max-width: 75%; border-bottom-left-radius: 4px !important;">
                                    @include('partials.helpdesk_attachment')
                                    <div class="text-muted text-start" style="font-size: 0.7rem;">
                                        {{ \Carbon\Carbon::parse($msg->created_at)->format('M d, h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="h-100 d-flex flex-column justify-content-center align-items-center text-muted">
                            <i class="bi bi-chat-square-text fs-1 mb-2 opacity-50"></i>
                            <p class="small">No messages yet.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Input Area -->
                <div class="card-footer bg-white border-top p-3">
                    <form action="{{ route('hr.chat.send', $employee->id) }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="message" class="form-control form-control-lg bg-light" placeholder="Reply to {{ $employee->first_name }}..." required autocomplete="off" autofocus>
                            <button type="submit" class="btn btn-accent px-4 shadow-sm">
                                <i class="bi bi-send-fill me-1"></i> Reply
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Auto-scroll Javascript -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var chatContainer = document.getElementById("chatContainer");
        chatContainer.scrollTop = chatContainer.scrollHeight;
    });
</script>
@endsection