@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold mb-0" style="color: #1A3E6F;">My Leave Credits</h4>
        </div>
    </div>

    <!-- Header Widget -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0" style="border-left: 5px solid #FDE047 !important;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-bold mb-2">Current Balance</h6>
                    <h2 class="fw-bold mb-0" style="color: #1A3E6F;">{{ number_format($currentBalance ?? 0, 2) }} <span class="fs-6 text-muted fw-normal">Credits</span></h2>
                </div>
            </div>
        </div>
        @if(auth()->user()?->position && auth()->user()->position->category === \App\Enums\PositionCategory::Teaching)
        <div class="col-md-8 text-md-end d-flex align-items-center justify-content-md-start justify-content-lg-end mt-3 mt-md-0">
            <button type="button" class="btn fw-bold px-4 shadow-sm" style="background-color: #1A3E6F; color: white;" data-bs-toggle="modal" data-bs-target="#claimSeminarModal">
                <i class="bi bi-plus-circle me-2"></i> Claim Seminar Credit
            </button>
        </div>
        @endif
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <ul class="nav nav-tabs card-header-tabs" id="creditTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="ledger-tab" data-bs-toggle="tab" data-bs-target="#ledger" type="button" role="tab" style="color: #1A3E6F;">Credit Ledger</button>
                </li>
                @if(auth()->user()?->position && auth()->user()->position->category === \App\Enums\PositionCategory::Teaching)
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="seminars-tab" data-bs-toggle="tab" data-bs-target="#seminars" type="button" role="tab" style="color: #1A3E6F;">My Seminars</button>
                </li>
                @endif
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="creditTabsContent">
                
                <!-- Tab 1: Credit Ledger -->
                <div class="tab-pane fade show active" id="ledger" role="tabpanel" aria-labelledby="ledger-tab">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-muted small fw-bold text-uppercase py-3 ps-4 border-0">Date</th>
                                    <th class="text-muted small fw-bold text-uppercase py-3 border-0">Transaction Type</th>
                                    <th class="text-muted small fw-bold text-uppercase py-3 border-0">Amount</th>
                                    <th class="text-muted small fw-bold text-uppercase py-3 border-0">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($creditLogs ?? [] as $log)
                                <tr>
                                    <td class="ps-4 py-3">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}</td>
                                    <td class="py-3"><span class="badge bg-secondary">{{ strtoupper($log->source) }}</span></td>
                                    <td class="py-3 fw-bold {{ $log->amount > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $log->amount > 0 ? '+' : '' }}{{ number_format($log->amount, 2) }}
                                    </td>
                                    <td class="py-3 text-muted small">{{ $log->description ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No credit logs found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if(auth()->user()?->position && auth()->user()->position->category === \App\Enums\PositionCategory::Teaching)
                <!-- Tab 2: My Seminars -->
                <div class="tab-pane fade" id="seminars" role="tabpanel" aria-labelledby="seminars-tab">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-muted small fw-bold text-uppercase py-3 ps-4 border-0">Seminar Title</th>
                                    <th class="text-muted small fw-bold text-uppercase py-3 border-0">Date</th>
                                    <th class="text-muted small fw-bold text-uppercase py-3 border-0">Hours</th>
                                    <th class="text-muted small fw-bold text-uppercase py-3 border-0">Credits Earned</th>
                                    <th class="text-muted small fw-bold text-uppercase py-3 border-0">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mySeminars ?? [] as $seminar)
                                <tr>
                                    <td class="ps-4 py-3 fw-bold" style="color: #1A3E6F;">{{ $seminar->title }}</td>
                                    <td class="py-3">{{ \Carbon\Carbon::parse($seminar->date)->format('M d, Y') }}</td>
                                    <td class="py-3">{{ $seminar->hours }} hrs</td>
                                    <td class="py-3 fw-bold text-success">{{ $seminar->credits_earned ? '+'.number_format($seminar->credits_earned, 2) : '-' }}</td>
                                    <td class="py-3">
                                        @if($seminar->status == 'Approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($seminar->status == 'Rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No seminars submitted.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

@if(auth()->user()?->position && auth()->user()->position->category === \App\Enums\PositionCategory::Teaching)
<!-- Claim Seminar Modal -->
<div class="modal fade" id="claimSeminarModal" tabindex="-1" aria-labelledby="claimSeminarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('leave.seminar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" style="background-color: #1A3E6F;">
                    <h5 class="modal-title fw-bold" id="claimSeminarModalLabel">Claim Seminar Credit</h5>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn fw-bold shadow-sm" style="background-color: #FDE047; color: #1A3E6F;">Submit Claim</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
