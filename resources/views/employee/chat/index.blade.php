@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="text-header-blue fw-bold m-0"><i class="bi bi-chat-dots-fill me-2 text-header-blue"></i> HR Helpdesk</h4>
                    <p class="text-muted small m-0">Request PDS updates, report attendance issues, or ask questions. You can also add voice messages, pictures, and documents.</p>
                </div>
            </div>

            <!-- Chat Box -->
            <div class="card shadow-sm border-0 d-flex flex-column" style="height: 65vh;">
                
                <!-- Messages Area -->
                <div class="card-body overflow-auto bg-light p-4" id="chatContainer">
                    @forelse($messages as $msg)
                        @if($msg->sender_id == session('user_id'))
                            <!-- My Message (Right Side) -->
                            <div class="d-flex justify-content-end mb-3">
                                <div class="bg-accent text-dark p-3 rounded-4 shadow-sm" style="max-width: 75%; border-bottom-right-radius: 4px !important;">
                                    @include('partials.helpdesk_attachment')
                                    <div class="text-black-50 text-end" style="font-size: 0.7rem;">
                                        {{ \Carbon\Carbon::parse($msg->created_at)->format('M d, h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- HR Message (Left Side) -->
                            <div class="d-flex justify-content-start mb-3">
                                <div class="bg-white text-dark border p-3 rounded-4 shadow-sm" style="max-width: 75%; border-bottom-left-radius: 4px !important;">
                                    <div class="fw-bold text-accent small mb-1">{{ $msg->sender->first_name }} (HR Officer)</div>
                                    @include('partials.helpdesk_attachment')
                                    <div class="text-muted text-start" style="font-size: 0.7rem;">
                                        {{ \Carbon\Carbon::parse($msg->created_at)->format('M d, h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <!-- Empty State -->
                        <div class="h-100 d-flex flex-column justify-content-center align-items-center text-muted">
                            <i class="bi bi-chat-square-text fs-1 mb-2 opacity-50"></i>
                            <p class="small">No messages yet. Send a message to HR to start the conversation.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Input Area -->
                <div class="card-footer bg-white border-top p-3">
                    @if ($errors->any())
                        <div class="alert alert-danger py-2 small mb-2">{{ $errors->first() }}</div>
                    @endif
                    <form action="{{ route('employee.chat.send') }}" method="POST" enctype="multipart/form-data" id="chatForm">
                        @csrf
                        <div class="input-group">
                            <label class="btn btn-outline-secondary mb-0" title="Attach File">
                                <i class="bi bi-paperclip"></i>
                                <input type="file" name="attachment" id="attachment" class="d-none" accept="image/*,.pdf,.doc,.docx">
                            </label>
                            <button type="button" class="btn btn-outline-danger" id="recordButton" title="Record Voice">
                                <i class="bi bi-mic-fill"></i>
                            </button>
                            <input type="text" name="message" id="messageInput" class="form-control" placeholder="Type your message to HR here..." autocomplete="off" autofocus>
                            <button type="submit" class="btn btn-accent" id="sendButton">
                                <i class="bi bi-send-fill"></i> Send
                            </button>
                        </div>
                        <div id="filePreview" class="small text-muted mt-1 d-none"></div>
                        <div id="recordingIndicator" class="small text-danger mt-1 d-none" role="status">
                            <span class="spinner-grow spinner-grow-sm" aria-hidden="true"></span>
                            Recording...
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chatContainer = document.getElementById('chatContainer');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        let mediaRecorder;
        let audioChunks = [];
        let audioBlob = null;
        let recordingStream = null;

        const recordBtn = document.getElementById('recordButton');
        const recordingIndicator = document.getElementById('recordingIndicator');
        const filePreview = document.getElementById('filePreview');
        const attachment = document.getElementById('attachment');
        const form = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');

        function showPreview(text) {
            filePreview.textContent = text;
            filePreview.classList.remove('d-none');
        }

        function setRecording(active) {
            recordBtn.classList.toggle('btn-danger', active);
            recordBtn.classList.toggle('btn-outline-danger', !active);
            recordingIndicator.classList.toggle('d-none', !active);
        }

        recordBtn.addEventListener('click', async () => {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
                setRecording(false);
                return;
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showPreview('Voice recording is not available in this browser.');
                return;
            }

            try {
                recordingStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            } catch (error) {
                showPreview('Microphone access was blocked. Allow the microphone to record a voice message.');
                return;
            }

            attachment.value = '';
            audioChunks = [];
            audioBlob = null;
            mediaRecorder = new MediaRecorder(recordingStream);
            mediaRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    audioChunks.push(event.data);
                }
            };
            mediaRecorder.onstop = () => {
                audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                recordingStream.getTracks().forEach((track) => track.stop());
                showPreview('Voice note recorded.');
            };
            mediaRecorder.start();
            setRecording(true);
        });

        attachment.addEventListener('change', function () {
            if (this.files.length > 0) {
                audioBlob = null;
                showPreview('File attached: ' + this.files[0].name);
            }
        });

        form.addEventListener('submit', function (event) {
            const text = messageInput.value.trim();
            const hasFile = attachment.files.length > 0;

            if (!text && !hasFile && !audioBlob) {
                event.preventDefault();
                showPreview('Type a message, attach a file, or record a voice note.');
                return;
            }

            if (!audioBlob) {
                return;
            }

            event.preventDefault();
            const formData = new FormData(this);
            formData.append('voice_message', audioBlob, 'voice.webm');
            sendButton.disabled = true;

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).then(async (response) => {
                if (response.status === 422) {
                    const data = await response.json();
                    const first = data.errors ? Object.values(data.errors).flat()[0] : 'Could not send the message.';
                    showPreview(first);
                    sendButton.disabled = false;
                    return;
                }

                if (!response.ok) {
                    showPreview('Could not send the message.');
                    sendButton.disabled = false;
                    return;
                }

                window.location.assign(response.url || @json(route('employee.chat')));
            }).catch(() => {
                showPreview('Could not send the message.');
                sendButton.disabled = false;
            });
        });
    });
</script>
@endsection