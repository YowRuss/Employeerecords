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
    </style>

    @php
        $teachingEmployees = collect($employees)->filter(function ($emp) {
            if (!$emp->position) return false;
            $cat = $emp->position->category;
            return $cat === \App\Enums\PositionCategory::Teaching || (is_object($cat) && isset($cat->value) && $cat->value === '0') || $cat === '0' || $cat === 0;
        });

        $nonTeachingEmployees = collect($employees)->filter(function ($emp) {
            if (!$emp->position) return false;
            $cat = $emp->position->category;
            return $cat === \App\Enums\PositionCategory::NonTeaching || (is_object($cat) && isset($cat->value) && $cat->value === '1') || $cat === '1' || $cat === 1;
        });

        $unassignedEmployees = collect($employees)->reject(function ($emp) use ($teachingEmployees, $nonTeachingEmployees) {
            return $teachingEmployees->contains('id', $emp->id) || $nonTeachingEmployees->contains('id', $emp->id);
        });
    @endphp

    <div class="card shadow-sm border-0 rounded-3 border-top border-4 border-accent">
        <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 border-bottom">
            <div>
                <h6 class="mb-1 fw-bold text-brand" style="color: #1A3E6F;">
                    <i class="bi bi-wallet2 me-2"></i> Employee Leave Balances
                </h6>
                <p class="text-muted small mb-0">Manage and monitor balances across Teaching and Non-Teaching personnel.</p>
            </div>
            <div>
                <!-- Sub-Tabs (Pills) -->
                <ul class="nav nav-pills balance-pills gap-2" id="balanceSubTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active d-flex align-items-center" id="pills-teaching-tab" data-bs-toggle="pill" data-bs-target="#pills-teaching" type="button" role="tab" aria-controls="pills-teaching" aria-selected="true">
                            <i class="bi bi-mortarboard-fill me-1"></i> Teaching Staff
                            <span class="badge rounded-pill ms-2 bg-light text-dark">{{ $teachingEmployees->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center" id="pills-non-teaching-tab" data-bs-toggle="pill" data-bs-target="#pills-non-teaching" type="button" role="tab" aria-controls="pills-non-teaching" aria-selected="false">
                            <i class="bi bi-briefcase-fill me-1"></i> Non-Teaching Staff
                            <span class="badge rounded-pill ms-2 bg-light text-dark">{{ $nonTeachingEmployees->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link d-flex align-items-center" id="pills-unassigned-tab" data-bs-toggle="pill" data-bs-target="#pills-unassigned" type="button" role="tab" aria-controls="pills-unassigned" aria-selected="false">
                            <i class="bi bi-question-circle-fill me-1"></i> Unassigned / N/A
                            <span class="badge rounded-pill ms-2 {{ $unassignedEmployees->count() > 0 ? 'bg-warning text-dark' : 'bg-light text-muted' }}">{{ $unassignedEmployees->count() }}</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="balanceSubTabsContent">

                <!-- 1. Teaching Staff Tab Pane -->
                <div class="tab-pane fade show active" id="pills-teaching" role="tabpanel" aria-labelledby="pills-teaching-tab">
                    <div class="p-3 bg-light border-bottom text-muted small d-flex align-items-center">
                        <i class="bi bi-info-circle-fill text-primary me-2 fs-6"></i>
                        <span>Teaching personnel earn service credits from approved seminar participation. Vacation and Sick leave credits do not apply to teachers.</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th class="text-start ps-4" style="width: 50%;">Employee</th>
                                    <th class="text-center" style="width: 30%;">Service Credits Balance</th>
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
                                        {{ number_format($emp->leaveCreditBalance->service_credits, 2) }}
                                    </td>
                                    <td class="py-3 text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-outline-secondary fw-bold" data-bs-toggle="modal" data-bs-target="#adjustModal-{{ $emp->id }}">
                                            <i class="bi bi-sliders"></i> Adjust
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-5">
                                        <i class="bi bi-mortarboard fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                        No teaching staff found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Non-Teaching Staff Tab Pane -->
                <div class="tab-pane fade" id="pills-non-teaching" role="tabpanel" aria-labelledby="pills-non-teaching-tab">
                    <div class="p-3 bg-light border-bottom text-muted small d-flex align-items-center">
                        <i class="bi bi-info-circle-fill text-primary me-2 fs-6"></i>
                        <span>Non-teaching personnel accrue Vacation Leave (VL) and Sick Leave (SL) monthly. Service credits do not apply.</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th class="text-start ps-4" style="width: 40%;">Employee</th>
                                    <th class="text-center" style="width: 20%;">Vacation Leave (VL) Balance</th>
                                    <th class="text-center" style="width: 20%;">Sick Leave (SL) Balance</th>
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
                                        {{ number_format($emp->leaveCreditBalance->vl_balance, 2) }}
                                    </td>
                                    <td class="py-3 text-center fw-bold" style="color: #1A3E6F; font-size: 0.95rem;">
                                        {{ number_format($emp->leaveCreditBalance->sl_balance, 2) }}
                                    </td>
                                    <td class="py-3 text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-outline-secondary fw-bold" data-bs-toggle="modal" data-bs-target="#adjustModal-{{ $emp->id }}">
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
                </div>

                <!-- 3. Unassigned / NA Employees Tab Pane -->
                <div class="tab-pane fade" id="pills-unassigned" role="tabpanel" aria-labelledby="pills-unassigned-tab">
                    <div class="p-3 bg-warning bg-opacity-10 border-bottom text-dark small d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2 fs-5"></i>
                        <span>These employees do not have an assigned position or category (Teaching vs. Non-Teaching). All balance categories are displayed for investigation.</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th class="text-start ps-4">Employee</th>
                                    <th class="text-center">Vacation Leave (VL)</th>
                                    <th class="text-center">Sick Leave (SL)</th>
                                    <th class="text-center">Service Credits</th>
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
                                        {{ number_format($emp->leaveCreditBalance->vl_balance, 2) }}
                                    </td>
                                    <td class="py-3 text-center fw-bold" style="color: #1A3E6F; font-size: 0.95rem;">
                                        {{ number_format($emp->leaveCreditBalance->sl_balance, 2) }}
                                    </td>
                                    <td class="py-3 text-center fw-bold" style="color: #1A3E6F; font-size: 0.95rem;">
                                        {{ number_format($emp->leaveCreditBalance->service_credits, 2) }}
                                    </td>
                                    <td class="py-3 text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-outline-secondary fw-bold" data-bs-toggle="modal" data-bs-target="#adjustModal-{{ $emp->id }}">
                                            <i class="bi bi-sliders"></i> Adjust
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="bi bi-check-circle text-success fs-2 d-block mb-2 opacity-50"></i>
                                        All employees have a valid position category assigned. No unassigned staff found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modals rendered outside tables for clean HTML structure and reliable backdrop behavior -->
    @foreach($employees ?? [] as $emp)
    <div class="modal fade text-start" id="adjustModal-{{ $emp->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('hr.credits.adjust', $emp->id) }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header text-white" style="background-color: #1A3E6F;">
                        <h5 class="modal-title fw-bold">Adjust Credits: {{ $emp->first_name }} {{ $emp->last_name }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small">Adjustment Type</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="adjustment_type" id="add-{{ $emp->id }}" value="add" checked>
                                    <label class="form-check-label fw-bold text-success" for="add-{{ $emp->id }}">Add (+)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="adjustment_type" id="deduct-{{ $emp->id }}" value="deduct">
                                    <label class="form-check-label fw-bold text-danger" for="deduct-{{ $emp->id }}">Deduct (-)</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small">Amount</label>
                            <input type="number" name="amount" step="0.5" min="0.5" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small">Leave Bucket</label>
                            <select name="leave_bucket" class="form-select" required>
                                <option value="" disabled selected>Select Bucket to Adjust...</option>
                                <option value="VL">Vacation Leave (VL)</option>
                                <option value="SL">Sick Leave (SL)</option>
                                <option value="SERVICE_CREDIT">Service Credits</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small">Remarks / Reason</label>
                            <textarea name="remarks" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn fw-bold shadow-sm" style="background-color: #FDE047; color: #1A3E6F;">Apply Adjustment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</div>
