<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CNHS-JHS HR System</title>

    <!-- External Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- ADD THIS LINE FOR THE BOOTSTRAP 5 THEME -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.z/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- Our Custom CSS -->
    <link rel="stylesheet" href="{{ asset('build/assets/css/style.css') }}">
</head>

<body class="preload">

    @php
    $currentUser = \Illuminate\Support\Facades\DB::table('users')->where('id', session('user_id'))->first();
    @endphp

    <header class="topbar" id="mainTopbar">
        <div class="d-flex align-items-center">
            <button class="btn btn-light border-0 me-2 d-flex align-items-center justify-content-center" id="sidebarToggle" style="background: transparent; padding: 0.5rem;">
                <i class="bi bi-list fs-4 text-dark mb-0"></i>
            </button>

            <img src="{{ asset('build/assets/logo.png') }}" alt="Logo" class="rounded-circle me-2 shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">

            <h5 class="m-0 fw-bold text-accent hide-on-mini">CNHS-JHS HR System</h5>
        </div>

        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle profile-dropdown" id="userMenu" data-bs-toggle="dropdown">
                <span class="me-2 d-none d-md-block text-dark small fw-bold">{{ session('full_name') }}</span>
                @if($currentUser && $currentUser->profile_image)
                <img src="data:{{ $currentUser->image_type }};base64,{{ base64_encode($currentUser->profile_image) }}" class="rounded-circle shadow-sm" alt="Profile">
                @else
                <div class="rounded-circle bg-accent text-dark d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.9rem;">
                    {{ strtoupper(substr(session('full_name'), 0, 1)) }}
                </div>
                @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border border-opacity-50 mt-2">
                <li>
                    <h6 class="dropdown-header">Manage Account</h6>
                </li>
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle me-2 text-accent"></i> My Profile</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" class="px-2">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm w-100"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </header>

    <aside class="sidebar" id="mainSidebar">
        <div class="py-4 text-center border-bottom border-opacity-50 mb-3 d-flex flex-column align-items-center mx-3">

            <img src="{{ asset('build/assets/logo.png') }}" alt="CNHS Logo" class="rounded-circle shadow-sm logo-large mb-2" style="width: 80px; height: 80px; object-fit: cover;">

            <img src="{{ asset('build/assets/logo.png') }}" alt="CNHS Logo Min" class="rounded-circle shadow-sm logo-small mb-1" style="width: 40px; height: 40px; object-fit: cover;">

            <h6 class="fw-bold m-0 hide-on-mini text-accent mt-2">CNHS-JHS</h6>
        </div>

        <nav class="nav flex-column">
            <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">
                <i class="bi bi-house-door-fill"></i> <span class="hide-on-mini">Dashboard</span>
            </a>

            @if(session('role_id') == 1)
            <a class="nav-link {{ request()->routeIs('pds.edit', 'pds.update') ? 'active' : '' }}" href="{{ route('pds.edit') }}">
                <i class="bi bi-person-vcard"></i> <span class="hide-on-mini">My PDS</span>
            </a>
            <a class="nav-link {{ request()->routeIs('saln.edit', 'saln.update') ? 'active' : '' }}" href="{{ route('saln.index') }}">
                <i class="bi bi-cash-coin"></i> <span class="hide-on-mini">My SALN</span>
            </a>
            <a class="nav-link {{ request()->routeIs('leave.index', 'leave.store') ? 'active' : '' }}" href="{{ route('leave.index') }}">
                <i class="bi bi-calendar-event"></i> <span class="hide-on-mini">Leave Requests</span>
            </a>
            <a class="nav-link {{ request()->routeIs('service_record.index', 'service_record.store', 'service_record.destroy') ? 'active' : '' }}" href="{{ route('service_record.index') }}">
                <i class="bi bi-card-list"></i> <span class="hide-on-mini">My Service Record</span>
            </a>
            @endif

            @if(session('role_id') == 2)
            <a class="nav-link {{ request()->routeIs('hr.staff_profiling') ? 'active' : '' }}" href="{{ route('hr.staff_profiling') }}">
                <i class="bi bi-people-fill"></i> <span class="hide-on-mini">Staff Profiling</span>
            </a>
            <a class="nav-link {{ request()->routeIs('hr.leave.index') ? 'active' : '' }}" href="{{ route('hr.leave.index') }}">
                <i class="bi bi-inboxes-fill"></i> <span class="hide-on-mini">Leave Monitoring</span>
            </a>
            <a class="nav-link {{ request()->routeIs('hr.service_record.directory', 'hr.service_record.index') ? 'active' : '' }}" href="{{ route('hr.service_record.directory') }}">
                <i class="bi bi-folder2-open"></i> <span class="hide-on-mini">Service Records</span>
            </a>
            @endif

            @if(session('role_id') == 3)
            <a class="nav-link" href="#"><i class="bi bi-gear-fill"></i> <span class="hide-on-mini">Manage Positions</span></a>
            <a class="nav-link" href="#"><i class="bi bi-database-fill"></i> <span class="hide-on-mini">Database Maintenance</span></a>
            @endif

            @if(session('role_id') == 4)
            <a class="nav-link {{ request()->routeIs('leaves.monitor') ? 'active' : '' }}" href="{{ route('leaves.monitor') }}"><i class="bi bi-check-circle-fill"></i> <span class="hide-on-mini">Leave Approvals</span></a>
            <a class="nav-link" href="#"><i class="bi bi-file-earmark-bar-graph-fill"></i> <span class="hide-on-mini">Institutional Reports</span></a>
            @endif
        </nav>
    </aside>

    <main class="main-content" id="mainContent">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (Required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Initialize the Searchable Dropdown -->
    <script>
        $(document).ready(function() {
            $('.searchable-dropdown').select2({
                theme: 'bootstrap-5', // <--- ADD THIS LINE HERE!
                width: '100%',
                dropdownParent: $('#addServiceRecordModal') // Important: Fixes search box inside Bootstrap Modals
            });
        });
    </script>

    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('mainSidebar');
        const topbar = document.getElementById('mainTopbar');
        const content = document.getElementById('mainContent');

        // 1. Check the browser's memory when the page loads
        if (localStorage.getItem('sidebarState') === 'minimized' && window.innerWidth > 992) {
            sidebar.classList.add('minimized');
            content.classList.add('expanded');
            topbar.classList.add('expanded');
        }

        // 2. Remove the preload class AFTER the page renders to enable smooth animations again
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.body.classList.remove('preload');
            }, 100); // 100ms is enough to skip the load flash
        });

        // 3. Handle the click and save the choice to memory
        sidebarToggle.addEventListener('click', () => {
            if (window.innerWidth > 992) {
                sidebar.classList.toggle('minimized');
                content.classList.toggle('expanded');
                topbar.classList.toggle('expanded');

                // Save the new state to localStorage
                if (sidebar.classList.contains('minimized')) {
                    localStorage.setItem('sidebarState', 'minimized');
                } else {
                    localStorage.setItem('sidebarState', 'expanded');
                }
            } else {
                sidebar.classList.toggle('show');
            }
        });
    </script>
</body>

</html>