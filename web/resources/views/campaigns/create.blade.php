@extends('layouts.app')
@section('title', 'Yeni Kampanya Oluştur - B&V Barber')

@section('content')
<form action="{{ route('campaigns.store') }}" method="POST" id="campaignForm">
    @csrf

    <!-- Page Header & Action Bar -->
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item">
                        <a href="{{ route('campaigns.index') }}" class="text-decoration-none text-muted">
                            <i class="ti ti-speakerphone me-1"></i>Kampanyalar & Kuponlar
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Yeni Kampanya</li>
                </ol>
            </nav>
            <h1 class="fs-3 fw-bold text-dark mb-0">Yeni Kampanya Oluştur</h1>
            <p class="text-muted small mb-0">Müşterileriniz için indirim veya hediye promosyonları yapılandırın.</p>
        </div>
        <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
            <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="ti ti-arrow-left"></i> Vazgeç
            </a>
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="ti ti-check"></i> Kampanyayı Kaydet
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEFT COLUMN: Form Details (8 Cols) -->
        <div class="col-12 col-lg-8">
            
            <!-- CARD 1: Temel Bilgiler -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="ti ti-info-circle fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Temel Bilgiler</h5>
                            <span class="text-muted small">Kampanyanın başlığı, türü ve müşteri bilgilendirme şartları</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Kampanya Başlığı <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="camp_title_input" class="form-control border-0 bg-light rounded-3 @error('title') is-invalid @enderror" placeholder="Örn: Yaz Sezonu İndirimi" value="{{ old('title') }}" required autofocus>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Kısa Açıklama (Opsiyonel)</label>
                        <textarea name="description" id="camp_desc_input" rows="2" class="form-control border-0 bg-light rounded-3 @error('description') is-invalid @enderror" placeholder="Müşterilere gösterilecek kısa tanıtım spotu...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary">Kampanya Türü <span class="text-danger">*</span></label>
                            <select name="type" id="camp_type_select" class="form-select border-0 bg-light rounded-3 @error('type') is-invalid @enderror" required>
                                <option value="auto_apply" {{ old('type') === 'auto_apply' ? 'selected' : '' }}>Sepette Otomatik Uygula</option>
                                <option value="coupon_required" {{ old('type') === 'coupon_required' ? 'selected' : '' }}>Kupon Kodu Gerektirir</option>
                            </select>
                            <div class="form-text small text-muted">Otomatik uygula seçilirse şartı sağlayan randevularda kendiliğinden aktif olur.</div>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold text-secondary">Kampanya Şartları & Kuralları</label>
                        <textarea name="terms" id="camp_terms_input" rows="5" class="form-control border-0 bg-light rounded-3 @error('terms') is-invalid @enderror" placeholder="Müşteri mobil uygulamasında kurallar sekmesinde listelenecek maddeler...">{{ old('terms', "• Bu kampanya sepetinizde otomatik olarak uygulanır.\n• Kampanya tüm müşterilerimiz için geçerlidir.\n• Bu kampanya kişi başı en fazla 1 defa kullanılabilir.\n• Diğer indirim ve kuponlarla birleştirilemez.\n• Sadece belirtilen geçerlilik tarihleri arasında geçerlidir.") }}</textarea>
                        @error('terms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- CARD 2: Kapsam ve Geçerlilik (Trigger Modülü) -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-info bg-opacity-10 text-info rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="ti ti-target fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Geçerlilik Kapsamı</h5>
                            <span class="text-muted small">Bu kampanya hangi hizmetlerde veya kategorilerde geçerli olacak?</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <label class="form-label fw-semibold text-secondary mb-2">Kapsam Türü <span class="text-danger">*</span></label>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-4">
                            <input type="radio" class="btn-check trigger-type-radio" name="trigger_type" id="trigger_all" value="all" {{ old('trigger_type', 'all') === 'all' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary rounded-3 w-100 p-3 text-start d-flex flex-column h-100" for="trigger_all">
                                <span class="fw-bold d-flex align-items-center gap-2">
                                    <i class="ti ti-world"></i> Tüm Hizmetler
                                </span>
                                <small class="text-muted mt-1" style="font-size: 0.75rem;">Şubedeki tüm hizmetlerde koşulsuz geçerlidir.</small>
                            </label>
                        </div>
                        <div class="col-12 col-md-4">
                            <input type="radio" class="btn-check trigger-type-radio" name="trigger_type" id="trigger_categories" value="categories" {{ old('trigger_type') === 'categories' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary rounded-3 w-100 p-3 text-start d-flex flex-column h-100" for="trigger_categories">
                                <span class="fw-bold d-flex align-items-center gap-2">
                                    <i class="ti ti-category"></i> Belirli Kategoriler
                                </span>
                                <small class="text-muted mt-1" style="font-size: 0.75rem;">Sadece seçilen kategorilere ait hizmetlerde geçerli.</small>
                            </label>
                        </div>
                        <div class="col-12 col-md-4">
                            <input type="radio" class="btn-check trigger-type-radio" name="trigger_type" id="trigger_services" value="services" {{ old('trigger_type') === 'services' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary rounded-3 w-100 p-3 text-start d-flex flex-column h-100" for="trigger_services">
                                <span class="fw-bold d-flex align-items-center gap-2">
                                    <i class="ti ti-scissors"></i> Belirli Hizmetler
                                </span>
                                <small class="text-muted mt-1" style="font-size: 0.75rem;">Tek tek işaretlenen özel hizmetlerde geçerli.</small>
                            </label>
                        </div>
                    </div>

                    <!-- Kategori Seçim Kutusu -->
                    <div id="categories_selector_wrapper" class="p-3 bg-light rounded-3 mb-3 d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-dark mb-0 small">Kategorileri Seçin</label>
                            <span class="badge bg-primary-subtle text-primary" id="cat_selected_count">0 seçildi</span>
                        </div>
                        <div class="row g-2" style="max-height: 200px; overflow-y: auto;">
                            @foreach($categories as $cat)
                                <div class="col-12 col-sm-6">
                                    <div class="form-check bg-white p-2 px-4 rounded-3 border-0 shadow-none">
                                        <input class="form-check-input cat-checkbox" type="checkbox" name="categories[]" value="{{ $cat->id }}" id="cat_{{ $cat->id }}" {{ in_array($cat->id, old('categories', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label small fw-semibold text-dark text-truncate" for="cat_{{ $cat->id }}">
                                            {{ $cat->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Hizmet Seçim Kutusu -->
                    <div id="services_selector_wrapper" class="p-3 bg-light rounded-3 mb-3 d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-dark mb-0 small">Hizmetleri Seçin</label>
                            <input type="text" id="service_filter_input" class="form-control form-control-sm border-0 bg-white rounded-pill px-3" placeholder="Hizmet ara..." style="max-width: 200px;">
                        </div>
                        <div class="row g-2" id="services_checkbox_container" style="max-height: 240px; overflow-y: auto;">
                            @foreach($services as $srv)
                                <div class="col-12 col-sm-6 service-item-row">
                                    <div class="form-check bg-white p-2 px-4 rounded-3 border-0 shadow-none d-flex align-items-center justify-content-between">
                                        <div class="text-truncate me-2">
                                            <input class="form-check-input srv-checkbox" type="checkbox" name="services[]" value="{{ $srv->id }}" id="srv_{{ $srv->id }}" {{ in_array($srv->id, old('services', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-semibold text-dark service-name-label text-truncate" for="srv_{{ $srv->id }}">
                                                {{ $srv->name }}
                                            </label>
                                        </div>
                                        <span class="badge bg-light text-secondary border small">₺{{ number_format($srv->price, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>

            <!-- CARD 3: Kampanya Ödülü (Reward Modülü) -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="ti ti-gift fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Kampanya Ödülü & Kazanım</h5>
                            <span class="text-muted small">Müşterinin bu kampanyadan elde edeceği faydayı belirleyin</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <label class="form-label fw-semibold text-secondary mb-2">Ödül Türü <span class="text-danger">*</span></label>
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-4">
                            <input type="radio" class="btn-check reward-type-radio" name="reward_type" id="reward_discount" value="discount" {{ old('reward_type', 'discount') === 'discount' ? 'checked' : '' }}>
                            <label class="btn btn-outline-success rounded-3 w-100 p-3 text-start d-flex flex-column h-100" for="reward_discount">
                                <span class="fw-bold d-flex align-items-center gap-2">
                                    <i class="ti ti-percentage"></i> İndirim
                                </span>
                                <small class="text-muted mt-1" style="font-size: 0.75rem;">Yüzde (%) veya Sabit Tutar (₺) indirimi uygular.</small>
                            </label>
                        </div>
                        <div class="col-12 col-md-4">
                            <input type="radio" class="btn-check reward-type-radio" name="reward_type" id="reward_gift_product" value="gift_product" {{ old('reward_type') === 'gift_product' ? 'checked' : '' }}>
                            <label class="btn btn-outline-success rounded-3 w-100 p-3 text-start d-flex flex-column h-100" for="reward_gift_product">
                                <span class="fw-bold d-flex align-items-center gap-2">
                                    <i class="ti ti-package"></i> Hediye Satış Ürünü
                                </span>
                                <small class="text-muted mt-1" style="font-size: 0.75rem;">Stoktan seçilen satış ürünü hediye verilir.</small>
                            </label>
                        </div>
                        <div class="col-12 col-md-4">
                            <input type="radio" class="btn-check reward-type-radio" name="reward_type" id="reward_gift_cafe" value="gift_cafe" {{ old('reward_type') === 'gift_cafe' ? 'checked' : '' }}>
                            <label class="btn btn-outline-success rounded-3 w-100 p-3 text-start d-flex flex-column h-100" for="reward_gift_cafe">
                                <span class="fw-bold d-flex align-items-center gap-2">
                                    <i class="ti ti-cup"></i> Hediye Kafe İçeceği
                                </span>
                                <small class="text-muted mt-1" style="font-size: 0.75rem;">Kafe menüsünden kahve veya meşrubat ikram edilir.</small>
                            </label>
                        </div>
                    </div>

                    <!-- İndirim Ayarları -->
                    <div id="reward_discount_section">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-secondary">İndirim Türü</label>
                                <select name="discount_type" id="camp_discount_type" class="form-select border-0 bg-light rounded-3">
                                    <option value="percentage" {{ old('discount_type', 'percentage') === 'percentage' ? 'selected' : '' }}>Yüzde (%)</option>
                                    <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Sabit Tutar (₺)</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-secondary">İndirim Tutarı / Oranı</label>
                                <div class="input-group">
                                    <input type="number" name="discount_value" id="camp_discount_value" step="0.01" min="0" class="form-control border-0 bg-light rounded-start-3" placeholder="0" value="{{ old('discount_value', 10) }}">
                                    <span class="input-group-text border-0 bg-light rounded-end-3" id="discount_unit_badge">%</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-secondary">Maksimum İndirim (₺)</label>
                                <input type="number" name="max_discount_amount" id="camp_max_discount" step="0.01" min="0" class="form-control border-0 bg-light rounded-3" placeholder="Limitsiz" value="{{ old('max_discount_amount') }}">
                                <div class="form-text small text-muted">Örn: Yüzde indirimde tavan sınır</div>
                            </div>
                        </div>
                    </div>

                    <!-- Hediye Satış Ürünü Seçimi -->
                    <div id="reward_gift_product_section" class="d-none">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary">Hediye Edilecek Satış Ürünü <span class="text-danger">*</span></label>
                            <select name="reward_product_id" id="camp_reward_product" class="form-select border-0 bg-light rounded-3">
                                <option value="">-- Ürün Seçin --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" {{ old('reward_product_id') == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->name }} (Mevcut Stok: {{ $prod->stock ?? 0 }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Hediye Kafe Ürünü Seçimi -->
                    <div id="reward_gift_cafe_section" class="d-none">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary">Hediye Edilecek Kafe Ürünü <span class="text-danger">*</span></label>
                            <select name="reward_cafe_product_id" id="camp_reward_cafe" class="form-select border-0 bg-light rounded-3">
                                <option value="">-- Kafe İçeceği / Ürün Seçin --</option>
                                @foreach($cafeProducts as $cprod)
                                    <option value="{{ $cprod->id }}" {{ old('reward_cafe_product_id') == $cprod->id ? 'selected' : '' }}>
                                        {{ $cprod->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>
            </div>

            <!-- CARD 4: Limitler ve Kurallar -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="ti ti-adjustments fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Limitler ve Öncelik</h5>
                            <span class="text-muted small">Sepet tutarı şartı ve müşteri kullanım sınırları</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-secondary">Min. Sepet Tutarı (₺)</label>
                            <input type="number" name="min_order_amount" id="camp_min_order" step="0.01" min="0" class="form-control border-0 bg-light rounded-3" placeholder="0 (Şartsız)" value="{{ old('min_order_amount', 0) }}">
                            <div class="form-text small text-muted">Boş veya 0 ise alt sınır aranmaz.</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-secondary">Kişi Başı Limit</label>
                            <input type="number" name="per_customer_limit" id="camp_per_customer" min="1" class="form-control border-0 bg-light rounded-3" placeholder="Limitsiz" value="{{ old('per_customer_limit', 1) }}">
                            <div class="form-text small text-muted">Bir müşterinin bu kampanyadan kaç kez yararlanabileceği.</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-secondary">Öncelik Sırası</label>
                            <input type="number" name="priority" min="0" class="form-control border-0 bg-light rounded-3" placeholder="0" value="{{ old('priority', 0) }}">
                            <div class="form-text small text-muted">Birden fazla kampanya çakışırsa yüksek olan önceliklidir.</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: Publishing, Dates & Actions (4 Cols) -->
        <div class="col-12 col-lg-4">
            
            <!-- CARD 5: Yayın Ayarları & Tarihler -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="ti ti-calendar-event fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Yayınlama & Tarihler</h5>
                            <span class="text-muted small">Zamanlama ve kitle ayarları</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Başlangıç Tarihi <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="camp_start_date" class="form-control border-0 bg-light rounded-3 @error('start_date') is-invalid @enderror" value="{{ old('start_date', date('Y-m-d')) }}" required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Bitiş Tarihi <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="camp_end_date" class="form-control border-0 bg-light rounded-3 @error('end_date') is-invalid @enderror" value="{{ old('end_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Hedef Kitle <span class="text-danger">*</span></label>
                        <select name="target_audience" id="camp_target_audience" class="form-select border-0 bg-light rounded-3" required>
                            <option value="all" {{ old('target_audience', 'all') === 'all' ? 'selected' : '' }}>Tüm Müşteriler</option>
                            <option value="new_customers" {{ old('target_audience') === 'new_customers' ? 'selected' : '' }}>Sadece Yeni Müşteriler</option>
                            <option value="loyalty_members" {{ old('target_audience') === 'loyalty_members' ? 'selected' : '' }}>Sadakat Üyeleri</option>
                        </select>
                    </div>

                    <!-- Hesaplanan Yayın Durumu Önizlemesi -->
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Yayın Durumu</label>
                        <div id="status_preview_badge_wrap">
                            <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill fw-medium d-inline-flex align-items-center gap-1" id="status_badge">
                                <i class="ti ti-circle-check"></i> Yayından {{ date('d.m.Y', strtotime('+30 days')) }}'nde kaldırılacak
                            </span>
                        </div>
                        <small class="text-muted d-block mt-1" id="status_help_text" style="font-size: 0.72rem;">
                            Başlangıç tarihi bugünden ileri ise kampanya başlangıç gününe kadar "Beklemede" durumunda kalır.
                        </small>
                    </div>

                    <div class="form-check form-switch p-3 bg-light rounded-3 d-flex justify-content-between align-items-center mb-0">
                        <div>
                            <label class="form-check-label fw-bold text-dark d-block" for="camp_is_active">Aktif Olarak Yayına Al</label>
                            <small class="text-muted" style="font-size: 0.75rem;">Kapalı tutulursa kampanya tamamen pasif kalır</small>
                        </div>
                        <input class="form-check-input fs-5 ms-0 mt-0" type="checkbox" name="is_active" id="camp_is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-pill py-3 shadow-sm d-flex align-items-center justify-content-center gap-2 fw-semibold">
                    <i class="ti ti-check fs-5"></i> Kampanyayı Kaydet
                </button>
                <a href="{{ route('campaigns.index') }}" class="btn btn-light rounded-pill py-2 text-secondary">
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
    const triggerRadios = document.querySelectorAll('.trigger-type-radio');
    const categoriesWrapper = document.getElementById('categories_selector_wrapper');
    const servicesWrapper = document.getElementById('services_selector_wrapper');
    const catCheckboxes = document.querySelectorAll('.cat-checkbox');
    const catSelectedCount = document.getElementById('cat_selected_count');
    const serviceFilterInput = document.getElementById('service_filter_input');

    const rewardRadios = document.querySelectorAll('.reward-type-radio');
    const discountSection = document.getElementById('reward_discount_section');
    const giftProductSection = document.getElementById('reward_gift_product_section');
    const giftCafeSection = document.getElementById('reward_gift_cafe_section');

    const discountTypeSelect = document.getElementById('camp_discount_type');
    const discountUnitBadge = document.getElementById('discount_unit_badge');

    const startDateInput = document.getElementById('camp_start_date');
    const endDateInput = document.getElementById('camp_end_date');
    const isActiveSwitch = document.getElementById('camp_is_active');
    const statusBadge = document.getElementById('status_badge');

    // --- Trigger Type Toggle ---
    function updateTriggerDisplay() {
        const selected = document.querySelector('.trigger-type-radio:checked')?.value || 'all';
        if (categoriesWrapper) categoriesWrapper.classList.toggle('d-none', selected !== 'categories');
        if (servicesWrapper) servicesWrapper.classList.toggle('d-none', selected !== 'services');
    }
    triggerRadios.forEach(radio => radio.addEventListener('change', updateTriggerDisplay));
    updateTriggerDisplay();

    // Category count update
    function updateCategoryCount() {
        if (!catCheckboxes || !catSelectedCount) return;
        const count = Array.from(catCheckboxes).filter(cb => cb.checked).length;
        catSelectedCount.textContent = count + ' seçildi';
    }
    catCheckboxes.forEach(cb => cb.addEventListener('change', updateCategoryCount));
    updateCategoryCount();

    // Service Live Filter
    if (serviceFilterInput) {
        serviceFilterInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.service-item-row').forEach(row => {
                const name = row.querySelector('.service-name-label')?.textContent.toLowerCase() || '';
                row.style.display = name.includes(query) ? '' : 'none';
            });
        });
    }

    // --- Reward Type Toggle ---
    function updateRewardDisplay() {
        const selected = document.querySelector('.reward-type-radio:checked')?.value || 'discount';
        if (discountSection) discountSection.classList.toggle('d-none', selected !== 'discount');
        if (giftProductSection) giftProductSection.classList.toggle('d-none', selected !== 'gift_product');
        if (giftCafeSection) giftCafeSection.classList.toggle('d-none', selected !== 'gift_cafe');
    }
    rewardRadios.forEach(radio => radio.addEventListener('change', updateRewardDisplay));
    updateRewardDisplay();

    // Discount type update
    function updateDiscountUnit() {
        const type = discountTypeSelect?.value || 'percentage';
        if (discountUnitBadge) discountUnitBadge.textContent = type === 'percentage' ? '%' : '₺';
    }
    if (discountTypeSelect) discountTypeSelect.addEventListener('change', updateDiscountUnit);
    updateDiscountUnit();

    // --- Status Calculation & Display ---
    function formatDateStr(val) {
        if (!val) return '';
        const parts = val.split('-');
        if (parts.length === 3) return `${parts[2]}.${parts[1]}.${parts[0]}`;
        return val;
    }

    function updateCalculatedStatus() {
        if (!statusBadge) return;
        const isActive = isActiveSwitch?.checked;
        const startVal = startDateInput?.value;
        const endVal = endDateInput?.value;

        if (!isActive) {
            statusBadge.className = 'badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill fw-medium d-inline-flex align-items-center gap-1';
            statusBadge.innerHTML = '<i class="ti ti-player-pause"></i> Pasif (Devre Dışı)';
            return;
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const startDate = startVal ? new Date(startVal) : null;
        if (startDate) startDate.setHours(0, 0, 0, 0);

        const endDate = endVal ? new Date(endVal) : null;
        if (endDate) endDate.setHours(23, 59, 59, 999);

        if (startDate && startDate > today) {
            statusBadge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning px-3 py-2 rounded-pill fw-medium d-inline-flex align-items-center gap-1';
            statusBadge.innerHTML = `<i class="ti ti-clock"></i> Beklemede ${formatDateStr(startVal)}'nde yayınlanacak`;
        } else if (endDate && endDate < today) {
            statusBadge.className = 'badge bg-danger-subtle text-danger border border-danger px-3 py-2 rounded-pill fw-medium d-inline-flex align-items-center gap-1';
            statusBadge.innerHTML = `<i class="ti ti-alert-circle"></i> Süresi Doldu (${formatDateStr(endVal)})`;
        } else {
            statusBadge.className = 'badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill fw-medium d-inline-flex align-items-center gap-1';
            const endStr = endVal ? `${formatDateStr(endVal)}'nde` : 'süresiz';
            statusBadge.innerHTML = `<i class="ti ti-circle-check"></i> Yayından ${endStr} kaldırılacak`;
        }
    }

    if (startDateInput) startDateInput.addEventListener('change', updateCalculatedStatus);
    if (endDateInput) endDateInput.addEventListener('change', updateCalculatedStatus);
    if (isActiveSwitch) isActiveSwitch.addEventListener('change', updateCalculatedStatus);
    updateCalculatedStatus();
});
</script>
@endpush
