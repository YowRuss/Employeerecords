@extends('layouts.app')

@section('content')
<style>
    /* CSS to show the user they can click the image */
    .profile-zoom-trigger {
        transition: transform 0.2s ease-in-out;
        cursor: zoom-in;
    }
    .profile-zoom-trigger:hover {
        transform: scale(1.05);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .profile-tabs .nav-link {
        color: #1A3E6F;
        background-color: transparent;
    }
    .profile-tabs .nav-link.active {
        color: #1A3E6F !important;
        background-color: yellow !important;
    }
</style>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-accent fw-bold m-0">Account Profile</h4>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger pb-0">
                <ul class="small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card p-4 shadow-sm border-0 border-top border-4 border-accent">
            <ul class="nav nav-tabs profile-tabs mb-4" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true">Profile Details</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">Security</button>
                </li>
            </ul>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="tab-content" id="profileTabsContent">
                    <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                        <div class="text-center mb-4">
                    @if($user->profile_image)
                        <!-- Wrapped the image in a clickable modal trigger -->
                        <a href="#" data-bs-toggle="modal" data-bs-target="#zoomProfileModal" title="Click to zoom">
                            <img src="data:{{ $user->image_type }};base64,{{ base64_encode($user->profile_image) }}" alt="Profile Image" class="rounded-circle border border-3 border-accent object-fit-cover profile-zoom-trigger" style="width: 120px; height: 120px;">
                        </a>
                    @else
                        <div class="rounded-circle bg-light border border-3 border-secondary d-flex justify-content-center align-items-center mx-auto text-secondary fw-bold" style="width: 120px; height: 120px; font-size: 2rem;">
                            {{ strtoupper(substr($user->first_name, 0, 1)) }}
                        </div>
                    @endif
                    
                    <div class="mt-3">
                        <label for="profile_image" class="form-label small fw-bold text-muted">Upload New Picture</label>
                        <input type="file" name="profile_image" id="profile_image" class="form-control form-control-sm w-75 mx-auto" accept="image/jpeg, image/png, image/gif">
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-25">

                <!-- NAME WITH SUFFIX ADDED -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Full Name</label>
                    <input type="text" class="form-control bg-light text-uppercase fw-bold" value="{{ trim($user->first_name . ' ' . $user->middle_name . ' ' . $user->last_name . ' ' . ($user->suffix ?? '')) }}" readonly>
                    <div class="form-text small">Contact HR to change your official name.</div>
                </div>

                <!-- SHOW USERNAME -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Username</label>
                    <input type="text" name="username" class="form-control" value="{{ $user->username ?? '' }}" placeholder="Enter username">
                    <div class="form-text small">Contact HR to change your official username.</div>
                </div>
                
                <!-- ADD OFFICIAL POSITION -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Official Position</label>
                    <input type="text" class="form-control bg-light" value="{{ $user->position->name ?? $user->position->position_name ?? 'No Position Assigned' }}" readonly>
                </div>

                <!-- CONDITIONAL LEARNING AREA -->
                @if($user->position && $user->position->category === 'Teaching')
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Learning Area</label>
                    <input type="text" class="form-control bg-light" value="{{ $user->learningArea->name ?? 'None Assigned' }}" readonly>
                </div>
                @endif

                <!--
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Account Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ $user->email ?? '' }}" placeholder="Enter email address">
                </div>-->

                <!-- EMERGENCY CONTACT SECTION -->
                <div class="p-3 bg-light border rounded mb-4">
                    <h6 class="fw-bold mb-3 text-uppercase text-muted" style="font-size: 0.85rem;"><i class="bi bi-telephone-fill me-1"></i> In Case of Emergency</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Contact Person</label>
                            <input type="text" name="emergency_contact_person" class="form-control form-control-sm text-uppercase" value="{{ $user->emergency_contact_person ?? '' }}" placeholder="Full Name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Contact Number</label>
                            <input type="text" name="emergency_contact_number" class="form-control form-control-sm" value="{{ $user->emergency_contact_number ?? '' }}" placeholder="Mobile or Telephone">
                        </div>
                    </div>
                </div>
            </div> <!-- End Profile Details Tab -->

            <!-- Security Tab -->
            <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                <!-- DUAL PASSWORD SETUP -->
                <div class="p-3 bg-light border rounded mb-4">
                    <h6 class="fw-bold mb-3 text-uppercase text-muted" style="font-size: 0.85rem;"><i class="bi bi-shield-lock-fill me-1"></i> Security</h6>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">Recovery Email (Gmail)</label>
                        <input type="email" name="recovery_email" class="form-control form-control-sm" value="{{ $user->recovery_email ?? '' }}" placeholder="Enter recovery email">
                        <small class="text-muted" style="font-size: 0.70rem;">Used for account recovery if you forget your password.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">New Password</label>
                        <div class="input-group input-group-sm">
                            <input type="password" id="new_password" name="password" class="form-control" placeholder="Enter new password">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password" style="border-color: #ced4da;">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text" style="font-size: 0.70rem;">Leave both password fields blank if you do not want to change your current password.</div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted mb-1">Confirm New Password</label>
                        <div class="input-group input-group-sm">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Re-enter new password">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation" style="border-color: #ced4da;">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div> <!-- End Security Tab -->
        </div> <!-- End Tab Content -->

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-accent py-2 fw-bold shadow-sm">Update Account Profile</button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- ============================================== -->
<!-- PROFILE IMAGE ZOOM MODAL                       -->
<!-- ============================================== -->
@if($user->profile_image)
<div class="modal fade" id="zoomProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body text-center position-relative p-0">
                <!-- Close Button -->
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 shadow" data-bs-dismiss="modal" aria-label="Close" style="z-index: 1050;"></button>
                
                <!-- Zoomed Image -->
                <img src="data:{{ $user->image_type }};base64,{{ base64_encode($user->profile_image) }}" alt="Zoomed Profile Image" class="img-fluid rounded shadow-lg border border-4 border-white" style="max-height: 80vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>
@endif

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

@endsection