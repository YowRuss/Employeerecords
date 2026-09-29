<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CNHS-JHS Employee Records System</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
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
                        <i class="bi bi-shield-lock"></i> Security Update
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
                        <span>Enhanced Security Verification</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-key-fill"></i></div>
                        <span>Secure Password Requirements</span>
                    </div>
                </div>

                <div class="pt-3 border-top border-secondary border-opacity-25 mt-4">
                    <span class="small text-secondary">&copy; {{ date('Y') }} CNHS-JHS HR Department</span>
                </div>
            </div>

            <!-- Right Form Section -->
            <div class="col-lg-7 form-side">
                <div class="form-header">
                    <h3>Reset Your Password</h3>
                    <p>Please enter your new password below.</p>
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

                <form action="{{ route('password.update') }}" method="POST" id="resetPasswordForm">
                    @csrf
                    
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <!-- New Password -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label mb-0">New Password</label>
                        </div>
                        <div class="input-group-custom">
                            <i class="bi bi-lock-fill input-icon-lead"></i>
                            <input type="password" 
                                   class="form-control-custom" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter new password" 
                                   required 
                                   autofocus
                                   style="padding-right: 2.8rem;">
                            
                            <button class="btn-toggle-password toggle-password" type="button" data-target="password" title="Toggle password visibility">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password_confirmation" class="form-label mb-0">Confirm New Password</label>
                        </div>
                        <div class="input-group-custom">
                            <i class="bi bi-check-circle-fill input-icon-lead"></i>
                            <input type="password" 
                                   class="form-control-custom" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Confirm your new password" 
                                   required 
                                   style="padding-right: 2.8rem;">
                            
                            <button class="btn-toggle-password toggle-password" type="button" data-target="password_confirmation" title="Toggle password visibility">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-login-submit" id="submitBtn">
                        <span>Reset Password</span>
                        <i class="bi bi-shield-lock-fill fs-5 ms-2"></i>
                    </button>
                </form>
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
            const resetPasswordForm = document.getElementById('resetPasswordForm');
            const submitBtn = document.getElementById('submitBtn');
            
            if (resetPasswordForm && submitBtn) {
                resetPasswordForm.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <span>Resetting...</span>
                    `;
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
