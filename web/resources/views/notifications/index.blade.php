@extends('layouts.app')
@section('title', 'Sistem Bildirimleri - B&V Barber')

@push('styles')
<style>
    .sn-kpi { transition: transform .2s ease, box-shadow .2s ease; }
    .sn-kpi:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1.25rem rgba(15, 23, 42, .08) !important; }

    /* Bildirim Satırı — Geniş, ferah ve şık padding */
    .sn-item {
        position: relative;
        transition: background-color .15s ease;
        padding: 22px 32px;
    }
    @media (max-width: 992px) {
        .sn-item {
            padding: 18px 20px;
        }
    }
    .sn-item:hover {
        background-color: #fafbfd;
    }
    .sn-item.is-unread::before {
        content: '';
        position: absolute;
        left: 0;
        top: 14px;
        bottom: 14px;
        width: 4.5px;
        border-radius: 0 4px 4px 0;
        background-color: var(--sn-accent, #E66239);
    }

    /* Squircle İkon Kutusu */
    .sn-icon-sq {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        border-radius: 13px !important;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Sol Tıklanabilir İçerik Linki — Satıra tam yayılan genişlik */
    .sn-content-link {
        display: flex;
        gap: 18px;
        flex: 1 1 0%;
        min-width: 0;
        align-items: flex-start;
        text-decoration: none;
    }
    .sn-content-body {
        flex: 1 1 0%;
        min-width: 0;
    }
    .sn-body-text {
        font-size: 13.5px;
        line-height: 1.5;
        color: #4B5563 !important;
        word-break: normal;
        overflow-wrap: break-word;
        text-wrap: pretty;
        margin-bottom: 5px;
    }

    /* Sağ Aksiyon Butonları */
    .sn-actions-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    /* Kategori Butonları */
    .sn-cat-btn {
        font-size: 13px;
        font-weight: 500;
        padding: 6.5px 16px;
        border-radius: 50rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all .2s ease;
    }
    .sn-cat-btn.is-active {
        background-color: #E66239 !important;
        color: #ffffff !important;
        border: 1px solid #E66239 !important;
        font-weight: 600;
    }
    .sn-cat-btn:not(.is-active) {
        background-color: #ffffff;
        color: #374151;
        border: 1px solid #e5e7eb;
    }
    .sn-cat-btn:not(.is-active):hover {
        background-color: #f9fafb;
        border-color: #d1d5db;
        color: #111827;
    }

    /* Durum Filtre Grubu */
    .sn-status-pill-group {
        background-color: #F1F3F5;
        border-radius: 50rem;
        padding: 4px;
        display: inline-flex;
        align-items: center;
    }
    .sn-status-pill {
        font-size: 13px;
        border-radius: 50rem;
        padding: 5px 16px;
        text-decoration: none;
        transition: all .15s ease;
    }
    .sn-status-pill.is-active {
        background-color: #ffffff;
        color: #111827;
        font-weight: 600;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .sn-status-pill:not(.is-active) {
        background-color: transparent;
        color: #6b7280;
        font-weight: 500;
    }
    .sn-status-pill:not(.is-active):hover {
        color: #111827;
    }

    /* Turuncu Aksiyon Butonu */
    .sn-btn-action {
        background-color: #E66239;
        color: #ffffff;
        border: none;
        border-radius: 50rem;
        padding: 7px 18px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        transition: background-color .15s ease, transform .15s ease;
    }
    .sn-btn-action:hover {
        background-color: #d2532c;
        color: #ffffff;
    }

    /* Yuvarlak Hızlı Aksiyon Butonları */
    .sn-btn-icon {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
        color: #4b5563;
        transition: all .15s ease;
        padding: 0;
    }
    .sn-btn-icon:hover {
        background-color: #f3f4f6;
        color: #111827;
        border-color: #d1d5db;
    }
    .sn-btn-icon-danger {
        border: none;
        background-color: transparent;
        color: #FA5252;
    }
    .sn-btn-icon-danger:hover {
        background-color: rgba(250, 82, 82, 0.1);
    .sn-new-banner { animation: sn-slide .35s ease; }
    @keyframes sn-slide { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
</style>
@endpush

@section('content')
@php
    $activeStatus = $filters['status'] ?? null;
    $activeCategory = $filters['category'] ?? null;
@endphp

<x-page-header title="Sistem Bildirimleri" description="Randevu, stok ve değerlendirme gibi işletme olaylarına ait size özel uyarılar. Bildirimden doğrudan ilgili işleme geçebilirsiniz.">
    <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="d-inline-block">
        @csrf
        <x-btn-outline icon="ti ti-checks" type="submit" id="markAllReadBtn" :disabled="$stats['unread'] === 0">
            Tümünü Okundu Yap
        </x-btn-outline>
    </form>
    <form method="POST" action="{{ route('notifications.destroy-read') }}" onsubmit="return confirm('Okunmuş tüm bildirimler silinecek. Emin misiniz?');" class="d-inline-block">
        @csrf
        @method('DELETE')
        <x-btn-danger icon="ti ti-trash" type="submit" id="clearReadBtn" :disabled="$stats['read'] === 0">
            Okunmuşları Temizle
        </x-btn-danger>
    </form>
</x-page-header>

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

{{-- Liste Kartı (Görsel 1) --}}
<div class="card shadow-sm border-0 rounded-4 overflow-hidden">
    {{-- Filtreler (Üst Bar) --}}
    <div class="card-header bg-white border-0 pt-4 px-4 px-lg-5 pb-3 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        {{-- Durum Filtreleri (Sol Segment) --}}
        <div class="sn-status-pill-group">
            @foreach([null => 'Tümü', 'unread' => 'Okunmamış', 'read' => 'Okunmuş'] as $value => $label)
                <a href="{{ route('notifications.index', array_filter(['status' => $value ?: null, 'category' => $activeCategory])) }}"
                   class="sn-status-pill {{ $activeStatus === ($value ?: null) ? 'is-active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Kategori Filtreleri (Sağ Butonlar) --}}
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('notifications.index', array_filter(['status' => $activeStatus])) }}"
               class="sn-cat-btn {{ $activeCategory === null ? 'is-active' : '' }}">
                Tüm Kategoriler
            </a>
            @foreach($categories as $category)
                @php
                    $catIcon = match ($category) {
                        \App\Enums\AdminNotificationCategory::Appointment => 'ti-calendar',
                        \App\Enums\AdminNotificationCategory::Stock => 'ti-package',
                        \App\Enums\AdminNotificationCategory::Review => 'ti-star',
                        \App\Enums\AdminNotificationCategory::System => 'ti-settings',
                        default => $category->icon(),
                    };
                @endphp
                <a href="{{ route('notifications.index', array_filter(['status' => $activeStatus, 'category' => $category->value])) }}"
                   class="sn-cat-btn {{ $activeCategory === $category->value ? 'is-active' : '' }}">
                    <i class="ti {{ $catIcon }} fs-6 me-1.5"></i>
                    <span>{{ $category->label() }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Bildirim Satırları --}}
    <div class="card-body p-0">
        <ul class="list-unstyled mb-0" id="notificationRows" data-latest-id="{{ $latestId }}">
            @forelse($notifications as $notif)
                @php
                    $level = $notif->level;
                    $accentColor = match ($level) {
                        \App\Enums\AdminNotificationLevel::Danger => '#FA5252',
                        \App\Enums\AdminNotificationLevel::Warning => '#F59F00',
                        \App\Enums\AdminNotificationLevel::Success => '#00C951',
                        \App\Enums\AdminNotificationLevel::Info => '#E66239',
                        default => '#E66239',
                    };

                    $bgSoft = match ($level) {
                        \App\Enums\AdminNotificationLevel::Danger => 'rgba(251, 44, 54, 0.12)',
                        \App\Enums\AdminNotificationLevel::Warning => 'rgba(240, 177, 0, 0.14)',
                        \App\Enums\AdminNotificationLevel::Success => 'rgba(0, 201, 81, 0.12)',
                        \App\Enums\AdminNotificationLevel::Info => 'rgba(230, 98, 57, 0.12)',
                        default => 'rgba(230, 98, 57, 0.12)',
                    };

                    $badgeTextColor = match ($level) {
                        \App\Enums\AdminNotificationLevel::Danger => '#DC2626',
                        \App\Enums\AdminNotificationLevel::Warning => '#D97706',
                        \App\Enums\AdminNotificationLevel::Success => '#059669',
                        \App\Enums\AdminNotificationLevel::Info => '#E66239',
                        default => '#E66239',
                    };

                    $iconClass = match ($notif->category) {
                        \App\Enums\AdminNotificationCategory::Appointment => 'ti-calendar',
                        \App\Enums\AdminNotificationCategory::Stock => 'ti-package',
                        \App\Enums\AdminNotificationCategory::Review => 'ti-star',
                        \App\Enums\AdminNotificationCategory::System => 'ti-settings',
                        default => 'ti-bell',
                    };

                    $actionUrl = $notif->safeActionUrl();
                    $actionLabel = $notif->action_label ?? 'Randevuya Git';
                @endphp
                <li class="sn-item {{ $notif->isRead() ? 'is-read' : 'is-unread' }} border-top"
                    id="notification-{{ $notif->id }}"
                    style="--sn-accent: {{ $accentColor }};">
                    <div class="d-flex flex-column flex-lg-row gap-4 align-items-lg-center justify-content-between">
                        
                        {{-- Sol İçerik Tıklama Alanı (Genişletilmiş) --}}
                        <a href="{{ route('notifications.show', $notif) }}" class="sn-content-link">
                            {{-- Squircle İkon (Görsel 1) --}}
                            <div class="sn-icon-sq" style="background-color: {{ $bgSoft }}; color: {{ $accentColor }};">
                                <i class="ti {{ $iconClass }} fs-4"></i>
                            </div>

                            {{-- Detay Alanı --}}
                            <div class="sn-content-body">
                                {{-- Başlık & Rozetler --}}
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <span class="fw-bold text-dark fs-6">{{ $notif->title }}</span>
                                    <span class="badge rounded-pill"
                                          style="background-color: {{ $bgSoft }}; color: {{ $badgeTextColor }}; font-size: 11px; font-weight: 600; padding: 2.5px 9px;">
                                        {{ $notif->category->label() }}
                                    </span>
                                    @unless($notif->isRead())
                                        <span class="badge rounded-pill text-white fw-bold"
                                              style="background-color: #E66239; font-size: 10px; padding: 2.5px 8px;">
                                            Yeni
                                        </span>
                                    @endunless
                                </div>

                                {{-- Bildirim Metni (Genişleyen ve taşmayan şık tipografi) --}}
                                <div class="sn-body-text">
                                    {{ $notif->body }}
                                </div>

                                {{-- Zaman (Saat ikonu ile) --}}
                                <div class="text-muted d-flex align-items-center" style="font-size: 11.5px; color: #9CA3AF !important;" title="{{ $notif->created_at?->format('d.m.Y H:i') }}">
                                    <i class="ti ti-clock me-1"></i>
                                    <span>{{ $notif->created_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                        </a>

                        {{-- Sağ Butonlar --}}
                        <div class="sn-actions-wrap">
                            @if($actionUrl)
                                <a href="{{ route('notifications.open', $notif) }}"
                                   class="sn-btn-action"
                                   id="open-notification-{{ $notif->id }}">
                                    <span>{{ $actionLabel }}</span>
                                    <i class="ti ti-arrow-right ms-1"></i>
                                </a>
                            @endif

                            {{-- Okundu / Okunmadı Yap Butonu --}}
                            <form method="POST" action="{{ route('notifications.toggle-read', $notif) }}" class="m-0">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="sn-btn-icon"
                                        title="{{ $notif->isRead() ? 'Okunmadı yap' : 'Okundu yap' }}"
                                        id="toggle-read-{{ $notif->id }}">
                                    <i class="ti {{ $notif->isRead() ? 'ti-mail-opened' : 'ti-mail' }} fs-5"></i>
                                </button>
                            </form>

                            {{-- Sil Butonu --}}
                            <form method="POST" action="{{ route('notifications.destroy', $notif) }}" onsubmit="return confirm('Bu bildirimi silmek istediğinize emin misiniz?');" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="sn-btn-icon sn-btn-icon-danger"
                                        title="Sil"
                                        id="delete-notification-{{ $notif->id }}">
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

    {{-- Sayfalama --}}
    @if($notifications->hasPages())
        <div class="card-footer bg-white border-top border-light-subtle px-4 px-lg-5 py-3">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script nonce="{{ $cspNonce }}">
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
