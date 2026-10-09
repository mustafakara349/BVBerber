@extends('layouts.auth')

@section('title', 'Yeni Şifre Belirle - B&V Barber')

@push('styles')
<style>
    /* Auth Page Background & Layout */
    body.bg-auth {
        background-color: #f8fafc;
        background-image: 
            radial-gradient(at 50% 0%, rgba(230, 98, 57, 0.07) 0px, transparent 65%),
            radial-gradient(#e2e8f0 1.2px, transparent 1.2px);
        background-size: 100% 100%, 28px 28px;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 24px 16px;
    }

    .auth-container {
        width: 100%;
        max-width: 440px;
        margin: 0 auto;
    }

    /* Card */
    .auth-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        position: relative;
    }

    .auth-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #E66239;
    }

    .auth-card-body {
        padding: 2.5rem 2.25rem;
    }

    @media (max-width: 576px) {
        .auth-card-body {
            padding: 2rem 1.5rem;
        }
    }

    /* Brand Logo & Header */
    .auth-logo-img {
        width: 80px;
        height: 80px;
        object-fit: contain;
    }

    /* Auth Alert Banner */
    .auth-alert {
        background: #fef2f2 !important;
        border: 1px solid #fee2e2 !important;
        border-left: 4px solid #ef4444 !important;
        color: #991b1b !important;
        border-radius: 12px !important;
        padding: 12px 16px !important;
    }
    
    .auth-alert-success {
        background: #f0fdf4 !important;
        border: 1px solid #dcfce7 !important;
        border-left: 4px solid #22c55e !important;
        color: #166534 !important;
        border-radius: 12px !important;
        padding: 12px 16px !important;
    }

    .auth-alert-icon {
        width: 32px;
        height: 32px;
        min-width: 32px;
        border-radius: 50%;
        background-color: #fee2e2;
        color: #dc2626;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-right: 14px;
    }

    .auth-alert-text {
        color: #991b1b;
        font-size: 0.9rem;
        line-height: 1.4;
    }

    /* Form Inputs */
    .form-label {
        font-size: 0.84rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.4rem;
    }

    .auth-input-group {
        border-radius: 10px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        overflow: hidden;
    }

    .auth-input-group:focus-within {
        border-color: #E66239;
        box-shadow: 0 0 0 3px rgba(230, 98, 57, 0.14);
    }

    .auth-input-group.has-error {
        border-color: #fca5a5;
    }

    .auth-input-group.has-error:focus-within {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.14);
    }

    .auth-input-group .input-group-text {
        background-color: transparent;
        border: none;
        color: #94a3b8;
        padding-left: 14px;
        padding-right: 10px;
        transition: color 0.15s ease;
    }

    .auth-input-group:focus-within .input-group-text {
        color: #E66239;
    }

    .auth-input-group.has-error .input-group-text {
        color: #ef4444;
    }

    .auth-input-group .form-control {
        border: none;
        background: transparent;
        font-size: 0.915rem;
        color: #1e293b;
        padding: 0.72rem 0.85rem 0.72rem 0.2rem;
        box-shadow: none !important;
    }

    .auth-input-group .form-control::placeholder {
        color: #94a3b8;
        font-size: 0.88rem;
    }

    .btn-toggle-password {
        background: transparent;
        border: none;
        color: #94a3b8;
        padding: 0 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: color 0.15s ease;
    }

    .btn-toggle-password:hover {
        color: #475569;
    }

    /* Solid Clean Button - No artificial shadow */
    .btn-auth-submit {
        background-color: #E66239;
        border: 1px solid #E66239;
        color: #ffffff;
        font-weight: 600;
        font-size: 0.95rem;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        box-shadow: none !important;
        transition: background-color 0.15s ease, border-color 0.15s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-auth-submit:hover {
        background-color: #cf542d;
        border-color: #cf542d;
        color: #ffffff;
    }

    /* Return link */
    .return-link {
        color: #64748b;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: color 0.2s ease;
    }
    
    .return-link:hover {
        color: #0f172a;
    }

    /* Footer */
    .auth-footer {
        text-align: center;
        margin-top: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-card-body">
            
            {{-- Brand & Header --}}
            <div class="text-center mb-4">
                <a href="/" class="d-inline-block mb-2">
                    <img src="{{ asset('images/icon.png') }}" alt="B&V Barber" class="auth-logo-img">
                </a>
                <h1 class="h5 fw-bold text-dark mt-2 mb-1">Yeni Şifre Belirle</h1>
                <p class="text-muted small mb-0">Lütfen hesabınız için yeni bir şifre oluşturun.</p>
            </div>

            {{-- Status Messages --}}
            @if (session('success'))
                <div class="alert auth-alert-success d-flex align-items-center mb-4" role="alert">
                    <i class="ti ti-circle-check fs-5 text-success me-2 flex-shrink-0"></i>
                    <div class="small fw-semibold">{{ session('success') }}</div>
                </div>
            @endif

            {{-- Error Alert Banner --}}
            @if ($errors->any())
                <div class="alert auth-alert d-flex align-items-center mb-4" role="alert">
                    <div class="auth-alert-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <div class="auth-alert-text flex-grow-1">
                        @if ($errors->count() == 1)
                            <div class="fw-semibold">{{ $errors->first() }}</div>
                        @else
                            <div class="fw-semibold mb-1">İşlem başarısız:</div>
                            <ul class="mb-0 ps-3 small fw-medium">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <button type="button" class="btn-close ms-2 small" data-bs-dismiss="alert" aria-label="Kapat" style="font-size: 0.7rem;"></button>
                </div>
            @endif

            {{-- Reset Password Form --}}
            <form method="POST" action="{{ route('password.update') }}" class="needs-validation" novalidate id="resetForm">
                @csrf

                {{-- Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label">Yeni Şifre</label>
                    <div class="input-group auth-input-group @if($errors->any()) has-error @endif">
                        <span class="input-group-text">
                            <i class="ti ti-lock fs-5"></i>
                        </span>
                        <input id="password" 
                               type="password" 
                               name="password"
                               class="form-control"
                               placeholder="En az 6 karakter" 
                               required 
                               minlength="6"
                               autofocus>
                        <button type="button" class="btn-toggle-password" id="togglePasswordBtn" tabindex="-1">
                            <i class="ti ti-eye fs-5" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Yeni Şifre (Tekrar)</label>
                    <div class="input-group auth-input-group @if($errors->any()) has-error @endif">
                        <span class="input-group-text">
                            <i class="ti ti-lock-check fs-5"></i>
                        </span>
                        <input id="password_confirmation" 
                               type="password" 
                               name="password_confirmation"
                               class="form-control"
                               placeholder="Şifreyi tekrar girin" 
                               required 
                               minlength="6">
                        <button type="button" class="btn-toggle-password" id="toggleConfirmBtn" tabindex="-1">
                            <i class="ti ti-eye fs-5" id="toggleConfirmIcon"></i>
                        </button>
                    </div>
                </div>

                {{-- Submit Button --}}
                <button class="btn btn-auth-submit w-100 mb-4" type="submit" id="submitBtn">
                    <i class="ti ti-device-floppy fs-5"></i>
                    <span>Şifreyi Kaydet</span>
                </button>
                
                <div class="text-center">
                    <a href="{{ route('login') }}" class="return-link">
                        <i class="ti ti-arrow-left"></i>
                        <span>İptal Et ve Çık</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Footer Info --}}
    <div class="auth-footer">
        <p class="text-muted small mb-0 opacity-75">
            &copy; {{ date('Y') }} B&V Barber. Tüm hakları saklıdır.
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle new password
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleIcon.className = isPassword ? 'ti ti-eye-off fs-5' : 'ti ti-eye fs-5';
            });
        }

        // Toggle confirm password
        const toggleConfirmBtn = document.getElementById('toggleConfirmBtn');
        const confirmInput = document.getElementById('password_confirmation');
        const toggleConfirmIcon = document.getElementById('toggleConfirmIcon');

        if (toggleConfirmBtn && confirmInput && toggleConfirmIcon) {
            toggleConfirmBtn.addEventListener('click', function () {
                const isPassword = confirmInput.getAttribute('type') === 'password';
                confirmInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleConfirmIcon.className = isPassword ? 'ti ti-eye-off fs-5' : 'ti ti-eye fs-5';
            });
        }

        // Button submit loading state
        const form = document.getElementById('resetForm');
        const submitBtn = document.getElementById('submitBtn');

        if (form && submitBtn) {
            form.addEventListener('submit', function () {
                if (form.checkValidity()) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Kaydediliyor...';
                    form.submit();
                }
            });
        }
    });
</script>
@endpush
