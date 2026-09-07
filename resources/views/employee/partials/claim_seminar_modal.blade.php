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
                        <label class="form-label fw-bold text-muted small">Seminar Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Date Attended</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Total Hours</label>
                            <input type="number" name="hours" step="0.5" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Certificate (PDF/Image)</label>
                        <input type="file" name="certificate" class="form-control" required>
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
