@extends('layouts.app')
@section('title', 'Çalışan Düzenle - B&V Barber')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fs-3 mb-1 fw-bold">Çalışan Profili</h1>
                <p class="text-muted">{{ $employee->user->full_name }} bilgilerini ve maaş ayarlarını yönetin.</p>
            </div>
            <a href="{{ route('employees.index') }}" class="btn btn-light rounded-pill border shadow-sm">
                <i class="ti ti-arrow-left me-1"></i> Geri Dön
            </a>
        </div>
    </div>
</div>

<form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <!-- Sol Taraf: Profil Resmi ve Özet -->
        <div class="col-xl-4 col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 mb-4 text-center">
                <div class="card-body p-4">
                    <div class="position-relative d-inline-block mb-3">
                        @if($employee->user && $employee->user->profile_photo)
                            <img src="{{ asset('storage/' . $employee->user->profile_photo) }}" class="rounded-circle shadow-lg object-fit-cover border border-4 border-white" style="width: 150px; height: 150px;" id="profilePreview" alt="Profil">
                        @else
                            <div class="rounded-circle shadow-lg d-flex align-items-center justify-content-center bg-primary-subtle text-primary border border-4 border-white mx-auto" style="width: 150px; height: 150px; font-size: 3rem; font-weight: bold;" id="profileInitial">
                                {{ mb_substr($employee->user->first_name, 0, 1) }}
                            </div>
                            <img src="" class="rounded-circle shadow-lg object-fit-cover border border-4 border-white d-none" style="width: 150px; height: 150px;" id="profilePreview" alt="Profil">
                        @endif
                        
                        <label for="profile_photo" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 shadow cursor-pointer" style="cursor: pointer;">
                            <i class="ti ti-camera fs-5"></i>
                        </label>
                        <input type="file" id="profile_photo" name="profile_photo" class="d-none @error('profile_photo') is-invalid @enderror" accept="image/*" onchange="previewImage(event)">
                    </div>
                    <h4 class="fw-bold mb-1">{{ $employee->user->full_name }}</h4>
                    <p class="text-muted mb-0">{{ $employee->title->name ?? 'Berber / Çalışan' }}</p>
                    
                    <div class="mt-4 pt-3 border-top text-start">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="isActive" {{ old('is_active', $employee->is_active) ? 'checked' : '' }} value="1">
                            <label class="form-check-label fw-medium" for="isActive">Sistemde Aktif (Randevu Alabilir)</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maaş Simülasyonu -->
            <div class="card shadow-sm border-0 rounded-4 bg-light bg-gradient">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="ti ti-calculator me-2 text-primary"></i>Kazanç Simülasyonu</h5>
                    <p class="small text-muted mb-3">Seçilen maaş tipine göre personelin <strong class="text-dark">10.000 ₺'lik</strong> bir hizmet cirosunda alacağı tahmini kazanç:</p>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Sabit Maaş:</span>
                        <span class="fw-bold text-dark" id="sim_salary">0,00 ₺</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Tahmini Prim:</span>
                        <span class="fw-bold text-success" id="sim_commission">0,00 ₺</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold text-dark">Toplam Tahmini Kazanç:</span>
                        <span class="fw-bold text-primary fs-5" id="sim_total">0,00 ₺</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sağ Taraf: Form Detayları -->
        <div class="col-xl-8 col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4 py-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="ti ti-user me-2"></i>Kişisel & İletişim Bilgileri</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">Ad <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control bg-light border-0 @error('first_name') is-invalid @enderror" value="{{ old('first_name', $employee->user->first_name) }}" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">Soyad <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control bg-light border-0 @error('last_name') is-invalid @enderror" value="{{ old('last_name', $employee->user->last_name) }}" required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">E-posta <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control bg-light border-0 @error('email') is-invalid @enderror" value="{{ old('email', $employee->user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">Telefon</label>
                            <input type="text" name="phone" class="form-control bg-light border-0 @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->user->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4 py-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="ti ti-lock me-2"></i>Erişim & Güvenlik</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">Yeni Şifre (İsteğe Bağlı)</label>
                            <input type="password" name="password" class="form-control bg-light border-0 @error('password') is-invalid @enderror" minlength="6" placeholder="Değiştirmek için yazın...">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-secondary">Sistem Rolü <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select bg-light border-0 @error('role_id') is-invalid @enderror" required>
                                <option value="">Seçiniz</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id', $employee->user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4 py-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="ti ti-cash-banknote me-2"></i>Çalışma ve Maaş Bilgileri</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-medium text-secondary">Unvan</label>
                            <select name="employee_title_id" class="form-select bg-light border-0 @error('employee_title_id') is-invalid @enderror">
                                <option value="">Unvan Seçin</option>
                                @foreach($titles as $t)
                                    <option value="{{ $t->id }}" {{ old('employee_title_id', $employee->employee_title_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                @endforeach
                            </select>
                            @error('employee_title_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label fw-medium text-secondary">İşe Başlama Tarihi <span class="text-danger">*</span></label>
                            <input type="date" name="hire_date" class="form-control bg-light border-0 @error('hire_date') is-invalid @enderror" value="{{ old('hire_date', $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '') }}" required>
                            @error('hire_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-secondary">Maaş Tipi <span class="text-danger">*</span></label>
                            <select name="salary_type" id="salary_type" class="form-select bg-light border-0 fw-bold @error('salary_type') is-invalid @enderror" required>
                                <option value="fixed" {{ old('salary_type', $employee->salary_type->value) == 'fixed' ? 'selected' : '' }}>Sabit Maaş</option>
                                <option value="commission" {{ old('salary_type', $employee->salary_type->value) == 'commission' ? 'selected' : '' }}>Sadece Prim</option>
                                <option value="fixed_plus_commission" {{ old('salary_type', $employee->salary_type->value) == 'fixed_plus_commission' ? 'selected' : '' }}>Maaş + Prim</option>
                                <option value="hourly" {{ old('salary_type', $employee->salary_type->value) == 'hourly' ? 'selected' : '' }}>Saatlik</option>
                            </select>
                            @error('salary_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-secondary">Maaş Tutarı (₺) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="salary_amount" id="salary_amount" class="form-control bg-light border-0 fw-bold text-primary @error('salary_amount') is-invalid @enderror" value="{{ old('salary_amount', $employee->salary_amount) }}" required>
                            @error('salary_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-secondary">Prim Oranı (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="commission_rate" id="commission_rate" class="form-control bg-light border-0 fw-bold text-success @error('commission_rate') is-invalid @enderror" value="{{ old('commission_rate', $employee->commission_rate) }}" required>
                                <span class="input-group-text bg-light border-0 fw-bold">%</span>
                            </div>
                            @error('commission_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

@push('scripts')
<script>
    // Profil resmi önizleme
    function previewImage(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('profilePreview');
                const initial = document.getElementById('profileInitial');
                
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                
                if (initial) {
                    initial.classList.add('d-none');
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Maaş Simülasyonu ve Input Görünürlüğü
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('salary_type');
        const amountInput = document.getElementById('salary_amount');
        const commInput = document.getElementById('commission_rate');
        
        const simSalary = document.getElementById('sim_salary');
        const simComm = document.getElementById('sim_commission');
        const simTotal = document.getElementById('sim_total');

        function updateSimulation() {
            const type = typeSelect.value;
            const amountCol = amountInput.closest('.col-md-4');
            const commCol = commInput.closest('.col-md-4');

            // Görünürlük Kontrolü
            if (type === 'commission') {
                amountCol.classList.add('d-none');
                commCol.classList.remove('d-none');
            } else if (type === 'fixed' || type === 'hourly') {
                amountCol.classList.remove('d-none');
                commCol.classList.add('d-none');
            } else { // fixed_plus_commission
                amountCol.classList.remove('d-none');
                commCol.classList.remove('d-none');
            }

            const amount = parseFloat(amountInput.value) || 0;
            const comm = parseFloat(commInput.value) || 0;
            const ciro = 10000; // Örnek aylık 10.000 TL ciro
            
            let calcSalary = 0;
            let calcComm = 0;

            if (type === 'fixed') {
                calcSalary = amount;
            } else if (type === 'commission') {
                calcSalary = 0;
                calcComm = (ciro * comm) / 100;
            } else if (type === 'fixed_plus_commission') {
                calcSalary = amount;
                calcComm = (ciro * comm) / 100;
            } else if (type === 'hourly') {
                calcSalary = amount * 160; // Ortalama 160 saat
            }

            simSalary.innerText = calcSalary.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
            simComm.innerText = calcComm.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
            simTotal.innerText = (calcSalary + calcComm).toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ₺';
        }

        typeSelect.addEventListener('change', updateSimulation);
        amountInput.addEventListener('input', updateSimulation);
        commInput.addEventListener('input', updateSimulation);

        // İlk yüklemede çalıştır
        updateSimulation();

    });
</script>
@endpush
