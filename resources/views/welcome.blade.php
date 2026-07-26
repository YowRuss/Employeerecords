<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers | CNHS-JHS HR System</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom Landing Page Styles -->
    <link rel="stylesheet" href="{{ asset('build/assets/css/welcome.css') }}">
</head>
<body>

    <!-- Sticky Glass Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-glass sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="/">
                <img src="{{ asset('build/assets/images/logo.png') }}" alt="CNHS Logo" class="brand-logo-img">
                <span>CNHS-JHS HR System</span>
            </a>
            
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-2 text-dark"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-1 mt-3 mt-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="/">Careers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Why Join Us</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a href="{{ route('login') }}" class="btn btn-outline-brand fw-bold px-4 py-2 rounded-pill">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Employee Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <!-- Ambient Glowing Orbs -->
        <div class="ambient-orb orb-hero-1"></div>
        <div class="ambient-orb orb-hero-2"></div>

        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="hero-badge-container mb-3">
                        <span class="pulse-dot"></span>
                        <span>We Are Hiring Dedicated Educators</span>
                    </div>

                    <h1 class="hero-title mb-4">
                        Shape the Future of <span class="text-highlight">Education</span> with Us.
                    </h1>

                    <p class="hero-subtitle mb-5">
                        Join the CNHS-JHS community. We are seeking passionate educators and dedicated professionals committed to fostering academic excellence and innovation.
                    </p>

                    <div class="d-flex flex-wrap gap-3 justify-content-start">
                        <a href="#open-positions" class="btn btn-brand btn-lg fw-bold px-4 py-3 rounded-pill">
                            Explore Job Openings <i class="bi bi-arrow-down-short fs-5"></i>
                        </a>
                        <a href="#about" class="btn btn-outline-light btn-lg fw-bold px-4 py-3 rounded-pill border-opacity-25">
                            Learn More
                        </a>
                    </div>
                </div>

                <div class="col-lg-6 d-none d-lg-block">
                    <div class="hero-visual-wrapper">
                        <div class="hero-img-container">
                            <img src="https://illustrations.popsy.co/amber/freelancer.svg" alt="Recruitment Graphic" class="hero-main-img img-fluid">
                            
                            <!-- Floating Badges -->
                            <div class="floating-badge badge-top-right">
                                <div class="floating-badge-icon"><i class="bi bi-shield-check"></i></div>
                                <div>
                                    <div style="font-size: 0.75rem; color: #64748b;">CIVIL SERVICE</div>
                                    <div>Accredited Positions</div>
                                </div>
                            </div>

                            <div class="floating-badge badge-bottom-left">
                                <div class="floating-badge-icon"><i class="bi bi-award-fill"></i></div>
                                <div>
                                    <div style="font-size: 0.75rem; color: #64748b;">GROWTH</div>
                                    <div>Continuous L&D Programs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Statistics Section -->
    <section class="stats-section py-4">
        <div class="container">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="stats-card">
                        <div class="stat-number">100+</div>
                        <div class="stat-label">Faculty & Staff</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stats-card">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Civil Service Aligned</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stats-card">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">HR Portal Access</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stats-card">
                        <div class="stat-number">Yearly</div>
                        <div class="stat-label">Step Increments</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Open Positions Section -->
    <section id="open-positions" class="py-5 bg-light">
        <div class="container py-4">
            <div class="text-center section-header mb-5">
                <h2>Current Career Opportunities</h2>
                <p>Explore available roles and start your teaching or administrative journey with CNHS-JHS.</p>
            </div>

            <div class="row g-4">
                @forelse($jobPostings as $job)
                    <div class="col-md-6 col-lg-4">
                        <div class="job-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="dept-badge">{{ $job->department }}</span>
                                <span class="type-badge">{{ $job->employment_type }}</span>
                            </div>
                            
                            <h4 class="job-title mb-2">{{ $job->position->position_name }}</h4>
                            
                            <div class="job-location mb-3">
                                <i class="bi bi-geo-alt-fill text-warning me-1"></i> {{ $job->location }}
                            </div>
                            
                            <p class="job-desc mb-4">{{ Str::limit($job->description, 110) }}</p>
                            
                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="d-block text-muted style-tiny" style="font-size: 0.75rem;">SALARY / GRADE</span>
                                    <span class="fw-bold text-dark">{{ $job->salary_info }}</span>
                                </div>
                                <a href="{{ route('careers.index', ['position_id' => $job->position_id]) }}" class="btn btn-brand job-apply-btn">
                                    <span>Apply Now</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">
                        <div class="mb-3">
                            <i class="bi bi-briefcase fs-1 text-warning opacity-75"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">No Open Positions Currently Listed</h4>
                        <p class="text-muted">Please check back soon or contact the HR Department for upcoming vacancy announcements.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Why Work With Us Section -->
    <section id="about" class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center section-header mb-5">
                <h2>Why Work With Us?</h2>
                <p>Discover the benefits and support mechanisms designed for every CNHS-JHS employee.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon-wrapper">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <h5 class="benefit-title">Professional Growth</h5>
                        <p class="benefit-desc">Access to continuous Learning & Development (L&D) interventions and seminars.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon-wrapper">
                            <i class="bi bi-heart-pulse-fill"></i>
                        </div>
                        <h5 class="benefit-title">Health Benefits</h5>
                        <p class="benefit-desc">Comprehensive PhilHealth, GSIS coverage, and internal health wellness privileges.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon-wrapper">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>
                        <h5 class="benefit-title">Leave Credits</h5>
                        <p class="benefit-desc">Generous vacation, sick, maternal/paternal, and special emergency leave credits.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon-wrapper">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h5 class="benefit-title">Job Security</h5>
                        <p class="benefit-desc">Plantilla appointments with structured step increments for career personnel.</p>
                    </div>
                </div>
            </div>

            <!-- Callout CTA Section -->
            <div class="cta-section mt-5">
                <div class="row align-items-center">
                    <div class="col-lg-8 mb-4 mb-lg-0">
                        <h3 class="cta-title">Ready to build your career with CNHS-JHS?</h3>
                        <p class="cta-subtitle mb-0">Browse our open positions today and take the next step in public education service.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="#open-positions" class="btn btn-brand btn-lg fw-bold px-4 py-3 rounded-pill">
                            View Available Roles
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-main">
        <div class="container text-center">
            <h5 class="footer-brand mb-2">CNHS-JHS HR System</h5>
            <p class="text-white-50 small mb-3">Cavite National High School - Junior High School Human Resource Portal</p>
            
            <div class="d-flex justify-content-center gap-4 mb-3">
                <a href="#open-positions" class="footer-link">Careers</a>
                <a href="#about" class="footer-link">Why Work With Us</a>
                <a href="{{ route('login') }}" class="footer-link">Employee Portal</a>
            </div>

            <div class="pt-3 border-top border-secondary border-opacity-25">
                <p class="small mb-0 text-white-50">&copy; {{ date('Y') }} CNHS-JHS HR Department. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>