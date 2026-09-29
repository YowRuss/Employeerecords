<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>CNHS-JHS Employee Records System</title>
    
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
                CNHS-JHS <span style="background: linear-gradient(135deg, var(--accent-yellow-dark), #b45309); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Employee Records</span>
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

    @include('layouts.sidebar')

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

        function collapseToggleFor(menu) {
            const parentId = menu.getAttribute('id');
            if (!parentId || !sidebar) {
                return null;
            }

            return sidebar.querySelector('[data-bs-target="#' + parentId + '"], [href="#' + parentId + '"]');
        }

        function closeSidebarDropdowns() {
            if (!sidebar) {
                return;
            }

            sidebar.querySelectorAll('.collapse.show').forEach(menu => {
                if (window.bootstrap && bootstrap.Collapse) {
                    bootstrap.Collapse.getOrCreateInstance(menu, { toggle: false }).hide();
                } else {
                    menu.classList.remove('show');
                }

                const toggle = collapseToggleFor(menu);
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        }

        function expandSidebar() {
            if (!sidebar) {
                return;
            }

            sidebar.classList.remove('minimized');
            content.classList.remove('expanded');
            topbar.classList.remove('expanded');
            localStorage.setItem('sidebarState', 'expanded');
        }

        if (localStorage.getItem('sidebarState') === 'minimized' && window.innerWidth > 992) {
            sidebar.classList.add('minimized');
            content.classList.add('expanded');
            topbar.classList.add('expanded');
            closeSidebarDropdowns();
        }

        if (sidebar) {
            sidebar.querySelectorAll('[data-bs-toggle="collapse"]').forEach(dropdown => {
                dropdown.addEventListener('click', function () {
                    if (window.innerWidth > 992 && sidebar.classList.contains('minimized')) {
                        expandSidebar();
                    }
                });
            });
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
            sidebarToggle.addEventListener('click', (event) => {
                event.preventDefault();

                if (window.innerWidth > 992) {
                    sidebar.classList.toggle('minimized');
                    content.classList.toggle('expanded');
                    topbar.classList.toggle('expanded');

                    if (sidebar.classList.contains('minimized')) {
                        localStorage.setItem('sidebarState', 'minimized');
                        closeSidebarDropdowns();
                    } else {
                        localStorage.setItem('sidebarState', 'expanded');
                    }
                } else {
                    toggleMobileSidebar();
                }
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeMobileSidebar);
        }

        // Close the mobile drawer after a real navigation link, not a dropdown toggle.
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 992 && link.getAttribute('data-bs-toggle') !== 'collapse') {
                    closeMobileSidebar();
                }
            });
        });

        // Force reload if page is restored from bfcache (browser back/forward button after session destroy)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>

    @yield('scripts')
</body>

</html>