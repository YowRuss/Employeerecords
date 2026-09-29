<!-- Claim Seminar Modal -->
<div class="modal fade" id="claimSeminarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('leave.seminar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" style="background-color: #1A3E6F;">
                    <h5 class="modal-title fw-bold">Claim Seminar Credit</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small" for="seminar_title">Seminar Title</label>
                        <input type="text" name="title" id="seminar_title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small" for="seminar_date">Date Attended</label>
                            <input type="date" name="date_attended" id="seminar_date" class="form-control @error('date_attended') is-invalid @enderror" value="{{ old('date_attended') }}" required>
                            @error('date_attended')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small" for="seminar_hours">Total Hours</label>
                            <input type="number" name="hours" id="seminar_hours" step="0.5" min="0.5" class="form-control @error('hours') is-invalid @enderror" value="{{ old('hours') }}" required>
                            @error('hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small" for="seminar_certificate">Certificate (PDF/Image)</label>
                        <input type="file" name="certificate" id="seminar_certificate" class="form-control @error('certificate') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                        @error('certificate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn fw-bold shadow-sm" style="background-color: #FDE047; color: #1A3E6F;">Submit Claim</button>
                </div>
            </div>
        </form>
    </div>
</div>
