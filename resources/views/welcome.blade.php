<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CNHS-JHS Employee Records System | Cagayan National High School - Junior High School</title>
    <meta name="description" content="Official Employee Records Management System of Cagayan National High School - Junior High School (CNHS-JHS). DepEd Region II and Civil Service Commission aligned.">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('build/assets/images/logo.png') }}" type="image/png">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom Landing Page Stylesheet -->
    <link rel="stylesheet" href="{{ asset('build/assets/css/welcome.css') }}">
</head>
<body>

    <!-- Sticky Glassmorphic Navbar -->
    <nav class="navbar navbar-expand-lg navbar-glass sticky-top py-3" id="mainNav">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-3 text-decoration-none" href="{{ route('home') }}">
                <img src="{{ asset('build/assets/images/logo.png') }}" alt="CNHS-JHS Seal" class="brand-logo-img">
                <div>
                    <div class="brand-title">CNHS-JHS Employee Records</div>
                    <div class="brand-subtitle">Cagayan National High School</div>
                </div>
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#landingNavbar" aria-controls="landingNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-2 text-dark"></i>
            </button>

            <div class="collapse navbar-collapse" id="landingNavbar">
                <ul class="navbar-nav mx-auto align-items-center gap-1 mt-3 mt-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="#modules">Core Modules</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="#roles">Portals & Roles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="#compliance">Governance & Standards</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="#faq">FAQ</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                    @if(session()->has('user_id'))
                        <a href="{{ route('dashboard') }}" class="btn btn-brand rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-2">
                            <i class="bi bi-speedometer2"></i>
                            <span>Go to Dashboard</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-brand rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-2">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span>Employee Sign In</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="hero">
        <!-- Glowing Ambient Mesh Orbs -->
        <div class="ambient-orb orb-hero-1"></div>
        <div class="ambient-orb orb-hero-2"></div>
        <div class="ambient-orb orb-hero-3"></div>

        <div class="container hero-content">
            <div class="row align-items-center g-5">
                
                <!-- Left Column: Value Proposition & Call to Action -->
                <div class="col-lg-6">
                    <div class="hero-badge-container mb-3">
                        <span class="pulse-dot"></span>
                        <span>DepEd Region II &bull; Division of Cagayan &bull; CSC Aligned</span>
                    </div>

                    <h1 class="hero-title mb-4">
                        Digital Personnel Records Built for <span class="text-highlight">Educational Excellence</span>.
                    </h1>

                    <p class="hero-subtitle mb-5">
                        The unified <strong>Employee Records Management System</strong> of <strong>Cagayan National High School - Junior High School</strong>. Automating CSC Form 212 Personal Data Sheets, CS Form 6 Leave monitoring, plantilla service records, and DepEd payroll with institutional precision.
                    </p>

                    <div class="d-flex flex-wrap gap-3 align-items-center mb-5">
                        @if(session()->has('user_id'))
                            <a href="{{ route('dashboard') }}" class="btn btn-brand btn-lg rounded-pill px-4 py-3 fw-bold d-inline-flex align-items-center gap-2">
                                <i class="bi bi-speedometer2 fs-5"></i>
                                <span>Access Dashboard</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-brand btn-lg rounded-pill px-4 py-3 fw-bold d-inline-flex align-items-center gap-2">
                                <i class="bi bi-person-fill-lock fs-5"></i>
                                <span>Access Employee Portal</span>
                            </a>
                        @endif

                        <a href="#modules" class="btn btn-outline-brand btn-lg rounded-pill px-4 py-3 fw-bold d-inline-flex align-items-center gap-2">
                            <i class="bi bi-grid-3x3-gap-fill fs-5"></i>
                            <span>Explore Core Modules</span>
                        </a>
                    </div>

                    <!-- Mini Highlight Trophies -->
                    <div class="row g-3 pt-2 border-top border-secondary border-opacity-10">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-patch-check-fill text-warning fs-4"></i>
                                <span class="small fw-bold text-secondary">CSC Form 212 (Rev. 2017) Digital Engine</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-shield-fill-check text-success fs-4"></i>
                                <span class="small fw-bold text-secondary">RA 10173 Data Privacy Protected</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Interactive System Interface Mockup -->
                <div class="col-lg-6 hero-mockup-wrapper">
                    <div class="position-relative">
                        
                        <!-- Floating Badges -->
                        <div class="floating-hero-badge badge-hero-top">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(234, 179, 8, 0.15); color: #ca8a04;">
                                <i class="bi bi-award-fill fs-5"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.72rem; color: #64748b; text-transform: uppercase;">Civil Service</div>
                                <div class="fw-bold" style="color: #0f172a;">MC No. 41 Compliant</div>
                            </div>
                        </div>

                        <div class="floating-hero-badge badge-hero-bottom">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.15); color: #10b981;">
                                <i class="bi bi-file-earmark-check-fill fs-5"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.72rem; color: #64748b; text-transform: uppercase;">Automated Export</div>
                                <div class="fw-bold" style="color: #0f172a;">Official PDF & Excel Sheets</div>
                            </div>
                        </div>

                        <!-- System Mockup Interface Card -->
                        <div class="system-mockup-card">
                            <div class="mockup-header">
                                <div class="mockup-dots">
                                    <span class="mockup-dot red"></span>
                                    <span class="mockup-dot yellow"></span>
                                    <span class="mockup-dot green"></span>
                                </div>
                                <div class="mockup-title">
                                    <i class="bi bi-building text-warning"></i>
                                    <span>CNHS-JHS Employee Records System &bull; Central Console</span>
                                </div>
                                <div>
                                    <span class="badge-live-status">
                                        <span class="pulse-dot" style="width: 7px; height: 7px;"></span> Live
                                    </span>
                                </div>
                            </div>

                            <div class="mockup-body">
                                <!-- Top Status Bar -->
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="mockup-stat-pill">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="small text-muted fw-bold">ACTIVE FACULTY</span>
                                                <i class="bi bi-people-fill text-primary"></i>
                                            </div>
                                            <div class="fs-4 fw-bolder text-dark mt-1">{{ $totalStaff ?? 14 }} <span class="fs-6 text-muted fw-normal">Staff</span></div>
                                            <div class="small text-success fw-semibold" style="font-size: 0.75rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i>{{ $teachingCount ?? 11 }} Teaching &bull; {{ $nonTeachingCount ?? 3 }} Non-Teaching
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mockup-stat-pill">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="small text-muted fw-bold">LEAVE CREDITS</span>
                                                <i class="bi bi-calendar-check-fill text-warning"></i>
                                            </div>
                                            <div class="fs-4 fw-bolder text-dark mt-1">35.75 <span class="fs-6 text-muted fw-normal">Days</span></div>
                                            <div class="small text-muted" style="font-size: 0.75rem;">
                                                VL: 18.25 &bull; SL: 17.50 (Good Standing)
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Feature Preview Cards -->
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="mockup-mini-card">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <div class="mockup-icon-box bg-warning bg-opacity-10 text-warning">
                                                    <i class="bi bi-person-vcard-fill"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold small text-dark">CSC Form 212</div>
                                                    <div class="text-muted style-tiny" style="font-size: 0.7rem;">PDS Complete</div>
                                                </div>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="mockup-mini-card">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <div class="mockup-icon-box bg-success bg-opacity-10 text-success">
                                                    <i class="bi bi-shield-check"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold small text-dark">SALN RA 6713</div>
                                                    <div class="text-muted style-tiny" style="font-size: 0.7rem;">Annual Audit Filed</div>
                                                </div>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="mockup-mini-card">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="mockup-icon-box bg-primary bg-opacity-10 text-primary">
                                                    <i class="bi bi-journal-text"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold small text-dark">DepEd Plantilla</div>
                                                    <div class="text-muted style-tiny" style="font-size: 0.72rem;">Step 2 &bull; SG 13 &bull; Cagayan</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="mockup-mini-card">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="mockup-icon-box bg-danger bg-opacity-10 text-danger">
                                                    <i class="bi bi-cash-stack"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold small text-dark">Payroll & 2316</div>
                                                    <div class="text-muted style-tiny" style="font-size: 0.72rem;">GSIS & Tax Verified</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Announcement Bar Inside Mockup -->
                                <div class="mt-3 p-2 px-3 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2 text-truncate">
                                        <i class="bi bi-megaphone-fill text-warning"></i>
                                        <span class="small fw-semibold text-secondary text-truncate">Division Memorandum: Annual Records Validation</span>
                                    </div>
                                    <span class="badge bg-white text-dark border ms-2" style="font-size: 0.65rem;">DepEd</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Key Institutional Statistics Section -->
    <section class="stats-section py-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="stat-number">100+</div>
                        <div class="stat-label">Faculty & Personnel</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Civil Service Aligned</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-calendar2-range-fill"></i>
                        </div>
                        <div class="stat-number">15+</div>
                        <div class="stat-label">Automated Leave Types</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stats-card">
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Digital Self-Service</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Modules Bento Grid Section -->
    <section class="py-5 bg-light" id="modules">
        <div class="container py-4">
            <div class="text-center section-header mb-5">
                <div class="section-tag">
                    <i class="bi bi-grid-fill"></i> System Architecture
                </div>
                <h2 class="section-title mb-3">Comprehensive Digital HR Architecture</h2>
                <p class="section-subtitle">
                    Engineered specifically for Cagayan National High School - JHS to eliminate manual paper trails, expedite approvals, and ensure institutional compliance.
                </p>
            </div>

            <!-- Bento Grid of 8 Core Modules -->
            <div class="row g-4">
                
                <!-- Module 1: PDS CSC Form 212 (Featured Large Card) -->
                <div class="col-lg-8">
                    <div class="bento-card bento-card-highlight">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="module-icon-wrapper bg-warning bg-opacity-15 text-warning">
                                <i class="bi bi-person-lines-fill"></i>
                            </div>
                            <span class="module-badge-tag">CSC Form 212 Rev. 2017</span>
                        </div>
                        <h4 class="module-title">Personal Data Sheet (PDS) Digital Engine</h4>
                        <p class="module-desc">
                            Complete digitisation of the official Civil Service Personal Data Sheet with 4 comprehensive parts and 11 sub-modules. Enables faculty and staff to maintain real-time personal information, civil service eligibility, work experience, learning & development, and character references with instant official government PDF exports.
                        </p>
                        <div class="row g-2 mt-auto pt-3 border-top">
                            <div class="col-md-6">
                                <ul class="module-features-list">
                                    <li><i class="bi bi-check-circle-fill"></i> Personal, Family & Dependents Registry</li>
                                    <li><i class="bi bi-check-circle-fill"></i> Civil Service & PRC Licensure Records</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="module-features-list">
                                    <li><i class="bi bi-check-circle-fill"></i> Work Experience & Voluntary Work Tracking</li>
                                    <li><i class="bi bi-check-circle-fill"></i> One-Click Official DepEd PDF Export</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Module 2: SALN RA 6713 -->
                <div class="col-lg-4">
                    <div class="bento-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="module-icon-wrapper bg-success bg-opacity-15 text-success">
                                <i class="bi bi-bank2"></i>
                            </div>
                            <span class="module-badge-tag">RA 6713 Compliance</span>
                        </div>
                        <h4 class="module-title">Annual SALN Management</h4>
                        <p class="module-desc">
                            Statement of Assets, Liabilities, and Net Worth management module adhering strictly to Republic Act No. 6713.
                        </p>
                        <ul class="module-features-list mt-auto">
                            <li><i class="bi bi-check-circle-fill"></i> Real Property & Personal Asset Ledger</li>
                            <li><i class="bi bi-check-circle-fill"></i> Automated Net Worth Calculation</li>
                            <li><i class="bi bi-check-circle-fill"></i> Official Word (.docx) & PDF Generation</li>
                        </ul>
                    </div>
                </div>

                <!-- Module 3: Leave Administration & Monitoring (Featured Card) -->
                <div class="col-lg-4">
                    <div class="bento-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="module-icon-wrapper bg-primary bg-opacity-15 text-primary">
                                <i class="bi bi-calendar-check-fill"></i>
                            </div>
                            <span class="module-badge-tag">CSC Rule XVI</span>
                        </div>
                        <h4 class="module-title">Leave Credits & CS Form 6</h4>
                        <p class="module-desc">
                            Automated monthly credit accrual (1.25 VL / 1.25 SL) with digital Form 6 routing, approval chains, and balance ledgers.
                        </p>
                        <ul class="module-features-list mt-auto">
                            <li><i class="bi bi-check-circle-fill"></i> 15+ Civil Service Leave Categories</li>
                            <li><i class="bi bi-check-circle-fill"></i> Teaching Seminar Credit Conversions</li>
                            <li><i class="bi bi-check-circle-fill"></i> Instant Printable Leave Approvals</li>
                        </ul>
                    </div>
                </div>

                <!-- Module 4: Official DepEd Service Records -->
                <div class="col-lg-4">
                    <div class="bento-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="module-icon-wrapper bg-info bg-opacity-15 text-info">
                                <i class="bi bi-card-checklist"></i>
                            </div>
                            <span class="module-badge-tag">DepEd & GSIS</span>
                        </div>
                        <h4 class="module-title">Official Service Records</h4>
                        <p class="module-desc">
                            Historical career ledger tracking plantilla appointments, promotions, salary grades, and step increments.
                        </p>
                        <ul class="module-features-list mt-auto">
                            <li><i class="bi bi-check-circle-fill"></i> Chronological Service History</li>
                            <li><i class="bi bi-check-circle-fill"></i> Automatic Step Increment Tracking</li>
                            <li><i class="bi bi-check-circle-fill"></i> DepEd-Formatted Excel Export</li>
                        </ul>
                    </div>
                </div>

                <!-- Module 5: Staff Profiling & JHS Learning Areas -->
                <div class="col-lg-4">
                    <div class="bento-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="module-icon-wrapper bg-danger bg-opacity-15 text-danger">
                                <i class="bi bi-mortarboard-fill"></i>
                            </div>
                            <span class="module-badge-tag">Curriculum Aligned</span>
                        </div>
                        <h4 class="module-title">Staff Profiling & Learning Areas</h4>
                        <p class="module-desc">
                            Specialized classification for Junior High School disciplines: English, Math, Science, AP, Filipino, MAPEH, TLE, and ESP.
                        </p>
                        <ul class="module-features-list mt-auto">
                            <li><i class="bi bi-check-circle-fill"></i> Teaching vs. Non-Teaching Segregation</li>
                            <li><i class="bi bi-check-circle-fill"></i> Departmental Specialization Directory</li>
                            <li><i class="bi bi-check-circle-fill"></i> Personnel Reassignment & Offboarding</li>
                        </ul>
                    </div>
                </div>

                <!-- Module 6: DepEd Payroll & BIR Form 2316 -->
                <div class="col-lg-4">
                    <div class="bento-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="module-icon-wrapper bg-warning bg-opacity-15 text-warning">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                            <span class="module-badge-tag">DepEd Payroll Engine</span>
                        </div>
                        <h4 class="module-title">Payroll & BIR Form 2316</h4>
                        <p class="module-desc">
                            Automated payroll period generation with mandatory GSIS, PhilHealth, Pag-IBIG contributions, and withholding taxes.
                        </p>
                        <ul class="module-features-list mt-auto">
                            <li><i class="bi bi-check-circle-fill"></i> Encrypted Online Employee Payslips</li>
                            <li><i class="bi bi-check-circle-fill"></i> Custom Deductions & Loan Management</li>
                            <li><i class="bi bi-check-circle-fill"></i> BIR 2316 Annual Tax PDF Generation</li>
                        </ul>
                    </div>
                </div>

                <!-- Module 7: Announcements & Event Attendance -->
                <div class="col-lg-4">
                    <div class="bento-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="module-icon-wrapper bg-success bg-opacity-15 text-success">
                                <i class="bi bi-megaphone-fill"></i>
                            </div>
                            <span class="module-badge-tag">Campus Broadcasts</span>
                        </div>
                        <h4 class="module-title">Announcements & Events</h4>
                        <p class="module-desc">
                            Digital circulars with read-receipt acknowledgment tracking, institutional seminars, and attendee registration.
                        </p>
                        <ul class="module-features-list mt-auto">
                            <li><i class="bi bi-check-circle-fill"></i> Pinned Priority Bulletins</li>
                            <li><i class="bi bi-check-circle-fill"></i> Mandatory Acknowledgment Receipts</li>
                            <li><i class="bi bi-check-circle-fill"></i> Seminar & Meeting Attendance Tracking</li>
                        </ul>
                    </div>
                </div>

                <!-- Module 8: HR Helpdesk & Personnel Inquiries -->
                <div class="col-lg-4">
                    <div class="bento-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="module-icon-wrapper bg-primary bg-opacity-15 text-primary">
                                <i class="bi bi-chat-dots-fill"></i>
                            </div>
                            <span class="module-badge-tag">Direct Communication</span>
                        </div>
                        <h4 class="module-title">HR Inquiries Helpdesk</h4>
                        <p class="module-desc">
                            A secure, private messaging bridge connecting teachers and non-teaching personnel directly with the HR administration.
                        </p>
                        <ul class="module-features-list mt-auto">
                            <li><i class="bi bi-check-circle-fill"></i> Confidential Records Inquiries</li>
                            <li><i class="bi bi-check-circle-fill"></i> Document Request Acceleration</li>
                            <li><i class="bi bi-check-circle-fill"></i> Real-Time Status Feedback</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Role-Based Experience Showcase -->
    <section class="py-5 bg-white" id="roles">
        <div class="container py-4">
            <div class="text-center section-header mb-5">
                <div class="section-tag">
                    <i class="bi bi-people-fill"></i> User Portals
                </div>
                <h2 class="section-title mb-3">Tailored Experience for Every Stakeholder</h2>
                <p class="section-subtitle">
                    Whether you are an educator in the classroom, a non-teaching support specialist, or an HR administrator, the portal adapts to your workflow.
                </p>

                <!-- Role Nav Pills (Teaching, Non-Teaching, HR Admin) -->
                <div class="role-tabs mt-4 d-inline-block">
                    <ul class="nav nav-pills" id="roleTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="teaching-tab" data-bs-toggle="pill" data-bs-target="#teaching" type="button" role="tab">Teaching Faculty</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="non-teaching-tab" data-bs-toggle="pill" data-bs-target="#non-teaching" type="button" role="tab">Non-Teaching Staff</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="hr-admin-tab" data-bs-toggle="pill" data-bs-target="#hr-admin" type="button" role="tab">HR & Administrators</button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tab-content mt-4" id="roleTabContent">
                
                <!-- Tab 1: Teaching Faculty -->
                <div class="tab-pane fade show active" id="teaching" role="tabpanel" aria-labelledby="teaching-tab">
                    <div class="role-card">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-6">
                                <h3 class="fw-bold text-dark mb-3">Designed for Classroom Mentors</h3>
                                <p class="text-muted mb-4">
                                    Educators at CNHS-JHS enjoy seamless digital tools that eliminate bureaucratic overhead so they can focus on teaching.
                                </p>
                                <div class="role-feature-item">
                                    <div class="role-feature-icon"><i class="bi bi-calendar-heart-fill"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Teaching Leave & Seminar Credit Claims</div>
                                        <div class="text-muted small">File vacation, sick, and maternity leaves online. Convert certified training seminars into accredited service credits.</div>
                                    </div>
                                </div>
                                <div class="role-feature-item">
                                    <div class="role-feature-icon"><i class="bi bi-file-earmark-person-fill"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">One-Click CSC Form 212 Updating</div>
                                        <div class="text-muted small">Update graduate studies, PRC IDs, and educational units anytime with instant official PDF export.</div>
                                    </div>
                                </div>
                                <div class="role-feature-item">
                                    <div class="role-feature-icon"><i class="bi bi-wallet2"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Digital Payslips & Year-End Tax Forms</div>
                                        <div class="text-muted small">Access confidential monthly payslips and download official BIR Form 2316 tax certificates on demand.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-4 rounded-4 bg-light border text-center">
                                    <img src="https://illustrations.popsy.co/amber/teaching.svg" alt="Teaching Personnel" class="img-fluid" style="max-height: 280px;">
                                    <div class="mt-3">
                                        <a href="{{ route('login') }}" class="btn btn-brand rounded-pill fw-bold px-4 py-2">
                                            Teacher Sign In <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Non-Teaching Staff -->
                <div class="tab-pane fade" id="non-teaching" role="tabpanel" aria-labelledby="non-teaching-tab">
                    <div class="role-card">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-6">
                                <h3 class="fw-bold text-dark mb-3">Empowering Administrative & Support Personnel</h3>
                                <p class="text-muted mb-4">
                                    Administrative aides, registrars, accountants, and office staff can easily track appointments, leave balances, and step increments.
                                </p>
                                <div class="role-feature-item">
                                    <div class="role-feature-icon"><i class="bi bi-clock-history"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Monthly Leave Accrual Monitoring</div>
                                        <div class="text-muted small">Real-time ledger displaying monthly 1.25 vacation and 1.25 sick leave credit additions under CSC Rule XVI.</div>
                                    </div>
                                </div>
                                <div class="role-feature-item">
                                    <div class="role-feature-icon"><i class="bi bi-award"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Plantilla Service Records</div>
                                        <div class="text-muted small">Track your government service history, step increments (every 3 years), and verified salary grades.</div>
                                    </div>
                                </div>
                                <div class="role-feature-item">
                                    <div class="role-feature-icon"><i class="bi bi-shield-check"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Digital SALN Submission</div>
                                        <div class="text-muted small">Submit annual asset declarations with automated formula computation for fast auditing.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-4 rounded-4 bg-light border text-center">
                                    <img src="https://illustrations.popsy.co/amber/work-from-home.svg" alt="Non-Teaching Personnel" class="img-fluid" style="max-height: 280px;">
                                    <div class="mt-3">
                                        <a href="{{ route('login') }}" class="btn btn-brand rounded-pill fw-bold px-4 py-2">
                                            Staff Sign In <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: HR & Administrators -->
                <div class="tab-pane fade" id="hr-admin" role="tabpanel" aria-labelledby="hr-admin-tab">
                    <div class="role-card">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-6">
                                <h3 class="fw-bold text-dark mb-3">Centralized Management for HR Officers</h3>
                                <p class="text-muted mb-4">
                                    Complete visibility and authority to process personnel actions, audit compliance, compute payroll, and manage institutional records.
                                </p>
                                <div class="role-feature-item">
                                    <div class="role-feature-icon"><i class="bi bi-sliders"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Staff Profiling & Department Allocations</div>
                                        <div class="text-muted small">Manage faculty assignments across 8 JHS learning areas, execute promotions, and organize plantillas.</div>
                                    </div>
                                </div>
                                <div class="role-feature-item">
                                    <div class="role-feature-icon"><i class="bi bi-check-circle"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Leave Approval Workflow & Ledger Adjustment</div>
                                        <div class="text-muted small">Review pending Form 6 requests, approve seminar claims, adjust credit balances, and generate printables.</div>
                                    </div>
                                </div>
                                <div class="role-feature-item">
                                    <div class="role-feature-icon"><i class="bi bi-receipt"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">Automated DepEd Payroll Generation</div>
                                        <div class="text-muted small">Process periodic compensation with statutory deductions, custom deductions, and Excel report generation.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-4 rounded-4 bg-light border text-center">
                                    <img src="https://illustrations.popsy.co/amber/presentation.svg" alt="HR Officers" class="img-fluid" style="max-height: 280px;">
                                    <div class="mt-3">
                                        <a href="{{ route('login') }}" class="btn btn-brand rounded-pill fw-bold px-4 py-2">
                                            Admin Portal Sign In <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Institutional Governance, Security & Compliance Section -->
    <section class="py-5 bg-light" id="compliance">
        <div class="container py-4">
            <div class="text-center section-header mb-5">
                <div class="section-tag">
                    <i class="bi bi-shield-lock-fill"></i> Compliance & Standards
                </div>
                <h2 class="section-title mb-3">Institutional Security & Civil Service Integrity</h2>
                <p class="section-subtitle">
                    Our portal is built from the ground up to respect Philippine statutory mandates, safeguard personnel data, and uphold public service standards.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="compliance-card">
                        <div class="compliance-icon">
                            <i class="bi bi-shield-shaded"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Data Privacy Act of 2012</h5>
                        <p class="text-muted small mb-0">
                            Strict adherence to Republic Act No. 10173. Sensitive personal identifiers, medical leave attachments, and SALN disclosures are strictly role-segregated and encrypted.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="compliance-card">
                        <div class="compliance-icon">
                            <i class="bi bi-file-earmark-check"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">CSC MC No. 41 Standards</h5>
                        <p class="text-muted small mb-0">
                            Leave computations and ledger formulas follow Civil Service Omnibus Rules on Leave (Rule XVI). Automated computation of vacation, sick, forced, and special privileges.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="compliance-card">
                        <div class="compliance-icon">
                            <i class="bi bi-award-fill"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">DepEd Plantilla & Merit System</h5>
                        <p class="text-muted small mb-0">
                            Aligned with Department of Education standards on teaching ranks (Teacher I-III, Master Teacher) and non-teaching salary grades, ensuring seamless step increment tracking.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Frequently Asked Questions (FAQ) Section -->
    <section class="py-5 bg-white" id="faq">
        <div class="container py-4">
            <div class="text-center section-header mb-5">
                <div class="section-tag">
                    <i class="bi bi-question-circle-fill"></i> FAQ
                </div>
                <h2 class="section-title mb-3">Frequently Asked Questions</h2>
                <p class="section-subtitle">
                    Helpful answers regarding portal access, leave filings, document exports, and account procedures.
                </p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="accordion custom-accordion" id="faqAccordion">
                        
                        <!-- FAQ 1 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    How do faculty and staff receive their login credentials?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Official accounts are provisioned by the CNHS-JHS HR Department upon appointment. Employees receive their official ID number and a temporary password via institutional email. Upon first login, users are required to update their security credentials.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 2 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Can I download official CSC Form 212 (PDS) and SALN forms directly?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes. Once you complete your Personal Data Sheet or annual SALN in the system, you can generate and download print-ready PDF and Word documents formatted exactly according to official Civil Service Commission specifications.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 3 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    How do teachers claim seminar credits to offset leave absences?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Teaching personnel who participate in DepEd-accredited seminars or division workshops can upload their Certificate of Participation in the Leave module. Upon HR verification, approved hours are converted into credited leave service credits.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 4 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    How are DepEd service records maintained and updated?
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Service records are centrally recorded by the HR Department. Every plantilla promotion, salary step increment (every 3 years), or approved transfer is entered into the system. Personnel can view their verified records anytime and download the official DepEd Excel format.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 5 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                    What should I do if I forget my password or require account recovery?
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Click the "Forgot Password?" link on the sign-in page to receive a password reset link to your registered recovery email. Alternatively, you may visit the CNHS-JHS HR Office for administrative password assistance.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pre-Footer Callout Banner -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="cta-banner">
                <div class="row align-items-center">
                    <div class="col-lg-8 mb-4 mb-lg-0">
                        <h2 class="cta-title mb-2">Ready to access your personnel records?</h2>
                        <p class="cta-subtitle mb-0">
                            Log into the CNHS-JHS Employee Records Management System today to manage your PDS, file leave applications, view payslips, and check school broadcasts.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="d-flex flex-column flex-sm-row justify-content-lg-end gap-2">
                            @if(session()->has('user_id'))
                                <a href="{{ route('dashboard') }}" class="btn btn-brand btn-lg rounded-pill fw-bold px-4 py-3">
                                    Open Dashboard <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-brand btn-lg rounded-pill fw-bold px-4 py-3">
                                    Sign In Now <i class="bi bi-box-arrow-in-right ms-1"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Institutional Footer -->
    <footer class="footer-main">
        <div class="container">
            <div class="row g-4 mb-5">
                
                <div class="col-lg-6">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ asset('build/assets/images/logo.png') }}" alt="CNHS Seal" style="width: 48px; height: 48px; border-radius: 50%; border: 2px solid #EAB308;">
                        <div>
                            <div class="footer-brand-title">Cagayan National High School - JHS</div>
                            <div class="small text-muted">Employee Records Management System</div>
                        </div>
                    </div>
                    <p class="text-secondary small mb-3 pe-lg-4" style="line-height: 1.6;">
                        A digital transformation initiative empowering public educators and personnel with secure, automated, and modern civil service record keeping. DepEd Region II (Cagayan Valley), Division of Cagayan.
                    </p>
                    <div class="d-flex gap-2">
                        <span class="badge bg-secondary bg-opacity-25 text-light px-2.5 py-1.5" style="font-size: 0.72rem;">DepEd Region II</span>
                        <span class="badge bg-secondary bg-opacity-25 text-light px-2.5 py-1.5" style="font-size: 0.72rem;">Cagayan Valley</span>
                        <span class="badge bg-secondary bg-opacity-25 text-light px-2.5 py-1.5" style="font-size: 0.72rem;">CSC MC 41</span>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <h6 class="text-white fw-bold mb-3">Navigation</h6>
                    <ul class="list-unstyled mb-0">
                        <li><a href="#hero" class="footer-link">Overview</a></li>
                        <li><a href="#modules" class="footer-link">Core Modules</a></li>
                        <li><a href="#roles" class="footer-link">User Portals</a></li>
                        <li><a href="#compliance" class="footer-link">Governance & Standards</a></li>
                        <li><a href="#faq" class="footer-link">FAQ</a></li>
                    </ul>
                </div>

                <div class="col-6 col-lg-3">
                    <h6 class="text-white fw-bold mb-3">Campus & HR</h6>
                    <p class="small text-secondary mb-2">
                        <i class="bi bi-geo-alt-fill text-warning me-1"></i> Cagayan National High School, Tuguegarao City, Cagayan
                    </p>
                    <p class="small text-secondary mb-2">
                        <i class="bi bi-clock-fill text-warning me-1"></i> Mon - Fri &bull; 8:00 AM - 5:00 PM
                    </p>
                    <p class="small text-secondary mb-0">
                        <i class="bi bi-envelope-fill text-warning me-1"></i> HR Records Section
                    </p>
                </div>

            </div>

            <div class="pt-4 border-top border-secondary border-opacity-25 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <p class="small text-muted mb-0">
                    &copy; {{ date('Y') }} Cagayan National High School - Junior High School HR Department. All rights reserved.
                </p>
                <div class="d-flex gap-3">
                    <span class="small text-muted">Confidential & Protected Public Record System</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Smooth Navbar Scroll Effect -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.getElementById('mainNav');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 40) {
                    navbar.classList.add('shadow-sm');
                    navbar.style.background = 'rgba(255, 255, 255, 0.96)';
                } else {
                    navbar.classList.remove('shadow-sm');
                    navbar.style.background = 'rgba(255, 255, 255, 0.92)';
                }
            });
        });
    </script>
</body>
</html>