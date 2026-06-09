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
            <!-- Campaigns Panel -->
            <div class="tab-pane fade show active" id="campaignsPanel" role="tabpanel" aria-labelledby="campaigns-tab">
                <div class="row g-4">
                    @forelse($campaigns as $camp)
                    <div class="col-md-6 col-lg-4">
                        <div class="card shadow-sm border border-light rounded-4 h-100 position-relative hover-translate transition-all">
                            <span class="position-absolute top-0 end-0 mt-3 me-3 badge {{ $camp->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} px-3 py-1.5 rounded-pill">
                                {{ $camp->is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                            
                            <div class="card-body p-4">
                                <div class="mb-3 text-primary d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-2.5 rounded-3 d-flex align-items-center justify-content-center me-2">
                                        <i class="ti ti-speakerphone fs-4"></i>
                                    </div>
                                    <span class="fw-bold fs-5 text-dark text-truncate">{{ $camp->title }}</span>
                                </div>
                                
                                <p class="text-secondary small mb-3 text-truncate-2">{{ $camp->description ?? 'Açıklama girilmemiş.' }}</p>
                                
                                <div class="bg-light p-3 rounded-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-secondary small">İndirim Oranı/Tutarı:</span>
                                        <span class="fw-bold text-dark fs-5">
                                            @if($camp->discount_type->value === 'percentage')
                                                %{{ number_format($camp->discount_value, 0) }}
                                            @else
                                                ₺{{ number_format($camp->discount_value, 2, ',', '.') }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-secondary small">Türü:</span>
                                        <span class="badge bg-white border text-secondary px-2 py-0.5 small">{{ $camp->discount_type->label() }}</span>
                                    </div>
                                </div>
                                
                                <div class="text-secondary small mb-4 d-flex align-items-center">
                                    <i class="ti ti-calendar me-1.5"></i>
                                    {{ $camp->start_date->format('d.m.Y') }} - {{ $camp->end_date->format('d.m.Y') }}
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <form method="POST" action="{{ route('campaigns.toggle', $camp->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            Durum Değiştir
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="{{ route('campaigns.destroy', $camp->id) }}" onsubmit="return confirm('Bu kampanyayı silmek istediğinize emin misiniz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle" title="Kampanyayı Sil">
                                            <i class="ti ti-trash fs-5"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="ti ti-speakerphone fs-1 d-block mb-2"></i>
                        Henüz oluşturulmuş kampanya bulunmuyor.
                    </div>
                    @endforelse
                </div>
            </div>
            
            <!-- Coupons Panel -->
            <div class="tab-pane fade" id="couponsPanel" role="tabpanel" aria-labelledby="coupons-tab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Kupon Kodu</th>
                                <th>Bağlı Kampanya</th>
                                <th class="text-center">Limit / Kullanım</th>
                                <th class="text-center">Geçerlilik Tarihi</th>
                                <th class="text-center">Durum</th>
                                <th class="text-end pe-4">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($coupons as $coup)
                            <tr>
                                <td class="ps-4 fw-bold text-dark font-monospace fs-5 text-primary">
                                    <span class="bg-primary bg-opacity-10 px-2.5 py-1 rounded text-primary">{{ $coup->code }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $coup->campaign->title ?? 'Bilinmeyen Kampanya' }}</div>
                                    <div class="text-secondary small">
                                        @if($coup->campaign?->discount_type?->value === 'percentage')
                                            %{{ number_format($coup->campaign->discount_value, 0) }} İndirim
                                        @else
                                            ₺{{ number_format($coup->campaign?->discount_value ?? 0, 2, ',', '.') }} İndirim
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center fw-medium">
                                    {{ $coup->used_count }} / {{ $coup->usage_limit }}
                                    <div class="progress mt-1.5 mx-auto" style="height: 5px; width: 80px;">
                                        <div class="progress-bar bg-success" style="width: {{ min(100, ($coup->used_count / max(1, $coup->usage_limit)) * 100) }}%"></div>
                                    </div>
                                </td>
                                <td class="text-center text-secondary small">{{ $coup->expires_at ? $coup->expires_at->format('d.m.Y H:i') : 'Süresiz' }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $coup->isValid() ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-2.5 py-1 rounded-pill">
                                        {{ $coup->isValid() ? 'Geçerli' : 'Geçersiz/Tükenmiş' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <form method="POST" action="{{ route('campaigns.coupons.destroy', $coup->id) }}" onsubmit="return confirm('Kupon kodunu silmek istediğinize emin misiniz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger border-0 rounded-circle btn-sm">
                                            <i class="ti ti-trash fs-5"></i>
                                        </button>
                                    </form>
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
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('campaigns.store') }}" class="modal-content border-0 rounded-4 shadow">
            @csrf
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="addCampaignModalLabel">Yeni Kampanya Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Kampanya Başlığı</label>
                    <input type="text" name="title" class="form-control border-0 bg-light rounded-3" placeholder="Örn: Yaz Sezonu İndirimi" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Açıklama</label>
                    <textarea name="description" rows="3" class="form-control border-0 bg-light rounded-3" placeholder="Kampanya koşulları, açıklaması..."></textarea>
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
                        <input type="number" name="discount_value" step="0.01" min="0" class="form-control border-0 bg-light rounded-3" placeholder="Oran veya Tutar" required>
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
                    <label class="form-label small fw-bold text-secondary">Bağlı Kampanya</label>
                    <select name="campaign_id" class="form-select border-0 bg-light rounded-3" required>
                        @foreach($campaigns as $camp)
                            <option value="{{ $camp->id }}">{{ $camp->title }} (@if($camp->discount_type->value === 'percentage')%{{ number_format($camp->discount_value, 0) }}@else₺{{ number_format($camp->discount_value, 2, ',', '.') }}@endif İndirim)</option>
                        @endforeach
                    </select>
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
                        <label class="form-label small fw-bold text-secondary">Maksimum Kullanım Limiti</label>
                        <input type="number" name="usage_limit" min="1" class="form-control border-0 bg-light rounded-3" placeholder="Örn: 100 Ziyaret" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary">Son Geçerlilik Zamanı</label>
                        <input type="datetime-local" name="expires_at" class="form-control border-0 bg-light rounded-3" value="{{ date('Y-m-d\TH:i', strtotime('+7 days')) }}" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Kupon Kodunu Üret</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function generateRandomCouponCode() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = 'BV-';
        for (let i = 0; i < 6; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('couponCodeInput').value = code;
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
</script>
@endpush
<style>
.hover-translate {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-translate:hover {
    transform: translateY(-4px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important;
}
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
</style>
@endsection
