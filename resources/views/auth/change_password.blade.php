<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Employee Records Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Custom Change Password Page CSS -->
    <link rel="stylesheet" href="{{ asset('build/assets/css/change-password.css') }}">
</head>
<body>

    <!-- Ambient Glowing Orbs -->
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>

    <div class="login-card">
        
        <div class="text-center mb-4">
            <img src="{{ asset('build/assets/images/logo.png') }}" alt="CNHS Logo" class="rounded-circle shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
        </div>
        
        <h4 class="text-center mb-2 text-accent fw-bold">Action Required</h4>
        <p class="text-center text-muted small mb-4">Please change your default password before continuing.</p>

        @if($errors->any())
            <div class="alert alert-danger p-2 text-center small fw-bold" role="alert">
                <ul class="mb-0 text-start">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('password.change.post') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="password" class="form-label text-muted small fw-bold">New Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password" required autofocus>
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password" style="border-color: #ced4da;">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="password_confirmation" class="form-label text-muted small fw-bold">Confirm New Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm your new password" required>
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation" style="border-color: #ced4da;">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-custom">Change Password</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleButtons = document.querySelectorAll('.toggle-password');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const passwordInput = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    if (passwordInput.type === "password") {
                        passwordInput.type = "text";
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    } else {
                        passwordInput.type = "password";
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
