@extends('layouts.app')
@section('title', 'Müşteri Raporları - B&V Barber')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="fs-3 mb-1">Müşteri Raporları & Analizleri</h1>
                <p class="text-muted">Müşteri büyüme oranları, demografi ve en sadık müşteri profilleri.</p>
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
        <form method="GET" action="{{ route('reports.show', 'customers') }}" class="row g-3 align-items-end">
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
    <!-- New Customers -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4" style="border-bottom: 4px solid #3b82f6 !important;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-secondary small fw-medium text-uppercase mb-1">Dönemsel Yeni Kayıt</h6>
                    <h3 class="fw-bold mb-0 text-primary">{{ $newCustomers }} Yeni Müşteri</h3>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                    <i class="ti ti-user-plus fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Active Customers -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4" style="border-bottom: 4px solid #10b981 !important;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-secondary small fw-medium text-uppercase mb-1">Sistemdeki Toplam Kayıt</h6>
                    <h3 class="fw-bold mb-0 text-success">{{ $totalCustomers }} Müşteri</h3>
                </div>
                <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                    <i class="ti ti-users-group fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Loyal Customers (visited 3+ times) -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4" style="border-bottom: 4px solid #f59e0b !important;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-secondary small fw-medium text-uppercase mb-1">Sadık Müşteri (3+ Geliş)</h6>
                    <h3 class="fw-bold mb-0 text-warning">{{ $loyalCustomers }} Müşteri</h3>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                    <i class="ti ti-award fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Area Chart: Signup Trend -->
    <div class="col-xl-8">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold text-dark mb-0">Yeni Müşteri Kayıt Trendi</h5>
                <p class="text-muted small mb-0">Seçilen dönem boyunca sisteme kayıt olan müşteri grafiği.</p>
            </div>
            <div class="card-body p-4">
                <div id="customerSignupChart" style="min-height: 330px;"></div>
            </div>
        </div>
    </div>
    
    <!-- Donut Chart: Gender Demographics -->
    <div class="col-xl-4">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold text-dark mb-0">Müşteri Cinsiyet Dağılımı</h5>
                <p class="text-muted small mb-0">Kayıtlı müşterilerin cinsiyet kırılımı.</p>
            </div>
            <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                @if($totalCustomers > 0)
                    <div id="genderDemographicsChart" class="w-100" style="min-height: 280px;"></div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="ti ti-mood-empty fs-1 mb-2 d-block"></i> Veri bulunmuyor.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- VIP Customers Table -->
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
        <h5 class="fw-bold text-dark mb-0"><i class="ti ti-vip text-warning me-2"></i>Dönemin En Çok Kazandıran Müşterileri (VIP 20)</h5>
        <p class="text-muted small mb-0">Seçilen tarihler arasında en fazla tamamlanan randevuya sahip ve en yüksek harcamayı yapan müşteriler.</p>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Müşteri Profil</th>
                        <th>Kayıt Tarihi</th>
                        <th>İletişim Bilgileri</th>
                        <th class="text-center">Tamamlanan Ziyaret</th>
                        <th class="text-center">Son Geliş Tarihi</th>
                        <th class="text-end pe-4">Toplam Harcama</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCustomers as $customer)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                @if($customer->profile_photo)
                                    <img src="{{ asset($customer->profile_photo) }}" class="rounded-circle me-3 object-fit-cover shadow-sm" width="40" height="40" alt="{{ $customer->full_name }}">
                                @else
                                    <div class="avatar bg-primary-subtle text-primary me-3 rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px; font-size: 14px;">
                                        {{ mb_substr($customer->first_name, 0, 1) }}{{ mb_substr($customer->last_name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('customers.show', $customer->id) }}" class="fw-semibold text-dark text-decoration-none">{{ $customer->full_name }}</a>
                                </div>
                            </div>
                        </td>
                        <td class="text-secondary small">{{ \Carbon\Carbon::parse($customer->created_at)->format('d.m.Y') }}</td>
                        <td class="text-secondary small">
                            <div>{{ $customer->phone ?? 'Telefon Yok' }}</div>
                            <div class="text-muted" style="font-size: 11px;">{{ $customer->email }}</div>
                        </td>
                        <td class="text-center fw-bold text-success">{{ $customer->visits_count }} Kez</td>
                        <td class="text-center text-secondary small">{{ \Carbon\Carbon::parse($customer->last_visit_date)->format('d.m.Y H:i') }}</td>
                        <td class="text-end pe-4 fw-bold text-dark">₺{{ number_format($customer->total_spent, 2, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted"><i class="ti ti-mood-empty fs-1 d-block mb-1"></i>Dönemde ziyaret gerçekleştiren müşteri bulunamadı.</td></tr>
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

        // 1. Customer Signup Trend Chart
        const signupOptions = {
            chart: {
                type: 'area',
                height: 330,
                toolbar: { show: false },
                fontFamily: 'Outfit, system-ui, -apple-system, sans-serif'
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            colors: ['#3b82f6'],
            series: [
                { name: 'Yeni Müşteri Kaydı', data: @json($chartRegData) }
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
        const signupChart = new ApexCharts(document.querySelector("#customerSignupChart"), signupOptions);
        signupChart.render();

        // 2. Gender Demographics Chart
        @if($totalCustomers > 0)
            const genderOptions = {
                chart: { type: 'donut', height: 280, fontFamily: 'Outfit, system-ui, -apple-system, sans-serif' },
                labels: {!! json_encode(array_column($genderDistribution, 'label')) !!},
                series: {!! json_encode(array_column($genderDistribution, 'total')) !!},
                colors: ['#3b82f6', '#ec4899', '#94a3b8'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + '%' }
            };
            const genderChart = new ApexCharts(document.querySelector("#genderDemographicsChart"), genderOptions);
            genderChart.render();
        @endif
    });
</script>
@endpush
@endsection
