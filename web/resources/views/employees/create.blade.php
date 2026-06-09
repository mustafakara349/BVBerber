@extends('layouts.app')
@section('title', 'Yeni Çalışan Ekle - B&V Barber')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fs-3 mb-1">Yeni Çalışan</h1>
                <p class="text-muted">Sisteme yeni bir personel kaydedin.</p>
            </div>
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Geri Dön
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-10">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4">
                <form action="{{ route('employees.store') }}" method="POST">
                    @csrf
                    
                    <h5 class="mb-3 text-primary border-bottom pb-2">Kişisel Bilgiler</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Ad <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Soyad <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">E-posta <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Telefon</label>
                            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Geçici Şifre <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="6">
                            <small class="text-muted">En az 6 karakter olmalıdır.</small>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Sistem Rolü <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                <option value="">Seçiniz</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h5 class="mb-3 text-primary border-bottom pb-2">Çalışma Bilgileri</h5>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-medium">Unvan</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Örn: Kıdemli Berber">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Maaş Tipi <span class="text-danger">*</span></label>
                            <select name="salary_type" class="form-select @error('salary_type') is-invalid @enderror" required>
                                <option value="fixed" {{ old('salary_type') == 'fixed' ? 'selected' : '' }}>Sabit Maaş</option>
                                <option value="commission" {{ old('salary_type') == 'commission' ? 'selected' : '' }}>Sadece Prim</option>
                                <option value="fixed_plus_commission" {{ old('salary_type') == 'fixed_plus_commission' ? 'selected' : '' }}>Maaş + Prim</option>
                                <option value="hourly" {{ old('salary_type') == 'hourly' ? 'selected' : '' }}>Saatlik</option>
                            </select>
                            @error('salary_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Maaş Tutarı (₺) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="salary_amount" class="form-control @error('salary_amount') is-invalid @enderror" value="{{ old('salary_amount', '0') }}" required>
                            @error('salary_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Prim Oranı (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="commission_rate" class="form-control @error('commission_rate') is-invalid @enderror" value="{{ old('commission_rate', '0') }}" required>
                            @error('commission_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-5 text-end">
                        <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                            <i class="ti ti-check me-1"></i> Çalışanı Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
