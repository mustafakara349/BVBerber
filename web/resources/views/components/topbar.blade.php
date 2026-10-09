<nav id="topbar" class="navbar bg-white border-bottom fixed-top topbar px-4 py-2 shadow-sm d-flex align-items-center justify-content-between" style="z-index: 1030; height: 70px;">
    <div class="d-flex align-items-center gap-2">
        <button id="toggleBtn" class="d-none d-lg-inline-flex btn btn-light btn-icon btn-sm rounded-circle shadow-sm border border-light" style="width: 38px; height: 38px; transition: all 0.2s ease;">
            <i class="ti ti-layout-sidebar-left-expand fs-5 text-dark"></i>
        </button>

        <button id="mobileBtn" class="btn btn-light btn-icon btn-sm d-lg-none rounded-circle shadow-sm border border-light me-2" style="width: 38px; height: 38px;">
            <i class="ti ti-layout-sidebar-left-expand fs-5 text-dark"></i>
        </button>
    </div>

    <div>
        <ul class="list-unstyled d-flex align-items-center mb-0 gap-3">
            {{-- Notifications (canlı akış: notifications.feed) --}}
            <li class="dropdown" id="topbarNotifications"
                data-feed-url="{{ route('notifications.feed') }}"
                data-poll-seconds="{{ config('admin_notifications.feed_poll_seconds') }}">
                <a class="position-relative btn-icon btn-sm btn-light rounded-circle d-flex align-items-center justify-content-center shadow-sm border border-light" data-bs-toggle="dropdown" aria-expanded="false" href="#" role="button" aria-label="Bildirimler" style="width: 38px; height: 38px; transition: all 0.2s ease;">
                    <i class="ti ti-bell fs-5 text-dark"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1.5 bg-danger border border-2 border-white rounded-circle mt-1 ms-n1.5 d-none" id="notificationBadge" style="width: 10px; height: 10px;">
                    </span>
                </a>
                
                <div class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-0 overflow-hidden" style="min-width: 340px; max-width: 380px; margin-top: 15px; border: 1px solid rgba(0, 0, 0, 0.05) !important;">
                    <div class="px-4 py-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark fs-6"><i class="ti ti-bell-ringing text-warning me-1"></i> Bildirimler</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 fw-semibold small d-none" id="notificationUnreadLabel" style="font-size: 11px;"></span>
                    </div>
                    <div class="py-1" style="max-height: 320px; overflow-y: auto;">
                        <ul class="list-unstyled p-0 m-0" id="notificationList">
                            <li class="px-4 py-4 text-center text-muted small" data-placeholder>
                                <i class="ti ti-loader-2 me-1"></i> Yükleniyor...
                            </li>
                        </ul>
                    </div>
                    <div class="bg-light border-top text-center py-2">
                        <a href="{{ route('notifications.index') }}" class="text-primary fw-semibold small text-decoration-none d-block py-1 hover-underline">Tüm Bildirimleri Gör</a>
                    </div>
                </div>
            </li>

            {{-- User Dropdown --}}
            <li class="dropdown">
                <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" class="d-flex align-items-center gap-2 p-1 rounded-pill hover-bg-light" style="transition: all 0.2s ease; text-decoration: none;">
                    @if(auth()->user()?->profile_photo_url)
                        <img src="{{ auth()->user()->profile_photo_url }}" alt="" class="avatar avatar-sm rounded-circle border border-2 border-primary-subtle shadow-sm" style="width: 38px; height: 38px; object-fit: cover;">
                    @else
                        <div class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 14px;">
                            {{ substr(auth()->user()?->first_name ?? 'A', 0, 1) }}{{ substr(auth()->user()?->last_name ?? 'A', 0, 1) }}
                        </div>
                    @endif
                    <div class="d-none d-md-block text-start me-1">
                        <span class="d-block fw-bold text-dark small" style="line-height: 1.1; font-size: 13px;">{{ auth()->user()?->full_name ?? 'Yönetici' }}</span>
                        <span class="text-secondary" style="font-size: 10px;">{{ auth()->user()?->role?->name ?? 'Admin' }}</span>
                    </div>
                    <i class="ti ti-chevron-down text-secondary d-none d-md-block" style="font-size: 12px;"></i>
                </a>

                <div class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-0 overflow-hidden" style="min-width: 280px; margin-top: 15px; border: 1px solid rgba(0, 0, 0, 0.05) !important;">
                    <!-- Header with beautiful dark profile background or subtle gradient -->
                    <div class="px-4 py-4 bg-dark text-white d-flex align-items-center gap-3" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                        @if(auth()->user()?->profile_photo_url)
                            <img src="{{ auth()->user()->profile_photo_url }}" alt="" class="avatar avatar-md rounded-circle border border-2 border-white border-opacity-25 shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                        @else
                            <div class="avatar avatar-md rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold border border-2 border-white border-opacity-25 shadow-sm" style="width: 50px; height: 50px; font-size: 18px;">
                                {{ substr(auth()->user()?->first_name ?? 'A', 0, 1) }}{{ substr(auth()->user()?->last_name ?? 'A', 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h5 class="mb-0 fw-bold text-white small" style="font-size: 14px;">{{ auth()->user()?->full_name ?? 'Yönetici' }}</h5>
                            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2 py-0.5 rounded-pill mt-1" style="font-size: 10px;">
                                <i class="ti ti-circle-filled me-1" style="font-size: 8px;"></i> Aktif / {{ auth()->user()?->role?->name ?? 'Admin' }}
                            </span>
                        </div>
                    </div>

                    <!-- Body Menu Options with Micro-Animations -->
                    <div class="p-2 d-flex flex-column gap-1 text-dark">
                        <a href="{{ route('dashboard') }}" class="dropdown-item px-3 py-2.5 rounded-3 d-flex align-items-center gap-2.5 hover-bg-light transition-all" style="font-size: 13px;">
                            <span class="p-1.5 bg-primary bg-opacity-10 text-primary rounded-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                <i class="ti ti-dashboard fs-5"></i>
                            </span>
                            <span class="fw-semibold text-dark">Yönetim Paneli</span>
                        </a>
                        
                        <a href="{{ route('finance.transactions') }}" class="dropdown-item px-3 py-2.5 rounded-3 d-flex align-items-center gap-2.5 hover-bg-light transition-all" style="font-size: 13px;">
                            <span class="p-1.5 bg-success bg-opacity-10 text-success rounded-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                <i class="ti ti-wallet fs-5"></i>
                            </span>
                            <span class="fw-semibold text-dark">Kasa & Ciro</span>
                        </a>

                        <a href="{{ route('settings.index') }}" class="dropdown-item px-3 py-2.5 rounded-3 d-flex align-items-center gap-2.5 hover-bg-light transition-all" style="font-size: 13px;">
                            <span class="p-1.5 bg-warning bg-opacity-10 text-warning rounded-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                <i class="ti ti-settings fs-5"></i>
                            </span>
                            <span class="fw-semibold text-dark">Sistem Ayarları</span>
                        </a>
                    </div>

                    <!-- Logout Action Bar -->
                    <div class="bg-light p-3 border-top d-flex gap-2">
                        <form method="POST" action="{{ route('logout') }}" class="w-100">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100 py-2 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-1.5 shadow-sm" style="transition: all 0.2s ease; font-size: 12px;">
                                <i class="ti ti-logout fs-5"></i> Güvenli Çıkış Yap
                            </button>
                        </form>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</nav>

@push('scripts')
<script>
    /**
     * Sistem bildirimleri canlı akışı.
     * - Tek bir polling döngüsü çalışır; sonuç `admin-notifications:update` olayı ile yayınlanır
     *   (bildirimler sayfası aynı veriyi ek istek atmadan kullanır).
     * - Sekme arka plandayken istek atılmaz.
     * - İçerik yalnızca textContent ile basılır (XSS'e karşı güvenli).
     */
    (function () {
        const root = document.getElementById('topbarNotifications');
        if (!root) return;

        const feedUrl = root.dataset.feedUrl;
        const intervalMs = Math.max(10, Number(root.dataset.pollSeconds || 20)) * 1000;
        const list = document.getElementById('notificationList');
        const dot = document.getElementById('notificationBadge');
        const label = document.getElementById('notificationUnreadLabel');
        let timer = null;
        let inFlight = false;

        function el(tag, className, text) {
            const node = document.createElement(tag);
            if (className) node.className = className;
            if (text !== undefined && text !== null) node.textContent = text;
            return node;
        }

        function renderItem(item) {
            const li = el('li');
            const link = el('a', 'dropdown-item px-4 py-3 border-bottom border-light d-flex gap-3 align-items-start' + (item.is_read ? '' : ' bg-primary bg-opacity-10'));
            link.href = item.open_url;
            link.style.whiteSpace = 'normal';

            const iconWrap = el('div', 'flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center bg-' + item.color + '-subtle text-' + item.color);
            iconWrap.style.width = '38px';
            iconWrap.style.height = '38px';
            iconWrap.appendChild(el('i', 'ti ' + item.icon + ' fs-5'));

            const content = el('div', 'flex-grow-1');
            const title = el('p', 'mb-0 text-dark fw-semibold small', item.title);
            title.style.fontSize = '13px';
            const body = el('p', 'mb-1 text-secondary small', item.body);
            body.style.fontSize = '12px';
            body.style.lineHeight = '1.4';
            const time = el('span', 'text-muted d-block', item.time);
            time.style.fontSize = '11px';

            content.append(title, body, time);
            link.append(iconWrap, content);
            li.appendChild(link);
            return li;
        }

        function render(data) {
            const unread = Number(data.unread_count || 0);
            dot.classList.toggle('d-none', unread === 0);
            label.classList.toggle('d-none', unread === 0);
            label.textContent = unread + ' Yeni';

            list.replaceChildren();
            if (!data.items.length) {
                const empty = el('li', 'px-4 py-4 text-center text-muted small');
                empty.appendChild(el('i', 'ti ti-bell-off d-block fs-3 mb-1'));
                empty.append('Henüz bildirim yok.');
                list.appendChild(empty);
            } else {
                data.items.forEach(item => list.appendChild(renderItem(item)));
            }

            document.dispatchEvent(new CustomEvent('admin-notifications:update', {
                detail: { unreadCount: unread, latestId: data.latest_id, items: data.items },
            }));
        }

        async function poll() {
            if (inFlight || document.hidden) return;
            inFlight = true;
            try {
                const response = await fetch(feedUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    cache: 'no-store',
                });
                if (response.ok) render(await response.json());
            } catch (e) {
                // Ağ hatası: bir sonraki döngüde yeniden denenir.
            } finally {
                inFlight = false;
            }
        }

        function start() {
            if (timer) return;
            poll();
            timer = setInterval(poll, intervalMs);
        }

        function stop() {
            clearInterval(timer);
            timer = null;
        }

        document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));
        start();
    })();
</script>
@endpush
