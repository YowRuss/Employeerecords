<!-- Tab 2: Employee Balances -->
<div class="tab-pane fade" id="balances" role="tabpanel" aria-labelledby="balances-tab">
    <style>
        .balance-pills .nav-link {
            color: #1A3E6F;
            font-weight: 600;
            border-radius: 50rem;
            padding: 0.45rem 1.25rem;
            font-size: 0.85rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease-in-out;
        }
        .balance-pills .nav-link:hover {
            background-color: #e2e8f0;
            color: #1A3E6F;
        }
        .balance-pills .nav-link.active {
            background-color: #FDE047 !important;
            color: #1A3E6F !important;
            border-color: #FDE047 !important;
            font-weight: 700;
            box-shadow: 0 2px 5px rgba(253, 224, 71, 0.45);
        }
        .balance-pills .nav-link .badge {
            font-size: 0.75rem;
            font-weight: 600;
        }
        .balance-pills .nav-link.active .badge {
            background-color: rgba(26, 62, 111, 0.15) !important;
            color: #1A3E6F !important;
        }
        .balance-pills {
            -webkit-overflow-scrolling: touch;
            white-space: nowrap;
        }
        .balance-pills .nav-item {
            flex-shrink: 0;
        }
        @media (max-width: 767.98px) {
            .balance-table {
                min-width: 580px;
            }
        }
    </style>

    <div class="card shadow-sm border-0 rounded-3 border-top border-4 border-accent">
        <div class="card-header bg-white py-3 d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 border-bottom">
            <div>
                <h6 class="mb-1 fw-bold text-brand" style="color: #1A3E6F;">
                    <i class="bi bi-wallet2 me-2"></i> Employee Leave Balances
                </h6>
                <p class="text-muted small mb-0">Manage and monitor balances across Teaching and Non-Teaching personnel.</p>
            </div>
            <div class="w-100 w-lg-auto overflow-hidden">
                <!-- Sub-Tabs (Pills) -->
                <ul class="nav nav-pills balance-pills gap-2 flex-nowrap overflow-x-auto pb-1" id="balanceSubTabs" role="tablist" style="scrollbar-width: thin;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active d-flex align-items-center" id="pills-teaching-tab" data-bs-toggle="pill" data-bs-target="#pills-teaching" type="button" role="tab" aria-controls="pills-teaching" aria-selected="true">
                            <i class="bi bi-mortarboard-fill me-1"></i> Teaching Staff
                            <span class="badge rounded-pill ms-2 bg-light text-dark">{{ $teachingEmployees->total() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center" id="pills-non-teaching-tab" data-bs-toggle="pill" data-bs-target="#pills-non-teaching" type="button" role="tab" aria-controls="pills-non-teaching" aria-selected="false">
                            <i class="bi bi-briefcase-fill me-1"></i> Non-Teaching Staff
                            <span class="badge rounded-pill ms-2 bg-light text-dark">{{ $nonTeachingEmployees->total() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center" id="pills-unassigned-tab" data-bs-toggle="pill" data-bs-target="#pills-unassigned" type="button" role="tab" aria-controls="pills-unassigned" aria-selected="false">
                            <i class="bi bi-question-circle-fill me-1"></i> Unassigned / N/A
                            <span class="badge rounded-pill ms-2 {{ $unassignedEmployees->total() > 0 ? 'bg-warning text-dark' : 'bg-light text-muted' }}">{{ $unassignedEmployees->total() }}</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="balanceSubTabsContent">

                <!-- 1. Teaching Staff Tab Pane -->
                <div class="tab-pane fade show active" id="pills-teaching" role="tabpanel" aria-labelledby="pills-teaching-tab">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 balance-table" style="font-size: 0.85rem;">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th class="text-start ps-4" style="width: 40%;">Employee</th>
                                    <th class="text-center" style="width: 20%;">Vacation Leave (VL)</th>
                                    <th class="text-center" style="width: 20%;">Sick Leave (SL)</th>
                                    <th class="text-end pe-4" style="width: 20%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teachingEmployees as $emp)
                                <tr>
                                    <td class="ps-4 py-3 text-start">
                                        <div class="fw-bold" style="color: #1A3E6F;">{{ $emp->last_name }}, {{ $emp->first_name }}</div>
                                        <div class="small text-muted">
                                            {{ $emp->position->position_name ?? $emp->position->name ?? 'Teaching Staff' }}
                                            <span class="mx-1">•</span>
                                            {{ $emp->email }}
                                        </div>
                                    </td>
                                    <td class="py-3 text-center fw-bold" style="color: #1A3E6F; font-size: 0.95rem;">
                                        {{ number_format($emp->leaveCreditBalance?->vl_balance ?? 0, 2) }}
                                    </td>
                                    <td class="py-3 text-center fw-bold" style="color: #1A3E6F; font-size: 0.95rem;">
                                        {{ number_format($emp->leaveCreditBalance?->sl_balance ?? 0, 2) }}
                                    </td>
                                    <td class="py-3 text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-outline-secondary fw-bold btn-adjust-balance"
                                            data-bs-toggle="modal"
                                            data-bs-target="#adjustBalancesModal"
                                            data-id="{{ $emp->id }}"
                                            data-name="{{ $emp->last_name }}, {{ $emp->first_name }}"
                                            data-vl="{{ number_format($emp->leaveCreditBalance?->vl_balance ?? 0, 2, '.', '') }}"
                                            data-sl="{{ number_format($emp->leaveCreditBalance?->sl_balance ?? 0, 2, '.', '') }}">
                                            <i class="bi bi-sliders"></i> Adjust
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i class="bi bi-mortarboard fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                        No teaching staff found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($teachingEmployees->hasPages())
                    <div class="border-top bg-white py-3 pagination-centered">
                        {{ $teachingEmployees->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>

                <!-- 2. Non-Teaching Staff Tab Pane -->
                <div class="tab-pane fade" id="pills-non-teaching" role="tabpanel" aria-labelledby="pills-non-teaching-tab">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 balance-table" style="font-size: 0.85rem;">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th class="text-start ps-4" style="width: 40%;">Employee</th>
                                    <th class="text-center" style="width: 20%;">Vacation Leave (VL)</th>
                                    <th class="text-center" style="width: 20%;">Sick Leave (SL)</th>
                                    <th class="text-end pe-4" style="width: 20%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nonTeachingEmployees as $emp)
                                <tr>
                                    <td class="ps-4 py-3 text-start">
                                        <div class="fw-bold" style="color: #1A3E6F;">{{ $emp->last_name }}, {{ $emp->first_name }}</div>
                                        <div class="small text-muted">
                                            {{ $emp->position->position_name ?? $emp->position->name ?? 'Non-Teaching Staff' }}
                                            <span class="mx-1">•</span>
                                            {{ $emp->email }}
                                        </div>
                                    </td>
                                    <td class="py-3 text-center fw-bold" style="color: #1A3E6F; font-size: 0.95rem;">
                                        {{ number_format($emp->leaveCreditBalance?->vl_balance ?? 0, 2) }}
                                    </td>
                                    <td class="py-3 text-center fw-bold" style="color: #1A3E6F; font-size: 0.95rem;">
                                        {{ number_format($emp->leaveCreditBalance?->sl_balance ?? 0, 2) }}
                                    </td>
                                    <td class="py-3 text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-outline-secondary fw-bold btn-adjust-balance"
                                            data-bs-toggle="modal"
                                            data-bs-target="#adjustBalancesModal"
                                            data-id="{{ $emp->id }}"
                                            data-name="{{ $emp->last_name }}, {{ $emp->first_name }}"
                                            data-vl="{{ number_format($emp->leaveCreditBalance?->vl_balance ?? 0, 2, '.', '') }}"
                                            data-sl="{{ number_format($emp->leaveCreditBalance?->sl_balance ?? 0, 2, '.', '') }}">
                                            <i class="bi bi-sliders"></i> Adjust
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i class="bi bi-briefcase fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                        No non-teaching staff found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($nonTeachingEmployees->hasPages())
                    <div class="border-top bg-white py-3 pagination-centered">
                        {{ $nonTeachingEmployees->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>

                <!-- 3. Unassigned / NA Employees Tab Pane -->
                <div class="tab-pane fade" id="pills-unassigned" role="tabpanel" aria-labelledby="pills-unassigned-tab">
                    <div class="p-3 bg-warning bg-opacity-10 border-bottom text-dark small d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2 fs-5"></i>
                        <span>These employees do not have an assigned position or category (Teaching vs. Non-Teaching). All balance categories are displayed for investigation.</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 balance-table" style="font-size: 0.85rem;">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th class="text-start ps-4">Employee</th>
                                    <th class="text-center">Vacation Leave (VL)</th>
                                    <th class="text-center">Sick Leave (SL)</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unassignedEmployees as $emp)
                                <tr>
                                    <td class="ps-4 py-3 text-start">
                                        <div class="fw-bold" style="color: #1A3E6F;">{{ $emp->last_name }}, {{ $emp->first_name }}</div>
                                        <div class="small text-muted">
                                            <span class="badge bg-secondary me-1">Category Unset</span>
                                            {{ $emp->email }}
                                        </div>
                                    </td>
                                    <td class="py-3 text-center fw-bold" style="color: #1A3E6F; font-size: 0.95rem;">
                                        {{ number_format($emp->leaveCreditBalance?->vl_balance ?? 0, 2) }}
                                    </td>
                                    <td class="py-3 text-center fw-bold" style="color: #1A3E6F; font-size: 0.95rem;">
                                        {{ number_format($emp->leaveCreditBalance?->sl_balance ?? 0, 2) }}
                                    </td>
                                    <td class="py-3 text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-outline-secondary fw-bold btn-adjust-balance"
                                            data-bs-toggle="modal"
                                            data-bs-target="#adjustBalancesModal"
                                            data-id="{{ $emp->id }}"
                                            data-name="{{ $emp->last_name }}, {{ $emp->first_name }}"
                                            data-vl="{{ number_format($emp->leaveCreditBalance?->vl_balance ?? 0, 2, '.', '') }}"
                                            data-sl="{{ number_format($emp->leaveCreditBalance?->sl_balance ?? 0, 2, '.', '') }}">
                                            <i class="bi bi-sliders"></i> Adjust
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i class="bi bi-check-circle text-success fs-2 d-block mb-2 opacity-50"></i>
                                        All employees have a valid position category assigned. No unassigned staff found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($unassignedEmployees->hasPages())
                    <div class="border-top bg-white py-3 pagination-centered">
                        {{ $unassignedEmployees->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade text-start" id="adjustBalancesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('hr.leaves.updateBalances', ['id' => 0]) }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" id="edit_balance_user_id" value="">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header text-white" style="background-color: #1A3E6F;">
                        <h5 class="modal-title fw-bold">Adjust Balances</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3 p-sm-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small" for="edit_vl_balance">Vacation Leave (VL)</label>
                            <input type="number" step="0.01" min="0" name="vl_balance" id="edit_vl_balance" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small" for="edit_sl_balance">Sick Leave (SL)</label>
                            <input type="number" step="0.01" min="0" name="sl_balance" id="edit_sl_balance" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3 border-top-0 d-flex flex-column flex-sm-row justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary fw-bold rounded-pill w-100 w-sm-auto order-2 order-sm-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn fw-bold shadow-sm rounded-pill w-100 w-sm-auto order-1 order-sm-2" style="background-color: #FDE047; color: #1A3E6F; border: 1px solid #EAB308;">Save Balances</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.btn-adjust-balance').forEach(function (button) {
            button.addEventListener('click', function () {
                var modal = document.getElementById('adjustBalancesModal');
                modal.querySelector('form').action = '/hr/leave-monitoring/balances/update/' + this.dataset.id;
                modal.querySelector('.modal-title').textContent = 'Adjust Balances: ' + this.dataset.name;
                document.getElementById('edit_balance_user_id').value = this.dataset.id;
                document.getElementById('edit_vl_balance').value = this.dataset.vl;
                document.getElementById('edit_sl_balance').value = this.dataset.sl;
            });
        });
    </script>
</div>
