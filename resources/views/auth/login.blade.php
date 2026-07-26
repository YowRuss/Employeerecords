<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CNHS-JHS HR System</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom Login Page CSS -->
    <link rel="stylesheet" href="{{ asset('build/assets/css/login.css') }}">
</head>
<body>

    <!-- Ambient Glowing Orbs -->
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>

    <!-- Login Card Container -->
    <div class="login-wrapper">
        <div class="row g-0">
            
            <!-- Left Hero Section -->
            <div class="col-lg-5 hero-side">
                <div>
                    <div class="badge-system mb-3">
                        <i class="bi bi-shield-check"></i> CNHS Portal v2.0
                    </div>

                    <div class="logo-glow-wrapper">
                        <img src="{{ asset('build/assets/images/logo.png') }}" alt="CNHS Logo" class="logo-img">
                    </div>

                    <h4 class="fw-bold mb-2 text-dark">Employee Records System</h4>
                    <p class="text-secondary small mb-4">Cavite National High School - Junior High School Human Resources Management Portal</p>
                </div>

                <div class="feature-list mt-3">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-lock-fill"></i></div>
                        <span>Encrypted & Authorized Access</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-person-vcard-fill"></i></div>
                        <span>Comprehensive Employee File System</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-speedometer2"></i></div>
                        <span>Fast & Real-time Records Lookup</span>
                    </div>
                </div>

                <div class="pt-3 border-top border-secondary border-opacity-25 mt-4">
                    <span class="small text-secondary">&copy; {{ date('Y') }} CNHS-JHS HR Department</span>
                </div>
            </div>

            <!-- Right Form Section -->
            <div class="col-lg-7 form-side">
                <div class="form-header">
                    <h3>Welcome Back</h3>
                    <p>Please enter your credentials to access your account</p>
                </div>

                @if(session('error'))
                    <div class="alert alert-custom-error mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" id="loginForm">
                    @csrf

                    <!-- ID Number / Email / Username -->
                    <div class="mb-3.5 mb-3">
                        <label for="id_number" class="form-label">Email or Username</label>
                        <div class="input-group-custom">
                            <i class="bi bi-person-fill input-icon-lead"></i>
                            <input type="text" 
                                   class="form-control-custom" 
                                   id="id_number" 
                                   name="id_number" 
                                   placeholder="Enter your email or username" 
                                   required 
                                   autofocus 
                                   autocomplete="username">
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label mb-0">Password</label>
                        </div>
                        <div class="input-group-custom">
                            <i class="bi bi-key-fill input-icon-lead"></i>
                            <input type="password" 
                                   class="form-control-custom" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password" 
                                   required 
                                   style="padding-right: 2.8rem;" 
                                   autocomplete="current-password">
                            
                            <button class="btn-toggle-password toggle-password" type="button" data-target="password" title="Toggle password visibility">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-login-submit" id="submitBtn">
                        <span>Sign In</span>
                        <i class="bi bi-arrow-right-short fs-4"></i>
                    </button>
                </form>

                <div class="footer-note">
                    <i class="bi bi-info-circle me-1"></i> Authorized Personnel Only. For account assistance, contact HR Admin.
                </div>
            </div>

        </div>
    </div>

    <!-- Interactive Scripts -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Password toggle logic
            const toggleButtons = document.querySelectorAll('.toggle-password');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const passwordInput = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    if (passwordInput.type === "password") {
                        passwordInput.type = "text";
                        icon.classList.remove('bi-eye-fill');
                        icon.classList.add('bi-eye-slash-fill');
                    } else {
                        passwordInput.type = "password";
                        icon.classList.remove('bi-eye-slash-fill');
                        icon.classList.add('bi-eye-fill');
                    }
                });
            });

            // Form Submit Loading State
            const loginForm = document.getElementById('loginForm');
            const submitBtn = document.getElementById('submitBtn');
            
            if (loginForm && submitBtn) {
                loginForm.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <span>Signing in...</span>
                    `;
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>