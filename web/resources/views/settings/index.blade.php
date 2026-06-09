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
                        <h5 class="fw-bold text-dark mb-0">Aktif Şube Yapılandırması</h5>
                        <p class="text-muted small mb-0">Yalnızca oturum açmış olduğunuz şubenizin randevu takvimini ve aktif modüllerini yönetin.</p>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('settings.branch.update') }}">
                            @csrf
                            
                            <div class="row g-4 mb-4">
                                <!-- Calendar Interval -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Randevu Takvim Aralığı</label>
                                    <select name="appointment_interval" class="form-select border-0 bg-light rounded-3 py-2.5 px-3" required>
                                        <option value="15" {{ ($branchSetting->appointment_interval ?? 30) == 15 ? 'selected' : '' }}>15 Dakika</option>
                                        <option value="30" {{ ($branchSetting->appointment_interval ?? 30) == 30 ? 'selected' : '' }}>30 Dakika (Önerilen)</option>
                                        <option value="45" {{ ($branchSetting->appointment_interval ?? 30) == 45 ? 'selected' : '' }}>45 Dakika</option>
                                        <option value="60" {{ ($branchSetting->appointment_interval ?? 30) == 60 ? 'selected' : '' }}>60 Dakika (1 Saat)</option>
                                    </select>
                                    <div class="form-text small text-muted">Müşteri uygulamasında sunulacak randevu saat slotu genişliği.</div>
                                </div>
                                
                                <!-- Cancel limit -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">İptal Limiti (Saat)</label>
                                    <input type="number" name="cancellation_limit_hours" class="form-control border-0 bg-light rounded-3 py-2.5 px-3" value="{{ $branchSetting->cancellation_limit_hours ?? 2 }}" min="0" required>
                                    <div class="form-text small text-muted">Müşterinin randevuya en geç kaç saat kala kendisinin iptal edebileceği limit.</div>
                                </div>

                                <!-- Currency -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Para Birimi Simgesi</label>
                                    <input type="text" name="currency" class="form-control border-0 bg-light rounded-3 py-2.5 px-3" value="{{ $branchSetting->currency ?? 'TRY' }}" max="5" required>
                                    <div class="form-text small text-muted">Fiyat etiketlerinde ve ciro hesaplamalarında kullanılacak birim (örn: TRY, USD, EUR).</div>
                                </div>
                            </div>
                            
                            <!-- Features Toggles -->
                            <h6 class="fw-bold mb-3 text-secondary text-uppercase small border-top pt-4">Aktif Sistem Modülleri</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="fw-bold text-dark small">Sadakat Sistemi</div>
                                            <div class="text-secondary" style="font-size: 10px;">Loyalty puan toplama</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="loyalty_enabled" id="loyalty_enabled" value="1" {{ ($branchSetting->loyalty_enabled ?? true) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="fw-bold text-dark small">Değerlendirme Sistemi</div>
                                            <div class="text-secondary" style="font-size: 10px;">Müşteri yorumları & puanlar</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="review_enabled" id="review_enabled" value="1" {{ ($branchSetting->review_enabled ?? true) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="fw-bold text-dark small">Online Ödeme</div>
                                            <div class="text-secondary" style="font-size: 10px;">Kredi kartıyla tahsilat</div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="online_payment_enabled" id="online_payment_enabled" value="1" {{ ($branchSetting->online_payment_enabled ?? true) ? 'checked' : '' }}>
                                        </div>
                                    </div>
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
<script>
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
