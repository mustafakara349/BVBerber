<div class="row g-3 mb-3">
    <div class="col-lg-3 col-12">
        <div class="card p-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-2">
            <div class="d-flex gap-3">
                <div class="icon-shape icon-md bg-primary text-white rounded-2">
                    <i class="ti ti-calendar-event fs-4"></i>
                </div>
                <div>
                    <h2 class="mb-3 fs-6">Bugünkü Randevular</h2>
                    <h3 class="fw-bold mb-0" id="widgetTodayAppointmentsTotal">{{ $widgets['appointments']['today_total'] ?? 0 }}</h3>
                    <p class="text-primary mb-0 small"><span id="widgetTodayAppointmentsCompleted">{{ $widgets['appointments']['today_completed'] ?? 0 }}</span> tamamlandı
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-12">
        <div class="card p-4 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-2">
            <div class="d-flex gap-3">
                <div class="icon-shape icon-md bg-success text-white rounded-2">
                    <i class="ti ti-currency-lira fs-4"></i>
                </div>
                <div>
                    <h2 class="mb-3 fs-6">Günlük Gelir</h2>
                    <h3 class="fw-bold mb-0" id="widgetDailyRevenue">₺{{ number_format($widgets['revenue']['daily'] ?? 0, 0, ',', '.') }}</h3>
                    <p class="text-success mb-0 small">Bugün</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-12">
        <div class="card p-4 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-2">
            <div class="d-flex gap-3">
                <div class="icon-shape icon-md bg-danger text-white rounded-2">
                    <i class="ti ti-coins fs-4"></i>
                </div>
                <div>
                    <h2 class="mb-3 fs-6">Günlük Gider</h2>
                    <h3 class="fw-bold mb-0" id="widgetDailyExpense">₺{{ number_format($widgets['revenue']['daily_expense'] ?? 0, 0, ',', '.') }}</h3>
                    <p class="text-danger mb-0 small">Bugün</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-12">
        <div class="card p-4 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-2">
            <div class="d-flex gap-3">
                <div class="icon-shape icon-md bg-warning text-white rounded-2">
                    <i class="ti ti-users fs-4"></i>
                </div>
                <div>
                    <h2 class="mb-3 fs-6">Yeni Müşteriler</h2>
                    <h3 class="fw-bold mb-0" id="widgetNewCustomers">{{ $widgets['customers']['new_this_month'] ?? 0 }}</h3>
                    <p class="text-warning mb-0 small">Bu ay</p>
                </div>
            </div>
        </div>
    </div>
</div>
