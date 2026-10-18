@extends('layouts.app')
@section('title', 'Toplu Duyuru ve Anlık Bildirim Gönder - B&V Barber')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h1 class="fs-3 mb-1">Toplu Duyuru & Anlık Push Bildirim</h1>
            <p class="text-muted">Müşterilerinizin telefonlarına uygulama kapalı olsa dahi anında sesli ve kilit ekranı uyarısıyla düşen bildirimler gönderin.</p>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center">
    <i class="ti ti-circle-check fs-2 me-3"></i>
    <div>{{ session('success') }}</div>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center">
    <i class="ti ti-alert-triangle fs-2 me-3"></i>
    <div>{{ session('error') }}</div>
</div>
@endif

<!-- İstatistik Kartları -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4 p-3">
            <div class="d-flex align-items-center">
                <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4 me-3">
                    <i class="ti ti-device-mobile fs-2"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Kayıtlı Cihazlar</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['total_devices'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4 p-3">
            <div class="d-flex align-items-center">
                <div class="p-3 bg-success bg-opacity-10 text-success rounded-4 me-3">
                    <i class="ti ti-brand-android fs-2"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Android Cihaz</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['android_devices'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4 p-3">
            <div class="d-flex align-items-center">
                <div class="p-3 bg-info bg-opacity-10 text-info rounded-4 me-3">
                    <i class="ti ti-brand-apple fs-2"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">iOS Cihaz</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['ios_devices'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4 p-3">
            <div class="d-flex align-items-center">
                <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-4 me-3">
                    <i class="ti ti-users fs-2"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Kayıtlı Müşteri</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['total_users'] }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Bildirim Gönderim Formu -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 rounded-4 p-4">
            <h5 class="fw-bold text-dark mb-4">
                <i class="ti ti-send me-2 text-primary"></i> Yeni Bildirim / Duyuru Oluştur
            </h5>

            <form action="{{ route('broadcast.send') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Hedef Kitle</label>
                    <select name="target" class="form-select rounded-3 py-2.5" required>
                        <option value="all">Tüm Kullanıcılar (Herkes)</option>
                        <option value="customers" selected>Yalnızca Müşteriler</option>
                        <option value="last_30_days">Son 30 Günde Randevusu Olanlar</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Bildirim Başlığı</label>
                    <input type="text" name="title" id="notif_title" class="form-control rounded-3 py-2.5" 
                           placeholder="Örn: Hafta Sonu Özel İndirimi! ✂️" maxlength="150" required
                           oninput="updatePreview()">
                    <div class="form-text small text-muted">Maksimum 150 karakter. Kısa ve dikkat çekici başlıklar tercih edin.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Bildirim Mesajı (Gövde)</label>
                    <textarea name="body" id="notif_body" rows="4" class="form-control rounded-3" 
                              placeholder="Örn: Bu hafta sonu tüm saç & sakal bakımlarında %20 indirim fırsatını kaçırmayın. Hemen randevunuzu oluşturun!" 
                              maxlength="500" required oninput="updatePreview()"></textarea>
                    <div class="form-text small text-muted">Kullanıcının kilit ekranında veya bildirim çubuğunda görünecek ana metin.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">Bildirime Tıklandığında Açılacak Sayfa</label>
                    <select name="action" class="form-select rounded-3 py-2.5">
                        <option value="general" selected>Uygulama Ana Sayfası / Bildirimler</option>
                        <option value="campaign">Kampanyalar Sayfası</option>
                        <option value="appointment">Randevularım Sayfası</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary rounded-3 w-100 py-3 fw-bold fs-6 shadow-sm"
                        onclick="return confirm('Bu duyuru seçilen kitledeki tüm cihazlara anlık push bildirimi olarak iletilecektir. Onaylıyor musunuz?');">
                    <i class="ti ti-broadcast me-2 fs-5"></i> Bildirimi Anında Gönder
                </button>
            </form>
        </div>
    </div>

    <!-- Canlı Önizleme & Son Gönderimler -->
    <div class="col-lg-5">
        <!-- Telefon Önizleme Kartı -->
        <div class="card shadow-sm border-0 rounded-4 p-4 mb-4">
            <h6 class="fw-bold text-secondary text-uppercase small mb-3">Telefon Kilit Ekranı Önizlemesi</h6>
            <div class="p-3 rounded-4 border bg-light position-relative shadow-sm">
                <div class="d-flex align-items-center mb-2">
                    <div class="bg-dark text-warning rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; font-size: 11px;">
                        <i class="ti ti-cut"></i>
                    </div>
                    <span class="fw-bold small text-dark me-2">BVBarber</span>
                    <span class="text-muted small ms-auto" style="font-size: 11px;">Şimdi</span>
                </div>
                <div class="fw-bold text-dark fs-6" id="preview_title">Hafta Sonu Özel İndirimi! ✂️</div>
                <div class="text-secondary small mt-1" id="preview_body" style="line-height: 1.4;">
                    Bu hafta sonu tüm saç & sakal bakımlarında %20 indirim fırsatını kaçırmayın. Hemen randevunuzu oluşturun!
                </div>
            </div>
            <div class="text-muted small text-center mt-2" style="font-size: 11px;">
                <i class="ti ti-info-circle me-1"></i> Uygulama kapalı olsa dahi bildirim bu şekilde belirecektir.
            </div>
        </div>

        <!-- Son Gönderilen Duyurular -->
        <div class="card shadow-sm border-0 rounded-4 p-4">
            <h6 class="fw-bold text-secondary text-uppercase small mb-3">Son Gönderilen Duyurular</h6>
            @forelse($recentBroadcasts as $b)
                <div class="p-3 border rounded-3 mb-2 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark">{{ $b->title }}</span>
                        <span class="badge bg-secondary-subtle text-secondary small">{{ $b->user_count }} Alıcı</span>
                    </div>
                    <p class="text-muted small mb-1 text-truncate">{{ $b->body }}</p>
                    <span class="text-muted" style="font-size: 11px;">
                        <i class="ti ti-clock me-1"></i> {{ \Carbon\Carbon::parse($b->sent_at)->format('d.m.Y H:i') }}
                    </span>
                </div>
            @empty
                <div class="text-center text-muted py-3 small">
                    <i class="ti ti-inbox fs-2 d-block mb-1"></i>
                    Henüz panelden duyuru gönderilmedi.
                </div>
            @endforelse
        </div>
    </div>
</div>

<script nonce="{{ $cspNonce }}">
function updatePreview() {
    const titleVal = document.getElementById('notif_title').value.trim();
    const bodyVal = document.getElementById('notif_body').value.trim();
    document.getElementById('preview_title').innerText = titleVal || 'Hafta Sonu Özel İndirimi! ✂️';
    document.getElementById('preview_body').innerText = bodyVal || 'Bu hafta sonu tüm saç & sakal bakımlarında %20 indirim fırsatını kaçırmayın. Hemen randevunuzu oluşturun!';
}
</script>
@endsection
