@extends('layouts.app')
@section('title', 'Raporlar - B&V Barber')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h1 class="fs-3 mb-1">Raporlar & Analizler</h1>
            <p class="text-muted">İşletmenizin genel finansal durumunu, randevu istatistiklerini ve müşteri sadakatini buradan takip edebilirsiniz.</p>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Total Appointments -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(79, 70, 229, 0.1) 100%); border-left: 5px solid #4f46e5 !important;">
            <div class="card-body p-4 text-center">
                <div class="mx-auto mb-3 bg-white shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="ti ti-calendar-check fs-3 text-primary"></i>
                </div>
                <h3 class="fw-bold mb-1 text-dark">{{ $generalStats['total_appointments'] }}</h3>
                <span class="text-secondary small fw-medium text-uppercase">Toplam Randevu</span>
            </div>
        </div>
    </div>

    <!-- Total Customers -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%); border-left: 5px solid #059669 !important;">
            <div class="card-body p-4 text-center">
                <div class="mx-auto mb-3 bg-white shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="ti ti-users fs-3 text-success"></i>
                </div>
                <h3 class="fw-bold mb-1 text-dark">{{ $generalStats['total_customers'] }}</h3>
                <span class="text-secondary small fw-medium text-uppercase">Toplam Müşteri</span>
            </div>
        </div>
    </div>

    <!-- Total Income -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.1) 0%, rgba(8, 145, 178, 0.1) 100%); border-left: 5px solid #0891b2 !important;">
            <div class="card-body p-4 text-center">
                <div class="mx-auto mb-3 bg-white shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="ti ti-trending-up fs-3 text-info"></i>
                </div>
                <h3 class="fw-bold mb-1 text-dark">₺{{ number_format($generalStats['total_income'], 2, ',', '.') }}</h3>
                <span class="text-secondary small fw-medium text-uppercase">Toplam Gelir</span>
            </div>
        </div>
    </div>

    <!-- Total Expense -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%); border-left: 5px solid #dc2626 !important;">
            <div class="card-body p-4 text-center">
                <div class="mx-auto mb-3 bg-white shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="ti ti-trending-down fs-3 text-danger"></i>
                </div>
                <h3 class="fw-bold mb-1 text-dark">₺{{ number_format($generalStats['total_expense'], 2, ',', '.') }}</h3>
                <span class="text-secondary small fw-medium text-uppercase">Toplam Gider</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <!-- Financial Reports Card -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4 h-100 hover-translate transition-all">
            <div class="card-body text-center p-5">
                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 90px; height: 90px;">
                    <i class="ti ti-cash-banknote fs-1 text-primary"></i>
                </div>
                <h4 class="fw-bold text-dark mb-3">Finansal Raporlar</h4>
                <p class="text-muted mb-4 small">Gelir ve gider işlemlerinizin detaylı dökümünü, ödeme yöntemlerine göre dağılımı ve kârlılık grafiklerini inceleyin.</p>
                <a href="{{ route('reports.show', 'finance') }}" class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-semibold">
                    <i class="ti ti-chart-bar me-1"></i> Raporu İncele
                </a>
            </div>
        </div>
    </div>
    
    <!-- Appointment Analytics Card -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4 h-100 hover-translate transition-all">
            <div class="card-body text-center p-5">
                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 90px; height: 90px;">
                    <i class="ti ti-calendar-stats fs-1 text-success"></i>
                </div>
                <h4 class="fw-bold text-dark mb-3">Randevu Analizleri</h4>
                <p class="text-muted mb-4 small">Randevu durumu dağılımları, berberlerin doluluk oranları, popüler hizmet analizleri ve iptal gerekçeleri raporları.</p>
                <a href="{{ route('reports.show', 'appointments') }}" class="btn btn-outline-success rounded-pill px-4 btn-sm fw-semibold">
                    <i class="ti ti-chart-bar me-1"></i> Raporu İncele
                </a>
            </div>
        </div>
    </div>
    
    <!-- Customer Reports Card -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4 h-100 hover-translate transition-all">
            <div class="card-body text-center p-5">
                <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 90px; height: 90px;">
                    <i class="ti ti-users-group fs-1 text-info"></i>
                </div>
                <h4 class="fw-bold text-dark mb-3">Müşteri Raporları</h4>
                <p class="text-muted mb-4 small">VIP sadık müşteriler listesi, cinsiyet demografisi ve dönemsel müşteri kayıt hızları analiz grafikleri.</p>
                <a href="{{ route('reports.show', 'customers') }}" class="btn btn-outline-info rounded-pill px-4 btn-sm fw-semibold">
                    <i class="ti ti-chart-bar me-1"></i> Raporu İncele
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.hover-translate {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.hover-translate:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.08)!important;
}
</style>
@endsection
