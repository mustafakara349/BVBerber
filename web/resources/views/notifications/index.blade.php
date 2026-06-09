@extends('layouts.app')
@section('title', 'Sistem Bildirimleri - B&V Barber')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="fs-3 mb-1">Sistem Bildirimleri</h1>
                <p class="text-muted">Kullanıcılara (Müşteriler, Berberler, Yöneticiler) gönderilen veya gönderilecek olan bildirimlerin yönetimi.</p>
            </div>
            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="btn btn-light border rounded-pill px-4 btn-sm fw-semibold">
                        <i class="ti ti-check-all me-1"></i> Tümünü Okundu Yap
                    </button>
                </form>
                <button class="btn btn-primary rounded-pill px-4 btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
                    <i class="ti ti-send me-1"></i> Bildirim Gönder / Yayınla
                </button>
            </div>
        </div>
    </div>
</div>

<!-- KPI Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-secondary small fw-medium text-uppercase mb-1">Toplam Gönderilen</h6>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['total'] }} Bildirim</h3>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                    <i class="ti ti-mail fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4" style="border-bottom: 4px solid #f59e0b !important;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-secondary small fw-medium text-uppercase mb-1">Okunmamış (Bekleyen)</h6>
                    <h3 class="fw-bold mb-0 text-warning">{{ $stats['unread'] }} Bildirim</h3>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                    <i class="ti ti-mail-opened fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4" style="border-bottom: 4px solid #10b981 !important;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-secondary small fw-medium text-uppercase mb-1">Okunmuş / Görüldü</h6>
                    <h3 class="fw-bold mb-0 text-success">{{ $stats['read'] }} Bildirim</h3>
                </div>
                <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                    <i class="ti ti-mail-forward fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Logs List -->
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
        <h5 class="fw-bold text-dark mb-0"><i class="ti ti-history text-secondary me-2"></i>Son Bildirim Gönderim Günlüğü (En Son 100)</h5>
        <p class="text-muted small mb-0">Sistemde kayıtlı kullanıcılara gönderilmiş en son bildirim kayıtları.</p>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Alıcı Kullanıcı</th>
                        <th>Bildirim İçeriği</th>
                        <th class="text-center">Tür</th>
                        <th class="text-center">Durum</th>
                        <th class="text-center">Zaman</th>
                        <th class="text-end pe-4">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notif)
                    <tr>
                        <td class="ps-4">
                            @if($notif->user)
                                <div class="fw-semibold text-dark">{{ $notif->user->full_name }}</div>
                                <div class="text-secondary small">{{ $notif->user->email }}</div>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Genel Alıcı</span>
                            @endif
                        </td>
                        <td class="py-3">
                            <div class="fw-bold text-dark">{{ $notif->title }}</div>
                            <div class="text-secondary small text-wrap text-break" style="max-width: 400px;">{{ $notif->body }}</div>
                        </td>
                        <td class="text-center">
                            @php
                                $typeBadge = match($notif->type) {
                                    'system' => 'bg-danger-subtle text-danger',
                                    'appointment' => 'bg-success-subtle text-success',
                                    'campaign' => 'bg-warning-subtle text-warning',
                                    default => 'bg-primary-subtle text-primary'
                                };
                                $typeLabel = match($notif->type) {
                                    'system' => 'Sistem',
                                    'appointment' => 'Randevu',
                                    'campaign' => 'Kampanya',
                                    default => 'Genel'
                                };
                            @endphp
                            <span class="badge {{ $typeBadge }} px-2.5 py-1 rounded-pill">
                                {{ $typeLabel }}
                            </span>
                        </td>
                        <td class="text-center">
                            <form method="POST" action="{{ route('notifications.toggle-read', $notif->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="badge {{ $notif->is_read ? 'bg-success text-white border-0' : 'bg-warning-subtle text-warning border-0' }} px-2.5 py-1 rounded-pill">
                                    {{ $notif->is_read ? 'Okundu' : 'Okunmadı' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-center text-secondary small">
                            {{ $notif->sent_at ? $notif->sent_at->format('d.m.Y H:i') : ($notif->created_at ? $notif->created_at->format('d.m.Y H:i') : '-') }}
                        </td>
                        <td class="text-end pe-4">
                            <form method="POST" action="{{ route('notifications.destroy', $notif->id) }}" onsubmit="return confirm('Bu bildirim kaydını silmek istediğinize emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger border-0 rounded-circle btn-sm" title="Bildirim Sil">
                                    <i class="ti ti-trash fs-5"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="ti ti-mail-opened fs-1 d-block mb-1"></i>
                            Kayıtlı sistem bildirimi bulunmuyor.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Send Notification Modal -->
<div class="modal fade" id="sendNotificationModal" tabindex="-1" aria-labelledby="sendNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('notifications.store') }}" class="modal-content border-0 rounded-4 shadow">
            @csrf
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="sendNotificationModalLabel">Yeni Bildirim Gönder / Yayınla</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Alıcı Grubu veya Kullanıcı</label>
                    <select name="user_id" class="form-select border-0 bg-light rounded-3 select2-recipient" required>
                        <optgroup label="Toplu Gruplar">
                            <option value="all">Tüm Sistem Üyeleri (Müşteriler & Personel)</option>
                            <option value="customers">Yalnızca Müşteriler</option>
                            <option value="employees">Yalnızca Personeller (Berber & Adminler)</option>
                        </optgroup>
                        <optgroup label="Bireysel Seçim">
                            @foreach($users as $usr)
                                <option value="{{ $usr->id }}">{{ $usr->full_name }} ({{ $usr->email }} - {{ $usr->role?->name }})</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Bildirim Türü</label>
                    <select name="type" class="form-select border-0 bg-light rounded-3" required>
                        <option value="general">Genel Duyuru / Mesaj</option>
                        <option value="campaign">Kampanya & Fırsat</option>
                        <option value="appointment">Randevu Bilgilendirmesi</option>
                        <option value="system">Sistem Uyarısı</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Bildirim Başlığı</label>
                    <input type="text" name="title" class="form-control border-0 bg-light rounded-3" placeholder="Örn: Hafta Sonu Fırsatı!" required>
                </div>
                
                <div class="mb-2">
                    <label class="form-label small fw-bold text-secondary">Bildirim İçeriği (Body)</label>
                    <textarea name="body" rows="4" class="form-control border-0 bg-light rounded-3" placeholder="Gönderilecek bildirim veya mesajın detayları..." required></textarea>
                </div>
            </div>
            <div class="modal-footer border-top-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Bildirimi Gönder</button>
            </div>
        </form>
    </div>
</div>
@endsection
