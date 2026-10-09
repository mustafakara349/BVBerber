@extends('layouts.app')
@section('title', 'Yeni Randevu - B&V Barber')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h1 class="fs-3 mb-1 fw-bold">Yeni Randevu</h1>
                <p class="text-muted mb-0">Sisteme yeni bir randevu oluşturun.</p>
            </div>
            <a href="{{ route('appointments.index') }}" class="btn btn-light rounded-pill px-4 shadow-sm fw-semibold flex-shrink-0">
                <i class="ti ti-arrow-left me-1"></i> Geri Dön
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-10">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('appointments.store') }}">
                    @csrf
                    <input type="hidden" name="branch_id" value="{{ session('active_branch_id', 1) }}">

                    <!-- Temel Randevu Bilgileri -->
                    <div class="mb-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                <i class="ti ti-calendar-event fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Temel Bilgiler</h5>
                                <small class="text-muted">Müşteri, personel ve zamanlama detayları</small>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Müşteri <span class="text-danger">*</span></label>
                                <select name="customer_id" class="form-select bg-light border-0 shadow-none p-3 @error('customer_id') is-invalid @enderror" required>
                                    <option value="">Müşteri seçin</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->full_name }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Berber <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-select bg-light border-0 shadow-none p-3 @error('employee_id') is-invalid @enderror" required>
                                    <option value="">Berber seçin</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->user->full_name }}</option>
                                    @endforeach
                                </select>
                                @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Tarih <span class="text-danger">*</span></label>
                                <input type="date" id="appointmentDate" class="form-control bg-light border-0 shadow-none p-3" required min="{{ date('Y-m-d') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">Saat <span class="text-danger">*</span> <small class="text-muted fw-normal">(Önce berber ve tarih seçin)</small></label>
                                <input type="hidden" name="start_at" id="startAtInput" required>
                                @error('start_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <div id="timeSlotsContainer" class="d-flex flex-wrap gap-2 mt-2 p-3 bg-light rounded-4 border border-light">
                                    <span class="text-muted small"><i class="ti ti-info-circle me-1"></i>Berber ve tarih seçimi bekleniyor...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-light my-4">

                    <!-- Hizmet Seçimi -->
                    <div class="mb-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                <i class="ti ti-cut fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Hizmet Seçimi</h5>
                                <small class="text-muted">Randevu alınacak hizmetleri belirleyin</small>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-12">
                                <div id="servicesContainer">
                                    @php
                                        $featuredServices = $services->where('is_popular', true);
                                        $otherServices = $services->where('is_popular', false)->groupBy(function ($s) {
                                            return $s->category ? $s->category->name : 'Diğer';
                                        });
                                    @endphp

                                    @if($featuredServices->isNotEmpty())
                                        <div class="mb-4 p-4 bg-light rounded-4 border-0">
                                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2"><i class="ti ti-star text-warning"></i> Öne Çıkan Hizmetler</h6>
                                            <div class="row g-3">
                                                @foreach($featuredServices as $service)
                                                    <div class="col-md-6">
                                                        <div class="form-check custom-checkbox bg-white p-3 rounded-3 shadow-sm border border-light m-0">
                                                            <input class="form-check-input service-check ms-0 me-2 mt-1" type="checkbox"
                                                                data-service-id="{{ $service->id }}" data-price="{{ $service->effective_price }}"
                                                                data-duration="{{ $service->duration_minutes }}" id="service_{{ $service->id }}">
                                                            <label class="form-check-label w-100 fw-medium text-dark d-flex flex-column" for="service_{{ $service->id }}">
                                                                <span>{{ $service->name }}</span>
                                                                <span class="text-primary fw-bold mt-1">₺{{ number_format($service->effective_price, 0, ',', '.') }} <small class="text-muted fw-normal">({{ $service->duration_minutes }} dk)</small></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <h6 class="fw-bold text-dark mb-3">Tüm Hizmetler</h6>
                                    <div class="accordion shadow-sm border-0 rounded-4 overflow-hidden" id="servicesAccordion">
                                        @foreach($otherServices as $categoryName => $catServices)
                                            <div class="accordion-item border-0 border-bottom border-light">
                                                <h2 class="accordion-header" id="heading_{{ Str::slug($categoryName) }}">
                                                    <button class="accordion-button collapsed py-3 bg-light fw-bold text-dark shadow-none" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse_{{ Str::slug($categoryName) }}"
                                                        aria-expanded="false" aria-controls="collapse_{{ Str::slug($categoryName) }}">
                                                        <i class="ti ti-folder me-2 text-muted"></i> {{ $categoryName }}
                                                    </button>
                                                </h2>
                                                <div id="collapse_{{ Str::slug($categoryName) }}" class="accordion-collapse collapse"
                                                    aria-labelledby="heading_{{ Str::slug($categoryName) }}">
                                                    <div class="accordion-body bg-white p-4">
                                                        <div class="row g-3">
                                                            @foreach($catServices as $service)
                                                                <div class="col-md-6">
                                                                    <div class="form-check custom-checkbox bg-light p-3 rounded-3 shadow-none border m-0">
                                                                        <input class="form-check-input service-check ms-0 me-2 mt-1" type="checkbox"
                                                                            data-service-id="{{ $service->id }}" data-price="{{ $service->effective_price }}"
                                                                            data-duration="{{ $service->duration_minutes }}" id="service_{{ $service->id }}">
                                                                        <label class="form-check-label w-100 fw-medium text-dark d-flex flex-column" for="service_{{ $service->id }}">
                                                                            <span>{{ $service->name }}</span>
                                                                            <span class="text-primary fw-bold mt-1">₺{{ number_format($service->effective_price, 0, ',', '.') }} <small class="text-muted fw-normal">({{ $service->duration_minutes }} dk)</small></span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div id="selectedServices"></div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-light my-4">

                    <!-- Ekstra Detaylar -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                <i class="ti ti-notes fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Ekstra Detaylar</h5>
                                <small class="text-muted">Randevu kaynağı, kupon ve notlar</small>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Kaynak</label>
                                <select name="source" class="form-select bg-light border-0 shadow-none p-3">
                                    <option value="admin_panel">Admin Paneli</option>
                                    <option value="phone">Telefon</option>
                                    <option value="walk_in">Walk-in</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Kupon Kodu <span class="text-muted fw-normal small">(Opsiyonel)</span></label>
                                <select name="coupon_code" id="couponCodeSelect" class="form-select bg-light border-0 shadow-none p-3 @error('coupon_code') is-invalid @enderror">
                                    <option value="">Kupon uygulanmasın</option>
                                </select>
                                @error('coupon_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text text-success d-none mt-2 fw-medium" id="couponMessage"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Müşteri Notu</label>
                                <textarea name="customer_note" class="form-control bg-light border-0 shadow-none p-3" rows="3" placeholder="Müşterinin özel bir isteği varsa buraya girebilirsiniz."></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Dahili Not</label>
                                <textarea name="internal_note" class="form-control bg-light border-0 shadow-none p-3" rows="3" placeholder="Sadece personelin görebileceği iç notlar."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 text-end">
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm fw-bold">
                            <i class="ti ti-check me-2"></i> Randevu Oluştur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('selectedServices');
            const checkboxes = document.querySelectorAll('.service-check');
            const employeeSelect = document.querySelector('select[name="employee_id"]');
            const dateInput = document.getElementById('appointmentDate');
            const timeSlotsContainer = document.getElementById('timeSlotsContainer');
            const startAtInput = document.getElementById('startAtInput');

            // Hizmet seçimi mantığı
            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    container.innerHTML = '';
                    let idx = 0;
                    checkboxes.forEach(c => {
                        if (c.checked) {
                            container.innerHTML += `
                            <input type="hidden" name="services[${idx}][service_id]" value="${c.dataset.serviceId}">
                            <input type="hidden" name="services[${idx}][unit_price]" value="${c.dataset.price}">
                            <input type="hidden" name="services[${idx}][duration_minutes]" value="${c.dataset.duration}">
                            <input type="hidden" name="services[${idx}][quantity]" value="1">
                        `;
                            idx++;
                        }
                    });
                });
            });

            // Randevu saatleri getirme mantığı
            function fetchAvailableSlots() {
                const empId = employeeSelect.value;
                const date = dateInput.value;

                if (!empId || !date) {
                    timeSlotsContainer.innerHTML = '<span class="text-muted small"><i class="ti ti-info-circle me-1"></i>Berber ve tarih seçimi bekleniyor...</span>';
                    startAtInput.value = '';
                    return;
                }

                timeSlotsContainer.innerHTML = '<span class="text-muted small"><i class="ti ti-loader ti-spin me-1"></i>Saatler yükleniyor...</span>';
                startAtInput.value = '';

                fetch(`/appointments/available-slots?employee_id=${empId}&date=${date}`)
                    .then(res => res.json())
                    .then(slots => {
                        timeSlotsContainer.innerHTML = '';
                        if (slots.length === 0) {
                            timeSlotsContainer.innerHTML = '<span class="text-danger small"><i class="ti ti-alert-circle me-1"></i>Seçilen tarihte uygun saat bulunmuyor.</span>';
                            return;
                        }

                        slots.forEach(slot => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = `btn btn-outline-primary px-3 py-2 fw-semibold rounded-3 ${!slot.is_available ? 'disabled opacity-50 border-secondary text-secondary' : ''}`;
                            btn.textContent = slot.time;
                            if (!slot.is_available) {
                                btn.style.cursor = 'not-allowed';
                                btn.title = 'Dolu';
                            } else {
                                btn.addEventListener('click', () => {
                                    // Reset other buttons
                                    document.querySelectorAll('#timeSlotsContainer button').forEach(b => {
                                        b.classList.remove('active', 'btn-primary', 'text-white');
                                        b.classList.add('btn-outline-primary');
                                    });
                                    btn.classList.add('active', 'btn-primary', 'text-white');
                                    btn.classList.remove('btn-outline-primary');
                                    startAtInput.value = `${date}T${slot.time}`;
                                });
                            }
                            timeSlotsContainer.appendChild(btn);
                        });
                    })
                    .catch(err => {
                        console.error(err);
                        timeSlotsContainer.innerHTML = '<span class="text-danger small">Saatler getirilirken hata oluştu.</span>';
                    });
            }

            const customerSelect = document.querySelector('select[name="customer_id"]');

            employeeSelect.addEventListener('change', fetchAvailableSlots);
            dateInput.addEventListener('change', fetchAvailableSlots);

            // Kupon filtreleme mantığı
            const allCoupons = @json($coupons);
            const couponSelect = document.getElementById('couponCodeSelect');

            function updateAvailableCoupons() {
                const customerId = parseInt(customerSelect.value);
                couponSelect.innerHTML = '<option value="">Kupon uygulanmasın</option>';

                if (!customerId) return;

                const eligibleCoupons = allCoupons.filter(coupon => {
                    if (coupon.expires_at && new Date(coupon.expires_at) < new Date()) {
                        return false;
                    }
                    if (coupon.used_count >= coupon.usage_limit) {
                        return false;
                    }
                    const isRestricted = coupon.users && coupon.users.length > 0;
                    if (isRestricted) {
                        return coupon.users.some(u => parseInt(u.id) === customerId);
                    }
                    return true;
                });

                let customerSpecificCoupon = null;

                eligibleCoupons.forEach(coupon => {
                    const option = document.createElement('option');
                    option.value = coupon.code;

                    let typeStr = coupon.discount_type === 'percentage' ? '%' : '₺';
                    let discountValFormatted = parseFloat(coupon.discount_value).toFixed(0);
                    let detail = `${coupon.code} - ${coupon.title || 'Kupon'} (${typeStr}${discountValFormatted} İndirim)`;

                    const isRestricted = coupon.users && coupon.users.length > 0;
                    if (isRestricted) {
                        detail += ' [Müşteriye Özel]';
                        customerSpecificCoupon = coupon.code;
                    }
                    option.textContent = detail;
                    couponSelect.appendChild(option);
                });

                if (customerSpecificCoupon) {
                    couponSelect.value = customerSpecificCoupon;
                }
            }

            customerSelect.addEventListener('change', updateAvailableCoupons);
            if (customerSelect.value) {
                updateAvailableCoupons();
            }
        });
    </script>
@endpush