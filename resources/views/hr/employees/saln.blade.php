@extends('layouts.app')

@section('content')
<!-- CSS Assets -->
<link rel="stylesheet" href="{{ asset('build/assets/css/pds.css') }}">

<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="text-header-blue fw-bold m-0"><i class="bi bi-file-earmark-bar-graph me-2 text-header-blue"></i> {{ $employee->first_name }} {{ $employee->last_name }}'s SALN</h4>
        <a href="{{ route('hr.view_profile', $employee->id) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Profile</a>
    </div>

    <!-- NET WORTH HIGHLIGHT BOX -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-white border-start border-success border-4 shadow-sm">
                <div class="card-body py-3 text-center">
                    <h6 class="mb-1 text-uppercase tracking-wide text-muted" style="font-size:0.75rem;">Total Assets</h6>
                    <h4 class="mb-0 fw-bold text-success">₱ {{ number_format($total_assets, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-white border-start border-danger border-4 shadow-sm mt-2 mt-md-0">
                <div class="card-body py-3 text-center">
                    <h6 class="mb-1 text-uppercase tracking-wide text-muted" style="font-size:0.75rem;">Total Liabilities</h6>
                    <h4 class="mb-0 fw-bold text-danger">₱ {{ number_format($total_liabilities, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-white border-start border-primary border-4 shadow-sm mt-2 mt-md-0">
                <div class="card-body py-3 text-center">
                    <h6 class="mb-1 text-uppercase tracking-wide text-muted" style="font-size:0.75rem;">Net Worth</h6>
                    <h4 class="mb-0 fw-bold text-primary">₱ {{ number_format($net_worth, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    @if(!$saln)
    <div class="alert alert-warning text-center shadow-sm">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> This employee has not submitted their SALN data yet.
    </div>
    @else
    <div class="d-flex justify-content-end mb-3 d-print-none">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print Official SALN
        </button>
    </div>
    
    <div id="saln-printable-area" class="bg-white p-4">
        <div class="card shadow-sm border-0 border-top border-4 border-accent">
        <div class="card-body p-0">
            <ul class="nav nav-tabs bg-light border-bottom pt-2 px-2 flex-nowrap overflow-auto" id="salnTabs" role="tablist" style="font-size: 0.85rem; white-space: nowrap;">
                <li class="nav-item"><button class="nav-link fw-bold text-dark active border-bottom-0" data-bs-toggle="tab" data-bs-target="#info" type="button">Basic Info & Children</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#assets" type="button">Assets</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#liabilities" type="button">Liabilities</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#business" type="button">Business Interests</button></li>
                <li class="nav-item"><button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#relatives" type="button">Relatives in Gov't</button></li>
            </ul>

            <div class="tab-content p-3 p-md-4">
                <!-- TAB 1: BASIC INFO -->
                <div class="tab-pane fade show active" id="info">
                    <div class="row align-items-center mb-4 border-bottom pb-3">
                        <div class="col-md-3 fw-bold text-muted">As of Date:</div>
                        <div class="col-md-3 fw-bold text-uppercase">{{ $saln_info->as_of_date ? \Carbon\Carbon::parse($saln_info->as_of_date)->format('m/d/Y') : 'N/A' }}</div>
                        <div class="col-md-3 fw-bold text-muted text-md-end mt-2 mt-md-0">Filing Type:</div>
                        <div class="col-md-3 fw-bold text-uppercase">{{ $saln_info->filing_type ?? 'N/A' }}</div>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="pds-section-card h-100 mb-0">
                                <div class="pds-section-header text-center">DECLARANT</div>
                                <div class="pds-section-body">
                                    <div class="row mb-2"><div class="col-4 text-muted small fw-bold">Full Name</div><div class="col-8 fw-bold text-uppercase border-bottom pb-1">{{ $saln_info->declarant_name ?? 'N/A' }}</div></div>
                                    <div class="row mb-2"><div class="col-4 text-muted small fw-bold">Address</div><div class="col-8 fw-bold text-uppercase border-bottom pb-1">{{ $saln_info->declarant_address ?? 'N/A' }}</div></div>
                                    <div class="row mb-2"><div class="col-4 text-muted small fw-bold">Position</div><div class="col-8 fw-bold text-uppercase border-bottom pb-1">{{ $saln_info->declarant_position ?? 'N/A' }}</div></div>
                                    <div class="row mb-2"><div class="col-4 text-muted small fw-bold">Agency/Office</div><div class="col-8 fw-bold text-uppercase border-bottom pb-1">{{ $saln_info->declarant_agency ?? 'N/A' }}</div></div>
                                    <div class="row mb-2"><div class="col-4 text-muted small fw-bold">Office Address</div><div class="col-8 fw-bold text-uppercase border-bottom pb-1">{{ $saln_info->declarant_office_address ?? 'N/A' }}</div></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="pds-section-card h-100 mb-0">
                                <div class="pds-section-header text-center">SPOUSE</div>
                                <div class="pds-section-body">
                                    <div class="row mb-2"><div class="col-4 text-muted small fw-bold">Full Name</div><div class="col-8 fw-bold text-uppercase border-bottom pb-1">{{ $saln_info->spouse_name ?? 'N/A' }}</div></div>
                                    <div class="row mb-2"><div class="col-4 text-muted small fw-bold">Position</div><div class="col-8 fw-bold text-uppercase border-bottom pb-1">{{ $saln_info->spouse_position ?? 'N/A' }}</div></div>
                                    <div class="row mb-2"><div class="col-4 text-muted small fw-bold">Agency/Office</div><div class="col-8 fw-bold text-uppercase border-bottom pb-1">{{ $saln_info->spouse_agency ?? 'N/A' }}</div></div>
                                    <div class="row mb-2"><div class="col-4 text-muted small fw-bold">Office Address</div><div class="col-8 fw-bold text-uppercase border-bottom pb-1">{{ $saln_info->spouse_office_address ?? 'N/A' }}</div></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pds-section-card mt-5">
                        <div class="pds-section-header">UNMARRIED CHILDREN BELOW EIGHTEEN (18) YEARS OF AGE</div>
                        <div class="pds-section-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center shadow-sm">
                                    <thead class="table-light text-muted">
                                        <tr>
                                            <th>Name</th>
                                            <th>Date of Birth</th>
                                            <th>Age</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($children as $child)
                                        <tr>
                                            <td class="text-start fw-bold text-uppercase">{{ $child->name }}</td>
                                            <td>{{ $child->date_of_birth }}</td>
                                            <td>{{ $child->age }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-muted py-3">No children recorded.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: ASSETS -->
                <div class="tab-pane fade" id="assets">
                    <div class="pds-section-card mb-4">
                        <div class="pds-section-header">1. REAL PROPERTIES</div>
                        <div class="pds-section-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center shadow-sm" style="font-size:0.8rem;">
                                    <thead class="table-light align-middle text-muted">
                                        <tr>
                                            <th>Description</th>
                                            <th>Kind</th>
                                            <th>Location</th>
                                            <th>Assessed Value</th>
                                            <th>Current Fair Market Value</th>
                                            <th>Acq. Year</th>
                                            <th>Acq. Mode</th>
                                            <th>Acq. Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($real_properties as $rp)
                                        <tr>
                                            <td class="fw-bold text-uppercase">{{ $rp->description }}</td>
                                            <td class="text-uppercase">{{ $rp->kind }}</td>
                                            <td class="text-uppercase">{{ $rp->exact_location }}</td>
                                            <td>₱{{ number_format($rp->assessed_value, 2) }}</td>
                                            <td>₱{{ number_format($rp->fair_market_value, 2) }}</td>
                                            <td>{{ $rp->acquisition_year }}</td>
                                            <td class="text-uppercase">{{ $rp->acquisition_mode }}</td>
                                            <td class="fw-bold text-success">₱{{ number_format($rp->acquisition_cost, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="8" class="text-muted py-3">No real properties recorded.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="pds-section-card">
                        <div class="pds-section-header">2. PERSONAL PROPERTIES</div>
                        <div class="pds-section-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center shadow-sm">
                                    <thead class="table-light text-muted">
                                        <tr>
                                            <th>Description</th>
                                            <th>Year Acquired</th>
                                            <th>Acquisition Cost/Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($personal_properties as $pp)
                                        <tr>
                                            <td class="text-start fw-bold text-uppercase">{{ $pp->description }}</td>
                                            <td>{{ $pp->year_acquired }}</td>
                                            <td class="fw-bold text-success">₱{{ number_format($pp->acquisition_cost, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-muted py-3">No personal properties recorded.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: LIABILITIES -->
                <div class="tab-pane fade" id="liabilities">
                    <div class="pds-section-card">
                        <div class="pds-section-header">LIABILITIES</div>
                        <div class="pds-section-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center shadow-sm">
                                    <thead class="table-light text-muted">
                                        <tr>
                                            <th>Nature</th>
                                            <th>Name of Creditors</th>
                                            <th>Outstanding Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($liabilities as $lia)
                                        <tr>
                                            <td class="text-start fw-bold text-uppercase">{{ $lia->nature }}</td>
                                            <td class="text-start text-uppercase">{{ $lia->name_of_creditors }}</td>
                                            <td class="fw-bold text-danger">₱{{ number_format($lia->outstanding_balance, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-muted py-3">No liabilities recorded.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: BUSINESS INTERESTS -->
                <div class="tab-pane fade" id="business">
                    <div class="pds-section-card">
                        <div class="pds-section-header">BUSINESS INTERESTS AND FINANCIAL CONNECTIONS</div>
                        <div class="pds-section-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center shadow-sm">
                                    <thead class="table-light text-muted">
                                        <tr>
                                            <th>Name of Entity/Business</th>
                                            <th>Business Address</th>
                                            <th>Nature of Business</th>
                                            <th>Date of Acquisition</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($businesses as $bus)
                                        <tr>
                                            <td class="text-start fw-bold text-uppercase">{{ $bus->business_name }}</td>
                                            <td class="text-uppercase">{{ $bus->business_address }}</td>
                                            <td class="text-uppercase">{{ $bus->nature_of_business }}</td>
                                            <td>{{ $bus->date_of_acquisition }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-muted py-3">No business interests recorded.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: RELATIVES -->
                <div class="tab-pane fade" id="relatives">
                    <div class="pds-section-card">
                        <div class="pds-section-header">RELATIVES IN THE GOVERNMENT SERVICE</div>
                        <div class="pds-section-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center shadow-sm">
                                    <thead class="table-light text-muted">
                                        <tr>
                                            <th>Name of Relative</th>
                                            <th>Relationship</th>
                                            <th>Position</th>
                                            <th>Name of Agency/Office and Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($relatives as $rel)
                                        <tr>
                                            <td class="text-start fw-bold text-uppercase">{{ $rel->relative_name }}</td>
                                            <td class="text-uppercase">{{ $rel->relationship }}</td>
                                            <td class="text-uppercase">{{ $rel->position }}</td>
                                            <td class="text-uppercase">{{ $rel->agency_address }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-muted py-3">No relatives in government service recorded.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- end tab-content -->
        </div> <!-- end card-body -->
    </div> <!-- end card -->
    </div> <!-- end saln-printable-area -->
    @endif
</div>

<style>
    @media print {
        /* Hide everything else on the page */
        body * {
            visibility: hidden;
        }

        /* Only show the SALN container and its children */
        #saln-printable-area, #saln-printable-area * {
            visibility: visible;
        }

        /* Reset the position to the top-left of the paper */
        #saln-printable-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Force A4 Legal size and remove browser margins/headers */
        @page {
            size: 8.5in 13in; /* Philippine Legal Size standard for SALN */
            margin: 0.5in;
        }

        /* Ensure Bootstrap background colors (like table headers) print accurately */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Show all tabs content during print */
        .tab-content > .tab-pane {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* Hide the navigation tabs and Net Worth highlight box */
        #salnTabs, .d-print-none, .row.mb-4 {
            display: none !important;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection