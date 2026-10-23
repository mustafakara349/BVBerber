@extends('layouts.app')
@section('title', 'Platform Ayarları - B&V Barber')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h1 class="fs-3 mb-1">Platform & Şube Ayarları</h1>
            <p class="text-muted">Sistem genelindeki global değişkenler ile aktif şubenizin randevu, sadakat ve finansal kurallarını yapılandırın.</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Navigation Tabs on Left/Top -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-3">
                <div class="nav flex-column nav-pills" id="settingsTab" role="tablist" aria-orientation="vertical">
                    <button class="nav-link active text-start py-2.5 px-3 rounded-3 fw-semibold mb-2" id="global-tab" data-bs-toggle="pill" data-bs-target="#globalPanel" type="button" role="tab" aria-controls="globalPanel" aria-selected="true">
                        <i class="ti ti-settings me-2 fs-5 align-middle"></i> Genel Ayarlar
                    </button>
                    <button class="nav-link text-start py-2.5 px-3 rounded-3 fw-semibold text-secondary" id="branch-tab" data-bs-toggle="pill" data-bs-target="#branchPanel" type="button" role="tab" aria-controls="branchPanel" aria-selected="false">
                        <i class="ti ti-building me-2 fs-5 align-middle"></i> Şube Yapılandırması
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Panels on Right -->
    <div class="col-md-9">
        <div class="tab-content" id="settingsTabContent">
            <!-- Global Settings Panel -->
            <div class="tab-pane fade show active" id="globalPanel" role="tabpanel" aria-labelledby="global-tab">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                        <h5 class="fw-bold text-dark mb-0">Genel Platform Ayarları</h5>
                        <p class="text-muted small mb-0">Tüm şubeleri ve genel kullanıcı arayüzünü etkileyen global sistem tanımları.</p>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('settings.global.update') }}">
                            @csrf
                            
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Uygulama / İşletme Adı</label>
                                    <input type="text" name="app_name" class="form-control border-0 bg-light rounded-3 py-2.5 px-3" value="{{ $globalSettings['app_name'] ?? 'B&V Barber' }}" required>
                                    <div class="form-text small text-muted">E-posta başlıklarında ve müşteri arayüzünde görüntülenecek ad.</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Varsayılan KDV Oranı (%)</label>
                                    <input type="number" name="tax_rate" class="form-control border-0 bg-light rounded-3 py-2.5 px-3" value="{{ $globalSettings['tax_rate'] ?? '20' }}" min="0" max="100" required>
                                    <div class="form-text small text-muted">Finansal işlemlerde fatura veya makbuz kesilirken uygulanan KDV.</div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end border-top pt-4">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                    <i class="ti ti-device-floppy me-1"></i> Değişiklikleri Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Branch Settings Panel -->
            <div class="tab-pane fade" id="branchPanel" role="tabpanel" aria-labelledby="branch-tab">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Aktif Şube Yapılandırması</h5>
                                <p class="text-muted small mb-0">Oturum açmış olduğunuz şubenizin randevu iptal sınırını ve harita yönlendirmelerini yönetin.</p>
                            </div>
                            <span class="badge bg-light text-primary border rounded-pill px-3 py-2 fw-medium">
                                <i class="ti ti-building me-1"></i> Şube ID: #{{ session('active_branch_id', 1) }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Bilgi Kutusu: Sabit Sistem Kuralları -->
                        <div class="alert alert-light border-0 rounded-3 p-3 mb-4 d-flex align-items-start gap-3">
                            <div class="text-primary fs-3 lh-1">
                                <i class="ti ti-info-circle"></i>
                            </div>
                            <div class="small text-secondary">
                                <div class="fw-bold text-dark mb-1">Şube Operasyon Standartları:</div>
                                <ul class="mb-0 ps-3">
                                    <li>Randevu saat slotları dükkan standardı olan <strong>30 dakikalık periyotlarla</strong> üretilmektedir.</li>
                                    <li><strong>Sadakat Sistemi</strong> (1 ₺ harcama = 1 Puan, <strong>1.000 Puan = 1 ₺</strong>) ve <strong>Değerlendirme Sistemi</strong> şubenizde daima aktiftir.</li>
                                </ul>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('settings.branch.update') }}">
                            @csrf
                            
                            <div class="row g-4 mb-4">
                                <!-- Cancellation Limit -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary" for="cancellation_limit_hours">
                                        <i class="ti ti-clock-pause text-primary me-1"></i> Randevu İptal Limiti (Saat)
                                    </label>
                                    <div class="input-group">
                                        <input type="number" 
                                               name="cancellation_limit_hours" 
                                               id="cancellation_limit_hours" 
                                               class="form-control border-0 bg-light rounded-start-3 py-2.5 px-3 @error('cancellation_limit_hours') is-invalid @enderror" 
                                               value="{{ old('cancellation_limit_hours', $branchSetting->cancellation_limit_hours ?? 2) }}" 
                                               min="0" 
                                               max="168" 
                                               required>
                                        <span class="input-group-text border-0 bg-light text-muted rounded-end-3 px-3">Saat Kala</span>
                                    </div>
                                    @error('cancellation_limit_hours')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text small text-muted">
                                        Müşterinin mobil uygulama üzerinden randevusunu en geç kaç saat öncesine kadar iptal edebileceğini belirler. Kalan süre bu limitin altına indiğinde iptal butonu engellenir ve telefon yönlendirmesi yapılır. (0 = Başlangıç anına kadar serbest).
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Google Haritalar Değerlendirme Linki -->
                            <div class="border-top pt-4 mb-4">
                                <label class="form-label small fw-bold text-secondary" for="google_review_url">
                                    <i class="ti ti-brand-google text-primary me-1"></i> Google Haritalar Değerlendirme Linki
                                </label>
                                <input type="url" 
                                       name="google_review_url" 
                                       id="google_review_url" 
                                       maxlength="500" 
                                       class="form-control border-0 bg-light rounded-3 py-2.5 px-3 @error('google_review_url') is-invalid @enderror" 
                                       value="{{ old('google_review_url', $branchSetting->google_review_url ?? '') }}" 
                                       placeholder="https://g.page/r/.../review">
                                @error('google_review_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small text-muted">
                                    Uygulama üzerinden 4 ve 5 yıldız veren memnun müşteriler Google Haritalar profilinize yönlendirilir. Boş bırakılırsa genel Google yönlendirme linki kullanılır.
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end border-top pt-4">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                    <i class="ti ti-device-floppy me-1"></i> Şube Ayarlarını Güncelle
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ $cspNonce }}">
    document.addEventListener('DOMContentLoaded', () => {
        const settingsTab = document.querySelectorAll('#settingsTab button');
        settingsTab.forEach(tabEl => {
            tabEl.addEventListener('show.bs.tab', (event) => {
                event.target.classList.remove('text-secondary');
                event.relatedTarget.classList.add('text-secondary');
            });
        });
    });
</script>
@endpush
@endsection
