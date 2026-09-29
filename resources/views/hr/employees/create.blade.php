@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-header-blue fw-bold m-0"><i class="bi bi-person-plus-fill me-2 text-header-blue"></i> Employee Onboarding</h4>
            <p class="text-muted small m-0">Create a new account and initialize their official PDS.</p>
        </div>
        <a href="{{ route('requisitions.index') }}" class="btn btn-light border shadow-sm btn-sm fw-bold text-muted px-3">
            <i class="bi bi-arrow-left me-1"></i> Personnel Requisitions
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">

            @if($errors->any())
            <div class="alert alert-danger rounded-3 shadow-sm">
                <ul class="small fw-bold mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('employees.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <h6 class="text-accent fw-bold text-uppercase small mb-3">1. Official Name</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="first_name" class="form-label small fw-bold text-muted">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="first_name" class="form-control text-uppercase" placeholder="JUAN" value="{{ old('first_name') }}" required>
                        </div>
                        <div class="col-md-2">
                            <label for="middle_initial" class="form-label small fw-bold text-muted">M.I.</label>
                            <input type="text" name="middle_initial" id="middle_initial" class="form-control text-uppercase text-center" placeholder="M" value="{{ old('middle_initial') }}" maxlength="2">
                        </div>
                        <div class="col-md-4">
                            <label for="last_name" class="form-label small fw-bold text-muted">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" id="last_name" class="form-control text-uppercase" placeholder="DELA CRUZ" value="{{ old('last_name') }}" required>
                        </div>
                        <div class="col-md-2">
                            <label for="suffix" class="form-label small fw-bold text-muted">Suffix</label>
                            <input type="text" name="suffix" id="suffix" class="form-control text-uppercase text-center" placeholder="JR" value="{{ old('suffix') }}">
                        </div>
                    </div>
                    <div class="d-flex align-items-center small mt-3 px-3 py-2 rounded-3 border" style="background-color: #fef9c3;">
                        <i class="bi bi-check-circle-fill text-accent me-2"></i>
                        Names will automatically cross-populate into the employee's Form 212 (PDS).
                    </div>
                </div>

                <hr class="text-muted opacity-25 my-4">

                <div class="mb-4">
                    <h6 class="text-accent fw-bold text-uppercase small mb-3">2. Employee Email / Username</h6>
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label for="username" class="form-label small fw-bold text-muted">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-envelope-at-fill text-accent"></i></span>
                                <input type="text" name="username" id="username" class="form-control" placeholder="juan.delacruz@gmail.com" value="{{ old('username') }}" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label for="password" class="form-label small fw-bold text-muted">Temporary Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-shield-lock text-accent"></i></span>
                                <input type="text" name="password" id="password" class="form-control" placeholder="Temporary password" required>
                                <button class="btn btn-accent fw-bold" type="button" onclick="generateRandomPassword()">
                                    <i class="bi bi-key-fill me-1"></i> Generate
                                </button>
                            </div>
                            <div class="form-text small mt-2 text-muted">
                                <i class="bi bi-info-circle me-1"></i> Give this password to the employee for their first login.
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="text-muted opacity-25 my-4">

                <div class="mb-4">
                    <h6 class="text-accent fw-bold text-uppercase small mb-3">3. Employee Type</h6>
                    <div class="col-md-6 px-0">
                        <label for="employee_type" class="form-label small fw-bold text-muted">Classification <span class="text-danger">*</span></label>
                        <select name="employee_type" id="employee_type" class="form-select" required>
                            <option value="" disabled {{ old('employee_type') === null ? 'selected' : '' }}>Select Employee Type</option>
                            <option value="1" {{ old('employee_type') == '1' ? 'selected' : '' }}>TEACHING</option>
                            <option value="0" {{ old('employee_type') == '0' ? 'selected' : '' }}>NON-TEACHING</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent w-100 fw-bold shadow-sm py-2 mt-2">
                    <i class="bi bi-cloud-arrow-up-fill me-2"></i> Register Account & Initialize Records
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const firstNameInput = document.getElementById('first_name');
        const lastNameInput = document.getElementById('last_name');
        const usernameInput = document.getElementById('username');

        function generateUsername() {
            let first = firstNameInput.value.trim().toLowerCase().replace(/[^a-z0-9]/g, '');
            let last = lastNameInput.value.trim().toLowerCase().replace(/[^a-z0-9]/g, '');

            if (first && last) {
                // Generates format: first.last@gmail.com
                usernameInput.value = first + "." + last + "@gmail.com";
            }
        }

        firstNameInput.addEventListener('input', generateUsername);
        lastNameInput.addEventListener('input', generateUsername);
    });

    // Random Password Generator remains the same...
    //old code
    //const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%";
    function generateRandomPassword() {
        const length = 4;
        const charset = "123456789";
        let password = "";
        for (let i = 0; i < length; i++) {
            password += charset[Math.floor(Math.random() * charset.length)];
        }
        const passField = document.getElementById('password');
        passField.value = password;
        passField.style.backgroundColor = '#fef9c3';
        setTimeout(() => { passField.style.backgroundColor = '#ffffff'; }, 300);
    }
</script>
@endsection