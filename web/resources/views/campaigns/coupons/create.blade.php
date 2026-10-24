@extends('layouts.app')
@section('title', 'Yeni Kupon Kodu Oluştur - B&V Barber')

@section('content')
<form action="{{ route('campaigns.coupons.store') }}" method="POST" id="couponForm">
    @csrf

    <!-- Page Header & Action Bar -->
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item">
                        <a href="{{ route('campaigns.index') }}#couponsPanel" class="text-decoration-none text-muted">
                            <i class="ti ti-ticket me-1"></i>Kampanyalar & Kuponlar
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Yeni Kupon Kodu</li>
                </ol>
            </nav>
            <h1 class="fs-3 fw-bold text-dark mb-0">Yeni Kupon Kodu Oluştur</h1>
            <p class="text-muted small mb-0">Müşterilerinize veya promosyonlarınıza özel indirim kuponları tanımlayın.</p>
        </div>
        <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
            <a href="{{ route('campaigns.index') }}#couponsPanel" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="ti ti-arrow-left"></i> Vazgeç
            </a>
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="ti ti-check"></i> Kuponu Kaydet
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEFT COLUMN: Form Details (8 Cols) -->
        <div class="col-12 col-lg-8">
            
            <!-- CARD 1: Kupon Kodu ve Tanımı -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="ti ti-ticket fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Kupon Kodu ve Tanım</h5>
                            <span class="text-muted small">Kuponun kodu, başlığı ve dahili kullanım açıklaması</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Kupon Kodu <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light text-muted">
                                <i class="ti ti-barcode"></i>
                            </span>
                            <input type="text" name="code" id="coupon_code_input" class="form-control border-0 bg-light font-monospace fw-bold text-uppercase fs-5 @error('code') is-invalid @enderror" placeholder="BV-INDIRIM20" value="{{ old('code') }}" required autofocus style="letter-spacing: 1px;">
                            <button type="button" class="btn btn-outline-primary px-3 rounded-end-3" id="btn_generate_code" title="Benzersiz Rastgele Kod Üret">
                                <i class="ti ti-sparkles me-1"></i> Rastgele Kod Üret
                            </button>
                        </div>
                        <div class="form-text small text-muted">Müşterinin randevu alırken gireceği promosyon kodudur.</div>
                        @error('code')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Kupon Başlığı (Opsiyonel)</label>
                        <input type="text" name="title" id="coupon_title_input" class="form-control border-0 bg-light rounded-3 @error('title') is-invalid @enderror" placeholder="Örn: VIP Sadakat İndirimi, İlk Ziyaret Hediyesi" value="{{ old('title') }}">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold text-secondary">Açıklama (Opsiyonel)</label>
                        <textarea name="description" id="coupon_desc_input" rows="2" class="form-control border-0 bg-light rounded-3 @error('description') is-invalid @enderror" placeholder="Kuponun amacı veya dahili not...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- CARD 2: İndirim Oranı ve Kullanım Kotaları -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="ti ti-percentage fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">İndirim Oranı ve Limitler</h5>
                            <span class="text-muted small">Uygulanacak indirim miktarı ve kullanım sınırları</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary">İndirim Türü <span class="text-danger">*</span></label>
                            <select name="discount_type" id="coupon_discount_type" class="form-select border-0 bg-light rounded-3 @error('discount_type') is-invalid @enderror" required>
                                <option value="percentage" {{ old('discount_type', 'percentage') === 'percentage' ? 'selected' : '' }}>Yüzde (%)</option>
                                <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Sabit Tutar (₺)</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary">İndirim Tutarı / Oranı <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="discount_value" id="coupon_discount_value" step="0.01" min="0" class="form-control border-0 bg-light rounded-start-3 @error('discount_value') is-invalid @enderror" placeholder="0" value="{{ old('discount_value', 15) }}" required>
                                <span class="input-group-text border-0 bg-light rounded-end-3" id="coupon_discount_badge">%</span>
                            </div>
                            @error('discount_value')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary">Maksimum Toplam Kullanım <span class="text-danger">*</span></label>
                            <input type="number" name="usage_limit" id="coupon_usage_limit" min="1" class="form-control border-0 bg-light rounded-3 @error('usage_limit') is-invalid @enderror" placeholder="Örn: 100" value="{{ old('usage_limit', 50) }}" required>
                            <div class="form-text small text-muted">Kupon bu sayıya ulaştığında otomatik olarak tükenecektir.</div>
                            @error('usage_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary">Kişi Başı Kullanım Limiti <span class="text-danger">*</span></label>
                            <input type="number" name="per_customer_limit" id="coupon_per_customer" min="1" class="form-control border-0 bg-light rounded-3 @error('per_customer_limit') is-invalid @enderror" placeholder="1" value="{{ old('per_customer_limit', 1) }}" required>
                            <div class="form-text small text-muted">Bir müşterinin bu kuponu kaç defa kullanabileceği.</div>
                            @error('per_customer_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 3: Özel Kullanıcı / Müşteri Atama -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-info bg-opacity-10 text-info rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="ti ti-users fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Özel Müşteri Atama (Opsiyonel)</h5>
                                <span class="text-muted small">Kuponu sadece belirli müşterilerinize tanımlayabilirsiniz</span>
                            </div>
                        </div>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-semibold" id="selected_users_badge">
                            Herkes Kullanabilir
                        </span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <input type="text" id="user_search_input" class="form-control border-0 bg-light rounded-pill px-3" placeholder="Müşteri ara (isim, soyisim veya telefon)..." style="max-width: 350px;">
                        <button type="button" class="btn btn-link text-secondary text-decoration-none small p-0" id="btn_clear_user_selection">
                            Seçimi Sıfırla
                        </button>
                    </div>

                    <div class="border rounded-3 p-2 bg-light" style="max-height: 240px; overflow-y: auto;" id="user_list_container">
                        @foreach($users as $user)
                            <div class="user-select-row p-2 bg-white rounded-3 mb-1 d-flex align-items-center justify-content-between shadow-none">
                                <label class="d-flex align-items-center gap-3 w-100 mb-0" style="cursor: pointer;" for="user_{{ $user->id }}">
                                    <input class="form-check-input user-checkbox flex-shrink-0 mt-0" type="checkbox" name="user_ids[]" value="{{ $user->id }}" id="user_{{ $user->id }}" {{ in_array($user->id, old('user_ids', [])) ? 'checked' : '' }}>
                                    <div class="d-flex flex-column text-truncate">
                                        <span class="fw-semibold text-dark user-full-name small">{{ $user->first_name }} {{ $user->last_name }}</span>
                                        <span class="text-muted user-phone-number" style="font-size: 0.72rem;">{{ $user->phone ?? 'Telefon yok' }}</span>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-text small text-muted mt-2">
                        <i class="ti ti-info-circle me-1"></i>Hiçbir müşteri seçilmezse kupon genel geçerli olur ve kodu bilen herkes yararlanabilir.
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: Expiration & Actions (4 Cols) -->
        <div class="col-12 col-lg-4">
            
            <!-- CARD 4: Geçerlilik Süresi -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="ti ti-clock fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Son Geçerlilik Zamanı</h5>
                            <span class="text-muted small">Kuponun sona ereceği tarih ve saat</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Bitiş Tarihi & Saati <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="expires_at" id="coupon_expires_at" class="form-control border-0 bg-light rounded-3 @error('expires_at') is-invalid @enderror" value="{{ old('expires_at', date('Y-m-d\TH:i', strtotime('+14 days'))) }}" required>
                        @error('expires_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Preset Time Buttons -->
                    <label class="form-label small fw-semibold text-secondary mb-1">Hızlı Süre Seçimi</label>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-sm btn-light rounded-pill border px-3 preset-days-btn" data-days="7">+7 Gün</button>
                        <button type="button" class="btn btn-sm btn-light rounded-pill border px-3 preset-days-btn" data-days="14">+14 Gün</button>
                        <button type="button" class="btn btn-sm btn-light rounded-pill border px-3 preset-days-btn" data-days="30">+1 Ay</button>
                        <button type="button" class="btn btn-sm btn-light rounded-pill border px-3 preset-days-btn" data-days="90">+3 Ay</button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-pill py-3 shadow-sm d-flex align-items-center justify-content-center gap-2 fw-semibold">
                    <i class="ti ti-check fs-5"></i> Kupon Kodunu Oluştur
                </button>
                <a href="{{ route('campaigns.index') }}#couponsPanel" class="btn btn-light rounded-pill py-2 text-secondary">
                    Vazgeç
                </a>
            </div>

        </div>
    </div>
</form>
@endsection

@push('scripts')
<script nonce="{{ $cspNonce }}">
document.addEventListener('DOMContentLoaded', () => {
    // --- Elements ---
    const codeInput = document.getElementById('coupon_code_input');
    const discountTypeSelect = document.getElementById('coupon_discount_type');
    const discountBadge = document.getElementById('coupon_discount_badge');
    const expiresAtInput = document.getElementById('coupon_expires_at');
    const btnGenerateCode = document.getElementById('btn_generate_code');
    const presetButtons = document.querySelectorAll('.preset-days-btn');

    // User Selection Elements
    const userSearchInput = document.getElementById('user_search_input');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const selectedUsersBadge = document.getElementById('selected_users_badge');
    const btnClearUserSelection = document.getElementById('btn_clear_user_selection');

    // --- Generate Random Code Function ---
    function generateCouponCode() {
        const prefixes = ['BV', 'BERBER', 'VIP', 'FIRSAT', 'OZEL', 'YAZ'];
        const randomPrefix = prefixes[Math.floor(Math.random() * prefixes.length)];
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let randomStr = '';
        for (let i = 0; i < 5; i++) {
            randomStr += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return `${randomPrefix}-${randomStr}`;
    }

    if (btnGenerateCode) {
        btnGenerateCode.addEventListener('click', () => {
            const newCode = generateCouponCode();
            if (codeInput) {
                codeInput.value = newCode;
            }
        });
    }

    // --- Discount Unit Sync ---
    function updateDiscountUnit() {
        const type = discountTypeSelect?.value || 'percentage';
        if (discountBadge) discountBadge.textContent = type === 'percentage' ? '%' : '₺';
    }
    if (discountTypeSelect) discountTypeSelect.addEventListener('change', updateDiscountUnit);
    updateDiscountUnit();

    // --- Preset Days Click Handlers ---
    presetButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const days = parseInt(btn.dataset.days, 10);
            const now = new Date();
            now.setDate(now.getDate() + days);
            const iso = now.toISOString().substring(0, 16);
            if (expiresAtInput) {
                expiresAtInput.value = iso;
            }
        });
    });

    // --- User Filter & Selection ---
    function updateUserSelectionCount() {
        if (!userCheckboxes || !selectedUsersBadge) return;
        const selectedCount = Array.from(userCheckboxes).filter(cb => cb.checked).length;
        if (selectedCount === 0) {
            selectedUsersBadge.textContent = 'Herkes Kullanabilir';
            selectedUsersBadge.className = 'badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-semibold';
        } else {
            selectedUsersBadge.textContent = `${selectedCount} Müşteriye Özel`;
            selectedUsersBadge.className = 'badge bg-success-subtle text-success rounded-pill px-3 py-2 fw-semibold';
        }
    }
    userCheckboxes.forEach(cb => cb.addEventListener('change', updateUserSelectionCount));
    updateUserSelectionCount();

    if (btnClearUserSelection) {
        btnClearUserSelection.addEventListener('click', () => {
            userCheckboxes.forEach(cb => cb.checked = false);
            updateUserSelectionCount();
        });
    }

    if (userSearchInput) {
        userSearchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.user-select-row').forEach(row => {
                const name = row.querySelector('.user-full-name')?.textContent.toLowerCase() || '';
                const phone = row.querySelector('.user-phone-number')?.textContent.toLowerCase() || '';
                row.style.display = (name.includes(query) || phone.includes(query)) ? '' : 'none';
            });
        });
    }
});
</script>
@endpush
