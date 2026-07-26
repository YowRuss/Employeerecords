<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Now | Careers | CNHS-JHS</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('build/assets/css/welcome.css') }}">
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <img src="{{ asset('build/assets/images/logo.png') }}" alt="CNHS Logo" class="me-2" style="width: 40px; height: 40px;">
                CNHS-JHS HR System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="/">Back to Home</a>
                    </li>
                    <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                        <a href="{{ route('login') }}" class="btn btn-outline-brand fw-bold px-4 rounded-pill">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Employee Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="hero-section text-center" style="padding: 60px 0 40px;">
        <div class="container">
            <span class="badge hero-badge mb-3 px-3 py-2 rounded-pill shadow-sm">Join Our Team</span>
            <h1 class="hero-title mb-3">Submit Your Application</h1>
            <p class="hero-subtitle mb-0">Take the next step in your career. Fill out the form below to apply.</p>
        </div>
    </section>

    <!-- Application Form Section -->
    <section class="py-5 bg-light">
        <div class="container pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    
                    @if(session('success'))
                    <div class="alert alert-success shadow-sm border-0 rounded-3 mb-4 d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2 fs-4"></i> 
                        <div>{{ session('success') }}</div>
                    </div>
                    @endif

                    <div class="card job-card shadow-sm border-0 p-lg-4">
                        <div class="card-header bg-transparent border-bottom py-3 mb-3">
                            <h5 class="fw-bold text-dark m-0"><i class="bi bi-person-lines-fill me-2" style="color: var(--brand-secondary);"></i> Applicant Details</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('careers.apply') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                {{--
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted small">Position Applying For <span class="text-danger">*</span></label>
                                    <select name="position_id" class="form-select form-select-lg" required>
                                        <option value="" disabled selected>Select an open position...</option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}">{{ $position->position_name }}</option>
                                        @endforeach
                                    </select>
                                </div>--}}
                                <div class="mb-4 p-3 bg-light rounded border border-primary border-opacity-25">
                                    <label class="form-label fw-bold text-muted small mb-1">Applying For Position:</label>
                                    <h4 class="fw-bold text-primary mb-0">{{ $selectedPosition->position_name }}</h4>
    
                                    <input type="hidden" name="position_id" value="{{ $selectedPosition->id }}">
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" class="form-control" required placeholder="e.g. Juan">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small">Middle Name <span class="text-danger">*</span></label>
                                        <input type="text" name="middle_name" class="form-control" required placeholder="e.g. Santos">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" class="form-control" required placeholder="e.g. Dela Cruz">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small">Suffix <span class="text-danger">*</span></label>
                                        <input type="text" name="suffix" class="form-control" required placeholder="e.g. Jr.">
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" required placeholder="name@example.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted small">Contact Number <span class="text-danger">*</span></label>
                                        <input type="text" name="contact_number" class="form-control" required placeholder="09123456789">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted small">Cover Letter / Message (Optional)</label>
                                    <textarea name="cover_letter" class="form-control" rows="4" placeholder="Briefly describe why you are a good fit for this role..."></textarea>
                                </div>

                                <div class="mb-5 p-4 bg-light rounded-3 border">
                                    <label class="form-label fw-bold text-dark mb-3"><i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Upload Resume <span class="text-danger">*</span></label>
                                    <input type="file" name="resume" class="form-control form-control-lg" accept=".pdf,.doc,.docx" required>
                                    <div class="form-text small mt-2"><i class="bi bi-info-circle me-1"></i> Accepted formats: PDF, DOC, DOCX. Max size: 5MB.</div>
                                </div>

                                <button type="submit" class="btn btn-brand btn-lg w-100 fw-bold shadow-sm rounded-pill py-3">
                                    Submit Application <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <h5 class="fw-bold mb-3" style="color: var(--brand-primary);">CNHS-JHS HR System</h5>
            <p class="text-white-50 small mb-0">&copy; {{ date('Y') }} CNHS-JHS. All rights reserved.</p>
            <div class="mt-3">
                <a href="#" class="text-white-50 text-decoration-none mx-2 small">Privacy Policy</a>
                <a href="#" class="text-white-50 text-decoration-none mx-2 small">Terms of Service</a>
                <a href="#" class="text-white-50 text-decoration-none mx-2 small">Contact HR</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>