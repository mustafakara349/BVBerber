@extends('layouts.app')
@section('title', 'Kampanyalar & Kuponlar - B&V Barber')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="fs-3 mb-1">Kampanya & Kupon Yönetimi</h1>
                <p class="text-muted">Müşteri sadakatini artırmak için promosyonlar, indirim kampanyaları ve kuponlar oluşturun.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary rounded-pill px-4 btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#addCampaignModal">
                    <i class="ti ti-plus me-1"></i> Yeni Kampanya
                </button>
                <button class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#addCouponModal">
                    <i class="ti ti-ticket me-1"></i> Yeni Kupon Kodu
                </button>
            </div>
        </div>
    </div>
</div>

<!-- KPI Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 text-center">
                <div class="mx-auto mb-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="ti ti-speakerphone fs-3"></i>
                </div>
                <h3 class="fw-bold mb-1 text-dark">{{ $stats['total_campaigns'] }}</h3>
                <span class="text-secondary small fw-medium text-uppercase">Toplam Kampanya</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4" style="border-bottom: 4px solid #10b981 !important;">
            <div class="card-body p-4 text-center">
                <div class="mx-auto mb-3 bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="ti ti-circle-check fs-3"></i>
                </div>
                <h3 class="fw-bold mb-1 text-success">{{ $stats['active_campaigns'] }}</h3>
                <span class="text-secondary small fw-medium text-uppercase">Aktif Kampanya</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 text-center">
                <div class="mx-auto mb-3 bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="ti ti-ticket fs-3"></i>
                </div>
                <h3 class="fw-bold mb-1 text-dark">{{ $stats['total_coupons'] }}</h3>
                <span class="text-secondary small fw-medium text-uppercase">Toplam Kupon</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4" style="border-bottom: 4px solid #3b82f6 !important;">
            <div class="card-body p-4 text-center">
                <div class="mx-auto mb-3 bg-blue bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: rgba(59, 130, 246, 0.1);">
                    <i class="ti ti-discount-check fs-3" style="color: #3b82f6;"></i>
                </div>
                <h3 class="fw-bold mb-1 text-primary">{{ $stats['active_coupons'] }}</h3>
                <span class="text-secondary small fw-medium text-uppercase">Geçerli Kupon</span>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Card -->
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
        <ul class="nav nav-tabs border-bottom-0" id="campaignTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-uppercase fs-7 pb-3 border-0 border-bottom border-2" id="campaigns-tab" data-bs-toggle="tab" data-bs-target="#campaignsPanel" type="button" role="tab" aria-controls="campaignsPanel" aria-selected="true">
                    Kampanyalar ({{ $campaigns->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-uppercase fs-7 pb-3 border-0 border-bottom border-2 ms-3 text-secondary" id="coupons-tab" data-bs-toggle="tab" data-bs-target="#couponsPanel" type="button" role="tab" aria-controls="couponsPanel" aria-selected="false">
                    Kupon Kodları ({{ $coupons->count() }})
                </button>
            </li>
        </ul>
    </div>
    
    <div class="card-body p-4">
        <div class="tab-content" id="campaignTabContent">
            <!-- Campaigns Panel (Table Layout) -->
            <div class="tab-pane fade show active" id="campaignsPanel" role="tabpanel" aria-labelledby="campaigns-tab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Kampanya Başlığı</th>
                                <th>İndirim</th>
                                <th>Tarih Aralığı</th>
                                <th class="text-center">Durum</th>
                                <th class="text-end pe-4">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $camp)
                            <tr style="cursor:pointer;" onclick="if(!event.target.closest('.btn, form, input, .form-check')){showCampaignDetails({{ $camp->id }})}">
                                <td class="ps-4">
                                    <div class="fw-bold text-dark fs-6">{{ $camp->title }}</div>
                                    <div class="text-secondary small">
                                        @if($camp->reward_type === 'gift_product')
                                            Satış Ürünü Hediyesi
                                        @elseif($camp->reward_type === 'gift_cafe')
                                            Kafe Ürünü Hediyesi
                                        @else
                                            {{ $camp->discount_type->label() ?? 'İndirim' }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        @if($camp->reward_type === 'gift_product')
                                            <span class="text-primary"><i class="bi bi-gift-fill me-1"></i> Ürün Hediyesi</span>
                                        @elseif($camp->reward_type === 'gift_cafe')
                                            <span class="text-danger"><i class="bi bi-cup-hot-fill me-1"></i> İçecek Hediyesi</span>
                                        @else
                                            @if($camp->discount_type->value === 'percentage')
                                                %{{ number_format($camp->discount_value, 0) }} İndirim
                                            @else
                                                ₺{{ number_format($camp->discount_value, 2, ',', '.') }} İndirim
                                            @endif
                                        @endif
                                    </div>
                                    <div class="text-secondary small">Min: ₺{{ number_format($camp->min_order_amount, 2, ',', '.') }}</div>
                                </td>
                                <td>
                                    <div class="text-dark">{{ $camp->start_date ? \Carbon\Carbon::parse($camp->start_date)->format('d.m.Y') : '' }}</div>
                                    <div class="text-secondary small">{{ $camp->end_date ? \Carbon\Carbon::parse($camp->end_date)->format('d.m.Y') : '' }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch m-0 d-flex justify-content-center align-items-center">
                                        <input class="form-check-input mt-0" type="checkbox" role="switch"
                                               id="campSwitch{{ $camp->id }}" 
                                               {{ $camp->is_active ? 'checked' : '' }}
                                               onchange="document.getElementById('campForm{{ $camp->id }}').submit()">
                                        <label class="form-check-label ms-2 small fw-medium {{ $camp->is_active ? 'text-success' : 'text-secondary' }}" for="campSwitch{{ $camp->id }}">
                                            {{ $camp->is_active ? 'Aktif' : 'Pasif' }}
                                        </label>
                                    </div>
                                    <form id="campForm{{ $camp->id }}" action="{{ route('campaigns.toggle', $camp) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('campaigns.usages', $camp) }}" class="btn btn-sm btn-light border rounded-circle text-info" title="Kullanım Geçmişi">
                                            <i class="ti ti-history"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-light border rounded-circle text-warning" onclick="editCampaign({{ $camp->id }})" title="Düzenle">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <form method="POST" action="{{ route('campaigns.destroy', $camp) }}" onsubmit="return confirm('Bu kampanyayı silmek istediğinize emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border rounded-circle text-danger" title="Sil">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="ti ti-speakerphone fs-1 d-block mb-2"></i>
                                    Henüz oluşturulmuş kampanya bulunmuyor.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Coupons Panel -->
            <div class="tab-pane fade" id="couponsPanel" role="tabpanel" aria-labelledby="coupons-tab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Kupon Kodu</th>
                                <th>Kupon Başlığı / Türü</th>
                                <th class="text-center">Limit / Kullanım</th>
                                <th class="text-center">Geçerlilik Tarihi</th>
                                <th class="text-center">Durum</th>
                                <th class="text-end pe-4">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($coupons as $coup)
                            <tr style="cursor:pointer;" onclick="if(!event.target.closest('.btn, form, input, .form-check')){showCouponDetails({{ $coup->id }})}">
                                <td class="ps-4 fw-bold text-dark font-monospace fs-5 text-primary">
                                    <span class="bg-primary bg-opacity-10 px-2.5 py-1 rounded text-primary">{{ $coup->code }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $coup->title ?? 'Genel Kupon' }}</div>
                                    <div class="text-secondary small">
                                        @if($coup->discount_type->value === 'percentage')
                                            %{{ number_format($coup->discount_value, 0) }} İndirim
                                        @else
                                            ₺{{ number_format($coup->discount_value ?? 0, 2, ',', '.') }} İndirim
                                        @endif
                                    </div>
                                    @if($coup->users->count() > 0)
                                    <div class="text-info small mt-1">
                                        <i class="ti ti-users"></i>
                                        @if($coup->users->count() <= 2)
                                            {{ $coup->users->pluck('first_name')->implode(', ') }}
                                        @else
                                            {{ $coup->users->count() }} Kişiye Özel
                                        @endif
                                    </div>
                                    @endif
                                </td>
                                <td class="text-center fw-medium">
                                    {{ $coup->used_count }} / {{ $coup->usage_limit }}
                                    <div class="progress mt-1.5 mx-auto" style="height: 5px; width: 80px;">
                                        <div class="progress-bar bg-success" style="width: {{ min(100, ($coup->used_count / max(1, $coup->usage_limit)) * 100) }}%"></div>
                                    </div>
                                </td>
                                <td class="text-center text-secondary small">{{ $coup->expires_at ? \Carbon\Carbon::parse($coup->expires_at)->format('d.m.Y H:i') : 'Süresiz' }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $coup->isValid() ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-2.5 py-1 rounded-pill">
                                        {{ $coup->isValid() ? 'Geçerli' : 'Geçersiz/Tükenmiş' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('campaigns.coupons.usages', $coup) }}" class="btn btn-sm btn-light border rounded-circle text-info" title="Kullanım Geçmişi">
                                            <i class="ti ti-history"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-light border rounded-circle text-warning" onclick="editCoupon({{ $coup->id }})" title="Düzenle">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <form method="POST" action="{{ route('campaigns.coupons.destroy', $coup) }}" onsubmit="return confirm('Kupon kodunu silmek istediğinize emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border rounded-circle text-danger" title="Sil">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="ti ti-ticket fs-1 d-block mb-2"></i>
                                    Oluşturulmuş kupon kodu bulunmuyor.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Campaign Modal -->
<div class="modal fade" id="addCampaignModal" tabindex="-1" aria-labelledby="addCampaignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" action="{{ route('campaigns.store') }}" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow">
            @csrf
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="addCampaignModalLabel">Yeni Kampanya Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Kampanya Kapsamı (Neyde Geçerli?)</label>
                        <select name="trigger_type" id="add_trigger_type" class="form-select border-0 bg-light rounded-3" required onchange="toggleTriggerFields('add')">
                            <option value="all">Tüm Hizmetlerde Geçerli</option>
                            <option value="categories">Belirli Kategorilerde Geçerli</option>
                            <option value="services">Belirli Hizmetlerde Geçerli</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Kampanya Ödülü</label>
                        <select name="reward_type" id="add_reward_type" class="form-select border-0 bg-light rounded-3" required onchange="toggleRewardFields('add')">
                            <option value="discount">İndirim (Yüzde veya Tutar)</option>
                            <option value="gift_product">Hediye Satış Ürünü</option>
                            <option value="gift_cafe">Hediye Kafe Ürünü</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Kampanya Başlığı</label>
                    <input type="text" name="title" class="form-control border-0 bg-light rounded-3" placeholder="Örn: Yaz Sezonu İndirimi" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Açıklama (Opsiyonel)</label>
                    <textarea name="description" rows="2" class="form-control border-0 bg-light rounded-3" placeholder="Kampanya detayı..."></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Kampanya Şartları</label>
                    <textarea name="terms" rows="6" class="form-control border-0 bg-light rounded-3" placeholder="Kampanya şartları... (Müşteri uygulamasında görünecek)">• Bu kampanya sepetinizde otomatik olarak uygulanır.
• Kampanya tüm müşterilerimiz için geçerlidir.
• Kampanya tüm hizmet kategorilerinde geçerlidir.
• Bu kampanya kişi başı en fazla 1 defa kullanılabilir.
• Bu kampanya diğer indirimlerle birleştirilemez.
• Sadece belirtilen geçerlilik tarihleri arasında kullanılabilir.</textarea>
                </div>
                


                <!-- Kapsam Seçimleri -->
                <div class="mb-3 d-none" id="add_categories_wrapper">
                    <label class="form-label small fw-bold text-secondary">Geçerli Hizmet Kategorileri</label>
                    <div class="border rounded-3 p-3 bg-light" style="max-height: 180px; overflow-y: auto;">
                        @foreach($categories as $cat)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $cat->id }}" id="cat_add_{{ $cat->id }}">
                            <label class="form-check-label small fw-semibold text-dark" for="cat_add_{{ $cat->id }}">
                                {{ $cat->name }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="mb-3 d-none" id="add_services_wrapper">
                    <label class="form-label small fw-bold text-secondary">Geçerli Hizmetler</label>
                    <div class="border rounded-3 p-3 bg-light" style="max-height: 180px; overflow-y: auto;">
                        @foreach($services as $srv)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="services[]" value="{{ $srv->id }}" id="srv_add_{{ $srv->id }}">
                            <label class="form-check-label small fw-semibold text-dark" for="srv_add_{{ $srv->id }}">
                                {{ $srv->name }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Ödül Seçimleri -->
                <div id="add_discount_wrapper">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">İndirim Türü</label>
                            <select name="discount_type" class="form-select border-0 bg-light rounded-3">
                                <option value="percentage">Yüzde (%)</option>
                                <option value="fixed">Sabit Tutar (₺)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">İndirim Tutarı / Oranı</label>
                            <input type="number" name="discount_value" step="0.01" min="0" class="form-control border-0 bg-light rounded-3">
                        </div>
                    </div>
                </div>

                <div class="mb-3 d-none" id="add_gift_product_wrapper">
                    <label class="form-label small fw-bold text-secondary">Hediye Edilecek Satış Ürünü</label>
                    <select name="reward_product_id" class="form-select border-0 bg-light rounded-3">
                        <option value="">-- Ürün Seçin --</option>
                        @foreach($products as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 d-none" id="add_gift_cafe_wrapper">
                    <label class="form-label small fw-bold text-secondary">Hediye Edilecek Kafe Ürünü</label>
                    <select name="reward_cafe_product_id" class="form-select border-0 bg-light rounded-3">
                        <option value="">-- İçecek / Ürün Seçin --</option>
                        @foreach($cafeProducts as $cprod)
                        <option value="{{ $cprod->id }}">{{ $cprod->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6" id="add_type_wrapper">
                        <label class="form-label small fw-bold text-secondary">Kampanya Türü</label>
                        <select name="type" class="form-select border-0 bg-light rounded-3" required>
                            <option value="auto_apply">Sepette Otomatik Uygula</option>
                            <option value="coupon_required">Kupon Kodu Gerektirir</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Hedef Kitle</label>
                        <select name="target_audience" class="form-select border-0 bg-light rounded-3" required>
                            <option value="all">Tüm Müşteriler</option>
                            <option value="new_customers">Sadece Yeni Müşteriler</option>
                            <option value="loyalty_members">Sadece Sadakat Üyeleri</option>
                        </select>
                    </div>
                </div>
                
                <div class="row g-3 mb-3" id="add_limits_wrapper">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Minimum Sipariş Tutarı (₺)</label>
                        <input type="number" name="min_order_amount" step="0.01" min="0" class="form-control border-0 bg-light rounded-3" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Maksimum İndirim Tutarı (₺)</label>
                        <input type="number" name="max_discount_amount" step="0.01" min="0" class="form-control border-0 bg-light rounded-3" placeholder="Limitsiz">
                    </div>
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Görsel (Opsiyonel)</label>
                        <input type="file" name="image" class="form-control border-0 bg-light rounded-3" accept="image/*">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">Kişi Başı Limit</label>
                        <input type="number" name="per_customer_limit" min="1" class="form-control border-0 bg-light rounded-3" placeholder="Limitsiz">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">Öncelik (Sıra)</label>
                        <input type="number" name="priority" min="0" value="0" class="form-control border-0 bg-light rounded-3">
                    </div>
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Başlangıç Tarihi</label>
                        <input type="date" name="start_date" class="form-control border-0 bg-light rounded-3" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Bitiş Tarihi</label>
                        <input type="date" name="end_date" class="form-control border-0 bg-light rounded-3" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                    </div>
                </div>
                
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked value="1">
                    <label class="form-check-label small fw-semibold text-secondary" for="is_active">Aktif Olarak Yayına Al</label>
                </div>
            </div>
            <div class="modal-footer border-top-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Kampanyayı Oluştur</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Coupon Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1" aria-labelledby="addCouponModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('campaigns.coupons.store') }}" class="modal-content border-0 rounded-4 shadow">
            @csrf
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="addCouponModalLabel">Yeni Kupon Kodu Oluştur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Kupon Başlığı (Opsiyonel)</label>
                    <input type="text" name="title" class="form-control border-0 bg-light rounded-3" placeholder="Örn: Telafi İndirimi">
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Açıklama (Opsiyonel)</label>
                    <textarea name="description" rows="2" class="form-control border-0 bg-light rounded-3" placeholder="Kupon detayı..."></textarea>
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">İndirim Türü</label>
                        <select name="discount_type" class="form-select border-0 bg-light rounded-3" required>
                            <option value="percentage">Yüzde (%)</option>
                            <option value="fixed">Sabit Tutar (₺)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">İndirim Tutarı / Oranı</label>
                        <input type="number" name="discount_value" step="0.01" min="0" class="form-control border-0 bg-light rounded-3" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Özel Kullanıcı Atama (Çoklu Seçim Yapabilirsiniz)</label>
                    <input type="text" id="add_coup_user_search" class="form-control form-control-sm border-0 bg-light mb-2 rounded-3" placeholder="Kullanıcı ara (isim, telefon)...">
                    <div class="list-group user-select-list" id="add_coup_user_list" style="max-height: 180px; overflow-y: auto;">
                        @foreach($users as $u)
                        <label class="list-group-item d-flex align-items-center gap-2 border-0 bg-light rounded-3 mb-1" style="cursor: pointer;">
                            <input class="form-check-input flex-shrink-0 mt-0" type="checkbox" name="user_ids[]" value="{{ $u->id }}">
                            <span class="user-text">{{ $u->first_name }} {{ $u->last_name }} <small class="text-muted">({{ $u->phone }})</small></span>
                        </label>
                        @endforeach
                    </div>
                    <div class="form-text small text-secondary mt-1">Boş bırakılırsa herkes kullanabilir. Sadece listeden üzerine tıklayarak kolayca seçebilirsiniz.</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Kupon Kodu</label>
                    <div class="input-group">
                        <input type="text" name="code" id="couponCodeInput" class="form-control border-0 bg-light rounded-start-3 font-monospace fw-bold text-uppercase" placeholder="KOD-YAZ" required>
                        <button type="button" class="btn btn-outline-secondary border-0 bg-light text-primary rounded-end-3" onclick="generateRandomCouponCode()">
                            Rastgele Üret
                        </button>
                    </div>
                </div>
                
                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Maks. Toplam Kullanım</label>
                        <input type="number" name="usage_limit" min="1" class="form-control border-0 bg-light rounded-3" placeholder="Örn: 100 Ziyaret" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Kişi Başı Limit</label>
                        <input type="number" name="per_customer_limit" min="1" value="1" class="form-control border-0 bg-light rounded-3" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Son Geçerlilik Zamanı</label>
                    <input type="datetime-local" name="expires_at" class="form-control border-0 bg-light rounded-3" value="{{ date('Y-m-d\TH:i', strtotime('+7 days')) }}" required>
                </div>
            </div>
            <div class="modal-footer border-top-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Kupon Kodunu Üret</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Campaign Modal -->
<div class="modal fade" id="editCampaignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" id="editCampaignForm" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow">
            @csrf
            @method('PUT')
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Kampanyayı Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Kampanya Kapsamı (Neyde Geçerli?)</label>
                        <select name="trigger_type" id="edit_trigger_type" class="form-select border-0 bg-light rounded-3" required onchange="toggleTriggerFields('edit')">
                            <option value="all">Tüm Hizmetlerde Geçerli</option>
                            <option value="categories">Belirli Kategorilerde Geçerli</option>
                            <option value="services">Belirli Hizmetlerde Geçerli</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Kampanya Ödülü</label>
                        <select name="reward_type" id="edit_reward_type" class="form-select border-0 bg-light rounded-3" required onchange="toggleRewardFields('edit')">
                            <option value="discount">İndirim (Yüzde veya Tutar)</option>
                            <option value="gift_product">Hediye Satış Ürünü</option>
                            <option value="gift_cafe">Hediye Kafe Ürünü</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Kampanya Başlığı</label>
                    <input type="text" name="title" id="edit_camp_title" class="form-control border-0 bg-light rounded-3" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Açıklama (Opsiyonel)</label>
                    <textarea name="description" id="edit_camp_desc" rows="2" class="form-control border-0 bg-light rounded-3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Kampanya Şartları</label>
                    <textarea name="terms" id="edit_camp_terms" rows="6" class="form-control border-0 bg-light rounded-3"></textarea>
                </div>

                <!-- Kapsam Seçimleri -->
                <div class="mb-3 d-none" id="edit_categories_wrapper">
                    <label class="form-label small fw-bold text-secondary">Geçerli Hizmet Kategorileri</label>
                    <div class="border rounded-3 p-3 bg-light" style="max-height: 180px; overflow-y: auto;">
                        @foreach($categories as $cat)
                        <div class="form-check mb-2">
                            <input class="form-check-input edit-camp-cat-checkbox" type="checkbox" name="categories[]" value="{{ $cat->id }}" id="cat_edit_{{ $cat->id }}">
                            <label class="form-check-label small fw-semibold text-dark" for="cat_edit_{{ $cat->id }}">
                                {{ $cat->name }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="mb-3 d-none" id="edit_services_wrapper">
                    <label class="form-label small fw-bold text-secondary">Geçerli Hizmetler</label>
                    <div class="border rounded-3 p-3 bg-light" style="max-height: 180px; overflow-y: auto;">
                        @foreach($services as $srv)
                        <div class="form-check mb-2">
                            <input class="form-check-input edit-camp-srv-checkbox" type="checkbox" name="services[]" value="{{ $srv->id }}" id="srv_edit_{{ $srv->id }}">
                            <label class="form-check-label small fw-semibold text-dark" for="srv_edit_{{ $srv->id }}">
                                {{ $srv->name }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Ödül Seçimleri -->
                <div id="edit_discount_wrapper">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">İndirim Türü</label>
                            <select name="discount_type" id="edit_camp_discount_type" class="form-select border-0 bg-light rounded-3">
                                <option value="percentage">Yüzde (%)</option>
                                <option value="fixed">Sabit Tutar (₺)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">İndirim Tutarı / Oranı</label>
                            <input type="number" name="discount_value" id="edit_camp_discount_value" step="0.01" min="0" class="form-control border-0 bg-light rounded-3">
                        </div>
                    </div>
                </div>

                <div class="mb-3 d-none" id="edit_gift_product_wrapper">
                    <label class="form-label small fw-bold text-secondary">Hediye Edilecek Satış Ürünü</label>
                    <select name="reward_product_id" id="edit_reward_product_id" class="form-select border-0 bg-light rounded-3">
                        <option value="">-- Ürün Seçin --</option>
                        @foreach($products as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 d-none" id="edit_gift_cafe_wrapper">
                    <label class="form-label small fw-bold text-secondary">Hediye Edilecek Kafe Ürünü</label>
                    <select name="reward_cafe_product_id" id="edit_reward_cafe_product_id" class="form-select border-0 bg-light rounded-3">
                        <option value="">-- İçecek / Ürün Seçin --</option>
                        @foreach($cafeProducts as $cprod)
                        <option value="{{ $cprod->id }}">{{ $cprod->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6" id="edit_type_wrapper">
                        <label class="form-label small fw-bold text-secondary">Kampanya Türü</label>
                        <select name="type" id="edit_camp_type" class="form-select border-0 bg-light rounded-3" required>
                            <option value="auto_apply">Sepette Otomatik Uygula</option>
                            <option value="coupon_required">Kupon Kodu Gerektirir</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Hedef Kitle</label>
                        <select name="target_audience" id="edit_camp_target" class="form-select border-0 bg-light rounded-3" required>
                            <option value="all">Tüm Müşteriler</option>
                            <option value="new_customers">Sadece Yeni Müşteriler</option>
                            <option value="loyalty_members">Sadece Sadakat Üyeleri</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3" id="edit_limits_wrapper">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Minimum Sipariş Tutarı (₺)</label>
                        <input type="number" name="min_order_amount" id="edit_camp_min_order" step="0.01" min="0" class="form-control border-0 bg-light rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Maksimum İndirim Tutarı (₺)</label>
                        <input type="number" name="max_discount_amount" id="edit_camp_max_discount" step="0.01" min="0" class="form-control border-0 bg-light rounded-3">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Görsel (Opsiyonel - Değiştirmek istemiyorsanız boş bırakın)</label>
                        <input type="file" name="image" class="form-control border-0 bg-light rounded-3" accept="image/*">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">Kişi Başı Limit</label>
                        <input type="number" name="per_customer_limit" id="edit_camp_per_customer_limit" min="1" class="form-control border-0 bg-light rounded-3">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">Öncelik (Sıra)</label>
                        <input type="number" name="priority" id="edit_camp_priority" min="0" class="form-control border-0 bg-light rounded-3">
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Başlangıç Tarihi</label>
                        <input type="date" name="start_date" id="edit_camp_start" class="form-control border-0 bg-light rounded-3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Bitiş Tarihi</label>
                        <input type="date" name="end_date" id="edit_camp_end" class="form-control border-0 bg-light rounded-3" required>
                    </div>
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="is_active" id="edit_camp_active" value="1">
                    <label class="form-check-label small fw-semibold text-secondary" for="edit_camp_active">Aktif Olarak Yayına Al</label>
                </div>
            </div>
            <div class="modal-footer border-top-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Değişiklikleri Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Coupon Modal -->
<div class="modal fade" id="editCouponModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="editCouponForm" class="modal-content border-0 rounded-4 shadow">
            @csrf
            @method('PUT')
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Kupon Kodunu Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Kupon Başlığı (Opsiyonel)</label>
                    <input type="text" name="title" id="edit_coup_title" class="form-control border-0 bg-light rounded-3">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Açıklama (Opsiyonel)</label>
                    <textarea name="description" id="edit_coup_desc" rows="2" class="form-control border-0 bg-light rounded-3"></textarea>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">İndirim Türü</label>
                        <select name="discount_type" id="edit_coup_discount_type" class="form-select border-0 bg-light rounded-3" required>
                            <option value="percentage">Yüzde (%)</option>
                            <option value="fixed">Sabit Tutar (₺)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">İndirim Tutarı / Oranı</label>
                        <input type="number" name="discount_value" id="edit_coup_discount_value" step="0.01" min="0" class="form-control border-0 bg-light rounded-3" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Özel Kullanıcı Atama (Çoklu Seçim Yapabilirsiniz)</label>
                    <input type="text" id="edit_coup_user_search" class="form-control form-control-sm border-0 bg-light mb-2 rounded-3" placeholder="Kullanıcı ara (isim, telefon)...">
                    <div class="list-group user-select-list" id="edit_coup_user_list" style="max-height: 180px; overflow-y: auto;">
                        @foreach($users as $u)
                        <label class="list-group-item d-flex align-items-center gap-2 border-0 bg-light rounded-3 mb-1" style="cursor: pointer;">
                            <input class="form-check-input flex-shrink-0 mt-0 edit-user-checkbox" type="checkbox" name="user_ids[]" value="{{ $u->id }}">
                            <span class="user-text">{{ $u->first_name }} {{ $u->last_name }} <small class="text-muted">({{ $u->phone }})</small></span>
                        </label>
                        @endforeach
                    </div>
                    <div class="form-text small text-secondary mt-1">Boş bırakılırsa herkes kullanabilir. Sadece listeden üzerine tıklayarak kolayca seçebilirsiniz.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Kupon Kodu</label>
                    <input type="text" name="code" id="edit_coup_code" class="form-control border-0 bg-light rounded-3 font-monospace fw-bold text-uppercase" required>
                </div>
                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Maks. Toplam Kullanım</label>
                        <input type="number" name="usage_limit" id="edit_coup_usage" min="1" class="form-control border-0 bg-light rounded-3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Kişi Başı Limit</label>
                        <input type="number" name="per_customer_limit" id="edit_coup_per_customer" min="1" class="form-control border-0 bg-light rounded-3" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Son Geçerlilik Zamanı</label>
                    <input type="datetime-local" name="expires_at" id="edit_coup_expires" class="form-control border-0 bg-light rounded-3" required>
                </div>
            </div>
            <div class="modal-footer border-top-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- View Details Modals -->
<div class="modal fade" id="viewCampaignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Kampanya İncelemesi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div id="view_camp_image_container" class="mb-3 text-center d-none">
                    <img id="view_camp_image" src="" alt="Kampanya Görseli" class="img-fluid rounded-3 border" style="max-height: 200px; object-fit: cover;">
                </div>
                <h6 id="view_camp_title" class="fw-bold mb-3 fs-5"></h6>
                <p id="view_camp_desc" class="text-secondary small mb-4"></p>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush mb-0 border rounded-3 h-100">
                            <li class="list-group-item bg-light"><span class="small fw-bold text-secondary">Kampanya Bilgileri</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Türü</span>
                                <span id="view_camp_type" class="fw-medium small"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">İndirim</span>
                                <span id="view_camp_discount" class="fw-medium small text-primary"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Hedef Kitle</span>
                                <span id="view_camp_target" class="fw-medium small"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Öncelik (Sıra)</span>
                                <span id="view_camp_priority" class="fw-medium small"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Durum</span>
                                <span id="view_camp_status" class="badge"></span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush mb-0 border rounded-3 h-100">
                            <li class="list-group-item bg-light"><span class="small fw-bold text-secondary">Şartlar & Tarihler</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Tarih Aralığı</span>
                                <span id="view_camp_dates" class="fw-medium small"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Min Sipariş</span>
                                <span id="view_camp_min" class="fw-medium small"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Maks İndirim</span>
                                <span id="view_camp_max" class="fw-medium small"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Kişi Başı Limit</span>
                                <span id="view_camp_per_customer" class="fw-medium small"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Geçerli Kategoriler</span>
                                <span id="view_camp_categories" class="fw-medium small text-end" style="max-width: 200px;"></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <h6 class="fw-bold mb-3"><i class="ti ti-users text-primary me-2"></i>Kullanım Geçmişi</h6>
                <div class="table-responsive border rounded-3" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3">Tarih / Saat</th>
                                <th>Müşteri</th>
                                <th>Randevu (Kod)</th>
                                <th>Personel</th>
                            </tr>
                        </thead>
                        <tbody id="view_camp_usages">
                            <!-- JS ile doldurulacak -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewCouponModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Kupon İncelemesi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div class="bg-primary bg-opacity-10 p-3 rounded-3 mb-3 text-center">
                    <span id="view_coup_code" class="fw-bold fs-4 text-primary font-monospace"></span>
                </div>
                
                <h6 id="view_coup_title" class="fw-bold mb-2"></h6>
                <p id="view_coup_desc" class="text-secondary small mb-4"></p>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush mb-0 border rounded-3 h-100">
                            <li class="list-group-item bg-light"><span class="small fw-bold text-secondary">İndirim & Atama</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">İndirim Türü</span>
                                <span id="view_coup_discount" class="fw-medium small text-primary"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Atanan Kullanıcı</span>
                                <span id="view_coup_user" class="fw-medium small"></span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush mb-0 border rounded-3 h-100">
                            <li class="list-group-item bg-light"><span class="small fw-bold text-secondary">Limitler & Tarihler</span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Kullanım Durumu</span>
                                <span id="view_coup_usage" class="fw-medium small"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-secondary small">Son Geçerlilik</span>
                                <span id="view_coup_expires" class="fw-medium small"></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <h6 class="fw-bold mb-3"><i class="ti ti-history text-primary me-2"></i>Kupon Kullanım Geçmişi</h6>
                <div class="table-responsive border rounded-3" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3">Tarih / Saat</th>
                                <th>Müşteri</th>
                                <th>Randevu (Kod)</th>
                                <th>Personel</th>
                            </tr>
                        </thead>
                        <tbody id="view_coup_usages">
                            <!-- JS ile doldurulacak -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const appCampaigns = {!! json_encode($campaigns) !!};
    const appCoupons = {!! json_encode($coupons) !!};

    function generateRandomCouponCode() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = 'BV-';
        for (let i = 0; i < 6; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('couponCodeInput').value = code;
    }

    function toggleTriggerFields(prefix) {
        const type = document.getElementById(prefix + '_trigger_type').value;
        const catWrapper = document.getElementById(prefix + '_categories_wrapper');
        const srvWrapper = document.getElementById(prefix + '_services_wrapper');
        
        if (type === 'categories') {
            catWrapper.classList.remove('d-none');
            srvWrapper.classList.add('d-none');
        } else if (type === 'services') {
            catWrapper.classList.add('d-none');
            srvWrapper.classList.remove('d-none');
        } else {
            catWrapper.classList.add('d-none');
            srvWrapper.classList.add('d-none');
        }
    }

    function toggleRewardFields(prefix) {
        const type = document.getElementById(prefix + '_reward_type').value;
        const discountWrapper = document.getElementById(prefix + '_discount_wrapper');
        const giftProdWrapper = document.getElementById(prefix + '_gift_product_wrapper');
        const giftCafeWrapper = document.getElementById(prefix + '_gift_cafe_wrapper');
        const limitsWrapper = document.getElementById(prefix + '_limits_wrapper');
        const typeWrapper = document.getElementById(prefix + '_type_wrapper');
        
        if (type === 'discount') {
            discountWrapper.classList.remove('d-none');
            giftProdWrapper.classList.add('d-none');
            giftCafeWrapper.classList.add('d-none');
            if(limitsWrapper) limitsWrapper.classList.remove('d-none');
            if(typeWrapper) typeWrapper.classList.remove('d-none');
        } else if (type === 'gift_product') {
            discountWrapper.classList.add('d-none');
            giftProdWrapper.classList.remove('d-none');
            giftCafeWrapper.classList.add('d-none');
            if(limitsWrapper) limitsWrapper.classList.add('d-none');
            if(typeWrapper) typeWrapper.classList.add('d-none');
        } else if (type === 'gift_cafe') {
            discountWrapper.classList.add('d-none');
            giftProdWrapper.classList.add('d-none');
            giftCafeWrapper.classList.remove('d-none');
            if(limitsWrapper) limitsWrapper.classList.add('d-none');
            if(typeWrapper) typeWrapper.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const tabElList = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabElList.forEach(tabEl => {
            tabEl.addEventListener('show.bs.tab', (event) => {
                event.target.classList.remove('text-secondary');
                event.relatedTarget.classList.add('text-secondary');
            });
        });
    });

    function showCampaignDetails(id) {
        const campaign = appCampaigns.find(c => c.id === id);
        if (!campaign) return;

        document.getElementById('view_camp_title').textContent = campaign.title;
        document.getElementById('view_camp_desc').textContent = campaign.description || 'Açıklama girilmemiş.';
        
        let typeStr = campaign.type === 'auto_apply' ? 'Otomatik Uygula' : 'Kupon Gerektirir';
        document.getElementById('view_camp_type').textContent = typeStr;
        
        let discountTypeStr = campaign.discount_type === 'percentage' ? '%' : '₺';
        document.getElementById('view_camp_discount').textContent = discountTypeStr + campaign.discount_value + ' İndirim';
        
        let dateStr = "";
        if(campaign.start_date) dateStr += new Date(campaign.start_date).toLocaleDateString();
        if(campaign.end_date) dateStr += ' - ' + new Date(campaign.end_date).toLocaleDateString();
        
        document.getElementById('view_camp_dates').textContent = dateStr;
        
        let targetMap = {
            'all': 'Tüm Müşteriler',
            'new_customers': 'Sadece Yeni Müşteriler',
            'loyalty_members': 'Sadakat Üyeleri'
        };
        document.getElementById('view_camp_target').textContent = targetMap[campaign.target_audience] || campaign.target_audience;
        
        document.getElementById('view_camp_min').textContent = '₺' + (campaign.min_order_amount || 0);
        document.getElementById('view_camp_max').textContent = campaign.max_discount_amount ? ('₺' + campaign.max_discount_amount) : 'Limitsiz';
        document.getElementById('view_camp_priority').textContent = campaign.priority || '0';
        document.getElementById('view_camp_per_customer').textContent = campaign.per_customer_limit || 'Limitsiz';
        
        // Durum
        const statusEl = document.getElementById('view_camp_status');
        if (campaign.is_active) {
            statusEl.textContent = 'Aktif';
            statusEl.className = 'badge bg-success-subtle text-success';
        } else {
            statusEl.textContent = 'Pasif';
            statusEl.className = 'badge bg-secondary-subtle text-secondary';
        }

        // Kategoriler
        const catNames = campaign.categories && campaign.categories.length > 0 
            ? campaign.categories.map(cat => cat.name).join(', ') 
            : 'Tüm Kategoriler';
        document.getElementById('view_camp_categories').textContent = catNames;

        // Görsel
        const imgContainer = document.getElementById('view_camp_image_container');
        const imgEl = document.getElementById('view_camp_image');
        if (campaign.image_path) {
            imgEl.src = `/storage/${campaign.image_path}`;
            imgContainer.classList.remove('d-none');
        } else {
            imgContainer.classList.add('d-none');
        }
        
        // Usages Tablosu
        const tbody = document.getElementById('view_camp_usages');
        tbody.innerHTML = '';
        if(campaign.usages && campaign.usages.length > 0) {
            campaign.usages.forEach(usage => {
                let tr = document.createElement('tr');
                
                let date = new Date(usage.used_at || usage.created_at);
                let dateFormatted = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                let customerName = usage.customer ? (usage.customer.first_name + ' ' + usage.customer.last_name) : 'Silinmiş Müşteri';
                let appointmentCode = usage.appointment ? usage.appointment.appointment_code : '-';
                let employeeName = (usage.appointment && usage.appointment.employee) ? usage.appointment.employee.first_name + ' ' + usage.appointment.employee.last_name : '-';
                
                tr.innerHTML = `
                    <td class="ps-3 small text-secondary">${dateFormatted}</td>
                    <td class="small fw-medium">${customerName}</td>
                    <td class="small font-monospace text-primary">${appointmentCode}</td>
                    <td class="small">${employeeName}</td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted small"><i class="ti ti-info-circle fs-4 d-block mb-1"></i>Henüz bu kampanya kullanılmamış.</td></tr>`;
        }
        
        new bootstrap.Modal(document.getElementById('viewCampaignModal')).show();
    }

    function editCampaign(id) {
        const campaign = appCampaigns.find(c => c.id === id);
        if (!campaign) return;

        document.getElementById('editCampaignForm').action = `/campaigns/${campaign.id}`;
        document.getElementById('edit_camp_title').value = campaign.title;
        document.getElementById('edit_camp_desc').value = campaign.description || '';
        document.getElementById('edit_camp_terms').value = campaign.terms || '';
        
        document.getElementById('edit_trigger_type').value = campaign.trigger_type || 'all';
        document.getElementById('edit_reward_type').value = campaign.reward_type || 'discount';
        toggleTriggerFields('edit');
        toggleRewardFields('edit');
        
        if (campaign.reward_product_id) document.getElementById('edit_reward_product_id').value = campaign.reward_product_id;
        if (campaign.reward_cafe_product_id) document.getElementById('edit_reward_cafe_product_id').value = campaign.reward_cafe_product_id;

        document.getElementById('edit_camp_type').value = campaign.type;
        document.getElementById('edit_camp_target').value = campaign.target_audience;
        document.getElementById('edit_camp_discount_type').value = campaign.discount_type || 'percentage';
        document.getElementById('edit_camp_discount_value').value = campaign.discount_value || 0;
        document.getElementById('edit_camp_min_order').value = campaign.min_order_amount || 0;
        document.getElementById('edit_camp_max_discount').value = campaign.max_discount_amount || '';
        document.getElementById('edit_camp_priority').value = campaign.priority || 0;
        document.getElementById('edit_camp_per_customer_limit').value = campaign.per_customer_limit || '';
        
        if (campaign.start_date) {
            document.getElementById('edit_camp_start').value = campaign.start_date.substring(0, 10);
        }
        if (campaign.end_date) {
            document.getElementById('edit_camp_end').value = campaign.end_date.substring(0, 10);
        }
        
        document.getElementById('edit_camp_active').checked = campaign.is_active;

        // Kategoriler
        document.querySelectorAll('.edit-camp-cat-checkbox').forEach(cb => cb.checked = false);
        if (campaign.categories && campaign.categories.length > 0) {
            campaign.categories.forEach(cat => {
                const cb = document.getElementById(`cat_edit_${cat.id}`);
                if (cb) cb.checked = true;
            });
        }
        
        // Hizmetler
        document.querySelectorAll('.edit-camp-srv-checkbox').forEach(cb => cb.checked = false);
        if (campaign.services && campaign.services.length > 0) {
            campaign.services.forEach(srv => {
                const cb = document.getElementById(`srv_edit_${srv.id}`);
                if (cb) cb.checked = true;
            });
        }
        
        new bootstrap.Modal(document.getElementById('editCampaignModal')).show();
    }

    function showCouponDetails(id) {
        const coupon = appCoupons.find(c => c.id === id);
        if (!coupon) return;

        document.getElementById('view_coup_code').textContent = coupon.code;
        document.getElementById('view_coup_title').textContent = coupon.title || 'Genel Kupon';
        document.getElementById('view_coup_desc').textContent = coupon.description || 'Açıklama girilmemiş.';
        
        let discountTypeStr = coupon.discount_type === 'percentage' ? '%' : '₺';
        document.getElementById('view_coup_discount').textContent = discountTypeStr + coupon.discount_value + ' İndirim';
        
        document.getElementById('view_coup_usage').textContent = coupon.used_count + ' / ' + coupon.usage_limit + ' (Kişi Başı: ' + coupon.per_customer_limit + ')';
        
        if (coupon.expires_at) {
            let d = new Date(coupon.expires_at);
            document.getElementById('view_coup_expires').textContent = d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        } else {
            document.getElementById('view_coup_expires').textContent = 'Süresiz';
        }
        
        document.getElementById('view_coup_user').textContent = (coupon.users && coupon.users.length > 0) 
            ? coupon.users.map(u => u.first_name + ' ' + u.last_name).join(', ') 
            : 'Herkes / Özel Atama Yok';
        
        // Usages Tablosu
        const tbody = document.getElementById('view_coup_usages');
        tbody.innerHTML = '';
        if(coupon.usages && coupon.usages.length > 0) {
            coupon.usages.forEach(usage => {
                let tr = document.createElement('tr');
                
                let date = new Date(usage.used_at || usage.created_at);
                let dateFormatted = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                let customerName = usage.customer ? (usage.customer.first_name + ' ' + usage.customer.last_name) : 'Silinmiş Müşteri';
                let appointmentCode = usage.appointment ? usage.appointment.appointment_code : '-';
                let employeeName = (usage.appointment && usage.appointment.employee) ? usage.appointment.employee.first_name + ' ' + usage.appointment.employee.last_name : '-';
                
                tr.innerHTML = `
                    <td class="ps-3 small text-secondary">${dateFormatted}</td>
                    <td class="small fw-medium">${customerName}</td>
                    <td class="small font-monospace text-primary">${appointmentCode}</td>
                    <td class="small">${employeeName}</td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted small"><i class="ti ti-info-circle fs-4 d-block mb-1"></i>Henüz bu kupon kullanılmamış.</td></tr>`;
        }
        
        new bootstrap.Modal(document.getElementById('viewCouponModal')).show();
    }

    function editCoupon(id) {
        const coupon = appCoupons.find(c => c.id === id);
        if (!coupon) return;

        document.getElementById('editCouponForm').action = `/campaigns/coupons/${coupon.id}`;
        document.getElementById('edit_coup_title').value = coupon.title || '';
        document.getElementById('edit_coup_desc').value = coupon.description || '';
        document.getElementById('edit_coup_discount_type').value = coupon.discount_type;
        document.getElementById('edit_coup_discount_value').value = coupon.discount_value;
        
        const checkboxes = document.querySelectorAll('#edit_coup_user_list .edit-user-checkbox');
        checkboxes.forEach(cb => cb.checked = false);
        if (coupon.users && coupon.users.length > 0) {
            const userIds = coupon.users.map(u => u.id.toString());
            checkboxes.forEach(cb => {
                if (userIds.includes(cb.value)) cb.checked = true;
            });
        }
        document.getElementById('edit_coup_code').value = coupon.code;
        document.getElementById('edit_coup_usage').value = coupon.usage_limit;
        document.getElementById('edit_coup_per_customer').value = coupon.per_customer_limit;
        
        if (coupon.expires_at) {
            document.getElementById('edit_coup_expires').value = coupon.expires_at.substring(0, 16);
        }
        
        new bootstrap.Modal(document.getElementById('editCouponModal')).show();
    }
    function setupUserFilter(inputId, listId) {
        const searchInput = document.getElementById(inputId);
        const listGroup = document.getElementById(listId);
        if (!searchInput || !listGroup) return;
        
        searchInput.addEventListener('input', function(e) {
            const filter = e.target.value.toLowerCase();
            const items = listGroup.querySelectorAll('.list-group-item');
            
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(filter)) {
                    item.classList.remove('d-none');
                    item.classList.add('d-flex');
                } else {
                    item.classList.remove('d-flex');
                    item.classList.add('d-none');
                }
            });
        });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        setupUserFilter('add_coup_user_search', 'add_coup_user_list');
        setupUserFilter('edit_coup_user_search', 'edit_coup_user_list');
    });
</script>
@endpush
<style>
.hover-translate {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-translate:hover {
    transform: translateY(-2px);
}
</style>
@endsection
