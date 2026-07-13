@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('build/assets/css/employee-form.css') }}">

<div class="row justify-content-center py-4">
    <div class="col-md-9 col-lg-7">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="text-brand fw-bold m-0"><i class="bi bi-person-plus-fill me-2"></i> Employee Onboarding</h4>
                <p class="text-muted small m-0">Create a new account and initialize their official PDS.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-light border shadow-sm btn-sm fw-bold text-muted px-3">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>

        <div class="card clean-card overflow-hidden">
            <div class="bg-brand" style="height: 6px; width: 100%;"></div>

            <div class="card-body p-4 p-md-5">

                @if($errors->any())
                <div class="alert alert-danger rounded-3 pb-0 border-0 shadow-sm">
                    <ul class="small fw-bold">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('employees.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-brand text-uppercase tracking-wide">1. Official Name</label>
                        <div class="row g-2 mb-2">
                            <div class="col-md-5">
                                <input type="text" name="first_name" id="first_name" class="form-control form-control-lg text-uppercase fs-6" placeholder="First Name (e.g., JUAN)" value="{{ old('first_name') }}" required>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="middle_initial" class="form-control form-control-lg text-uppercase fs-6 text-center" placeholder="M.I." value="{{ old('middle_initial') }}" maxlength="2">
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="last_name" id="last_name" class="form-control form-control-lg text-uppercase fs-6" placeholder="Last Name (e.g., DELA CRUZ)" value="{{ old('last_name') }}" required>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="suffix" id="suffix" class="form-control form-control-lg text-uppercase fs-6 text-center" placeholder="Suffix (JR)" value="{{ old('suffix') }}">
                            </div>
                        </div>
                        <div class="d-flex align-items-center text-success small fw-bold bg-success bg-opacity-10 p-2 rounded">
                            <i class="bi bi-check-circle-fill me-2"></i> Names will automatically cross-populate into the employee's Form 212 (PDS).
                        </div>
                    </div>

                    <hr class="text-muted opacity-25 my-4">

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-brand text-uppercase tracking-wide">2. System Credentials</label>

                        <div class="mb-3">
                            <div class="input-group input-group-lg shadow-sm rounded-3">
                                <span class="input-group-text bg-white"><i class="bi bi-person-badge"></i></span>
                                <input type="text" name="username" id="username" class="form-control with-icon fs-6" placeholder="Auto-generated username" value="{{ old('username') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="input-group input-group-lg shadow-sm rounded-3">
                                <span class="input-group-text bg-white"><i class="bi bi-shield-lock"></i></span>
                                <input type="text" name="password" id="password" class="form-control with-icon bg-white fs-6" placeholder="Click generate or type manually" required>
                                <button class="btn btn-light border fw-bold text-brand px-4" type="button" onclick="generateRandomPassword()">
                                    <i class="bi bi-key-fill me-1"></i> Generate
                                </button>
                            </div>
                            <div class="form-text small mt-2 text-muted">
                                <i class="bi bi-info-circle me-1"></i> Provide this temporary password to the employee for their first login.
                            </div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <button type="submit" class="btn btn-brand btn-lg w-100 fw-bold shadow-sm rounded-3 py-3">
                            <i class="bi bi-cloud-arrow-up-fill me-2"></i> Register Account & Initialize Records
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    // 1. Generate Username Script
    document.addEventListener("DOMContentLoaded", function() {
        const firstNameInput = document.getElementById('first_name');
        const lastNameInput = document.getElementById('last_name');
        const usernameInput = document.getElementById('username');

        function generateUsername() {
            let first = firstNameInput.value.trim().toLowerCase();
            let last = lastNameInput.value.trim().toLowerCase().replace(/\s+/g, '');

            if (first && last) {
                // Takes first letter of first name + last name
                usernameInput.value = first.charAt(0) + last;
            }
        }

        firstNameInput.addEventListener('input', generateUsername);
        lastNameInput.addEventListener('input', generateUsername);
    });

    // 2. Generate Random Password Script
    function generateRandomPassword() {
        const length = 10;
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%";
        let password = "";

        for (let i = 0; i < length; i++) {
            const randomIndex = Math.floor(Math.random() * charset.length);
            password += charset[randomIndex];
        }

        // Inject into the password field
        const passField = document.getElementById('password');
        passField.value = password;

        // Flash effect for visual confirmation
        passField.style.backgroundColor = '#f0f4f8';
        setTimeout(() => {
            passField.style.backgroundColor = '#ffffff';
        }, 300);
    }
</script>
@endsection