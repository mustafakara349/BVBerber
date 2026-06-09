@extends('layouts.app')
@section('title', 'Randevu Analizleri - B&V Barber')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="fs-3 mb-1">Randevu Raporları & Analizleri</h1>
                <p class="text-muted">Randevu yoğunluğu, berber performansları, hizmet popülerliği ve iptal analizleri.</p>
            </div>
            <a href="{{ route('reports.index') }}" class="btn btn-light border rounded-pill px-3">
                <i class="ti ti-arrow-left me-1"></i> Geri Dön
            </a>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('reports.show', 'appointments') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary">Rapor Dönemi</label>
                <select name="period" id="periodSelect" class="form-select border-0 bg-light rounded-3">
                    <option value="today" {{ $filters['period'] === 'today' ? 'selected' : '' }}>Bugün</option>
                    <option value="this_week" {{ $filters['period'] === 'this_week' ? 'selected' : '' }}>Bu Hafta</option>
                    <option value="this_month" {{ $filters['period'] === 'this_month' ? 'selected' : '' }}>Bu Ay</option>
                    <option value="last_30_days" {{ $filters['period'] === 'last_30_days' ? 'selected' : '' }}>Son 30 Gün</option>
                    <option value="last_month" {{ $filters['period'] === 'last_month' ? 'selected' : '' }}>Geçen Ay</option>
                    <option value="this_year" {{ $filters['period'] === 'this_year' ? 'selected' : '' }}>Bu Yıl</option>
                    <option value="custom" {{ $filters['period'] === 'custom' ? 'selected' : '' }}>Özel Tarih Aralığı</option>
                </select>
            </div>
            
            <div class="col-md-3 date-range-fields" style="display: {{ $filters['period'] === 'custom' ? 'block' : 'none' }};">
                <label class="form-label small fw-bold text-secondary">Başlangıç Tarihi</label>
                <input type="date" name="start_date" class="form-control border-0 bg-light rounded-3" value="{{ $filters['start_date'] }}">
            </div>
            
            <div class="col-md-3 date-range-fields" style="display: {{ $filters['period'] === 'custom' ? 'block' : 'none' }};">
                <label class="form-label small fw-bold text-secondary">Bitiş Tarihi</label>
                <input type="date" name="end_date" class="form-control border-0 bg-light rounded-3" value="{{ $filters['end_date'] }}">
            </div>
            
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary rounded-3 w-100 fw-semibold py-2">
                    <i class="ti ti-filter me-1"></i> Filtrele ve Güncelle
                </button>
            </div>
        </form>
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="row g-4 mb-4">
    <!-- Total Appointments -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 text-center">
                <h6 class="text-secondary small fw-medium text-uppercase mb-1">Toplam Randevu</h6>
                <h3 class="fw-bold mb-0 text-dark">{{ $totalAppointments }}</h3>
                <span class="text-muted small">Tüm Durumlar</span>
            </div>
        </div>
    </div>
    
    <!-- Completed Appointments -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4" style="border-bottom: 4px solid #10b981 !important;">
            <div class="card-body p-4 text-center">
                <h6 class="text-secondary small fw-medium text-uppercase mb-1">Tamamlanan</h6>
                <h3 class="fw-bold mb-0 text-success">{{ $completedCount }}</h3>
                <span class="text-success small fw-medium">
                    %{{ $totalAppointments > 0 ? round(($completedCount / $totalAppointments) * 100, 1) : 0 }} Başarı
                </span>
            </div>
        </div>
    </div>

    <!-- Cancelled / Rejected -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4" style="border-bottom: 4px solid #ef4444 !important;">
            <div class="card-body p-4 text-center">
                <h6 class="text-secondary small fw-medium text-uppercase mb-1">İptal / Red</h6>
                <h3 class="fw-bold mb-0 text-danger">{{ $cancelledCount + $rejectedCount }}</h3>
                <span class="text-danger small fw-medium">
                    %{{ $totalAppointments > 0 ? round((($cancelledCount + $rejectedCount) / $totalAppointments) * 100, 1) : 0 }} Oran
                </span>
            </div>
        </div>
    </div>

    <!-- No Show -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-4" style="border-bottom: 4px solid #f59e0b !important;">
            <div class="card-body p-4 text-center">
                <h6 class="text-secondary small fw-medium text-uppercase mb-1">Gelmeyen (No-Show)</h6>
                <h3 class="fw-bold mb-0 text-warning">{{ $noShowCount }}</h3>
                <span class="text-warning small fw-medium">
                    %{{ $totalAppointments > 0 ? round(($noShowCount / $totalAppointments) * 100, 1) : 0 }} Kayıp
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Area Chart: Completed vs Cancelled Trend -->
    <div class="col-xl-8">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold text-dark mb-0">Randevu Dağılım Trendi</h5>
                <p class="text-muted small mb-0">Tamamlanan randevular ile iptal olan randevuların zaman bazlı seyri.</p>
            </div>
            <div class="card-body p-4">
                <div id="appointmentTrendChart" style="min-height: 330px;"></div>
            </div>
        </div>
    </div>
    
    <!-- Donut Chart: Appointment Status Distribution -->
    <div class="col-xl-4">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold text-dark mb-0">Randevu Durum Payı</h5>
                <p class="text-muted small mb-0">Toplam randevuların statülerine göre genel yüzdeleri.</p>
            </div>
            <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                @if($totalAppointments > 0)
                    <div id="statusDistributionChart" class="w-100" style="min-height: 280px;"></div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="ti ti-mood-empty fs-1 mb-2 d-block"></i> Veri bulunmuyor.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Barber Stats -->
    <div class="col-xl-6">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                <h5 class="fw-bold text-dark mb-0"><i class="ti ti-users-group text-primary me-2"></i>Berberlerin Randevu & Ciro Payı</h5>
                <p class="text-muted small mb-0">Berberlerin tamamladığı işlem sayıları ve ürettikleri toplam ciro.</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Berber</th>
                                <th class="text-center">Tamamlanan</th>
                                <th class="text-center">İptal/Red/Gelmeyen</th>
                                <th class="text-end pe-4">Toplam Ciro</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($barberStats as $barber)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold text-dark">{{ $barber->first_name }} {{ $barber->last_name }}</div>
                                    <div class="text-secondary small">{{ $barber->title }}</div>
                                </td>
                                <td class="text-center fw-bold text-success">{{ $barber->completed_count }}</td>
                                <td class="text-center text-danger">{{ $barber->cancelled_count }}</td>
                                <td class="text-end pe-4 fw-bold text-dark">₺{{ number_format($barber->total_revenue, 2, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">Kayıt bulunamadı.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Service Stats -->
    <div class="col-xl-6">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                <h5 class="fw-bold text-dark mb-0"><i class="ti ti-scissors text-success me-2"></i>Hizmet Popülerlik Raporu</h5>
                <p class="text-muted small mb-0">Hangi hizmetlerin daha çok tercih edildiği ve ciroya katkıları.</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Hizmet Adı</th>
                                <th class="text-center">Tercih Edilme</th>
                                <th class="text-end pe-4">Toplam Gelir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviceStats as $srv)
                            <tr>
                                <td class="ps-4 fw-semibold text-dark">{{ $srv->name }}</td>
                                <td class="text-center fw-bold text-primary">{{ $srv->completed_count }} Kez</td>
                                <td class="text-end pe-4 fw-bold text-success">₺{{ number_format($srv->total_revenue, 2, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">Kayıt bulunamadı.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancellation Reason Stats Card -->
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
        <h5 class="fw-bold text-dark mb-0"><i class="ti ti-alert-triangle text-danger me-2"></i>Randevu Reddetme & İptal Gerekçeleri Analizi</h5>
        <p class="text-muted small mb-0">İptal edilen randevular için girilen gerekçelerin sıklığı.</p>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">İptal / Red Nedeni Açıklaması</th>
                        <th class="text-center pe-4" style="width: 150px;">Tekrar Sayısı</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cancellationReasons as $reason)
                    <tr>
                        <td class="ps-4 text-dark font-monospace text-wrap">{{ $reason->cancellation_reason }}</td>
                        <td class="text-center pe-4 fw-bold text-danger">{{ $reason->total }} Kez</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center py-4 text-muted">Gerekçe girilmiş herhangi bir iptal kaydı bulunmuyor.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Toggle Custom Date Fields
        const periodSelect = document.getElementById('periodSelect');
        if (periodSelect) {
            periodSelect.addEventListener('change', function() {
                const dateFields = document.querySelectorAll('.date-range-fields');
                dateFields.forEach(field => {
                    field.style.display = this.value === 'custom' ? 'block' : 'none';
                });
            });
        }

        // 1. Appointment Trend Chart
        const trendOptions = {
            chart: {
                type: 'area',
                height: 330,
                toolbar: { show: false },
                fontFamily: 'Outfit, system-ui, -apple-system, sans-serif'
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            colors: ['#10b981', '#ef4444'],
            series: [
                { name: 'Tamamlanan', data: @json($chartCompletedData) },
                { name: 'İptal / Reddedilen', data: @json($chartCancelledData) }
            ],
            xaxis: {
                categories: @json($chartTimeline),
                labels: { style: { colors: '#94a3b8' } }
            },
            yaxis: {
                labels: { style: { colors: '#94a3b8' } }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.3,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            }
        };
        const trendChart = new ApexCharts(document.querySelector("#appointmentTrendChart"), trendOptions);
        trendChart.render();

        // 2. Status Distribution Chart
        @if($totalAppointments > 0)
            const statusOptions = {
                chart: { type: 'donut', height: 280, fontFamily: 'Outfit, system-ui, -apple-system, sans-serif' },
                labels: ['Tamamlanan', 'İptal Edilen', 'Reddedilen', 'Gelmeyen (No-Show)', 'Bekleyen/Onaylı'],
                series: [
                    {{ $completedCount }},
                    {{ $cancelledCount }},
                    {{ $rejectedCount }},
                    {{ $noShowCount }},
                    {{ $totalAppointments - ($completedCount + $cancelledCount + $rejectedCount + $noShowCount) }}
                ],
                colors: ['#10b981', '#ef4444', '#dc2626', '#f59e0b', '#3b82f6'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + '%' }
            };
            const statusChart = new ApexCharts(document.querySelector("#statusDistributionChart"), statusOptions);
            statusChart.render();
        @endif
    });
</script>
@endpush
@endsection
