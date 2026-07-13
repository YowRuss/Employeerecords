@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="text-accent fw-bold m-0">
            <i class="bi bi-file-earmark-person me-2"></i> Service Record: {{ $user->first_name }} {{ $user->last_name }}
        </h4>
        <div>
            <!-- Button for HR to go back to employee list -->
            <a href="#" class="btn btn-outline-secondary btn-sm me-2"><i class="bi bi-arrow-left"></i> Back to Employee List</a>
            <button class="btn btn-accent btn-sm fw-bold shadow-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print Document</button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- ADD RECORD FORM (Visible to HR, Hidden when printing) -->
    <div class="card bg-light border-0 shadow-sm border-start border-4 border-accent d-print-none mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-1"></i> Add Service Record Entry</h6>
            <!-- Posts to the HR Store Route -->
            <form action="{{ route('hr.service_record.store', $user->id) }}" method="POST">
                @csrf
                <div class="row g-2 mb-2">
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Date From <span class="text-danger">*</span></label>
                        <input type="date" name="date_from" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Date To <span class="text-danger">*</span></label>
                        <input type="text" name="date_to" class="form-control form-control-sm text-uppercase" placeholder="YYYY-MM-DD or Present" required>
                    </div>
                    <!-- Replace your old Designation input with this Dropdown -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Designation <span class="text-danger">*</span></label>
                        <select name="designation" class="form-select form-select-sm text-uppercase" required>
                            <option value="" disabled selected>Select Position...</option>
                            @foreach($positions as $pos)
                            <option value="{{ $pos->position_name }}">{{ $pos->position_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Status <span class="text-danger">*</span></label>
                        <input type="text" name="status" class="form-control form-control-sm text-uppercase" placeholder="e.g. Perm" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Salary <span class="text-danger">*</span></label>
                        <input type="text" name="salary" class="form-control form-control-sm" placeholder="e.g. 239,280.00" required>
                    </div>
                </div>

                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Station/Place <span class="text-danger">*</span></label>
                        <input type="text" name="station_place" class="form-control form-control-sm text-uppercase" placeholder="-do- or location name" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Branch</label>
                        <input type="text" name="branch" class="form-control form-control-sm text-uppercase" placeholder="-do- or Nat'l">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Leave w/out pay</label>
                        <input type="text" name="leave_without_pay" class="form-control form-control-sm text-uppercase" placeholder="None or -do-">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Separation Date</label>
                        <input type="text" name="separation_date" class="form-control form-control-sm text-uppercase">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Separation Cause</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="separation_cause" class="form-control text-uppercase" placeholder="None or NBC 562">
                            <button type="submit" class="btn btn-accent fw-bold px-3">Add Entry</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- OFFICIAL DOCUMENT PREVIEW -->
    <div class="card shadow-sm border-0 border-top border-4 border-accent mb-5">
        <div class="card-body p-4 p-md-5">

            <!-- Document Header -->
            <div class="text-center mb-4">
                <h6 class="fw-bold mb-0">Republic of the Philippines</h6>
                <h6 class="fw-bold mb-0">Department of Education</h6>
                <p class="mb-0 small">REGION II - CAGAYAN VALLEY</p>
                <p class="mb-0 small">SCHOOLS DIVISION OF TUGUEGARAO CITY</p>
                <p class="mb-0 small">CAGAYAN NATIONAL HIGH SCHOOL</p>
                <h5 class="fw-bold mt-4 tracking-wide text-decoration-underline">SERVICE RECORD</h5>
            </div>

            <!-- Employee Info Header -->
            <div class="row mb-4" style="font-size: 0.9rem;">
                <div class="col-md-7">
                    <table class="table table-borderless table-sm mb-1">
                        <tr>
                            <td width="15%" class="fw-bold text-end">NAME:</td>
                            <td class="fw-bold border-bottom border-dark text-uppercase text-center">{{ $user->last_name }}</td>
                            <td class="fw-bold border-bottom border-dark text-uppercase text-center">{{ $user->first_name }}</td>
                            <td class="fw-bold border-bottom border-dark text-uppercase text-center">{{ $user->middle_name ?? '' }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="text-center small text-muted">(Surname)</td>
                            <td class="text-center small text-muted">(Given Name)</td>
                            <td class="text-center small text-muted">(M.I)</td>
                        </tr>
                    </table>
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td width="15%" class="fw-bold text-end">BIRTH:</td>
                            <td class="fw-bold border-bottom border-dark text-center">{{ isset($personal_info->date_of_birth) ? \Carbon\Carbon::parse($personal_info->date_of_birth)->format('F d, Y') : 'N/A' }}</td>
                            <td class="fw-bold border-bottom border-dark text-center text-uppercase">{{ $personal_info->place_of_birth ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="text-center small text-muted">(Date of Birth)</td>
                            <td class="text-center small text-muted">(Place of Birth)</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-5 d-flex align-items-center">
                    <p class="small text-muted mb-0 fst-italic">
                        (If married woman, give also maiden name. Date herein should be checked from birth or baptismal certificate or some other reliable documents.)
                    </p>
                </div>
            </div>

            <p class="small text-justify" style="text-indent: 2rem;">
                This is to certify that the employee named herein above actually rendered services in this Office as shown in the Service Record below each line of which is supported by appointments and other papers actually issued by this Office and approved by the authorities concerned.
            </p>

            <!-- Data Table -->
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-sm align-middle text-center border-dark" style="font-size: 0.75rem;">
                    <thead class="align-middle fw-bold bg-light">
                        <tr>
                            <th colspan="2">SERVICES</th>
                            <th colspan="3">RECORD OF APPOINTMENT</th>
                            <th colspan="2">OFFICE/ENTITY/DIVISION</th>
                            <th>Leave of</th>
                            <th colspan="2">Separation</th>
                            <th width="3%" class="d-print-none"></th>
                        </tr>
                        <tr>
                            <th colspan="2">Inclusive Dates</th>
                            <th rowspan="2">Designation</th>
                            <th rowspan="2">Status</th>
                            <th rowspan="2">Salary</th>
                            <th rowspan="2">Station/Place</th>
                            <th rowspan="2">Branch</th>
                            <th rowspan="2">absences<br>w/out pay</th>
                            <th rowspan="2">Date</th>
                            <th rowspan="2">Cause</th>
                            <th rowspan="2" class="d-print-none"></th>
                        </tr>
                        <tr>
                            <th>From</th>
                            <th>To</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($record->date_from)->format('m/d/y') }}</td>
                            <td>{{ $record->date_to == 'PRESENT' ? 'Present' : (\Carbon\Carbon::hasFormat($record->date_to, 'Y-m-d') ? \Carbon\Carbon::parse($record->date_to)->format('m/d/y') : $record->date_to) }}</td>
                            <td class="text-start">{{ $record->designation }}</td>
                            <td>{{ $record->status }}</td>
                            <td>{{ $record->salary }}</td>
                            <td>{{ $record->station_place }}</td>
                            <td>{{ $record->branch }}</td>
                            <td>{{ $record->leave_without_pay }}</td>
                            <td>{{ $record->separation_date }}</td>
                            <td>{{ $record->separation_cause }}</td>
                            <td class="d-print-none">
                                <!-- HR DELETE ROUTE -->
                                <form action="{{ route('hr.service_record.destroy', $record->id) }}" method="POST" onsubmit="return confirm('Delete this record?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0 m-0"><i class="bi bi-x-circle"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-muted py-3">No service records found.</td>
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

            <div class="row mt-5 pt-3">
                <div class="col-6">
                    <p class="small fw-bold mb-0">Not valid</p>
                    <p class="small fw-bold">w/out seal</p>
                </div>
                <div class="col-6 text-center">
                    <p class="fw-bold mb-4">CERTIFIED CORRECT:</p>
                    <h6 class="fw-bold mb-0 text-decoration-underline mt-5">CARMEN A. ACAIN</h6>
                    <p class="small">Secondary School Principal IV</p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection