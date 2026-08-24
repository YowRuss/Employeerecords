<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CNHS-JHS HR System</title>
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('build/assets/images/logo.png') }}" type="image/png">

    <!-- External Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- Our Custom CSS -->
    <link rel="stylesheet" href="{{ asset('build/assets/css/style.css') }}">
</head>

<body class="preload">

    @php
    $currentUser = \Illuminate\Support\Facades\DB::table('users')->where('id', session('user_id'))->first();
    @endphp

    <header class="topbar" id="mainTopbar">
        <div class="d-flex align-items-center">
            <button class="btn btn-light border-0 me-3 d-flex align-items-center justify-content-center shadow-sm" id="sidebarToggle" style="border-radius: 8px; padding: 0.4rem 0.6rem;">
                <i class="bi bi-list fs-4 text-dark mb-0"></i>
            </button>

            <h5 class="m-0 fw-bolder text-nowrap d-none d-sm-block" style="background: linear-gradient(135deg, #0f172a, #334155); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: -0.5px; font-size: 1.2rem;">
                CNHS-JHS <span style="background: linear-gradient(135deg, var(--accent-yellow-dark), #b45309); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">HR System</span>
            </h5>
        </div>

        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle profile-dropdown p-1 pe-3 rounded-pill bg-light border transition" id="userMenu" data-bs-toggle="dropdown" style="transition: all 0.2s;">
                @if($currentUser && $currentUser->profile_image)
                <img src="data:{{ $currentUser->image_type }};base64,{{ base64_encode($currentUser->profile_image) }}" class="rounded-circle shadow-sm me-2" alt="Profile" style="width: 36px; height: 36px; object-fit: cover; border: 2px solid white;">
                @else
                <div class="rounded-circle bg-accent shadow-sm text-dark d-flex align-items-center justify-content-center fw-bold me-2" style="width: 36px; height: 36px; font-size: 0.9rem; border: 2px solid white;">
                    {{ strtoupper(substr(session('full_name'), 0, 1)) }}
                </div>
                @endif
                <span class="d-none d-md-block text-dark small fw-bold text-nowrap">{{ session('full_name') }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end mt-2 shadow-sm border-0">
                <li>
                    <h6 class="dropdown-header">Manage Account</h6>
                </li>
                <li><a class="dropdown-item py-2" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle me-2 text-muted"></i> My Profile</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" class="px-2 m-0">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold rounded-3 py-2"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </header>

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

            @if(session('role_id') == 1)
            <a class="nav-link {{ request()->routeIs('pds.edit', 'pds.update') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('pds.edit') }}">
                <i class="bi bi-person-vcard-fill fs-5"></i> <span class="hide-on-mini">My PDS</span>
            </a>
            <a class="nav-link {{ request()->routeIs('saln.index') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('saln.index') }}">
                <i class="bi bi-wallet-fill fs-5"></i> <span class="hide-on-mini">My SALN</span>
            </a>
            <a class="nav-link {{ request()->routeIs('leave.index', 'leave.store') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('leave.index') }}">
                <i class="bi bi-calendar2-check-fill fs-5"></i> <span class="hide-on-mini">Leave Requests</span>
            </a>
            <a class="nav-link {{ request()->routeIs('service_record.index', 'service_record.store', 'service_record.destroy') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('service_record.index') }}">
                <i class="bi bi-journal-text fs-5"></i> <span class="hide-on-mini">Service Record</span>
            </a>
            <a class="nav-link {{ request()->routeIs('employee.announcements') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('employee.announcements') }}">
                <i class="bi bi-megaphone-fill fs-5"></i> <span class="hide-on-mini">Announcements</span>
            </a>
            <a class="nav-link {{ request()->routeIs('employee.events') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('employee.events') }}">
                <i class="bi bi-calendar-event-fill fs-5"></i> <span class="hide-on-mini">Events</span>
            </a>
            @if(session('role_id') == 1)
                @php
                    // Count unread messages inside this specific employee's chat room that were sent by HR
                    $empUnreadCount = \App\Models\HrMessage::where('employee_id', session('user_id'))
                                                        ->where('sender_id', '!=', session('user_id'))
                                                        ->where('is_read', 0)
                                                        ->count();
                @endphp

                <a class="nav-link {{ request()->routeIs('employee.chat') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('employee.chat') }}">
                    <i class="bi bi-chat-dots-fill fs-5"></i> <span class="hide-on-mini">Message HR</span>
                    
                    <!-- Only show the badge if there is a reply from HR -->
                    @if($empUnreadCount > 0)
                        <span class="badge bg-danger rounded-pill hide-on-mini shadow-sm ms-auto" style="font-size: 0.75rem;">
                            {{ $empUnreadCount }}
                        </span>
                    @endif
                </a>
            @endif
            @endif

            @if(session('role_id') == 2)
            <a class="nav-link {{ request()->routeIs('hr.staff_profiling') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('hr.staff_profiling') }}">
                <i class="bi bi-people-fill fs-5"></i> <span class="hide-on-mini">Staff Profiling</span>
            </a>
            <a class="nav-link {{ request()->routeIs('hr.leave.index') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('hr.leave.index') }}">
                <i class="bi bi-calendar3-range-fill fs-5"></i> <span class="hide-on-mini">Leave Monitoring</span>
            </a>
            <a class="nav-link {{ request()->routeIs('hr.service_record.directory', 'hr.service_record.index') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('hr.service_record.directory') }}">
                <i class="bi bi-folder-fill fs-5"></i> <span class="hide-on-mini">Service Records</span>
            </a>
            <a class="nav-link {{ request()->routeIs('requisitions.*') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('requisitions.index') }}">
                <i class="bi bi-clipboard-data-fill fs-5"></i> <span class="hide-on-mini">Requisitions</span>
            </a>
            <a class="nav-link {{ request()->routeIs('hr.applications.*') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('hr.applications.index') }}">
                <i class="bi bi-person-lines-fill fs-5"></i> <span class="hide-on-mini">Job Applicants</span>
            </a>
            <a class="nav-link {{ request()->routeIs('hr.job_postings.*') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('hr.job_postings.index') }}">
                <i class="bi bi-briefcase-fill fs-5"></i> <span class="hide-on-mini">Job Postings</span>
            </a>
            
            <a class="nav-link {{ request()->routeIs('hr.settings.positions_areas') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('hr.settings.positions_areas') }}">
                <i class="bi bi-gear-fill fs-5"></i> <span class="hide-on-mini">Positions & Areas</span>
            </a>

            <a class="nav-link {{ request()->routeIs('announcements.*') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('announcements.index') }}">
                <i class="bi bi-megaphone-fill fs-5"></i> <span class="hide-on-mini">Announcements</span>
            </a>

            <a class="nav-link {{ request()->routeIs('events.*') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('events.index') }}">
                <i class="bi bi-calendar-event-fill fs-5"></i> <span class="hide-on-mini">Events</span>
            </a>

            <a class="nav-link {{ request()->routeIs('hr.reports.*') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('hr.reports.index') }}">
                <i class="bi bi-bar-chart-fill fs-5"></i> <span class="hide-on-mini">System Reports</span>
            </a>

            @if(session('role_id') == 2)
                @php
                    // Count all unread messages sent by employees (not sent by the logged-in HR)
                    $hrUnreadCount = \App\Models\HrMessage::where('sender_id', '!=', session('user_id'))
                                                        ->where('is_read', 0)
                                                        ->count();
                @endphp

                <a class="nav-link {{ request()->routeIs('hr.chat.*') ? 'active bg-warning text-dark fw-bold' : 'text-muted' }} d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="{{ route('hr.chat.inbox') }}">
                    <i class="bi bi-inbox-fill fs-5"></i> <span class="hide-on-mini">Helpdesk Messages</span>
                    
                    <!-- Only show the badge if there are unread messages -->
                    @if($hrUnreadCount > 0)
                        <span class="badge bg-danger rounded-pill hide-on-mini shadow-sm ms-auto" style="font-size: 0.75rem;">
                            {{ $hrUnreadCount }}
                        </span>
                    @endif
                </a>
            @endif
            @endif

            @if(session('role_id') == 3)
            <a class="nav-link text-muted d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="#"><i class="bi bi-briefcase-fill fs-5"></i> <span class="hide-on-mini">Manage Positions</span></a>
            <a class="nav-link text-muted d-flex align-items-center gap-3 px-3 py-2 mb-1 rounded" href="#"><i class="bi bi-database-fill-gear fs-5"></i> <span class="hide-on-mini">Data Maintenance</span></a>
            @endif
        </nav>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content" id="mainContent">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.searchable-dropdown').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#addServiceRecordModal')
            });
        });

        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('mainSidebar');
        const topbar = document.getElementById('mainTopbar');
        const content = document.getElementById('mainContent');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (localStorage.getItem('sidebarState') === 'minimized' && window.innerWidth > 992) {
            sidebar.classList.add('minimized');
            content.classList.add('expanded');
            topbar.classList.add('expanded');
        }

        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.body.classList.remove('preload');
            }, 100);
        });

        function toggleMobileSidebar() {
            sidebar.classList.toggle('show');
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('show');
            }
        }

        function closeMobileSidebar() {
            sidebar.classList.remove('show');
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('show');
            }
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                if (window.innerWidth > 992) {
                    sidebar.classList.toggle('minimized');
                    content.classList.toggle('expanded');
                    topbar.classList.toggle('expanded');
                    localStorage.setItem('sidebarState', sidebar.classList.contains('minimized') ? 'minimized' : 'expanded');
                } else {
                    toggleMobileSidebar();
                }
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeMobileSidebar);
        }

        // Close mobile drawer when clicking nav links on small screens
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 992) {
                    closeMobileSidebar();
                }
            });
        });
    </script>
</body>

</html>