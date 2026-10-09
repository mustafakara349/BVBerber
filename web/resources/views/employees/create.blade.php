@extends('layouts.app')
@section('title', 'Yeni Çalışan Ekle - B&V Barber')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h1 class="fs-3 mb-1 fw-bold">Yeni Çalışan</h1>
                <p class="text-muted mb-0">Sisteme yeni bir personel kaydedin.</p>
            </div>
            <a href="{{ route('employees.index') }}" class="btn btn-light rounded-pill px-4 shadow-sm fw-semibold flex-shrink-0">
                <i class="ti ti-arrow-left me-1"></i> Geri Dön
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-10">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Kişisel Bilgiler -->
                    <div class="mb-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                <i class="ti ti-user-edit fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Kişisel Bilgiler</h5>
                                <small class="text-muted">Çalışanın temel kimlik ve görünüm bilgileri</small>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Ad <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control bg-light border-0 shadow-none p-3 @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" placeholder="Çalışan adı" required>
                                @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Soyad <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control bg-light border-0 shadow-none p-3 @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" placeholder="Çalışan soyadı" required>
                                @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">Profil Fotoğrafı</label>
                                <input type="file" name="profile_photo" class="form-control bg-light border-0 shadow-none p-3 @error('profile_photo') is-invalid @enderror" accept="image/*">
                                <small class="text-muted mt-2 d-block">Personel için bir profil fotoğrafı yükleyebilirsiniz. (Maks. 2MB)</small>
                                @error('profile_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-light my-4">

                    <!-- Hesap ve İletişim Bilgileri -->
                    <div class="mb-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                <i class="ti ti-mail fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Hesap ve İletişim</h5>
                                <small class="text-muted">Sisteme giriş detayları ve iletişim numarası</small>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">E-posta <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control bg-light border-0 shadow-none p-3 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="ornek@firma.com" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Telefon</label>
                                <input type="tel" name="phone" class="form-control bg-light border-0 shadow-none p-3 @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="5XX XXX XX XX">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Geçici Şifre <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control bg-light border-0 shadow-none p-3 @error('password') is-invalid @enderror" placeholder="******" required minlength="6">
                                <small class="text-muted mt-2 d-block">En az 6 karakter olmalıdır.</small>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Sistem Rolü <span class="text-danger">*</span></label>
                                <select name="role_id" class="form-select bg-light border-0 shadow-none p-3 @error('role_id') is-invalid @enderror" required>
                                    <option value="">Seçiniz</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-light my-4">

                    <!-- Çalışma ve Finans Bilgileri -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                <i class="ti ti-wallet fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Çalışma ve Finans</h5>
                                <small class="text-muted">Unvan, maaş tipi ve komisyon detayları</small>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Unvan</label>
                                <select name="employee_title_id" class="form-select bg-light border-0 shadow-none p-3 @error('employee_title_id') is-invalid @enderror">
                                    <option value="">Unvan Seçin</option>
                                    @foreach($titles as $t)
                                        <option value="{{ $t->id }}" {{ old('employee_title_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                    @endforeach
                                </select>
                                @error('employee_title_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">İşe Başlama Tarihi <span class="text-danger">*</span></label>
                                <input type="date" name="hire_date" class="form-control bg-light border-0 shadow-none p-3 @error('hire_date') is-invalid @enderror" value="{{ old('hire_date', date('Y-m-d')) }}" required>
                                @error('hire_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Maaş Tipi <span class="text-danger">*</span></label>
                                <select name="salary_type" class="form-select bg-light border-0 shadow-none p-3 @error('salary_type') is-invalid @enderror" required>
                                    <option value="fixed" {{ old('salary_type') == 'fixed' ? 'selected' : '' }}>Sabit Maaş</option>
                                    <option value="commission" {{ old('salary_type') == 'commission' ? 'selected' : '' }}>Sadece Prim</option>
                                    <option value="fixed_plus_commission" {{ old('salary_type') == 'fixed_plus_commission' ? 'selected' : '' }}>Maaş + Prim</option>
                                    <option value="hourly" {{ old('salary_type') == 'hourly' ? 'selected' : '' }}>Saatlik</option>
                                </select>
                                @error('salary_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Maaş Tutarı (₺) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="salary_amount" class="form-control bg-light border-0 shadow-none p-3 @error('salary_amount') is-invalid @enderror" value="{{ old('salary_amount', '0') }}" required>
                                @error('salary_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Prim Oranı (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="commission_rate" class="form-control bg-light border-0 shadow-none p-3 @error('commission_rate') is-invalid @enderror" value="{{ old('commission_rate', '0') }}" required>
                                @error('commission_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 text-end">
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm fw-bold">
                            <i class="ti ti-check me-2"></i> Çalışanı Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
