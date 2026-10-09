@extends('layouts.app')
@section('title', 'Yeni Müşteri Ekle - B&V Barber')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h1 class="fs-3 mb-1 fw-bold">Yeni Müşteri</h1>
                <p class="text-muted mb-0">Sisteme yeni bir müşteri kaydedin.</p>
            </div>
            <a href="{{ route('customers.index') }}" class="btn btn-light rounded-pill px-4 shadow-sm fw-semibold flex-shrink-0">
                <i class="ti ti-arrow-left me-1"></i> Geri Dön
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-10">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf
                    
                    <!-- Kişisel Bilgiler -->
                    <div class="mb-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                <i class="ti ti-user fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Kişisel Bilgiler</h5>
                                <small class="text-muted">Müşterinin temel kimlik bilgileri</small>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Ad <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control bg-light border-0 shadow-none p-3 @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" placeholder="Müşterinin adı" required>
                                @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Soyad <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control bg-light border-0 shadow-none p-3 @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" placeholder="Müşterinin soyadı" required>
                                @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Doğum Tarihi</label>
                                <input type="date" name="birth_date" class="form-control bg-light border-0 shadow-none p-3 @error('birth_date') is-invalid @enderror" value="{{ old('birth_date') }}">
                                @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Cinsiyet</label>
                                <select name="gender" class="form-select bg-light border-0 shadow-none p-3 @error('gender') is-invalid @enderror">
                                    <option value="">Seçiniz</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Erkek</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Kadın</option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Diğer</option>
                                </select>
                                @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-light my-4">

                    <!-- İletişim Bilgileri -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                <i class="ti ti-phone fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">İletişim Bilgileri</h5>
                                <small class="text-muted">Randevu bilgilendirmeleri için iletişim kanalları</small>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Telefon</label>
                                <input type="tel" name="phone" class="form-control bg-light border-0 shadow-none p-3 @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="5XX XXX XX XX">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">E-posta <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control bg-light border-0 shadow-none p-3 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="ornek@email.com" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 text-end">
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm fw-bold">
                            <i class="ti ti-check me-2"></i> Müşteriyi Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
