@extends('layouts.app')

@section('content')
<style>
    .theme-primary { color: #1A3E6F !important; }
    .bg-theme-primary { background-color: #1A3E6F !important; color: white !important; }
            .btn-theme-primary, .btn-warning { background: linear-gradient(135deg, var(--accent-yellow), var(--accent-light-yellow)) !important; color: #1e293b !important; border: 1px solid #facc15 !important; font-weight: bold !important; }
    .btn-theme-primary:hover, .btn-warning:hover { background: var(--accent-yellow) !important; color: #0f172a !important; border-color: #ca8a04 !important; }
    .btn-outline-warning { color: var(--accent-yellow-dark) !important; border-color: var(--accent-yellow-dark) !important; background: transparent !important; font-weight: bold !important; }
    .btn-outline-warning:hover { background: var(--accent-yellow) !important; color: #0f172a !important; border-color: var(--accent-yellow) !important; }
    .nav-tabs { border-bottom: 2px solid var(--accent-yellow); margin-bottom: 0; border-top: none; }
    .nav-tabs .nav-link.active { background: linear-gradient(135deg, var(--accent-yellow), #facc15) !important; color: #0f172a !important; font-weight: 700; border: none; }
    .border-warning { border-color: var(--accent-yellow) !important; }
    
    .table-theme thead th { background-color: #f8f9fa !important; color: #1A3E6F !important; font-weight: 700; border-bottom: 2px solid #dee2e6 !important; padding: 0.75rem 1rem; }
    .table-theme td { vertical-align: middle; padding: 0.75rem 1rem; }
    .table-theme tbody tr { border-bottom: 1px solid #eee; }
    
    .card-theme { border-radius: 8px; border: 1px solid #dee2e6 !important; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05); }
    .section-title { font-size: 1rem; font-weight: 700; color: #1A3E6F; margin-bottom: 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-label { font-size: 0.75rem; font-weight: 700; color: #1A3E6F; text-transform: uppercase; margin-bottom: 0.25rem; }
    
    .form-row-added { animation: fadeIn 0.3s ease-in; background-color: #f8f9fa; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* Mobile Responsive Buttons */
    @media (max-width: 767.98px) {
        .btn-theme-primary { width: 100% !important; margin-bottom: 0.5rem; }
        .btn-action-add { width: 100% !important; margin-top: 0.5rem; }
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <h4 class="text-header-blue fw-bold m-0"><i class="bi bi-file-earmark-bar-graph me-2 text-header-blue"></i> Statement of Assets, Liabilities and Net Worth (SALN)</h4>
        <a href="{{ route('saln.export') }}" class="btn btn-theme-primary rounded shadow-sm px-4"><i class="bi bi-printer me-2"></i>Print SALN</a>
    </div>

    <!-- Global Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-theme bg-white overflow-hidden">
        <div class="card-body p-0">
            @php $activeTab = session('active_tab', 'info'); @endphp
            
            <!-- Nav Tabs -->
            <ul class="nav nav-tabs px-4 pt-3 bg-white" id="salnTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab == 'info' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#info" type="button">Basic Info & Children</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab == 'assets' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#assets" type="button">Assets</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab == 'liabilities' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#liabilities" type="button">Liabilities & Net Worth</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab == 'business' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#business" type="button">Business Interests</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $activeTab == 'relatives' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#relatives" type="button">Relatives in Gov't</button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content p-4 p-md-5 bg-white border-top border-warning border-2">
                
                <!-- TAB 1: BASIC INFO & CHILDREN -->
                <div class="tab-pane fade {{ $activeTab == 'info' ? 'show active' : '' }}" id="info">
                    <form action="{{ route('saln.update_info') }}" method="POST">
                        @csrf
                        <div class="row align-items-center mb-5 bg-light p-4 rounded-3 border">
                            <div class="col-md-3 fw-bold text-secondary">As of Date:</div>
                            <div class="col-md-3">
                                <input type="date" name="as_of_date" class="form-control" value="{{ $saln_info->as_of_date ?? '' }}">
                            </div>
                            <div class="col-md-2 fw-bold text-secondary text-md-end mt-3 mt-md-0">Filing Type:</div>
                            <div class="col-md-4">
                                <select name="filing_type" class="form-select">
                                    <option value="Joint Filing" {{ ($saln_info->filing_type ?? '') == 'JOINT FILING' ? 'selected' : '' }}>Joint Filing</option>
                                    <option value="Separate Filing" {{ ($saln_info->filing_type ?? '') == 'SEPARATE FILING' ? 'selected' : '' }}>Separate Filing</option>
                                    <option value="Not Applicable" {{ ($saln_info->filing_type ?? '') == 'NOT APPLICABLE' ? 'selected' : '' }}>Not Applicable</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-5 mb-5">
                            <div class="col-lg-6">
                                <div class="border-bottom pb-2 mb-3">
                                    <h4 class="section-title"><i class="bi bi-person-fill me-2"></i>Declarant</h4>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="declarant_name" class="form-control text-uppercase" value="{{ $saln_info->declarant_name ?? $auto_name }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="declarant_address" class="form-control text-uppercase" value="{{ !empty($saln_info?->declarant_address) ? $saln_info->declarant_address : $auto_address }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Position</label>
                                    <input type="text" name="declarant_position" class="form-control text-uppercase bg-light" value="{{ $user->position->name ?? $user->position->position_name ?? 'No Position Assigned' }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Agency/Office</label>
                                    <input type="text" name="declarant_agency" class="form-control text-uppercase" value="{{ $saln_info->declarant_agency ?? 'CNHS-JHS' }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Office Address</label>
                                    <input type="text" name="declarant_office_address" class="form-control text-uppercase" value="{{ $saln_info->declarant_office_address ?? 'VIGAN CITY' }}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="border-bottom pb-2 mb-3">
                                    <h4 class="section-title"><i class="bi bi-person-heart me-2"></i>Spouse</h4>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="spouse_name" class="form-control text-uppercase" value="{{ !empty($saln_info?->spouse_name) ? $saln_info->spouse_name : $auto_spouse_name }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Position</label>
                                    <input type="text" name="spouse_position" class="form-control text-uppercase" value="{{ !empty($saln_info?->spouse_position) ? $saln_info->spouse_position : $auto_spouse_position }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Agency/Office</label>
                                    <input type="text" name="spouse_agency" class="form-control text-uppercase" value="{{ !empty($saln_info?->spouse_agency) ? $saln_info->spouse_agency : $auto_spouse_agency }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Office Address</label>
                                    <input type="text" name="spouse_office_address" class="form-control text-uppercase" value="{{ !empty($saln_info?->spouse_office_address) ? $saln_info->spouse_office_address : $auto_spouse_office_address }}">
                                </div>
                            </div>
                        </div>
                        <div class="text-end mb-5 border-bottom pb-4">
                            <button type="submit" class="btn btn-theme-primary px-5 rounded shadow-sm"><i class="bi bi-save me-2"></i>Save Basic Info</button>
                        </div>
                    </form>

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-3 mt-4 border-bottom pb-2">
                        <h4 class="section-title"><i class="bi bi-people-fill me-2"></i>Unmarried Children Below 18</h4>
                        <button type="button" class="btn btn-sm btn-warning fw-bold shadow-sm btn-action-add" onclick="addFormRow('children')">
                            <i class="bi bi-plus-circle-fill me-1"></i> Add Child
                        </button>
                    </div>
                    <div class="table-responsive mb-3">
                        <table class="table table-theme table-hover border rounded overflow-hidden" id="childrenTable">
                            <thead>
                                <tr>
                                    <th>Child's Full Name</th>
                                    <th>Date of Birth</th>
                                    <th>Age</th>
                                    <th class="text-center" width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($children as $child)
                                <tr>
                                    <td class="fw-bold">{{ $child->name }}</td>
                                    <td>{{ $child->date_of_birth }}</td>
                                    <td><span class="badge bg-secondary rounded-pill px-3">{{ $child->age }} yrs</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning rounded-circle p-2 lh-1" onclick='openEditModal("child", @json($child))'><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-outline-danger rounded-circle p-2 lh-1 ms-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('saln.delete_record', ['table' => 'saln_unmarried_children', 'id' => $child->id]) }}"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                @empty
                                <tr class="empty-row"><td colspan="4" class="text-center text-muted py-4">No children declared.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Dynamic Add Row Container -->
                    <div id="childrenFormContainer"></div>
                    
                    <!-- Hidden Form Template for Child -->
                    <template id="tpl-children">
                        <form action="{{ route('saln.add_child') }}" method="POST" class="form-row-added row g-3 p-3 border rounded shadow-sm mb-3 position-relative">
                            @csrf
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="this.closest('form').remove()"></button>
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control text-uppercase" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" max="{{ \Carbon\Carbon::now()->toDateString() }}" min="{{ \Carbon\Carbon::now()->subYears(18)->addDay()->toDateString() }}" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-theme-primary w-100"><i class="bi bi-check2 me-1"></i>Save</button>
                            </div>
                        </form>
                    </template>
                </div>

                <!-- TAB 2: ASSETS -->
                <div class="tab-pane fade {{ $activeTab == 'assets' ? 'show active' : '' }}" id="assets">
                    
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-3 border-bottom pb-2">
                        <h4 class="section-title"><i class="bi bi-house-door-fill me-2"></i>1. Real Properties</h4>
                        <button type="button" class="btn btn-sm btn-warning fw-bold shadow-sm btn-action-add" onclick="addFormRow('realProperty')">
                            <i class="bi bi-plus-circle-fill me-1"></i> Add Real Property
                        </button>
                    </div>
                    
                    <div class="table-responsive mb-3">
                        <table class="table table-theme table-hover border rounded overflow-hidden">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Kind & Location</th>
                                    <th>Values (Assessed / Market)</th>
                                    <th>Acq. Details</th>
                                    <th>Acq. Cost (₱)</th>
                                    <th class="text-center" width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($real_properties as $rp)
                                <tr class="saved-real-property" data-cost="{{ $rp->acquisition_cost }}">
                                    <td class="fw-bold">{{ $rp->description }}</td>
                                    <td>
                                        <div class="text-muted small">Kind: <span class="text-dark fw-semibold">{{ $rp->kind }}</span></div>
                                        <div class="text-muted small">Loc: <span class="text-dark fw-semibold">{{ $rp->exact_location }}</span></div>
                                    </td>
                                    <td>
                                        <div class="text-muted small">Assessed: <span class="text-dark">₱{{ number_format($rp->assessed_value, 2) }}</span></div>
                                        <div class="text-muted small">Market: <span class="text-dark">₱{{ number_format($rp->fair_market_value, 2) }}</span></div>
                                    </td>
                                    <td>
                                        <div class="text-muted small">Year: <span class="text-dark">{{ $rp->acquisition_year }}</span></div>
                                        <div class="text-muted small">Mode: <span class="text-dark">{{ $rp->acquisition_mode }}</span></div>
                                    </td>
                                    <td class="fw-bold theme-primary fs-5">₱{{ number_format($rp->acquisition_cost, 2) }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning rounded-circle p-2 lh-1" onclick='openEditModal("realProperty", @json($rp))'><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-outline-danger rounded-circle p-2 lh-1 ms-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('saln.delete_record', ['table' => 'saln_real_properties', 'id' => $rp->id]) }}"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="realPropertyFormContainer"></div>

                    <template id="tpl-realProperty">
                        <form action="{{ route('saln.add_real_property') }}" method="POST" class="form-row-added row g-3 p-4 border rounded shadow-sm mb-4 position-relative">
                            @csrf
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="this.closest('form').remove(); updateLiveNetWorth();"></button>
                            <div class="col-md-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control text-uppercase" required></div>
                            <div class="col-md-3"><label class="form-label">Kind</label><input type="text" name="kind" class="form-control text-uppercase" required></div>
                            <div class="col-md-6"><label class="form-label">Exact Location</label><input type="text" name="exact_location" class="form-control text-uppercase" required></div>
                            <div class="col-md-3"><label class="form-label">Assessed Value</label><input type="number" step="0.01" name="assessed_value" class="form-control"></div>
                            <div class="col-md-3"><label class="form-label">Fair Market Value</label><input type="number" step="0.01" name="fair_market_value" class="form-control"></div>
                            <div class="col-md-2"><label class="form-label">Acq. Year</label><input type="text" name="acquisition_year" class="form-control"></div>
                            <div class="col-md-2"><label class="form-label">Acq. Mode</label><input type="text" name="acquisition_mode" class="form-control text-uppercase"></div>
                            <div class="col-md-2"><label class="form-label">Acq. Cost (₱)</label><input type="number" step="0.01" name="acquisition_cost" class="form-control input-asset" oninput="updateLiveNetWorth()" required></div>
                            <div class="col-12 text-end"><button type="submit" class="btn btn-theme-primary px-4"><i class="bi bi-check2 me-1"></i>Save Property</button></div>
                        </form>
                    </template>

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-3 mt-5 border-bottom pb-2">
                        <h4 class="section-title"><i class="bi bi-car-front-fill me-2"></i>2. Personal Properties</h4>
                        <button type="button" class="btn btn-sm btn-warning fw-bold shadow-sm btn-action-add" onclick="addFormRow('personalProperty')">
                            <i class="bi bi-plus-circle-fill me-1"></i> Add Personal Property
                        </button>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-theme table-hover border rounded overflow-hidden">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Year Acquired</th>
                                    <th>Acquisition Cost (₱)</th>
                                    <th class="text-center" width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($personal_properties as $pp)
                                <tr class="saved-personal-property" data-cost="{{ $pp->acquisition_cost }}">
                                    <td class="fw-bold">{{ $pp->description }}</td>
                                    <td>{{ $pp->year_acquired }}</td>
                                    <td class="fw-bold theme-primary fs-5">₱{{ number_format($pp->acquisition_cost, 2) }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning rounded-circle p-2 lh-1" onclick='openEditModal("personalProperty", @json($pp))'><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-outline-danger rounded-circle p-2 lh-1 ms-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('saln.delete_record', ['table' => 'saln_personal_properties', 'id' => $pp->id]) }}"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div id="personalPropertyFormContainer"></div>

                    <template id="tpl-personalProperty">
                        <form action="{{ route('saln.add_personal_property') }}" method="POST" class="form-row-added row g-3 p-4 border rounded shadow-sm mb-4 position-relative">
                            @csrf
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="this.closest('form').remove(); updateLiveNetWorth();"></button>
                            <div class="col-md-5"><label class="form-label">Description</label><input type="text" name="description" class="form-control text-uppercase" required></div>
                            <div class="col-md-3"><label class="form-label">Year Acquired</label><input type="text" name="year_acquired" class="form-control text-uppercase" required></div>
                            <div class="col-md-2"><label class="form-label">Acq. Cost (₱)</label><input type="number" step="0.01" name="acquisition_cost" class="form-control input-asset" oninput="updateLiveNetWorth()" required></div>
                            <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-theme-primary w-100"><i class="bi bi-check2 me-1"></i>Save</button></div>
                        </form>
                    </template>
                </div>

                <!-- TAB 3: LIABILITIES & NET WORTH -->
                <div class="tab-pane fade {{ $activeTab == 'liabilities' ? 'show active' : '' }}" id="liabilities">
                    
                    <!-- DYNAMIC NET WORTH BANNER -->
                    <div class="row mb-5 g-4">
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 border-start border-4 h-100 bg-white" style="border-left-color: #1A3E6F !important;">
                                <div class="card-body py-4">
                                    <div class="text-uppercase small fw-bold text-muted mb-1">Total Assets</div>
                                    <h3 class="fw-bold text-dark mb-0" id="displayTotalAssets">₱ {{ number_format($total_assets, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 border-start border-4 h-100 bg-white" style="border-left-color: #1A3E6F !important;">
                                <div class="card-body py-4">
                                    <div class="text-uppercase small fw-bold text-muted mb-1">Total Liabilities</div>
                                    <h3 class="fw-bold text-dark mb-0" id="displayTotalLiabilities">₱ {{ number_format($total_liabilities, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 border-start border-4 h-100 bg-white border-warning">
                                <div class="card-body py-4">
                                    <div class="text-uppercase small fw-bold text-dark mb-1">Net Worth Summary</div>
                                    <h3 class="fw-bold text-dark mb-0" id="displayNetWorth">₱ {{ number_format($net_worth, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-3 border-bottom pb-2">
                        <h4 class="section-title"><i class="bi bi-credit-card-fill me-2"></i>Liabilities</h4>
                        <button type="button" class="btn btn-sm btn-warning fw-bold shadow-sm btn-action-add" onclick="addFormRow('liability')">
                            <i class="bi bi-plus-circle-fill me-1"></i> Add Liability
                        </button>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-theme table-hover border rounded overflow-hidden">
                            <thead>
                                <tr>
                                    <th>Nature of Liability</th>
                                    <th>Name of Creditors</th>
                                    <th>Outstanding Balance (₱)</th>
                                    <th class="text-center" width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($liabilities as $lia)
                                <tr class="saved-liability" data-cost="{{ $lia->outstanding_balance }}">
                                    <td class="fw-bold">{{ $lia->nature }}</td>
                                    <td>{{ $lia->name_of_creditors }}</td>
                                    <td class="fw-bold text-danger fs-5">₱{{ number_format($lia->outstanding_balance, 2) }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning rounded-circle p-2 lh-1" onclick='openEditModal("liability", @json($lia))'><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-outline-danger rounded-circle p-2 lh-1 ms-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('saln.delete_record', ['table' => 'saln_liabilities', 'id' => $lia->id]) }}"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div id="liabilityFormContainer"></div>

                    <template id="tpl-liability">
                        <form action="{{ route('saln.add_liability') }}" method="POST" class="form-row-added row g-3 p-4 border rounded shadow-sm mb-4 position-relative">
                            @csrf
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="this.closest('form').remove(); updateLiveNetWorth();"></button>
                            <div class="col-md-4"><label class="form-label">Nature (e.g., Loan)</label><input type="text" name="nature" class="form-control text-uppercase" required></div>
                            <div class="col-md-4"><label class="form-label">Name of Creditor</label><input type="text" name="name_of_creditors" class="form-control text-uppercase" required></div>
                            <div class="col-md-2"><label class="form-label">Balance (₱)</label><input type="number" step="0.01" name="outstanding_balance" class="form-control input-liability" oninput="updateLiveNetWorth()" required></div>
                            <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-theme-primary w-100"><i class="bi bi-check2 me-1"></i>Save</button></div>
                        </form>
                    </template>
                </div>

                <!-- TAB 4: BUSINESS INTERESTS -->
                <div class="tab-pane fade {{ $activeTab == 'business' ? 'show active' : '' }}" id="business">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-3 border-bottom pb-2">
                        <h4 class="section-title"><i class="bi bi-briefcase-fill me-2"></i>Business Interests and Financial Connections</h4>
                        <button type="button" class="btn btn-sm btn-warning fw-bold shadow-sm btn-action-add" onclick="addFormRow('business')">
                            <i class="bi bi-plus-circle-fill me-1"></i> Add Business
                        </button>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-theme table-hover border rounded overflow-hidden">
                            <thead>
                                <tr>
                                    <th>Name of Entity/Business</th>
                                    <th>Business Address</th>
                                    <th>Nature of Business</th>
                                    <th>Date of Acquisition</th>
                                    <th class="text-center" width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($businesses as $bus)
                                <tr>
                                    <td class="fw-bold">{{ $bus->business_name }}</td>
                                    <td>{{ $bus->business_address }}</td>
                                    <td><span class="badge bg-secondary">{{ $bus->nature_of_business }}</span></td>
                                    <td>{{ $bus->date_of_acquisition }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning rounded-circle p-2 lh-1" onclick='openEditModal("business", @json($bus))'><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-outline-danger rounded-circle p-2 lh-1 ms-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('saln.delete_record', ['table' => 'saln_business_interests', 'id' => $bus->id]) }}"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div id="businessFormContainer"></div>

                    <template id="tpl-business">
                        <form action="{{ route('saln.add_business') }}" method="POST" class="form-row-added row g-3 p-4 border rounded shadow-sm mb-4 position-relative">
                            @csrf
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="this.closest('form').remove()"></button>
                            <div class="col-md-3"><label class="form-label">Business Name</label><input type="text" name="business_name" class="form-control text-uppercase" required></div>
                            <div class="col-md-4"><label class="form-label">Business Address</label><input type="text" name="business_address" class="form-control text-uppercase" required></div>
                            <div class="col-md-3"><label class="form-label">Nature</label><input type="text" name="nature_of_business" class="form-control text-uppercase" required></div>
                            <div class="col-md-2"><label class="form-label">Date Acquired</label><input type="text" name="date_of_acquisition" class="form-control text-uppercase" required></div>
                            <div class="col-12 text-end"><button type="submit" class="btn btn-theme-primary px-4"><i class="bi bi-check2 me-1"></i>Save Business</button></div>
                        </form>
                    </template>
                </div>

                <!-- TAB 5: RELATIVES IN GOV -->
                <div class="tab-pane fade {{ $activeTab == 'relatives' ? 'show active' : '' }}" id="relatives">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-3 border-bottom pb-2">
                        <h4 class="section-title"><i class="bi bi-diagram-3-fill me-2"></i>Relatives in the Government Service</h4>
                        <button type="button" class="btn btn-sm btn-warning fw-bold shadow-sm btn-action-add" onclick="addFormRow('relative')">
                            <i class="bi bi-plus-circle-fill me-1"></i> Add Relative
                        </button>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-theme table-hover border rounded overflow-hidden">
                            <thead>
                                <tr>
                                    <th>Name of Relative</th>
                                    <th>Relationship</th>
                                    <th>Position</th>
                                    <th>Agency/Office and Address</th>
                                    <th class="text-center" width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($relatives as $rel)
                                <tr>
                                    <td class="fw-bold">{{ $rel->relative_name }}</td>
                                    <td><span class="badge bg-secondary">{{ $rel->relationship }}</span></td>
                                    <td>{{ $rel->position }}</td>
                                    <td>{{ $rel->agency_address }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning rounded-circle p-2 lh-1" onclick='openEditModal("relative", @json($rel))'><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-outline-danger rounded-circle p-2 lh-1 ms-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('saln.delete_record', ['table' => 'saln_relatives_gov', 'id' => $rel->id]) }}"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div id="relativeFormContainer"></div>

                    <template id="tpl-relative">
                        <form action="{{ route('saln.add_relative') }}" method="POST" class="form-row-added row g-3 p-4 border rounded shadow-sm mb-4 position-relative">
                            @csrf
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="this.closest('form').remove()"></button>
                            <div class="col-md-3"><label class="form-label">Relative Name</label><input type="text" name="relative_name" class="form-control text-uppercase" required></div>
                            <div class="col-md-2"><label class="form-label">Relationship</label><input type="text" name="relationship" class="form-control text-uppercase" required></div>
                            <div class="col-md-3"><label class="form-label">Position</label><input type="text" name="position" class="form-control text-uppercase" required></div>
                            <div class="col-md-4"><label class="form-label">Agency & Address</label><input type="text" name="agency_address" class="form-control text-uppercase" required></div>
                            <div class="col-12 text-end"><button type="submit" class="btn btn-theme-primary px-4"><i class="bi bi-check2 me-1"></i>Save Relative</button></div>
                        </form>
                    </template>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- UNIVERSAL DELETE MODAL -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-0 fs-5">Are you sure you want to delete this record?</p>
                <p class="text-muted small mt-1">This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center bg-light">
                <button type="button" class="btn btn-secondary px-4 fw-bold rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <form id="universalDeleteForm" method="POST" action="">
                    @csrf
                    <button type="submit" class="btn btn-danger px-4 fw-bold shadow-sm rounded-pill"><i class="bi bi-trash me-1"></i> Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- EDIT MODALS -->
<!-- Edit Child Modal -->
<div class="modal fade" id="editChildModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil me-2"></i> Edit Child</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editChildForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" id="edit_child_name" class="form-control text-uppercase" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="edit_child_dob" class="form-control" max="{{ \Carbon\Carbon::now()->toDateString() }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Real Property Modal -->
<div class="modal fade" id="editRealPropertyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil me-2"></i> Edit Real Property</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editRealPropertyForm" method="POST">
                @csrf
                <div class="modal-body row g-3">
                    <div class="col-md-4"><label class="form-label">Description</label><input type="text" name="description" id="edit_rp_desc" class="form-control text-uppercase" required></div>
                    <div class="col-md-4"><label class="form-label">Kind</label><input type="text" name="kind" id="edit_rp_kind" class="form-control text-uppercase" required></div>
                    <div class="col-md-4"><label class="form-label">Exact Location</label><input type="text" name="exact_location" id="edit_rp_loc" class="form-control text-uppercase" required></div>
                    <div class="col-md-4"><label class="form-label">Assessed Value</label><input type="number" step="0.01" name="assessed_value" id="edit_rp_assessed" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Fair Market Value</label><input type="number" step="0.01" name="fair_market_value" id="edit_rp_fair" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Acq. Year</label><input type="text" name="acquisition_year" id="edit_rp_year" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Acq. Mode</label><input type="text" name="acquisition_mode" id="edit_rp_mode" class="form-control text-uppercase"></div>
                    <div class="col-md-6"><label class="form-label">Acq. Cost (?)</label><input type="number" step="0.01" name="acquisition_cost" id="edit_rp_cost" class="form-control" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Personal Property Modal -->
<div class="modal fade" id="editPersonalPropertyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil me-2"></i> Edit Personal Property</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPersonalPropertyForm" method="POST">
                @csrf
                <div class="modal-body row g-3">
                    <div class="col-md-12"><label class="form-label">Description</label><input type="text" name="description" id="edit_pp_desc" class="form-control text-uppercase" required></div>
                    <div class="col-md-6"><label class="form-label">Year Acquired</label><input type="text" name="year_acquired" id="edit_pp_year" class="form-control text-uppercase" required></div>
                    <div class="col-md-6"><label class="form-label">Acq. Cost (?)</label><input type="number" step="0.01" name="acquisition_cost" id="edit_pp_cost" class="form-control" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Liability Modal -->
<div class="modal fade" id="editLiabilityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil me-2"></i> Edit Liability</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editLiabilityForm" method="POST">
                @csrf
                <div class="modal-body row g-3">
                    <div class="col-md-12"><label class="form-label">Nature</label><input type="text" name="nature" id="edit_lia_nature" class="form-control text-uppercase" required></div>
                    <div class="col-md-12"><label class="form-label">Name of Creditor</label><input type="text" name="name_of_creditors" id="edit_lia_creditor" class="form-control text-uppercase" required></div>
                    <div class="col-md-12"><label class="form-label">Outstanding Balance (?)</label><input type="number" step="0.01" name="outstanding_balance" id="edit_lia_balance" class="form-control" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Business Modal -->
<div class="modal fade" id="editBusinessModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil me-2"></i> Edit Business Interest</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBusinessForm" method="POST">
                @csrf
                <div class="modal-body row g-3">
                    <div class="col-md-6"><label class="form-label">Business Name</label><input type="text" name="business_name" id="edit_bus_name" class="form-control text-uppercase" required></div>
                    <div class="col-md-6"><label class="form-label">Business Address</label><input type="text" name="business_address" id="edit_bus_address" class="form-control text-uppercase" required></div>
                    <div class="col-md-6"><label class="form-label">Nature of Business</label><input type="text" name="nature_of_business" id="edit_bus_nature" class="form-control text-uppercase" required></div>
                    <div class="col-md-6"><label class="form-label">Date Acquired</label><input type="text" name="date_of_acquisition" id="edit_bus_date" class="form-control text-uppercase" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Relative Modal -->
<div class="modal fade" id="editRelativeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil me-2"></i> Edit Relative in Gov't</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editRelativeForm" method="POST">
                @csrf
                <div class="modal-body row g-3">
                    <div class="col-md-6"><label class="form-label">Relative Name</label><input type="text" name="relative_name" id="edit_rel_name" class="form-control text-uppercase" required></div>
                    <div class="col-md-6"><label class="form-label">Relationship</label><input type="text" name="relationship" id="edit_rel_relationship" class="form-control text-uppercase" required></div>
                    <div class="col-md-6"><label class="form-label">Position</label><input type="text" name="position" id="edit_rel_position" class="form-control text-uppercase" required></div>
                    <div class="col-md-6"><label class="form-label">Agency & Address</label><input type="text" name="agency_address" id="edit_rel_agency" class="form-control text-uppercase" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DYNAMIC JAVASCRIPT -->
<script>
    // Handles adding dynamic form rows from templates
        function openEditModal(type, data) {
        if (type === 'child') {
            document.getElementById('editChildForm').action = "/my-saln/update-child/" + data.id;
            document.getElementById('edit_child_name').value = data.name;
            document.getElementById('edit_child_dob').value = data.date_of_birth;
            new bootstrap.Modal(document.getElementById('editChildModal')).show();
        } else if (type === 'realProperty') {
            document.getElementById('editRealPropertyForm').action = "/my-saln/update-real-property/" + data.id;
            document.getElementById('edit_rp_desc').value = data.description;
            document.getElementById('edit_rp_kind').value = data.kind;
            document.getElementById('edit_rp_loc').value = data.exact_location;
            document.getElementById('edit_rp_assessed').value = data.assessed_value;
            document.getElementById('edit_rp_fair').value = data.fair_market_value;
            document.getElementById('edit_rp_year').value = data.acquisition_year;
            document.getElementById('edit_rp_mode').value = data.acquisition_mode;
            document.getElementById('edit_rp_cost').value = data.acquisition_cost;
            new bootstrap.Modal(document.getElementById('editRealPropertyModal')).show();
        } else if (type === 'personalProperty') {
            document.getElementById('editPersonalPropertyForm').action = "/my-saln/update-personal-property/" + data.id;
            document.getElementById('edit_pp_desc').value = data.description;
            document.getElementById('edit_pp_year').value = data.year_acquired;
            document.getElementById('edit_pp_cost').value = data.acquisition_cost;
            new bootstrap.Modal(document.getElementById('editPersonalPropertyModal')).show();
        } else if (type === 'liability') {
            document.getElementById('editLiabilityForm').action = "/my-saln/update-liability/" + data.id;
            document.getElementById('edit_lia_nature').value = data.nature;
            document.getElementById('edit_lia_creditor').value = data.name_of_creditors;
            document.getElementById('edit_lia_balance').value = data.outstanding_balance;
            new bootstrap.Modal(document.getElementById('editLiabilityModal')).show();
        } else if (type === 'business') {
            document.getElementById('editBusinessForm').action = "/my-saln/update-business/" + data.id;
            document.getElementById('edit_bus_name').value = data.business_name;
            document.getElementById('edit_bus_address').value = data.business_address;
            document.getElementById('edit_bus_nature').value = data.nature_of_business;
            document.getElementById('edit_bus_date').value = data.date_of_acquisition;
            new bootstrap.Modal(document.getElementById('editBusinessModal')).show();
        } else if (type === 'relative') {
            document.getElementById('editRelativeForm').action = "/my-saln/update-relative/" + data.id;
            document.getElementById('edit_rel_name').value = data.relative_name;
            document.getElementById('edit_rel_relationship').value = data.relationship;
            document.getElementById('edit_rel_position').value = data.position;
            document.getElementById('edit_rel_agency').value = data.agency_address;
            new bootstrap.Modal(document.getElementById('editRelativeModal')).show();
        }
    }

    function addFormRow(type) {
        const template = document.getElementById('tpl-' + type);
        const container = document.getElementById(type + 'FormContainer');
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
    }

    // Handles real-time calculation of Net Worth
    function updateLiveNetWorth() {
        let totalAssets = 0;
        let totalLiabilities = 0;

        // 1. Sum up saved values from the database tables
        document.querySelectorAll('.saved-real-property').forEach(el => {
            totalAssets += parseFloat(el.getAttribute('data-cost')) || 0;
        });
        document.querySelectorAll('.saved-personal-property').forEach(el => {
            totalAssets += parseFloat(el.getAttribute('data-cost')) || 0;
        });
        document.querySelectorAll('.saved-liability').forEach(el => {
            totalLiabilities += parseFloat(el.getAttribute('data-cost')) || 0;
        });

        // 2. Add any values currently typed into the dynamic forms on screen
        document.querySelectorAll('.input-asset').forEach(input => {
            totalAssets += parseFloat(input.value) || 0;
        });
        document.querySelectorAll('.input-liability').forEach(input => {
            totalLiabilities += parseFloat(input.value) || 0;
        });

        // 3. Calculate Net Worth
        let netWorth = totalAssets - totalLiabilities;

        // 4. Update the UI
        const formatter = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' });
        
        document.getElementById('displayTotalAssets').innerText = formatter.format(totalAssets);
        document.getElementById('displayTotalLiabilities').innerText = formatter.format(totalLiabilities);
        document.getElementById('displayNetWorth').innerText = formatter.format(netWorth);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Setup Delete Modal
        var deleteModal = document.getElementById('deleteConfirmModal');
        deleteModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var deleteUrl = button.getAttribute('data-url');
            document.getElementById('universalDeleteForm').action = deleteUrl;
        });
        
        // Initialize calculations on load
        updateLiveNetWorth();
    });
</script>
@endsection






