<div class="row g-3 mb-4 db-stagger-grid">
    <div class="col-lg-3 col-sm-6 col-12">
        <div class="db-stat-card db-stat-primary h-100">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div class="db-stat-title">Bugünkü Randevular</div>
                <div class="db-stat-icon icon-primary">
                    <i class="ti ti-calendar-event"></i>
                </div>
            </div>
            <div class="db-stat-value" id="widgetTodayAppointmentsTotal">{{ $widgets['appointments']['today_total'] ?? 0 }}</div>
            <div class="mt-2">
                <span class="db-stat-badge bg-primary-subtle text-primary">
                    <i class="ti ti-check me-0.5"></i>
                    <span id="widgetTodayAppointmentsCompleted">{{ $widgets['appointments']['today_completed'] ?? 0 }}</span> tamamlandı
                </span>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 col-12">
        <div class="db-stat-card db-stat-success h-100">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div class="db-stat-title">Günlük Gelir</div>
                <div class="db-stat-icon icon-success">
                    <i class="ti ti-currency-lira"></i>
                </div>
            </div>
            <div class="db-stat-value" id="widgetDailyRevenue">₺{{ number_format($widgets['revenue']['daily'] ?? 0, 2, ',', '.') }}</div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 col-12">
        <div class="db-stat-card db-stat-danger h-100">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div class="db-stat-title">Günlük Gider</div>
                <div class="db-stat-icon icon-danger">
                    <i class="ti ti-coins"></i>
                </div>
            </div>
            <div class="db-stat-value" id="widgetDailyExpense">₺{{ number_format($widgets['revenue']['daily_expense'] ?? 0, 2, ',', '.') }}</div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 col-12">
        <div class="db-stat-card db-stat-warning h-100">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div class="db-stat-title">Yeni Müşteriler</div>
                <div class="db-stat-icon icon-warning">
                    <i class="ti ti-users"></i>
                </div>
            </div>
            <div class="db-stat-value" id="widgetNewCustomers">{{ $widgets['customers']['new_this_month'] ?? 0 }}</div>
            <div class="mt-2">
                <span class="db-stat-badge bg-warning-subtle text-warning-emphasis">
                    <i class="ti ti-sparkles me-0.5"></i> Bu ay
                </span>
            </div>
        </div>
    </div>
</div>

