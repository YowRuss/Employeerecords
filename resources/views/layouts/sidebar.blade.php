<aside class="sidebar" id="mainSidebar">
    <div class="position-relative py-2 text-center mb-1 d-flex flex-column align-items-center mx-2 mt-2">
        <!-- Close button for mobile -->
        <button class="btn btn-sm btn-light border-0 d-lg-none position-absolute rounded-circle shadow-sm d-flex align-items-center justify-content-center" 
                style="right: 5px; top: 0; width: 32px; height: 32px;" 
                onclick="closeMobileSidebar()">
            <i class="bi bi-x-lg text-secondary m-0"></i>
        </button>
        
        <img src="{{ asset('build/assets/images/logo.png') }}" alt="CNHS Logo" class="logo-large rounded-circle mb-2 shadow-sm border" style="width: 65px; height: 65px; object-fit: contain;">
        <img src="{{ asset('build/assets/images/logo.png') }}" alt="CNHS Logo Min" class="logo-small rounded-circle mb-1 shadow-sm border hide-on-expanded" style="width: 36px; height: 36px; object-fit: contain;">
        <h6 class="fw-bold m-0 hide-on-mini text-dark mt-1 tracking-tight" style="font-size: 0.95rem;">CNHS-JHS</h6>
    </div>
    <hr class="my-2 mx-3 border-secondary opacity-10">

    <nav class="nav flex-column mt-2 px-2">
        <a class="nav-link {{ request()->is('dashboard') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="/dashboard">
            <i class="bi bi-grid-1x2-fill fs-5"></i> <span class="hide-on-mini">Dashboard</span>
        </a>

        {{-- Role 1: Employee Navigation --}}
        @if(session('role_id') == 1)
        @php
            $isEmpPersonalInfoActive = request()->routeIs('pds.*', 'saln.*', 'service_record.*');
            $isEmpPayrollActive = request()->routeIs('employee.payroll.*', 'employee.loans.*', 'employee.attendance.*', 'employee.tax.*', 'leave.*');
            $empUnreadCount = \App\Models\HrMessage::where('employee_id', session('user_id'))
                                                ->where('sender_id', '!=', session('user_id'))
                                                ->where('is_read', 0)
                                                ->count();
        @endphp

        {{-- 1. Personal Info Dropdown --}}
        <div class="nav-item mb-1">
            <a class="nav-link {{ $isEmpPersonalInfoActive ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center justify-content-between px-3 py-2 rounded"
               data-bs-toggle="collapse"
               data-bs-target="#empPersonalInfoSubmenu"
               href="#empPersonalInfoSubmenu"
               role="button"
               aria-expanded="{{ $isEmpPersonalInfoActive ? 'true' : 'false' }}"
               aria-controls="empPersonalInfoSubmenu"
               style="cursor: pointer;">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-person-vcard-fill fs-5"></i>
                    <span class="hide-on-mini">Personal Info</span>
                </div>
                <i class="bi bi-chevron-down hide-on-mini submenu-arrow" style="font-size: 0.8rem; transition: transform 0.25s ease;"></i>
            </a>

            <div @class(['collapse', 'show' => $isEmpPersonalInfoActive]) id="empPersonalInfoSubmenu">
                <div class="py-1 ps-3 pe-1 ms-3 my-1 border-start border-2 hide-on-mini" style="border-color: rgba(26, 62, 111, 0.25) !important;">
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('pds.*') ? 'active bg-warning text-dark fw-bold' : 'text-secondary' }}"
                       href="{{ route('pds.edit') }}"
                       style="{{ request()->routeIs('pds.*') ? 'background-color: #ffc107; color: #212529 !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-person-vcard-fill {{ request()->routeIs('pds.*') ? 'text-dark' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>My PDS</span>
                    </a>

                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('saln.*') ? 'active bg-warning text-dark fw-bold' : 'text-secondary' }}"
                       href="{{ route('saln.index') }}"
                       style="{{ request()->routeIs('saln.*') ? 'background-color: #ffc107; color: #212529 !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-wallet-fill {{ request()->routeIs('saln.*') ? 'text-dark' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>My SALN</span>
                    </a>

                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('service_record.*') ? 'active bg-warning text-dark fw-bold' : 'text-secondary' }}"
                       href="{{ route('service_record.index') }}"
                       style="{{ request()->routeIs('service_record.*') ? 'background-color: #ffc107; color: #212529 !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-journal-text {{ request()->routeIs('service_record.*') ? 'text-dark' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Service Record</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- 2. Payroll Dropdown --}}
        <div class="nav-item mb-1">
            <a class="nav-link {{ $isEmpPayrollActive ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center justify-content-between px-3 py-2 rounded"
               data-bs-toggle="collapse"
               data-bs-target="#empPayrollSubmenu"
               href="#empPayrollSubmenu"
               role="button"
               aria-expanded="{{ $isEmpPayrollActive ? 'true' : 'false' }}"
               aria-controls="empPayrollSubmenu"
               style="cursor: pointer;">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-wallet-fill fs-5"></i>
                    <span class="hide-on-mini">Payroll</span>
                </div>
                <i class="bi bi-chevron-down hide-on-mini submenu-arrow" style="font-size: 0.8rem; transition: transform 0.25s ease;"></i>
            </a>

            <div @class(['collapse', 'show' => $isEmpPayrollActive]) id="empPayrollSubmenu">
                <div class="py-1 ps-3 pe-1 ms-3 my-1 border-start border-2 hide-on-mini" style="border-color: rgba(26, 62, 111, 0.25) !important;">
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('employee.payroll.*') ? 'active bg-warning text-dark fw-bold' : 'text-secondary' }}"
                       href="{{ route('employee.payroll.index') }}"
                       style="{{ request()->routeIs('employee.payroll.*') ? 'background-color: #ffc107; color: #212529 !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-receipt {{ request()->routeIs('employee.payroll.*') ? 'text-dark' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>My Payslips</span>
                    </a>

                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('employee.loans.*') ? 'active bg-warning text-dark fw-bold' : 'text-secondary' }}"
                       href="{{ route('employee.loans.index') }}"
                       style="{{ request()->routeIs('employee.loans.*') ? 'background-color: #ffc107; color: #212529 !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-bank {{ request()->routeIs('employee.loans.*') ? 'text-dark' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>My Loans</span>
                    </a>

                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('employee.attendance.*') ? 'active bg-warning text-dark fw-bold' : 'text-secondary' }}"
                       href="{{ route('employee.attendance.index') }}"
                       style="{{ request()->routeIs('employee.attendance.*') ? 'background-color: #ffc107; color: #212529 !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-clock-history {{ request()->routeIs('employee.attendance.*') ? 'text-dark' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>My Attendance</span>
                    </a>

                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('leave.*') ? 'active bg-warning text-dark fw-bold' : 'text-secondary' }}"
                       href="{{ route('leave.index') }}"
                       style="{{ request()->routeIs('leave.*') ? 'background-color: #ffc107; color: #212529 !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-calendar2-check-fill {{ request()->routeIs('leave.*') ? 'text-dark' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Leave Requests</span>
                    </a>

                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('employee.tax.*') ? 'active bg-warning text-dark fw-bold' : 'text-secondary' }}"
                       href="{{ route('employee.tax.index') }}"
                       style="{{ request()->routeIs('employee.tax.*') ? 'background-color: #ffc107; color: #212529 !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-file-earmark-pdf-fill {{ request()->routeIs('employee.tax.*') ? 'text-dark' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Tax Documents</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Standalone: Announcements --}}
        <a class="nav-link {{ request()->routeIs('employee.announcements') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('employee.announcements') }}">
            <i class="bi bi-megaphone-fill fs-5"></i> <span class="hide-on-mini">Announcements</span>
        </a>

        {{-- Standalone: Events --}}
        <a class="nav-link {{ request()->routeIs('employee.events') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('employee.events') }}">
            <i class="bi bi-calendar-event-fill fs-5"></i> <span class="hide-on-mini">Events</span>
        </a>

        {{-- Standalone: Message HR --}}
        <a class="nav-link {{ request()->routeIs('employee.chat', 'employee.chat.send') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('employee.chat') }}">
            <i class="bi bi-chat-dots-fill fs-5"></i> <span class="hide-on-mini">Message HR</span>
            @if($empUnreadCount > 0)
                <span class="badge bg-danger rounded-pill hide-on-mini shadow-sm ms-auto" style="font-size: 0.75rem;">
                    {{ $empUnreadCount }}
                </span>
            @endif
        </a>
        @endif

        {{-- Role 2: HR Navigation --}}
        @if(session('role_id') == 2)

        {{-- 1. Employee Info Dropdown Group (Collapsible) --}}
        @php
            $isEmployeeInfoActive = request()->routeIs('hr.staff_profiling', 'hr.leave.*', 'hr.service_record.*', 'requisitions.*', 'hr.settings.positions_areas', 'positions.*');
        @endphp
        <div class="nav-item mb-1">
            <a class="nav-link {{ $isEmployeeInfoActive ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center justify-content-between px-3 py-2 rounded" 
               data-bs-toggle="collapse" 
               href="#employeeInfoSubmenu" 
               role="button" 
               aria-expanded="{{ $isEmployeeInfoActive ? 'true' : 'false' }}" 
               aria-controls="employeeInfoSubmenu"
               style="cursor: pointer;">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-people-fill fs-5"></i>
                    <span class="hide-on-mini">Employee Info</span>
                </div>
                <i class="bi bi-chevron-down hide-on-mini submenu-arrow" style="font-size: 0.8rem; transition: transform 0.25s ease;"></i>
            </a>

            <div class="collapse {{ $isEmployeeInfoActive ? 'show' : '' }}" id="employeeInfoSubmenu">
                <div class="py-1 ps-3 pe-1 ms-3 my-1 border-start border-2 hide-on-mini" style="border-color: rgba(26, 62, 111, 0.25) !important;">
                    {{-- 1. Staff Profiling --}}
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('hr.staff_profiling') ? 'fw-bold text-white shadow-sm' : 'text-secondary' }}" 
                       href="{{ route('hr.staff_profiling') }}"
                       style="{{ request()->routeIs('hr.staff_profiling') ? 'background-color: #1A3E6F; color: #ffffff !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-person-lines-fill {{ request()->routeIs('hr.staff_profiling') ? 'text-white' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Staff Profiling</span>
                    </a>

                    {{-- 2. Leave Monitoring --}}
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('hr.leave.*') ? 'fw-bold text-white shadow-sm' : 'text-secondary' }}" 
                       href="{{ route('hr.leave.index') }}"
                       style="{{ request()->routeIs('hr.leave.*') ? 'background-color: #1A3E6F; color: #ffffff !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-calendar3-range-fill {{ request()->routeIs('hr.leave.*') ? 'text-white' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Leave Monitoring</span>
                    </a>

                    {{-- 3. Requisitions --}}
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('requisitions.*') ? 'fw-bold text-white shadow-sm' : 'text-secondary' }}" 
                       href="{{ route('requisitions.index') }}"
                       style="{{ request()->routeIs('requisitions.*') ? 'background-color: #1A3E6F; color: #ffffff !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-clipboard-data-fill {{ request()->routeIs('requisitions.*') ? 'text-white' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Requisitions</span>
                    </a>

                    {{-- 4. Positions & Areas --}}
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('hr.settings.positions_areas', 'positions.*') ? 'fw-bold text-white shadow-sm' : 'text-secondary' }}" 
                       href="{{ route('hr.settings.positions_areas') }}"
                       style="{{ request()->routeIs('hr.settings.positions_areas', 'positions.*') ? 'background-color: #1A3E6F; color: #ffffff !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-briefcase-fill {{ request()->routeIs('hr.settings.positions_areas', 'positions.*') ? 'text-white' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Positions & Areas</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- 3. Payroll Dropdown Group (Collapsible) --}}
        @php
            $isPayrollActive = request()->routeIs('hr.payroll.*', 'hr.bir2316.*', 'hr.settings.deductions.*', 'payroll.salary_settings', 'payroll.loans.*', 'payroll.remittances.*', 'payroll.allowances.*', 'payroll.attendance.*', 'payroll.holidays.*');
        @endphp
        <div class="nav-item mb-1">
            <a class="nav-link {{ $isPayrollActive ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center justify-content-between px-3 py-2 rounded" 
               data-bs-toggle="collapse" 
               href="#payrollSubmenu" 
               role="button" 
               aria-expanded="{{ $isPayrollActive ? 'true' : 'false' }}" 
               aria-controls="payrollSubmenu"
               style="cursor: pointer;">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-wallet-fill fs-5"></i>
                    <span class="hide-on-mini">Payroll</span>
                </div>
                <i class="bi bi-chevron-down hide-on-mini submenu-arrow" style="font-size: 0.8rem; transition: transform 0.25s ease;"></i>
            </a>

            <div class="collapse {{ $isPayrollActive ? 'show' : '' }}" id="payrollSubmenu">
                <div class="py-1 ps-3 pe-1 ms-3 my-1 border-start border-2 hide-on-mini" style="border-color: rgba(26, 62, 111, 0.25) !important;">
                    {{-- 1. Payroll Management --}}
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('hr.payroll.index', 'hr.payroll.show') ? 'fw-bold text-white shadow-sm' : 'text-secondary' }}" 
                       href="{{ route('hr.payroll.index') }}"
                       style="{{ request()->routeIs('hr.payroll.index', 'hr.payroll.show') ? 'background-color: #1A3E6F; color: #ffffff !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-cash-stack {{ request()->routeIs('hr.payroll.index', 'hr.payroll.show') ? 'text-white' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Payroll Management</span>
                    </a>

                    {{-- 2. Employee Profiles --}}
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('hr.payroll.employees') ? 'fw-bold text-white shadow-sm' : 'text-secondary' }}" 
                       href="{{ route('hr.payroll.employees') }}"
                       style="{{ request()->routeIs('hr.payroll.employees') ? 'background-color: #1A3E6F; color: #ffffff !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-person-badge-fill {{ request()->routeIs('hr.payroll.employees') ? 'text-white' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Employee Profiles</span>
                    </a>

                    {{-- 3. Holiday Calendar --}}
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('payroll.holidays.*') ? 'fw-bold text-white shadow-sm' : 'text-secondary' }}"
                       href="{{ route('payroll.holidays.index') }}"
                       style="{{ request()->routeIs('payroll.holidays.*') ? 'background-color: #1A3E6F; color: #ffffff !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-calendar2-week-fill {{ request()->routeIs('payroll.holidays.*') ? 'text-white' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Holiday Calendar</span>
                    </a>

                    {{-- 4. Payroll Settings --}}
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('hr.settings.deductions.*') ? 'fw-bold text-white shadow-sm' : 'text-secondary' }}" 
                       href="{{ route('hr.settings.deductions.index') }}"
                       style="{{ request()->routeIs('hr.settings.deductions.*') ? 'background-color: #1A3E6F; color: #ffffff !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-sliders {{ request()->routeIs('hr.settings.deductions.*') ? 'text-white' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Payroll Settings</span>
                    </a>

                    {{-- 5. Loan Management --}}
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('payroll.loans.*') ? 'fw-bold text-white shadow-sm' : 'text-secondary' }}" 
                       href="{{ route('payroll.loans.index') }}"
                       style="{{ request()->routeIs('payroll.loans.*') ? 'background-color: #1A3E6F; color: #ffffff !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-bank {{ request()->routeIs('payroll.loans.*') ? 'text-white' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Loan Management</span>
                    </a>

                    {{-- 5. Statutory Remittances --}}
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('payroll.remittances.*') ? 'fw-bold text-white shadow-sm' : 'text-secondary' }}" 
                       href="{{ route('payroll.remittances.index') }}"
                       style="{{ request()->routeIs('payroll.remittances.*') ? 'background-color: #1A3E6F; color: #ffffff !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-building-check {{ request()->routeIs('payroll.remittances.*') ? 'text-white' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Statutory Remittances</span>
                    </a>

                    {{-- 6. BIR Form 2316 --}}
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('hr.bir2316.*') ? 'fw-bold text-white shadow-sm' : 'text-secondary' }}" 
                       href="{{ route('hr.bir2316.index') }}"
                       style="{{ request()->routeIs('hr.bir2316.*') ? 'background-color: #1A3E6F; color: #ffffff !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-file-earmark-pdf-fill {{ request()->routeIs('hr.bir2316.*') ? 'text-white' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>BIR Form 2316</span>
                    </a>

                    {{-- 7. Allowances & Other Incomes --}}
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('payroll.allowances.*') ? 'fw-bold text-white shadow-sm' : 'text-secondary' }}" 
                       href="{{ route('payroll.allowances.index') }}"
                       style="{{ request()->routeIs('payroll.allowances.*') ? 'background-color: #1A3E6F; color: #ffffff !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-coin {{ request()->routeIs('payroll.allowances.*') ? 'text-white' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Allowances & Other Incomes</span>
                    </a>

                    {{-- 8. Attendance & Lates --}}
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('payroll.attendance.*') ? 'fw-bold text-white shadow-sm' : 'text-secondary' }}" 
                       href="{{ route('payroll.attendance.index') }}"
                       style="{{ request()->routeIs('payroll.attendance.*') ? 'background-color: #1A3E6F; color: #ffffff !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-clock-history {{ request()->routeIs('payroll.attendance.*') ? 'text-white' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Attendance & Lates</span>
                    </a>

                    {{-- 9. Salary Settings --}}
                    <a class="nav-link py-2 px-3 mb-1 rounded d-flex align-items-center gap-2 sidebar-sub-link {{ request()->routeIs('payroll.salary_settings') ? 'fw-bold text-white shadow-sm' : 'text-secondary' }}" 
                       href="{{ route('payroll.salary_settings') }}"
                       style="{{ request()->routeIs('payroll.salary_settings') ? 'background-color: #1A3E6F; color: #ffffff !important;' : '' }} font-size: 0.875rem; margin: 0.2rem 0;">
                        <i class="bi bi-cash-coin {{ request()->routeIs('payroll.salary_settings') ? 'text-white' : 'text-muted' }}" style="font-size: 1rem; margin-right: 0;"></i>
                        <span>Salary Settings</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- 4. Helpdesk Messages --}}
        @php
            $hrUnreadCount = \App\Models\HrMessage::where('sender_id', '!=', session('user_id'))
                                                ->where('is_read', 0)
                                                ->count();
        @endphp

        <a class="nav-link {{ request()->routeIs('hr.chat.*') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('hr.chat.inbox') }}">
            <i class="bi bi-inbox-fill fs-5"></i> <span class="hide-on-mini">Helpdesk Messages</span>
            @if($hrUnreadCount > 0)
                <span class="badge bg-danger rounded-pill hide-on-mini shadow-sm ms-auto" style="font-size: 0.75rem;">
                    {{ $hrUnreadCount }}
                </span>
            @endif
        </a>
        @endif

        {{-- Role 3: Admin Navigation --}}
        @if(session('role_id') == 3)
        <a class="nav-link text-muted d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="#"><i class="bi bi-briefcase-fill fs-5"></i> <span class="hide-on-mini">Manage Positions</span></a>
        <a class="nav-link text-muted d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="#"><i class="bi bi-database-fill-gear fs-5"></i> <span class="hide-on-mini">Data Maintenance</span></a>
        <a class="nav-link {{ request()->routeIs('hr.settings.deductions.*') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('hr.settings.deductions.index') }}">
            <i class="bi bi-sliders fs-5"></i> <span class="hide-on-mini">Payroll Settings</span>
        </a>
        @endif
    </nav>
</aside>

<style>
    /* Chevron rotation for collapsible sidebar menus */
    .nav-link[aria-expanded="true"] .submenu-arrow {
        transform: rotate(180deg) !important;
    }
    .sidebar-sub-link:hover {
        background-color: rgba(26, 62, 111, 0.08) !important;
        color: #1A3E6F !important;
    }
    .sidebar-sub-link.active:hover {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }
</style>
