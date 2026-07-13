@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="text-accent fw-bold m-0"><i class="bi bi-file-earmark-bar-graph me-2"></i> Statement of Assets, Liabilities and Net Worth (SALN)</h4>
        <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i> Print / Export SALN</button>
    </div>

    <!-- NET WORTH HIGHLIGHT BOX -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white shadow-sm border-0">
                <div class="card-body py-3 text-center">
                    <h6 class="mb-1 text-uppercase tracking-wide opacity-75" style="font-size:0.75rem;">Total Assets</h6>
                    <h4 class="mb-0 fw-bold">₱ {{ number_format($total_assets, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white shadow-sm border-0 mt-2 mt-md-0">
                <div class="card-body py-3 text-center">
                    <h6 class="mb-1 text-uppercase tracking-wide opacity-75" style="font-size:0.75rem;">Total Liabilities</h6>
                    <h4 class="mb-0 fw-bold">₱ {{ number_format($total_liabilities, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm border-0 mt-2 mt-md-0">
                <div class="card-body py-3 text-center">
                    <h6 class="mb-1 text-uppercase tracking-wide opacity-75" style="font-size:0.75rem;">Net Worth</h6>
                    <h4 class="mb-0 fw-bold">₱ {{ number_format($net_worth, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0 border-top border-4 border-accent">
        <div class="card-body p-0">
            @php $activeTab = session('active_tab', 'info'); @endphp

            <ul class="nav nav-tabs bg-light border-bottom pt-2 px-2 flex-nowrap overflow-auto" id="salnTabs" role="tablist" style="font-size: 0.85rem; white-space: nowrap;">
                <li class="nav-item"><button class="nav-link fw-bold text-dark {{ $activeTab == 'info' ? 'active border-bottom-0' : '' }}" data-bs-toggle="tab" data-bs-target="#info" type="button">Basic Info & Children</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark {{ $activeTab == 'assets' ? 'active border-bottom-0' : '' }}" data-bs-toggle="tab" data-bs-target="#assets" type="button">Assets</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark {{ $activeTab == 'liabilities' ? 'active border-bottom-0' : '' }}" data-bs-toggle="tab" data-bs-target="#liabilities" type="button">Liabilities</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark {{ $activeTab == 'business' ? 'active border-bottom-0' : '' }}" data-bs-toggle="tab" data-bs-target="#business" type="button">Business Interests</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark {{ $activeTab == 'relatives' ? 'active border-bottom-0' : '' }}" data-bs-toggle="tab" data-bs-target="#relatives" type="button">Relatives in Gov't</button></li>
            </ul>

            <div class="tab-content p-3 p-md-4">
                
                <!-- TAB 1: BASIC INFO -->
                <div class="tab-pane fade {{ $activeTab == 'info' ? 'show active' : '' }}" id="info">
                    <form action="{{ route('saln.update_info') }}" method="POST">
                        @csrf
                        <div class="row align-items-center mb-4">
                            <div class="col-md-3 fw-bold text-muted">As of Date:</div>
                            <div class="col-md-3"><input type="date" name="as_of_date" class="form-control" value="{{ $saln_info->as_of_date ?? '' }}"></div>
                            <div class="col-md-3 fw-bold text-muted text-md-end mt-2 mt-md-0">Filing Type:</div>
                            <div class="col-md-3">
                                <select name="filing_type" class="form-select">
                                    <option value="Joint Filing" {{ ($saln_info->filing_type ?? '') == 'JOINT FILING' ? 'selected' : '' }}>Joint Filing</option>
                                    <option value="Separate Filing" {{ ($saln_info->filing_type ?? '') == 'SEPARATE FILING' ? 'selected' : '' }}>Separate Filing</option>
                                    <option value="Not Applicable" {{ ($saln_info->filing_type ?? '') == 'NOT APPLICABLE' ? 'selected' : '' }}>Not Applicable</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-6">
                                <h6 class="bg-secondary text-white p-2 rounded-1 fw-bold">DECLARANT</h6>
                                <div class="mb-2"><label class="small text-muted fw-bold">Full Name</label><input type="text" name="declarant_name" class="form-control text-uppercase" value="{{ $saln_info->declarant_name ?? '' }}"></div>
                                <div class="mb-2"><label class="small text-muted fw-bold">Address</label><input type="text" name="declarant_address" class="form-control text-uppercase" value="{{ $saln_info->declarant_address ?? '' }}"></div>
                                <div class="mb-2"><label class="small text-muted fw-bold">Position</label><input type="text" name="declarant_position" class="form-control text-uppercase" value="{{ $saln_info->declarant_position ?? '' }}"></div>
                                <div class="mb-2"><label class="small text-muted fw-bold">Agency/Office</label><input type="text" name="declarant_agency" class="form-control text-uppercase" value="{{ $saln_info->declarant_agency ?? '' }}"></div>
                                <div class="mb-2"><label class="small text-muted fw-bold">Office Address</label><input type="text" name="declarant_office_address" class="form-control text-uppercase" value="{{ $saln_info->declarant_office_address ?? '' }}"></div>
                            </div>
                            <div class="col-lg-6">
                                <h6 class="bg-secondary text-white p-2 rounded-1 fw-bold">SPOUSE</h6>
                                <div class="mb-2"><label class="small text-muted fw-bold">Full Name</label><input type="text" name="spouse_name" class="form-control text-uppercase" value="{{ $saln_info->spouse_name ?? '' }}"></div>
                                <div class="mb-2"><label class="small text-muted fw-bold">Position</label><input type="text" name="spouse_position" class="form-control text-uppercase" value="{{ $saln_info->spouse_position ?? '' }}"></div>
                                <div class="mb-2"><label class="small text-muted fw-bold">Agency/Office</label><input type="text" name="spouse_agency" class="form-control text-uppercase" value="{{ $saln_info->spouse_agency ?? '' }}"></div>
                                <div class="mb-2"><label class="small text-muted fw-bold">Office Address</label><input type="text" name="spouse_office_address" class="form-control text-uppercase" value="{{ $saln_info->spouse_office_address ?? '' }}"></div>
                            </div>
                        </div>
                        <div class="text-end mt-3"><button type="submit" class="btn btn-accent fw-bold px-4 shadow-sm">Save Basic Info</button></div>
                    </form>

                    <hr class="text-muted opacity-25 my-5">
                    
                    <h6 class="fw-bold text-accent">UNMARRIED CHILDREN BELOW EIGHTEEN (18) YEARS OF AGE</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm text-center shadow-sm">
                            <thead class="table-light text-muted"><tr><th>Name</th><th>Date of Birth</th><th>Age</th><th width="5%"></th></tr></thead>
                            <tbody>
                                @forelse($children as $child)
                                <tr>
                                    <td class="text-start fw-bold">{{ $child->name }}</td><td>{{ $child->date_of_birth }}</td><td>{{ $child->age }}</td>
                                    <td><button class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('saln.delete_record', ['table' => 'saln_unmarried_children', 'id' => $child->id]) }}"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-muted py-3">No children recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <form action="{{ route('saln.add_child') }}" method="POST" class="row g-2 bg-light p-3 border rounded shadow-sm">
                        @csrf
                        <div class="col-md-6"><input type="text" name="name" class="form-control form-control-sm text-uppercase" placeholder="Child's Full Name" required></div>
                        <div class="col-md-4"><input type="date" name="date_of_birth" class="form-control form-control-sm" required></div>
                        <div class="col-md-2"><button type="submit" class="btn btn-sm btn-accent w-100 fw-bold">Add Child</button></div>
                    </form>
                </div>

                <!-- TAB 2: ASSETS -->
                <div class="tab-pane fade {{ $activeTab == 'assets' ? 'show active' : '' }}" id="assets">
                    <h6 class="bg-secondary text-white p-2 rounded-1 fw-bold">1. REAL PROPERTIES</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm text-center shadow-sm" style="font-size:0.8rem;">
                            <thead class="table-light align-middle text-muted">
                                <tr><th>Description</th><th>Kind</th><th>Location</th><th>Assessed Value</th><th>Current Fair Market Value</th><th>Acq. Year</th><th>Acq. Mode</th><th>Acq. Cost</th><th width="5%"></th></tr>
                            </thead>
                            <tbody>
                                @foreach($real_properties as $rp)
                                <tr>
                                    <td class="fw-bold">{{ $rp->description }}</td><td>{{ $rp->kind }}</td><td>{{ $rp->exact_location }}</td>
                                    <td>₱{{ number_format($rp->assessed_value, 2) }}</td><td>₱{{ number_format($rp->fair_market_value, 2) }}</td>
                                    <td>{{ $rp->acquisition_year }}</td><td>{{ $rp->acquisition_mode }}</td><td class="fw-bold text-success">₱{{ number_format($rp->acquisition_cost, 2) }}</td>
                                    <td><button class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('saln.delete_record', ['table' => 'saln_real_properties', 'id' => $rp->id]) }}"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <form action="{{ route('saln.add_real_property') }}" method="POST" class="row g-2 bg-light p-3 border rounded shadow-sm mb-5">
                        @csrf
                        <div class="col-md-3"><input type="text" name="description" class="form-control form-control-sm text-uppercase" placeholder="Description (e.g., Lot)" required></div>
                        <div class="col-md-3"><input type="text" name="kind" class="form-control form-control-sm text-uppercase" placeholder="Kind (e.g., Residential)" required></div>
                        <div class="col-md-6"><input type="text" name="exact_location" class="form-control form-control-sm text-uppercase" placeholder="Exact Location" required></div>
                        <div class="col-md-3"><input type="number" step="0.01" name="assessed_value" class="form-control form-control-sm" placeholder="Assessed Value (₱)"></div>
                        <div class="col-md-3"><input type="number" step="0.01" name="fair_market_value" class="form-control form-control-sm" placeholder="Fair Market Value (₱)"></div>
                        <div class="col-md-2"><input type="text" name="acquisition_year" class="form-control form-control-sm" placeholder="Acq. Year"></div>
                        <div class="col-md-2"><input type="text" name="acquisition_mode" class="form-control form-control-sm text-uppercase" placeholder="Acq. Mode"></div>
                        <div class="col-md-2"><input type="number" step="0.01" name="acquisition_cost" class="form-control form-control-sm" placeholder="Acq. Cost (₱)" required></div>
                        <div class="col-md-12 text-end"><button type="submit" class="btn btn-sm btn-accent fw-bold px-4">Add Real Property</button></div>
                    </form>

                    <h6 class="bg-secondary text-white p-2 rounded-1 fw-bold">2. PERSONAL PROPERTIES</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm text-center shadow-sm">
                            <thead class="table-light text-muted"><tr><th>Description</th><th>Year Acquired</th><th>Acquisition Cost/Amount</th><th width="5%"></th></tr></thead>
                            <tbody>
                                @foreach($personal_properties as $pp)
                                <tr>
                                    <td class="text-start fw-bold">{{ $pp->description }}</td><td>{{ $pp->year_acquired }}</td><td class="fw-bold text-success">₱{{ number_format($pp->acquisition_cost, 2) }}</td>
                                    <td><button class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('saln.delete_record', ['table' => 'saln_personal_properties', 'id' => $pp->id]) }}"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <form action="{{ route('saln.add_personal_property') }}" method="POST" class="row g-2 bg-light p-3 border rounded shadow-sm">
                        @csrf
                        <div class="col-md-5"><input type="text" name="description" class="form-control form-control-sm text-uppercase" placeholder="Description (e.g., Car, Jewelry)" required></div>
                        <div class="col-md-3"><input type="text" name="year_acquired" class="form-control form-control-sm text-uppercase" placeholder="Year Acquired" required></div>
                        <div class="col-md-2"><input type="number" step="0.01" name="acquisition_cost" class="form-control form-control-sm" placeholder="Acq. Cost (₱)" required></div>
                        <div class="col-md-2"><button type="submit" class="btn btn-sm btn-accent w-100 fw-bold">Add Property</button></div>
                    </form>
                </div>

                <!-- TAB 3: LIABILITIES -->
                <div class="tab-pane fade {{ $activeTab == 'liabilities' ? 'show active' : '' }}" id="liabilities">
                    <h6 class="bg-secondary text-white p-2 rounded-1 fw-bold">LIABILITIES</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm text-center shadow-sm">
                            <thead class="table-light text-muted"><tr><th>Nature</th><th>Name of Creditors</th><th>Outstanding Balance</th><th width="5%"></th></tr></thead>
                            <tbody>
                                @foreach($liabilities as $lia)
                                <tr>
                                    <td class="text-start fw-bold">{{ $lia->nature }}</td><td class="text-start">{{ $lia->name_of_creditors }}</td><td class="fw-bold text-danger">₱{{ number_format($lia->outstanding_balance, 2) }}</td>
                                    <td><button class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('saln.delete_record', ['table' => 'saln_liabilities', 'id' => $lia->id]) }}"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <form action="{{ route('saln.add_liability') }}" method="POST" class="row g-2 bg-light p-3 border rounded shadow-sm">
                        @csrf
                        <div class="col-md-4"><input type="text" name="nature" class="form-control form-control-sm text-uppercase" placeholder="Nature (e.g., Personal Loan)" required></div>
                        <div class="col-md-4"><input type="text" name="name_of_creditors" class="form-control form-control-sm text-uppercase" placeholder="Name of Creditor" required></div>
                        <div class="col-md-2"><input type="number" step="0.01" name="outstanding_balance" class="form-control form-control-sm" placeholder="Balance (₱)" required></div>
                        <div class="col-md-2"><button type="submit" class="btn btn-sm btn-accent w-100 fw-bold">Add Liability</button></div>
                    </form>
                </div>

                <!-- TAB 4: BUSINESS INTERESTS -->
                <div class="tab-pane fade {{ $activeTab == 'business' ? 'show active' : '' }}" id="business">
                    <h6 class="bg-secondary text-white p-2 rounded-1 fw-bold">BUSINESS INTERESTS AND FINANCIAL CONNECTIONS</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm text-center shadow-sm">
                            <thead class="table-light text-muted"><tr><th>Name of Entity/Business</th><th>Business Address</th><th>Nature of Business</th><th>Date of Acquisition</th><th width="5%"></th></tr></thead>
                            <tbody>
                                @foreach($businesses as $bus)
                                <tr>
                                    <td class="text-start fw-bold">{{ $bus->business_name }}</td><td>{{ $bus->business_address }}</td><td>{{ $bus->nature_of_business }}</td><td>{{ $bus->date_of_acquisition }}</td>
                                    <td><button class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('saln.delete_record', ['table' => 'saln_business_interests', 'id' => $bus->id]) }}"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <form action="{{ route('saln.add_business') }}" method="POST" class="row g-2 bg-light p-3 border rounded shadow-sm">
                        @csrf
                        <div class="col-md-3"><input type="text" name="business_name" class="form-control form-control-sm text-uppercase" placeholder="Business Name" required></div>
                        <div class="col-md-3"><input type="text" name="business_address" class="form-control form-control-sm text-uppercase" placeholder="Business Address" required></div>
                        <div class="col-md-2"><input type="text" name="nature_of_business" class="form-control form-control-sm text-uppercase" placeholder="Nature" required></div>
                        <div class="col-md-2"><input type="text" name="date_of_acquisition" class="form-control form-control-sm text-uppercase" placeholder="Date Acquired" required></div>
                        <div class="col-md-2"><button type="submit" class="btn btn-sm btn-accent w-100 fw-bold">Add Business</button></div>
                    </form>
                </div>

                <!-- TAB 5: RELATIVES -->
                <div class="tab-pane fade {{ $activeTab == 'relatives' ? 'show active' : '' }}" id="relatives">
                    <h6 class="bg-secondary text-white p-2 rounded-1 fw-bold">RELATIVES IN THE GOVERNMENT SERVICE</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm text-center shadow-sm">
                            <thead class="table-light text-muted"><tr><th>Name of Relative</th><th>Relationship</th><th>Position</th><th>Name of Agency/Office and Address</th><th width="5%"></th></tr></thead>
                            <tbody>
                                @foreach($relatives as $rel)
                                <tr>
                                    <td class="text-start fw-bold">{{ $rel->relative_name }}</td><td>{{ $rel->relationship }}</td><td>{{ $rel->position }}</td><td>{{ $rel->agency_address }}</td>
                                    <td><button class="btn btn-sm btn-outline-danger p-1" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-url="{{ route('saln.delete_record', ['table' => 'saln_relatives_gov', 'id' => $rel->id]) }}"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <form action="{{ route('saln.add_relative') }}" method="POST" class="row g-2 bg-light p-3 border rounded shadow-sm">
                        @csrf
                        <div class="col-md-3"><input type="text" name="relative_name" class="form-control form-control-sm text-uppercase" placeholder="Relative Name" required></div>
                        <div class="col-md-2"><input type="text" name="relationship" class="form-control form-control-sm text-uppercase" placeholder="Relationship" required></div>
                        <div class="col-md-2"><input type="text" name="position" class="form-control form-control-sm text-uppercase" placeholder="Position" required></div>
                        <div class="col-md-3"><input type="text" name="agency_address" class="form-control form-control-sm text-uppercase" placeholder="Agency & Address" required></div>
                        <div class="col-md-2"><button type="submit" class="btn btn-sm btn-accent w-100 fw-bold">Add Relative</button></div>
                    </form>
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
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                <form id="universalDeleteForm" method="POST" action="">
                    @csrf
                    <button type="submit" class="btn btn-danger px-4 fw-bold shadow-sm"><i class="bi bi-trash me-1"></i> Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var deleteModal = document.getElementById('deleteConfirmModal');
        deleteModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var deleteUrl = button.getAttribute('data-url');
            document.getElementById('universalDeleteForm').action = deleteUrl;
        });
    });
</script>
@endsection