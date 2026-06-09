<aside id="sidebar" class="sidebar shadow-sm" style="padding-top: 70px;">
    <style>
        /* Force high-visibility active sidebar menu item background and styles */
        .sidebar .nav-link.active {
            color: #E66239 !important;
            background-color: rgba(230, 98, 57, 0.14) !important;
            font-weight: 600 !important;
            position: relative !important;
        }
        
        .sidebar .nav-link.active::before {
            content: "" !important;
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            height: 100% !important;
            width: 4px !important;
            background-color: #E66239 !important;
            border-radius: 8px 0 0 8px !important;
        }
        
        .sidebar .nav-link.active i,
        .sidebar .nav-link.active .nav-text {
            color: #E66239 !important;
        }
    </style>

    <div class="logo-area px-4" style="height: 70px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; padding-left: 24px; position: absolute; top: 0; left: 0; width: 100%; background: #ffffff;">
        <a href="{{ route('dashboard') }}" class="d-inline-flex align-items-center" style="text-decoration: none;">
            <span class="fw-bold text-primary fs-5">B&V</span>
            <span class="logo-text ms-2 fw-semibold text-dark">Barber</span>
        </a>
    </div>

    <ul class="nav flex-column">
        <li class="px-4 py-2"><small class="nav-text">Ana Menü</small></li>

        <li>
            <a class="nav-link {{ (request()->is('dashboard') || request()->routeIs('dashboard')) ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="ti ti-dashboard"></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('appointments*') || request()->routeIs('appointments.*')) ? 'active' : '' }}" href="{{ route('appointments.index') }}">
                <i class="ti ti-calendar-event"></i>
                <span class="nav-text">Randevular</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('employees*') || request()->routeIs('employees.*')) ? 'active' : '' }}" href="{{ route('employees.index') }}">
                <i class="ti ti-users"></i>
                <span class="nav-text">Çalışanlar</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('customers*') || request()->routeIs('customers.*')) ? 'active' : '' }}" href="{{ route('customers.index') }}">
                <i class="ti ti-user-circle"></i>
                <span class="nav-text">Müşteriler</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('services*') || request()->routeIs('services.*')) ? 'active' : '' }}" href="{{ route('services.index') }}">
                <i class="ti ti-cut"></i>
                <span class="nav-text">Hizmetler</span>
            </a>
        </li>

        <li class="px-4 pt-4 pb-2"><small class="nav-text">Stok & Satış</small></li>

        <li>
            <a class="nav-link {{ (request()->is('products') || request()->routeIs('products.index')) ? 'active' : '' }}" href="{{ route('products.index') }}">
                <i class="ti ti-box"></i>
                <span class="nav-text">Ürün Yönetimi</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('products-sales*') || request()->routeIs('products.sales.*')) ? 'active' : '' }}" href="{{ route('products.sales.index') }}">
                <i class="ti ti-shopping-cart"></i>
                <span class="nav-text">Hızlı Satış</span>
            </a>
        </li>

        <li class="px-4 pt-4 pb-2"><small class="nav-text">Finans</small></li>

        <li>
            <a class="nav-link {{ (request()->is('finance/transactions*') || request()->routeIs('finance.transactions*')) ? 'active' : '' }}" href="{{ route('finance.transactions') }}">
                <i class="ti ti-report-money"></i>
                <span class="nav-text">İşlemler</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('finance/debts*') || request()->routeIs('finance.debts*')) ? 'active' : '' }}" href="{{ route('finance.debts.index') }}">
                <i class="ti ti-file-invoice"></i>
                <span class="nav-text">Borç Takibi</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('finance/expenses*') || request()->routeIs('finance.expenses*')) ? 'active' : '' }}" href="{{ route('finance.expenses') }}">
                <i class="ti ti-receipt"></i>
                <span class="nav-text">Giderler</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('finance/commissions*') || request()->routeIs('finance.commissions*')) ? 'active' : '' }}" href="{{ route('finance.commissions.index') }}">
                <i class="ti ti-wallet"></i>
                <span class="nav-text">Prim & Hakediş</span>
            </a>
        </li>

        <li class="px-4 pt-4 pb-2"><small class="nav-text">Pazarlama</small></li>

        <li>
            <a class="nav-link {{ (request()->is('campaigns*') || request()->routeIs('campaigns.*')) ? 'active' : '' }}" href="{{ route('campaigns.index') }}">
                <i class="ti ti-speakerphone"></i>
                <span class="nav-text">Kampanyalar</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('reviews*') || request()->routeIs('reviews.*')) ? 'active' : '' }}" href="{{ route('reviews.index') }}">
                <i class="ti ti-star"></i>
                <span class="nav-text">Değerlendirmeler</span>
            </a>
        </li>

        <li class="px-4 pt-4 pb-2"><small class="nav-text">Sistem</small></li>

        <li>
            <a class="nav-link {{ (request()->is('notifications*') || request()->routeIs('notifications.*')) ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                <i class="ti ti-bell"></i>
                <span class="nav-text">Bildirimler</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('reports*') || request()->routeIs('reports.*')) ? 'active' : '' }}" href="{{ route('reports.index') }}">
                <i class="ti ti-chart-bar"></i>
                <span class="nav-text">Raporlar</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('settings*') || request()->routeIs('settings.*')) ? 'active' : '' }}" href="{{ route('settings.index') }}">
                <i class="ti ti-settings"></i>
                <span class="nav-text">Ayarlar</span>
            </a>
        </li>
    </ul>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const normalizePath = (path) => path.replace(/\/+$/, '').toLowerCase();
            const currentPath = normalizePath(window.location.pathname);
            
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                const href = link.getAttribute('href');
                if (href) {
                    try {
                        const url = new URL(href, window.location.origin);
                        const linkPath = normalizePath(url.pathname);
                        
                        if (linkPath === currentPath) {
                            link.classList.add('active');
                        } else if (linkPath !== '' && linkPath !== '/dashboard' && currentPath.startsWith(linkPath)) {
                            link.classList.add('active');
                        }
                    } catch (e) {}
                }
            });
        });
    </script>
</aside>
