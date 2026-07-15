<hr class="text-muted opacity-25 my-5">
<div class="card shadow-sm border-0 border-start border-4 border-accent mb-2 bg-light">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-3 text-uppercase"><i class="bi bi-pen-fill me-2"></i> {{ isset($tab) ? strtoupper($tab) : 'PAGE' }} Authorization / Signature</h6>
        <form action="{{ route('pds.signature') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <!-- Passes the current tab so the controller knows where to redirect back -->
            <input type="hidden" name="active_tab" value="{{ $tab ?? 'personal' }}">
            <div class="row align-items-center">
                <div class="col-md-4 text-center border-end-md mb-3 mb-md-0">
                    <span class="d-block small fw-bold text-muted mb-2">Current E-Signature</span>
                    @if(!empty($personal_info->e_signature))
                    <div class="bg-white p-2 border rounded d-inline-block shadow-sm">
                        <img src="data:image/jpeg;base64,{{ base64_encode($personal_info->e_signature) }}" alt="E-Signature" style="max-height: 70px; max-width: 200px; object-fit: contain;">
                    </div>
                    <div class="text-success small fw-bold mt-1"><i class="bi bi-check-circle-fill"></i> Uploaded</div>
                    @else
                    <div class="bg-white p-3 border rounded text-muted small d-inline-block fst-italic">No signature uploaded yet.</div>
                    @endif
                </div>
                <div class="col-md-8 ps-md-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted">Upload / Replace Signature</label>
                            <input type="file" name="e_signature" class="form-control form-control-sm bg-white" accept="image/png, image/jpeg" {{ empty($personal_info->e_signature) ? 'required' : '' }}>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Date Accomplished</label>
                            <input type="date" name="signature_date" class="form-control form-control-sm bg-white" value="{{ $personal_info->signature_date ?? date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-accent btn-sm w-100 fw-bold shadow-sm py-2">
                                <i class="bi bi-save me-1"></i> Save Signature
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>