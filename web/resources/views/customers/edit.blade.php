@extends('layouts.app')
@section('title', 'Müşteri Düzenle - B&V Barber')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fs-3 mb-1 fw-bold">Müşteri Düzenle</h1>
                <p class="text-muted">{{ $customer->full_name }} bilgilerini ve hesap ayarlarını güncelleyin.</p>
            </div>
            <a href="{{ route('customers.index') }}" class="btn btn-light rounded-pill border shadow-sm">
                <i class="ti ti-arrow-left me-1"></i> Geri Dön
            </a>
        </div>
    </div>
</div>

<form action="{{ route('customers.update', $customer) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <!-- Sol Taraf: Özet ve Avatar -->
        <div class="col-xl-4 col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 mb-4 text-center">
                <div class="card-body p-4">
                    @if($customer->profile_photo_url)
                        <img src="{{ $customer->profile_photo_url }}" class="rounded-circle shadow-lg object-fit-cover border border-4 border-white mb-3" style="width: 150px; height: 150px;" alt="{{ $customer->full_name }}">
                    @else
                        <div class="rounded-circle shadow-lg d-flex align-items-center justify-content-center bg-primary-subtle text-primary border border-4 border-white mx-auto mb-3" style="width: 150px; height: 150px; font-size: 3rem; font-weight: bold;">
                            {{ mb_substr($customer->first_name, 0, 1) }}{{ mb_substr($customer->last_name, 0, 1) }}
                        </div>
                    @endif
                    <h4 class="fw-bold mb-1">{{ $customer->full_name }}</h4>
                    <p class="text-muted mb-0">Kayıtlı Müşteri</p>
                    
                    <div class="mt-4 pt-3 border-top text-start">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-mail text-secondary me-2"></i>
                            <span class="fw-medium text-dark">{{ $customer->email }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="ti ti-phone text-secondary me-2"></i>
                            <span class="fw-medium text-dark">{{ $customer->phone ?? 'Belirtilmemiş' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sağ Taraf: Form Detayları -->
        <div class="col-xl-8 col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4 py-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="ti ti-user me-2"></i>Kişisel Bilgiler</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">Ad <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control bg-light border-0 @error('first_name') is-invalid @enderror" value="{{ old('first_name', $customer->first_name) }}" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">Soyad <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control bg-light border-0 @error('last_name') is-invalid @enderror" value="{{ old('last_name', $customer->last_name) }}" required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">E-posta <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control bg-light border-0 @error('email') is-invalid @enderror" value="{{ old('email', $customer->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">Telefon</label>
                            <input type="text" name="phone" class="form-control bg-light border-0 @error('phone') is-invalid @enderror" value="{{ old('phone', $customer->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">Doğum Tarihi</label>
                            <input type="date" name="birth_date" class="form-control bg-light border-0 @error('birth_date') is-invalid @enderror" value="{{ old('birth_date', $customer->birth_date?->format('Y-m-d')) }}">
                            @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">Cinsiyet</label>
                            <select name="gender" class="form-select bg-light border-0 @error('gender') is-invalid @enderror">
                                <option value="">Seçiniz</option>
                                <option value="male" {{ old('gender', $customer->gender?->value) == 'male' ? 'selected' : '' }}>Erkek</option>
                                <option value="female" {{ old('gender', $customer->gender?->value) == 'female' ? 'selected' : '' }}>Kadın</option>
                                <option value="other" {{ old('gender', $customer->gender?->value) == 'other' ? 'selected' : '' }}>Diğer</option>
                            </select>
                            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4 py-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="ti ti-settings me-2"></i>Hesap Ayarları</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">Hesap Durumu <span class="text-danger">*</span></label>
                            <select name="status" class="form-select bg-light border-0 @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $customer->status->value) == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ old('status', $customer->status->value) == 'inactive' ? 'selected' : '' }}>Pasif</option>
                                <option value="blocked" {{ old('status', $customer->status->value) == 'blocked' ? 'selected' : '' }}>Engelli</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">Yeni Şifre (İsteğe Bağlı)</label>
                            <input type="password" name="password" class="form-control bg-light border-0 @error('password') is-invalid @enderror" minlength="6" placeholder="Değiştirmek için yazın...">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm fw-bold fs-6">
                    <i class="ti ti-check me-1"></i> Değişiklikleri Kaydet
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
