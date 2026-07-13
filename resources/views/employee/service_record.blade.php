@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <h4 class="text-accent fw-bold m-0"><i class="bi bi-file-earmark-person me-2"></i> My Service Record</h4>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print / Export PDF</button>
    </div>

    <!-- OFFICIAL DOCUMENT PREVIEW -->
    <div class="card shadow-sm border-0 border-top border-4 border-accent mb-5">
        <div class="card-body p-4 p-md-5">
            
            <div class="text-center mb-4">
                <h6 class="fw-bold mb-0">Republic of the Philippines</h6>
                <h6 class="fw-bold mb-0">Department of Education</h6>
                <p class="mb-0 small">REGION II - CAGAYAN VALLEY</p>
                <p class="mb-0 small">SCHOOLS DIVISION OF TUGUEGARAO CITY</p>
                <p class="mb-0 small">CAGAYAN NATIONAL HIGH SCHOOL</p>
                <h5 class="fw-bold mt-4 tracking-wide text-decoration-underline">SERVICE RECORD</h5>
            </div>

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

            <div class="table-responsive mt-3">
                <table class="table table-bordered table-sm align-middle text-center border-dark" style="font-size: 0.75rem;">
                    <thead class="align-middle fw-bold bg-light">
                        <tr>
                            <th colspan="2">SERVICES</th>
                            <th colspan="3">RECORD OF APPOINTMENT</th>
                            <th colspan="2">OFFICE/ENTITY/DIVISION</th>
                            <th>Leave of</th>
                            <th colspan="2">Separation</th>
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
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-muted py-3">No service records found.</td>
                        </tr>
                        @endforelse
                        <tr>
                            <td colspan="10" class="fw-bold text-center bg-light">*** NOTHING FOLLOWS ***</td>
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