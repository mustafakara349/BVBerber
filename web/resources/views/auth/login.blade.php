@extends('layouts.auth')

@section('title', 'Giriş - B&V Barber Admin')

@section('content')
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card" style="max-width:420px; width:100%;">
        <div class="card-body p-5">
            <div class="text-center mb-3">
                <a href="/" class="mb-4 d-inline-block">
                    <span class="fw-bold text-primary fs-3">B&V</span>
                    <span class="fw-semibold fs-5 ms-1">Barber</span>
                </a>
                <h1 class="card-title mb-5 h5">Yönetim Paneline Giriş</h1>
            </div>

            <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">E-posta Adresi</label>
                    <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           placeholder="admin@bvbarber.com" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label d-flex justify-content-between">
                        <span>Şifre</span>
                    </label>
                    <input id="password" type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Şifrenizi girin" required minlength="6">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input id="remember" name="remember" class="form-check-input" type="checkbox" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="remember">Beni hatırla</label>
                    </div>
                </div>

                <button class="btn btn-primary w-100" type="submit"><i class="ti ti-login me-1"></i> Giriş Yap</button>
            </form>
        </div>
    </div>
</div>
@endsection
