<aside id="sidebar" class="sidebar shadow-sm"
    style="display: flex; flex-direction: column; overflow: hidden; height: 100vh; padding-top: 0 !important;">
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

        /* Submenu styling responsive to collapsed state */
        .sidebar-submenu {
            border-left: 2px solid rgba(230,98,57,0.25); 
            margin-left: 24px;
        }
        .sidebar.collapsed .sidebar-submenu {
            border-left: none !important;
            margin-left: 0 !important;
            padding-left: 0 !important;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sidebar.collapsed .sidebar-submenu .nav-link {
            justify-content: center;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .sidebar.collapsed .nav-link[data-bs-toggle="collapse"] .ti-chevron-down {
            display: none !important;
        }
    </style>

    <div class="logo-area px-4"
        style="height: 70px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; padding-left: 24px; background: #ffffff; z-index: 10; flex-shrink: 0; position: relative !important; top: auto !important; left: auto !important;">
        <a href="{{ route('dashboard') }}" class="d-inline-flex align-items-center" style="text-decoration: none;">
            <span class="fw-bold text-primary fs-5">B&V</span>
            <span class="logo-text ms-2 fw-semibold text-dark">Barber</span>
        </a>
    </div>

    <ul class="nav flex-column flex-nowrap"
        style="flex: 1; overflow-y: auto; overflow-x: hidden; padding-bottom: 24px;">
        <li class="px-4 py-2"><small class="nav-text">Ana Menü</small></li>

        <li>
            <a class="nav-link {{ (request()->is('dashboard') || request()->routeIs('dashboard')) ? 'active' : '' }}"
                href="{{ route('dashboard') }}">
                <i class="ti ti-dashboard"></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('appointments*') || request()->routeIs('appointments.*')) ? 'active' : '' }}"
                href="{{ route('appointments.index') }}">
                <i class="ti ti-calendar-event"></i>
                <span class="nav-text">Randevular</span>
            </a>
        </li>

        <li>
            @php
                $isEmployeesActive = request()->is('employees*') || request()->routeIs('employees.*');
                $isTimeBlocksActive = request()->is('employee-time-blocks*') || request()->routeIs('employee-time-blocks.*');
                $isEmployeeGroupActive = $isEmployeesActive || $isTimeBlocksActive;
            @endphp
            <a class="nav-link d-flex align-items-center justify-content-between {{ $isEmployeeGroupActive ? 'active' : '' }}"
                data-bs-toggle="collapse" href="#employeesAccordion" role="button"
                aria-expanded="{{ $isEmployeeGroupActive ? 'true' : 'false' }}" aria-controls="employeesAccordion"
                style="cursor: pointer;">
                <span class="d-flex align-items-center gap-2">
                    <i class="ti ti-users"></i>
                    <span class="nav-text">Çalışan Yönetimi</span>
                </span>
                <i class="ti ti-chevron-down nav-text" style="font-size: 0.75rem; transition: transform 0.2s;"></i>
            </a>
            <div class="collapse {{ $isEmployeeGroupActive ? 'show' : '' }}" id="employeesAccordion">
                <ul class="nav flex-column ps-3 pt-1 pb-1 sidebar-submenu">
                    <li>
                        <a class="nav-link py-2 {{ $isEmployeesActive ? 'active' : '' }}"
                            href="{{ route('employees.index') }}" style="font-size: 0.875rem;">
                            <i class="ti ti-user-edit" style="font-size: 1rem;"></i>
                            <span class="nav-text">Çalışanlar</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link py-2 {{ $isTimeBlocksActive ? 'active' : '' }}"
                            href="{{ route('employee-time-blocks.index') }}" style="font-size: 0.875rem;">
                            <i class="ti ti-calendar-off" style="font-size: 1rem;"></i>
                            <span class="nav-text">İzin Yönetimi</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('customers*') || request()->routeIs('customers.*')) ? 'active' : '' }}"
                href="{{ route('customers.index') }}">
                <i class="ti ti-user-circle"></i>
                <span class="nav-text">Müşteriler</span>
            </a>
        </li>

        <li>
            {{-- Hizmet & Ürün Yönetimi - Accordion Menü --}}
            @php
                $isServicesActive = request()->is('services*') || request()->routeIs('services.*');
                $isCafeActive = request()->is('cafe*') || request()->routeIs('cafe.*') || request()->is('cafe-categories*') || request()->routeIs('cafe-categories.*');
                $isProductsActive = request()->is('products') || request()->routeIs('products.index') || request()->routeIs('products.create') || request()->routeIs('products.edit') || request()->routeIs('products.show') || request()->routeIs('products.store') || request()->routeIs('products.update') || request()->routeIs('products.destroy');
                $isMenuGroupActive = $isServicesActive || $isCafeActive || $isProductsActive;
            @endphp
            <a class="nav-link d-flex align-items-center justify-content-between {{ $isMenuGroupActive ? 'active' : '' }}"
                data-bs-toggle="collapse" href="#servicesAccordion" role="button"
                aria-expanded="{{ $isMenuGroupActive ? 'true' : 'false' }}" aria-controls="servicesAccordion"
                style="cursor: pointer;">
                <span class="d-flex align-items-center gap-2">
                    <i class="ti ti-layout-grid"></i>
                    <span class="nav-text">Hizmet & Ürün</span>
                </span>
                <i class="ti ti-chevron-down nav-text" style="font-size: 0.75rem; transition: transform 0.2s;"></i>
            </a>
            <div class="collapse {{ $isMenuGroupActive ? 'show' : '' }}" id="servicesAccordion">
                <ul class="nav flex-column ps-3 pt-1 pb-1 sidebar-submenu">
                    <li>
                        <a class="nav-link py-2 {{ $isServicesActive ? 'active' : '' }}"
                            href="{{ route('services.index') }}" style="font-size: 0.875rem;">
                            <i class="ti ti-cut" style="font-size: 1rem;"></i>
                            <span class="nav-text">Berber & Bakım</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link py-2 {{ $isCafeActive ? 'active' : '' }}" href="{{ route('cafe.index') }}"
                            style="font-size: 0.875rem;">
                            <i class="ti ti-coffee" style="font-size: 1rem;"></i>
                            <span class="nav-text">Cafe Bölümü</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link py-2 {{ $isProductsActive ? 'active' : '' }}"
                            href="{{ route('products.index') }}" style="font-size: 0.875rem;">
                            <i class="ti ti-box" style="font-size: 1rem;"></i>
                            <span class="nav-text">Ürünler</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>


        <li class="px-4 pt-4 pb-2"><small class="nav-text">Stok & Tedarik</small></li>




        <li>
            <a class="nav-link {{ (request()->is('products-sales*') || request()->routeIs('products.sales.*')) ? 'active' : '' }}"
                href="{{ route('products.sales.index') }}">
                <i class="ti ti-shopping-cart"></i>
                <span class="nav-text">Hızlı Satış</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('suppliers*') || request()->routeIs('suppliers.*')) ? 'active' : '' }}"
                href="{{ route('suppliers.index') }}">
                <i class="ti ti-truck"></i>
                <span class="nav-text">Tedarikçiler</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('purchase-orders*') || request()->routeIs('purchase-orders.*')) ? 'active' : '' }}"
                href="{{ route('purchase-orders.index') }}">
                <i class="ti ti-file-invoice"></i>
                <span class="nav-text">Mal Alımları</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('stock-movements*') || request()->routeIs('stock-movements.*')) ? 'active' : '' }}"
                href="{{ route('stock-movements.index') }}">
                <i class="ti ti-transfer-in"></i>
                <span class="nav-text">Stok Hareketleri</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('stock-counts*') || request()->routeIs('stock-counts.*')) ? 'active' : '' }}"
                href="{{ route('stock-counts.index') }}">
                <i class="ti ti-clipboard-list"></i>
                <span class="nav-text">Sayım & Fire</span>
            </a>
        </li>

        <li class="px-4 pt-4 pb-2"><small class="nav-text">Finans</small></li>

        <li>
            <a class="nav-link {{ (request()->is('finance/transactions*') || request()->routeIs('finance.transactions*')) ? 'active' : '' }}"
                href="{{ route('finance.transactions') }}">
                <i class="ti ti-report-money"></i>
                <span class="nav-text">İşlemler</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('finance/receivables*') || request()->is('finance/payables*') || request()->routeIs('finance.receivables*') || request()->routeIs('finance.payables*')) ? 'active' : '' }}"
                href="{{ route('finance.receivables.index') }}">
                <i class="ti ti-file-invoice"></i>
                <span class="nav-text">Borç & Alacak</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('finance/expenses*') || request()->routeIs('finance.expenses*')) ? 'active' : '' }}"
                href="{{ route('finance.expenses') }}">
                <i class="ti ti-receipt"></i>
                <span class="nav-text">Giderler</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('finance/commissions*') || request()->routeIs('finance.commissions*')) ? 'active' : '' }}"
                href="{{ route('finance.commissions.index') }}">
                <i class="ti ti-wallet"></i>
                <span class="nav-text">Prim & Hakediş</span>
            </a>
        </li>

        <li class="px-4 pt-4 pb-2"><small class="nav-text">Pazarlama</small></li>

        <li>
            <a class="nav-link {{ (request()->is('campaigns*') || request()->routeIs('campaigns.*')) ? 'active' : '' }}"
                href="{{ route('campaigns.index') }}">
                <i class="ti ti-speakerphone"></i>
                <span class="nav-text">Kampanyalar</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('reviews*') || request()->routeIs('reviews.*')) ? 'active' : '' }}"
                href="{{ route('reviews.index') }}">
                <i class="ti ti-star"></i>
                <span class="nav-text">Değerlendirmeler</span>
            </a>
        </li>

        <li class="px-4 pt-4 pb-2"><small class="nav-text">Sistem</small></li>

        <li>
            <a class="nav-link {{ (request()->is('notifications*') || request()->routeIs('notifications.*')) ? 'active' : '' }}"
                href="{{ route('notifications.index') }}">
                <i class="ti ti-bell"></i>
                <span class="nav-text">Bildirimler</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('reports*') || request()->routeIs('reports.*')) ? 'active' : '' }}"
                href="{{ route('reports.index') }}">
                <i class="ti ti-chart-bar"></i>
                <span class="nav-text">Raporlar</span>
            </a>
        </li>

        <li>
            <a class="nav-link {{ (request()->is('settings*') || request()->routeIs('settings.*')) ? 'active' : '' }}"
                href="{{ route('settings.index') }}">
                <i class="ti ti-settings"></i>
                <span class="nav-text">Ayarlar</span>
            </a>
        </li>
        <li class="py-4"></li>
    </ul>
</aside>