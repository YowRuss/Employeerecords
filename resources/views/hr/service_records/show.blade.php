@extends('layouts.app')

@section('content')
<style>
    /* DepEd Accent Yellow Button */
    .btn-yellow-action {
        background-color: #FDE047 !important;
        color: #1F2937 !important;
        border: 1px solid #EAB308 !important;
        font-weight: 700 !important;
        border-radius: 50rem !important;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        text-decoration: none;
        white-space: nowrap !important;
    }
    .btn-yellow-action:hover {
        background-color: #EAB308 !important;
        color: #1F2937 !important;
        border-color: #CA8A04 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .btn-yellow-action:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    }

    /* Primary Blue Action Button */
    .btn-blue-action {
        background-color: #1A3E6F !important;
        color: #ffffff !important;
        border: 1px solid #143056 !important;
        font-weight: 700 !important;
        border-radius: 50rem !important;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        text-decoration: none;
        white-space: nowrap !important;
    }
    .btn-blue-action:hover {
        background-color: #143056 !important;
        color: #ffffff !important;
        border-color: #0f233f !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .btn-blue-action:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    }

    /* Table styling & mobile responsiveness */
    .service-record-table {
        min-width: 860px;
        width: 100%;
    }
    .service-record-table th,
    .service-record-table td {
        vertical-align: middle;
        white-space: nowrap;
    }
    .service-record-table td.col-designation,
    .service-record-table td.col-station {
        white-space: normal;
        min-width: 130px;
    }
    .service-record-table thead th {
        font-size: 0.78rem;
    }

    /* Touch scrollbar */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    @media (max-width: 767.98px) {
        .service-record-paper {
            padding: 1.25rem 1rem !important;
        }
        .sr-header-title {
            font-size: 1.2rem !important;
        }
        .sr-doc-title {
            font-size: 1.1rem !important;
            margin-top: 1rem !important;
        }
    }

    @media (min-width: 992px) {
        .border-start-lg {
            border-left: 1px solid #dee2e6 !important;
        }
        .bg-lg-transparent {
            background-color: transparent !important;
        }
        .border-lg-0 {
            border: 0 !important;
        }
    }

    @media print {
        .topbar, #mainSidebar, .d-print-none, .btn {
            display: none !important;
        }
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
        }
        .card {
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>

<div class="container-fluid px-2 px-sm-3 py-3">
    <!-- Header & Toolbar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <div class="mb-2">
                <a href="{{ route('hr.staff_profiling') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center">
                    <i class="bi bi-arrow-left me-1"></i> Back to Staff Profiling
                </a>
            </div>
            <h4 class="text-header-blue fw-bold mb-0 sr-header-title">
                <i class="bi bi-file-earmark-person me-2 text-header-blue"></i> Service Record: {{ $user->first_name }} {{ $user->last_name }}
            </h4>
        </div>
        <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto d-print-none">
            <button type="button" class="btn btn-blue-action px-4 py-2 shadow-sm d-inline-flex justify-content-center align-items-center w-100 w-md-auto text-nowrap" data-bs-toggle="modal" data-bs-target="#addServiceRecordModal">
                <i class="bi bi-plus-circle me-2"></i> Add Service Record Entry
            </button>
            <a href="{{ route('hr.service_records.print', $user->id) }}" class="btn btn-yellow-action px-4 py-2 shadow-sm d-inline-flex justify-content-center align-items-center w-100 w-md-auto text-nowrap">
                <i class="bi bi-printer me-2"></i> Print Service Record
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- OFFICIAL DOCUMENT PREVIEW (PAPER CONTAINER) -->
    <div class="card shadow-sm border-0 mx-auto mb-4 mb-md-5 rounded-3" style="max-width: 1000px; background-color: #ffffff;">
        <div class="card-body p-3 p-sm-4 p-md-5 service-record-paper">

            <!-- Document Header -->
            <div class="text-center mb-4">
                <h6 class="fw-bold mb-0 text-dark">Republic of the Philippines</h6>
                <h6 class="fw-bold mb-1 text-dark">Department of Education</h6>
                <p class="mb-0 small text-muted">REGION II - CAGAYAN VALLEY</p>
                <p class="mb-0 small text-muted">SCHOOLS DIVISION OF TUGUEGARAO CITY</p>
                <p class="mb-0 small text-muted">CAGAYAN NATIONAL HIGH SCHOOL</p>
                <h5 class="fw-bold mt-3 mt-md-4 tracking-wide text-decoration-underline text-uppercase text-dark sr-doc-title">SERVICE RECORD</h5>
            </div>

            <!-- Demographics Section (Name/Birth) -->
            <div class="row g-3 mb-4" style="font-size: 0.9rem;">
                <div class="col-12 col-lg-8">
                    <!-- Name Row -->
                    <div class="mb-3">
                        <div class="fw-bold text-uppercase mb-2">NAME:</div>
                        <div class="row g-2">
                            <div class="col-12 col-md-4 mb-3 mb-md-0">
                                <div class="border-bottom border-dark text-center fw-bold text-uppercase py-1 text-truncate" title="{{ $user->last_name }}">
                                    {{ $user->last_name }}
                                </div>
                                <div class="text-center text-muted" style="font-size: 0.75rem;">(Surname)</div>
                            </div>
                            <div class="col-12 col-md-4 mb-3 mb-md-0">
                                <div class="border-bottom border-dark text-center fw-bold text-uppercase py-1 text-truncate" title="{{ $user->first_name }}">
                                    {{ $user->first_name }}
                                </div>
                                <div class="text-center text-muted" style="font-size: 0.75rem;">(Given Name)</div>
                            </div>
                            <div class="col-12 col-md-4 mb-3 mb-md-0">
                                <div class="border-bottom border-dark text-center fw-bold text-uppercase py-1">
                                    {{ $user->middle_name ? (strlen($user->middle_name) > 2 ? substr($user->middle_name, 0, 1) . '.' : $user->middle_name) : '-' }}
                                </div>
                                <div class="text-center text-muted" style="font-size: 0.75rem;">(M.I.)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Birth Row -->
                    <div class="mb-2">
                        <div class="fw-bold text-uppercase mb-2">BIRTH:</div>
                        <div class="row g-2">
                            <div class="col-12 col-md-6 mb-3 mb-md-0">
                                <div class="border-bottom border-dark text-center fw-bold py-1">
                                    {{ isset($personal_info->date_of_birth) ? \Carbon\Carbon::parse($personal_info->date_of_birth)->format('F d, Y') : 'N/A' }}
                                </div>
                                <div class="text-center text-muted" style="font-size: 0.75rem;">(Date of Birth)</div>
                            </div>
                            <div class="col-12 col-md-6 mb-3 mb-md-0">
                                <div class="border-bottom border-dark text-center fw-bold text-uppercase py-1 text-truncate" title="{{ $personal_info->place_of_birth ?? 'N/A' }}">
                                    {{ $personal_info->place_of_birth ?? 'N/A' }}
                                </div>
                                <div class="text-center text-muted" style="font-size: 0.75rem;">(Place of Birth)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instruction Note -->
                <div class="col-12 col-lg-4 mt-3 mt-lg-0 d-flex align-items-center">
                    <div class="p-3 p-lg-0 ps-lg-3 border-start-lg border border-lg-0 rounded-3 bg-light bg-lg-transparent w-100">
                        <p class="small text-muted mb-0 fst-italic">
                            (If married woman, give also maiden name. Date herein should be checked from birth or baptismal certificate or some other reliable documents.)
                        </p>
                    </div>
                </div>
            </div>

            <p class="small text-justify" style="text-indent: 2rem; line-height: 1.6;">
                This is to certify that the employee named herein above actually rendered services in this Office as shown in the Service Record below each line of which is supported by appointments and other papers actually issued by this Office and approved by the authorities concerned.
            </p>

            <!-- Mobile Scroll Indicator (Visible only on mobile/tablet) -->
            <div class="d-md-none mb-2 py-2 px-3 bg-light rounded-3 border d-flex align-items-center justify-content-between text-muted small d-print-none">
                <span class="d-flex align-items-center"><i class="bi bi-arrow-left-right text-primary me-2"></i> Swipe horizontally to view full table</span>
                <span class="badge bg-secondary">11 Columns</span>
            </div>

            <!-- Data Table -->
            <div class="table-responsive mt-2">
                <table class="table table-bordered table-hover align-middle table-sm text-center text-uppercase service-record-table mb-0" style="font-size: 0.85rem;">
                    <thead class="align-middle fw-bold bg-light">
                        <tr>
                            <th colspan="2" class="table-light">SERVICES</th>
                            <th colspan="3" class="table-light">RECORD OF APPOINTMENT</th>
                            <th colspan="2" class="table-light">OFFICE/ENTITY/DIVISION</th>
                            <th rowspan="2" class="table-light">Leave of<br>Absence<br>w/out pay</th>
                            <th colspan="2" class="table-light">Separation</th>
                            <th rowspan="3" class="table-light d-print-none text-center" style="width: 50px;">Actions</th>
                        </tr>
                        <tr>
                            <th colspan="2" class="table-light">Inclusive Dates</th>
                            <th rowspan="2" class="table-light">Designation</th>
                            <th rowspan="2" class="table-light">Status</th>
                            <th rowspan="2" class="table-light">Salary</th>
                            <th rowspan="2" class="table-light">Station/Place</th>
                            <th rowspan="2" class="table-light">Branch</th>
                            <th rowspan="2" class="table-light">Date</th>
                            <th rowspan="2" class="table-light">Cause</th>
                        </tr>
                        <tr>
                            <th class="table-light">From</th>
                            <th class="table-light">To</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($record->date_from)->format('m/d/y') }}</td>
                            <td>{{ $record->date_to == 'PRESENT' ? 'Present' : (\Carbon\Carbon::hasFormat($record->date_to, 'Y-m-d') ? \Carbon\Carbon::parse($record->date_to)->format('m/d/y') : $record->date_to) }}</td>
                            <td class="text-start col-designation">{{ $record->designation }}</td>
                            <td>{{ $record->status }}</td>
                            <td>{{ $record->salary }}</td>
                            <td class="col-station">{{ $record->station_place }}</td>
                            <td>{{ $record->branch }}</td>
                            <td>{{ $record->leave_without_pay }}</td>
                            <td>{{ $record->separation_date }}</td>
                            <td>{{ $record->separation_cause }}</td>
                            <td class="d-print-none text-center">
                                <form action="{{ route('hr.service_record.destroy', $record->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this service record entry?');" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0 d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px;" title="Delete Entry">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-muted py-4">No service records found.</td>
                        </tr>
                        @endforelse
                        <tr>
                            <td colspan="11" class="fw-bold text-center bg-light">*** NOTHING FOLLOWS ***</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="small text-muted mt-2">
                Issued in compliance with Executive Order No. 54 dated August 10, 1954, and in accordance with Circular no. 58, dated August 10, 1954 of the system.
            </p>

            <div class="row mt-4 mt-md-5 pt-3">
                <div class="col-5 col-sm-6">
                    <p class="small fw-bold mb-0">Not valid</p>
                    <p class="small fw-bold">w/out seal</p>
                </div>
                <div class="col-7 col-sm-6 text-center text-sm-end text-md-center">
                    <p class="fw-bold mb-4 mb-md-5">CERTIFIED CORRECT:</p>
                    <h6 class="fw-bold mb-0 text-decoration-underline mt-4">CARMEN A. ACAIN</h6>
                    <p class="small mb-0">Secondary School Principal IV</p>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ADD SERVICE RECORD MODAL -->
