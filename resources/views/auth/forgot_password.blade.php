<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - CNHS-JHS Employee Records System</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom Login Page CSS (Reusing login.css for beautiful layout) -->
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
                        <i class="bi bi-envelope-check"></i> Account Recovery
                    </div>

                    <div class="logo-glow-wrapper">
                        <img src="{{ asset('build/assets/images/logo.png') }}" alt="CNHS Logo" class="logo-img">
                    </div>

                    <h4 class="fw-bold mb-2 text-dark">Employee Records System</h4>
                    <p class="text-secondary small mb-4">Cagayan National High School - Junior High School Employee Records Management System</p>
                </div>

                <div class="feature-list mt-3">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                        <span>Secure Password Reset</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-envelope-paper-heart"></i></div>
                        <span>Email Verification Required</span>
                    </div>
                </div>

                <div class="pt-3 border-top border-secondary border-opacity-25 mt-4">
                    <span class="small text-secondary">&copy; {{ date('Y') }} CNHS-JHS HR Department</span>
                </div>
            </div>

            <!-- Right Form Section -->
            <div class="col-lg-7 form-side">
                <div class="form-header">
                    <h3>Forgot Password?</h3>
                    <p>Enter your recovery email or username to receive a reset link.</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-custom-error mb-4 align-items-start" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
                        <div>
                            <ul class="mb-0 text-start ps-3" style="list-style-type: disc;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-custom-error mb-4 align-items-start" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success mb-4 d-flex align-items-center" role="alert" style="border-radius: 12px; font-size: 0.875rem; font-weight: 600;">
                        <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @else

                <form action="{{ route('password.email') }}" method="POST" id="forgotPasswordForm">
                    @csrf

                    <!-- Recovery Email or Username -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="id_number" class="form-label mb-0">Recovery Email or Username</label>
                        </div>
                        <div class="input-group-custom">
                            <i class="bi bi-person-badge-fill input-icon-lead"></i>
                            <input type="text" 
                                   class="form-control-custom" 
                                   id="id_number" 
                                   name="id_number" 
                                   placeholder="Enter recovery email or username" 
                                   required 
                                   autofocus>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn-login-submit" id="submitBtn">
                        <span>Send Reset Link</span>
                        <i class="bi bi-envelope-fill fs-5 ms-2"></i>
                    </button>
                    
                    <div class="text-center mt-4">
                        <a href="{{ route('login') }}" class="small fw-bold text-decoration-none" style="color: #1A3E6F;">Back to Login</a>
                    </div>
                </form>
                
                @endif
            </div>
        </div>
    </div>

    <!-- Interactive Scripts -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Form Submit Loading State
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            const submitBtn = document.getElementById('submitBtn');
            
            if (forgotPasswordForm && submitBtn) {
                forgotPasswordForm.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <span>Sending Link...</span>
                    `;
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
