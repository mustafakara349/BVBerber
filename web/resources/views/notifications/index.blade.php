@extends('layouts.app')
@section('title', 'Sistem Bildirimleri - B&V Barber')

@push('styles')
<style>
    .sn-live-dot { width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; position: relative; }
    .sn-live-dot::after { content: ''; position: absolute; inset: -4px; border-radius: 50%; background: rgba(16, 185, 129, .35); animation: sn-pulse 1.8s ease-out infinite; }
    @keyframes sn-pulse { 0% { transform: scale(.6); opacity: 1; } 100% { transform: scale(1.8); opacity: 0; } }

    .sn-kpi { transition: transform .2s ease, box-shadow .2s ease; }
    .sn-kpi:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1.25rem rgba(15, 23, 42, .08) !important; }

    .sn-item { position: relative; transition: background-color .15s ease; }
    .sn-item:hover { background-color: #f8fafc; }
    .sn-item.is-unread { background-color: rgba(59, 130, 246, .035); }
    .sn-item.is-unread::before { content: ''; position: absolute; left: 0; top: 14px; bottom: 14px; width: 3px; border-radius: 0 3px 3px 0; background: var(--bs-primary); }
    .sn-icon { width: 44px; height: 44px; flex-shrink: 0; }
    .sn-filter .btn { font-size: 12.5px; }
    .sn-new-banner { animation: sn-slide .35s ease; }
    @keyframes sn-slide { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
</style>
@endpush

@section('content')
@php
    $activeStatus = $filters['status'] ?? null;
    $activeCategory = $filters['category'] ?? null;
@endphp

{{-- Başlık --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="fs-3 mb-1 d-flex align-items-center gap-2">
            Sistem Bildirimleri
            <span class="badge bg-success-subtle text-success rounded-pill fw-semibold d-inline-flex align-items-center gap-2 px-2.5 py-1" style="font-size: 11px;">
                <span class="sn-live-dot"></span> Canlı
            </span>
        </h1>
        <p class="text-muted mb-0">Randevu, stok ve değerlendirme gibi işletme olaylarına ait size özel uyarılar. Bildirimden doğrudan ilgili işleme geçebilirsiniz.</p>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            <button type="submit" id="markAllReadBtn" class="btn btn-light border rounded-pill px-4 btn-sm fw-semibold" @disabled($stats['unread'] === 0)>
                <i class="ti ti-checks me-1"></i> Tümünü Okundu Yap
            </button>
        </form>
        <form method="POST" action="{{ route('notifications.destroy-read') }}" onsubmit="return confirm('Okunmuş tüm bildirimler silinecek. Emin misiniz?');">
            @csrf
            @method('DELETE')
            <button type="submit" id="clearReadBtn" class="btn btn-outline-danger rounded-pill px-4 btn-sm fw-semibold" @disabled($stats['read'] === 0)>
                <i class="ti ti-trash me-1"></i> Okunmuşları Temizle
            </button>
        </form>
    </div>
</div>

{{-- KPI --}}
<div class="row g-4 mb-4">
    @foreach([
        ['label' => 'Toplam Bildirim', 'value' => $stats['total'], 'color' => 'primary', 'icon' => 'ti-bell', 'status' => null],
        ['label' => 'Okunmamış', 'value' => $stats['unread'], 'color' => 'warning', 'icon' => 'ti-bell-ringing', 'status' => 'unread'],
        ['label' => 'Okunmuş', 'value' => $stats['read'], 'color' => 'success', 'icon' => 'ti-bell-check', 'status' => 'read'],
    ] as $kpi)
    <div class="col-md-4">
        <a href="{{ route('notifications.index', array_filter(['status' => $kpi['status'], 'category' => $activeCategory])) }}" class="text-decoration-none">
            <div class="card sn-kpi shadow-sm border-0 rounded-4 h-100" style="border-bottom: 4px solid var(--bs-{{ $kpi['color'] }}) !important;">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-secondary small fw-medium text-uppercase mb-1">{{ $kpi['label'] }}</h6>
                        <h3 class="fw-bold mb-0 text-{{ $kpi['color'] === 'primary' ? 'dark' : $kpi['color'] }}" @if($kpi['status'] === 'unread') id="kpiUnreadCount" @endif>{{ $kpi['value'] }}</h3>
                    </div>
                    <div class="bg-{{ $kpi['color'] }} bg-opacity-10 text-{{ $kpi['color'] }} rounded-3 p-3">
                        <i class="ti {{ $kpi['icon'] }} fs-3"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- Yeni bildirim bandı (canlı akış) --}}
<div id="newNotificationsBanner" class="alert alert-primary border-0 rounded-4 shadow-sm d-none align-items-center justify-content-between sn-new-banner" role="status">
    <span><i class="ti ti-bell-ringing me-2"></i><strong id="newNotificationsText">Yeni bildirim var.</strong></span>
    <a href="{{ route('notifications.index', array_filter($filters)) }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" id="refreshNotificationsBtn">
        <i class="ti ti-refresh me-1"></i> Listeyi Yenile
    </a>
</div>

{{-- Liste --}}
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-3 d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div class="sn-filter btn-group" role="group" aria-label="Durum filtresi">
            @foreach([null => 'Tümü', 'unread' => 'Okunmamış', 'read' => 'Okunmuş'] as $value => $label)
                <a href="{{ route('notifications.index', array_filter(['status' => $value ?: null, 'category' => $activeCategory])) }}"
                   class="btn btn-sm {{ $activeStatus === ($value ?: null) ? 'btn-dark' : 'btn-light border' }} px-3 fw-semibold">{{ $label }}</a>
            @endforeach
        </div>
        <div class="sn-filter d-flex flex-wrap gap-2">
            <a href="{{ route('notifications.index', array_filter(['status' => $activeStatus])) }}"
               class="btn btn-sm rounded-pill px-3 fw-semibold {{ $activeCategory === null ? 'btn-primary' : 'btn-light border' }}">Tüm Kategoriler</a>
            @foreach($categories as $category)
                <a href="{{ route('notifications.index', array_filter(['status' => $activeStatus, 'category' => $category->value])) }}"
                   class="btn btn-sm rounded-pill px-3 fw-semibold {{ $activeCategory === $category->value ? 'btn-primary' : 'btn-light border' }}">
                    <i class="ti {{ $category->icon() }} me-1"></i>{{ $category->label() }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="card-body p-0">
        <ul class="list-unstyled mb-0" id="notificationRows" data-latest-id="{{ $latestId }}">
            @forelse($notifications as $notif)
                @php
                    $color = $notif->level->color();
                    $actionUrl = $notif->safeActionUrl();
                    $subject = $notif->subject;
                    $canQuickConfirm = $notif->event === 'appointment.created'
                        && $subject instanceof \App\Models\Appointment
                        && $subject->status === \App\Enums\AppointmentStatus::Pending;
                @endphp
                <li class="sn-item {{ $notif->isRead() ? '' : 'is-unread' }} border-top px-4 py-3" id="notification-{{ $notif->id }}">
                    <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center">
                        <div class="d-flex gap-3 flex-grow-1 align-items-start">
                            <div class="sn-icon rounded-circle bg-{{ $color }}-subtle text-{{ $color }} d-flex align-items-center justify-content-center">
                                <i class="ti {{ $notif->category->icon() }} fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <span class="fw-bold text-dark">{{ $notif->title }}</span>
                                    <span class="badge bg-{{ $color }}-subtle text-{{ $color }} rounded-pill px-2 py-1" style="font-size: 10.5px;">{{ $notif->category->label() }}</span>
                                    @unless($notif->isRead())
                                        <span class="badge bg-primary rounded-pill px-2 py-1" style="font-size: 10px;">Yeni</span>
                                    @endunless
                                </div>
                                <div class="text-secondary small text-break" style="max-width: 720px;">{{ $notif->body }}</div>

                                @if($subject instanceof \App\Models\Product)
                                    <div class="small mt-1">
                                        <span class="text-muted">Güncel stok:</span>
                                        <span class="fw-semibold {{ $subject->stock_quantity <= 0 ? 'text-danger' : 'text-dark' }}">{{ $subject->stock_quantity }} adet</span>
                                    </div>
                                @elseif($subject instanceof \App\Models\Appointment && $notif->category === \App\Enums\AdminNotificationCategory::Appointment)
                                    <div class="small mt-1">
                                        <span class="text-muted">Güncel durum:</span>
                                        <span class="badge bg-{{ $subject->status->color() }}-subtle text-{{ $subject->status->color() }} rounded-pill">{{ $subject->status->label() }}</span>
                                    </div>
                                @endif

                                <div class="text-muted mt-1" style="font-size: 11.5px;" title="{{ $notif->created_at?->format('d.m.Y H:i') }}">
                                    <i class="ti ti-clock me-1"></i>{{ $notif->created_at?->diffForHumans() }}
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-md-auto ps-5 ps-md-0">
                            @if($canQuickConfirm)
                                <form method="POST" action="{{ route('appointments.update-status', $subject) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ \App\Enums\AppointmentStatus::Confirmed->value }}">
                                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-semibold" id="quick-confirm-{{ $notif->id }}">
                                        <i class="ti ti-check me-1"></i> Onayla
                                    </button>
                                </form>
                            @endif

                            @if($actionUrl)
                                <a href="{{ route('notifications.open', $notif) }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" id="open-notification-{{ $notif->id }}">
                                    {{ $notif->action_label ?? 'Görüntüle' }} <i class="ti ti-arrow-right ms-1"></i>
                                </a>
                            @endif

                            <form method="POST" action="{{ route('notifications.toggle-read', $notif) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-light border btn-sm rounded-circle" title="{{ $notif->isRead() ? 'Okunmadı yap' : 'Okundu yap' }}" id="toggle-read-{{ $notif->id }}">
                                    <i class="ti {{ $notif->isRead() ? 'ti-mail' : 'ti-mail-opened' }}"></i>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('notifications.destroy', $notif) }}" onsubmit="return confirm('Bu bildirimi silmek istediğinize emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger border-0 btn-sm rounded-circle" title="Sil" id="delete-notification-{{ $notif->id }}">
                                    <i class="ti ti-trash fs-5"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            @empty
                <li class="text-center py-5 text-muted border-top">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                        <i class="ti ti-bell-off fs-1"></i>
                    </div>
                    <div class="fw-semibold text-dark">Gösterilecek bildirim yok</div>
                    <div class="small">Yeni randevu, stok uyarısı veya değerlendirme geldiğinde burada anında görünecek.</div>
                </li>
            @endforelse
        </ul>
    </div>

    @if($notifications->hasPages())
        <div class="card-footer bg-transparent border-top px-4 py-3">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const rows = document.getElementById('notificationRows');
        const banner = document.getElementById('newNotificationsBanner');
        const bannerText = document.getElementById('newNotificationsText');
        const kpiUnread = document.getElementById('kpiUnreadCount');
        const pageLatestId = Number(rows?.dataset.latestId || 0);

        // Canlı akış topbar tarafından tek bir polling ile sağlanır; sayfa sadece olayı dinler.
        document.addEventListener('admin-notifications:update', function (event) {
            const { unreadCount, items } = event.detail;
            if (kpiUnread) kpiUnread.textContent = unreadCount;

            const freshCount = items.filter(item => item.id > pageLatestId).length;
            if (freshCount > 0) {
                bannerText.textContent = freshCount + ' yeni bildirim geldi.';
                banner.classList.remove('d-none');
                banner.classList.add('d-flex');
            }
        });
    })();
</script>
@endpush