<div class="modal fade" id="addServiceRecordModal" tabindex="-1" aria-labelledby="addServiceRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('hr.service_record.store', $user->id) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="addServiceRecordModalLabel" style="color: #1A3E6F;"><i class="bi bi-plus-circle me-1"></i> Add Service Record Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-sm-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Date From <span class="text-danger">*</span></label>
                            <input type="date" name="date_from" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Date To <span class="text-danger">*</span></label>
                            <input type="text" name="date_to" class="form-control text-uppercase" placeholder="YYYY-MM-DD or Present" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Designation <span class="text-danger">*</span></label>
                            <select name="designation" id="sr_designation_select" class="form-select text-uppercase searchable-dropdown" required>
                                <option value="" disabled selected>Select Position...</option>
                                @foreach($positions as $pos)
                                <option value="{{ $pos->position_name }}">{{ $pos->position_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Status <span class="text-danger">*</span></label>
                            <input type="text" name="status" class="form-control text-uppercase" placeholder="e.g. Perm" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Salary <span class="text-danger">*</span></label>
                            <input type="text" name="salary" class="form-control" placeholder="e.g. 239,280.00" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Station/Place <span class="text-danger">*</span></label>
                            <input type="text" name="station_place" class="form-control text-uppercase" placeholder="-do- or location name" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Branch</label>
                            <input type="text" name="branch" class="form-control text-uppercase" placeholder="-do- or Nat'l">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Leave w/out pay</label>
                            <input type="text" name="leave_without_pay" class="form-control text-uppercase" placeholder="None or -do-">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Separation Date</label>
                            <input type="text" name="separation_date" class="form-control text-uppercase">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Separation Cause</label>
                            <input type="text" name="separation_cause" class="form-control text-uppercase" placeholder="None or NBC 562">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 border-top-0 d-flex flex-column flex-sm-row justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary px-4 fw-bold rounded-pill w-100 w-sm-auto order-2 order-sm-1" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-yellow-action px-4 py-2 shadow-sm w-100 w-sm-auto order-1 order-sm-2">Save Entry</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#sr_designation_select').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Search & select position...',
        allowClear: true,
        dropdownParent: $('#addServiceRecordModal')
    });

    $('#addServiceRecordModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $('#sr_designation_select').val(null).trigger('change');
    });
});
</script>
@endsection