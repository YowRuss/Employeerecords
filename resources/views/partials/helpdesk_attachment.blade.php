@if(filled($msg->message))
    <p class="mb-1 fs-6">{{ $msg->message }}</p>
@endif
@if($msg->attachment_type === 'audio')
    <audio controls class="mt-2" style="height: 30px;">
        <source src="{{ asset('storage/' . $msg->attachment_path) }}" type="audio/webm">
    </audio>
@elseif($msg->attachment_type === 'image')
    <img src="{{ asset('storage/' . $msg->attachment_path) }}" alt="attachment" class="img-fluid rounded mt-2" style="max-width: 200px;">
@elseif($msg->attachment_type === 'document')
    <a href="{{ asset('storage/' . $msg->attachment_path) }}" target="_blank" class="btn btn-sm btn-light mt-2">
        <i class="bi bi-file-earmark-text"></i> View Document
    </a>
@endif
