<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 border-top-0" style="font-size: 0.95rem;">
        <thead class="bg-light" style="border-bottom: 2px solid #f1f5f9;">
            <tr>
                <th class="ps-4 text-muted small fw-bold text-uppercase py-3 border-0">Employee Details</th>
                <th class="text-muted small fw-bold text-uppercase py-3 border-0 d-none d-md-table-cell">Position / Role</th>
                @if($showContact)
                <th class="text-muted small fw-bold text-uppercase py-3 border-0 d-none d-lg-table-cell">Contact Info</th>
                @else
                <th class="text-muted small fw-bold text-uppercase py-3 border-0 d-none d-lg-table-cell">Username</th>
                @endif
                <th class="text-muted small fw-bold text-uppercase py-3 border-0 d-none d-lg-table-cell">Status</th>
                <th class="text-muted small fw-bold text-uppercase py-3 border-0 text-end pe-4">Quick Actions</th>
            </tr>
        </thead>
        <tbody style="border-top: none;">
            @forelse($employees as $emp)
            <tr class="transition-all employee-row" data-learning-area="{{ $emp->learning_area_id ?? '' }}" style="cursor: pointer;" onclick="window.location='{{ route('hr.view_profile', $emp->id) }}';">
                <td class="ps-4 py-3 border-light">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-accent text-dark d-flex align-items-center justify-content-center fw-bold me-3 shadow-sm flex-shrink-0" style="width: 45px; height: 45px; font-size: 1.1rem; opacity: 0.9;">
                            {{ strtoupper(substr($emp->first_name, 0, 1)) }}{{ strtoupper(substr($emp->last_name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="fw-bold text-dark text-uppercase mb-1 text-break" style="font-size: 0.95rem; letter-spacing: 0.2px;">
                                {{ $emp->last_name }}, {{ $emp->first_name }} {{ $emp->middle_name }}
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-upc-scan me-1"></i> ID: {{ str_pad($emp->id, 4, '0', STR_PAD_LEFT) }}
                            </div>
                            <!-- Mobile only details -->
                            <div class="d-md-none mt-2">
                                <span class="badge bg-light text-dark border shadow-sm px-2 py-1 mb-1 text-uppercase" style="font-size: 0.75rem;">
                                    <i class="bi bi-briefcase-fill me-1 text-muted"></i> {{ $emp->position->position_name ?? 'NOT ASSIGNED' }}
                                </span>
                                @if($emp->position?->category === 'Teaching' && $emp->learningArea)
                                    <br><small class="text-muted">{{ $emp->learningArea->name }}</small>
                                @endif
                                <br>
                                <span class="badge bg-light text-secondary border px-2 py-1 fw-medium" style="font-size: 0.75rem;">
                                    @if($showContact)
                                        <i class="bi bi-telephone-fill me-1"></i> {{ $emp->mobile_no ?? 'N/A' }}
                                    @else
                                        <i class="bi bi-person-badge me-1"></i> {{ $emp->username }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="py-3 border-light d-none d-md-table-cell">
                    <span class="badge bg-light text-dark border shadow-sm px-3 py-2 text-uppercase">
                        <i class="bi bi-briefcase-fill me-1 text-muted"></i> {{ $emp->position->position_name ?? 'NOT ASSIGNED' }}
                    </span>
                    @if($emp->position?->category === 'Teaching' && $emp->learningArea)
                        <br><small class="text-muted d-inline-block mt-1">{{ $emp->learningArea->name }}</small>
                    @endif
                </td>
                <td class="text-muted py-3 border-light d-none d-lg-table-cell">
                    @if($showContact)
                        <div class="small"><i class="bi bi-envelope-fill me-1 text-muted"></i> {{ $emp->email_address ?? 'N/A' }}</div>
                        <div class="small mt-1"><i class="bi bi-telephone-fill me-1 text-muted"></i> {{ $emp->mobile_no ?? 'N/A' }}</div>
                    @else
                        <span class="badge bg-light text-secondary border px-2 py-1 fw-medium" style="font-size: 0.85rem;">
                            <i class="bi bi-person-badge text-secondary me-1"></i> {{ $emp->username }}
                        </span>
                    @endif
                </td>
                <td class="py-3 border-light d-none d-lg-table-cell">
                    @if($emp->pds_id)
                        @if(($emp->pds_status ?? 'Draft') == 'Approved')
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 fw-medium"><i class="bi bi-check-circle-fill me-1"></i> Approved</span>
                        @elseif(($emp->pds_status ?? 'Draft') == 'Pending')
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1 fw-medium"><i class="bi bi-hourglass-split me-1"></i> Pending</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 fw-medium"><i class="bi bi-file-earmark-text me-1"></i> Draft</span>
                        @endif
                    @else
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 fw-medium"><i class="bi bi-x-circle-fill me-1"></i> Incomplete</span>
                    @endif
                </td>
                <td class="text-end pe-4 py-3 border-light" onclick="event.stopPropagation();">
                    @if($showReminder)
                        <a href="mailto:{{ $emp->email ?? '' }}?subject=Action Required: Please complete your Personal Data Sheet" class="btn btn-sm shadow-sm border text-white" style="background-color: #1A3E6F;">
                            <i class="bi bi-envelope-exclamation-fill me-1"></i> Send Reminder
                        </a>
                    @else
                        <div class="btn-group shadow-sm rounded-pill bg-white">
                            <a href="{{ route('hr.view_profile', $emp->id) }}" class="btn btn-sm btn-light border-end" data-bs-toggle="tooltip" title="View Profile" style="padding: 0.4rem 0.8rem;">
                                <i class="bi bi-person-lines-fill text-info"></i>
                            </a>
                            <a href="{{ route('hr.view_pds', $emp->id) }}" class="btn btn-sm btn-light border-end" data-bs-toggle="tooltip" title="View PDS" style="padding: 0.4rem 0.8rem;">
                                <i class="bi bi-file-earmark-person-fill text-primary"></i>
                            </a>
                            <a href="{{ route('hr.view_saln', $emp->id) }}" class="btn btn-sm btn-light border-end" data-bs-toggle="tooltip" title="View SALN" style="padding: 0.4rem 0.8rem;">
                                <i class="bi bi-file-earmark-bar-graph-fill text-success"></i>
                            </a>
                            <button class="btn btn-sm btn-light btn-promote" data-bs-toggle="modal" data-bs-target="#promoteEmployeeModal" data-id="{{ $emp->id }}" data-name="{{ $emp->first_name }} {{ $emp->last_name }}" data-position="{{ $emp->position->position_name ?? 'NOT ASSIGNED' }}" data-bs-toggle="tooltip" title="Promote Employee" style="padding: 0.4rem 0.8rem;">
                                <i class="bi bi-arrow-up-circle" style="color: #1A3E6F;"></i>
                            </button>
                        </div>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-5 border-light">
                    <div class="py-4">
                        <i class="bi bi-search fs-1 text-muted opacity-50 mb-3 d-block"></i>
                        @if(!empty(request('search')))
                            <h6 class="fw-bold text-dark">No staff matched.</h6>
                            <p class="mb-0 text-muted">Try searching for a different name, username, or position.</p>
                        @else
                            <h6 class="fw-bold text-dark">No staff found in this category.</h6>
                        @endif
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
