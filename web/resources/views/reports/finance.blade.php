@extends('layouts.app')
@section('title', 'Finansal Raporlar - B&V Barber')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="fs-3 mb-1">Finansal Raporlar & Analizler</h1>
                <p class="text-muted">Gelir, gider ve karlılık durumunun dönemsel detayları.</p>
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
        <form method="GET" action="{{ route('reports.show', 'finance') }}" class="row g-3 align-items-end">
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

<!-- KPI Summary Widgets -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4" style="border-bottom: 4px solid #10b981 !important;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-secondary small fw-medium text-uppercase mb-1">Dönemsel Gelir</h6>
                    <h3 class="fw-bold mb-0 text-success">₺{{ number_format($totalIncome, 2, ',', '.') }}</h3>
                </div>
                <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                    <i class="ti ti-trending-up fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4" style="border-bottom: 4px solid #ef4444 !important;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-secondary small fw-medium text-uppercase mb-1">Dönemsel Gider</h6>
                    <h3 class="fw-bold mb-0 text-danger">₺{{ number_format($totalExpense, 2, ',', '.') }}</h3>
                </div>
                <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3">
                    <i class="ti ti-trending-down fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4" style="border-bottom: 4px solid #6366f1 !important;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-secondary small fw-medium text-uppercase mb-1">Dönemsel Net Durum</h6>
                    <h3 class="fw-bold mb-0 {{ $netProfit >= 0 ? 'text-indigo' : 'text-danger' }}">
                        ₺{{ number_format($netProfit, 2, ',', '.') }}
                    </h3>
                </div>
                <div class="bg-indigo bg-opacity-10 text-indigo rounded-3 p-3" style="color: #4f46e5; background-color: rgba(79, 70, 229, 0.1);">
                    <i class="ti ti-wallet fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Income vs Expense Trend Chart -->
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark mb-0">Gelir & Gider Karşılaştırma Grafiği</h5>
            <p class="text-muted small mb-0">Seçilen dönem boyunca gerçekleşen nakit giriş ve çıkış dalgalanması.</p>
        </div>
    </div>
    <div class="card-body p-4">
        <div id="financialTrendChart" style="min-height: 350px;"></div>
    </div>
</div>

<!-- Pie Charts Row -->
<div class="row g-4 mb-4">
    <!-- Income by Payment Method -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold text-dark mb-0">Gelir Yöntem Dağılımı</h5>
                <p class="text-muted small mb-0">Kazanılan paranın hangi ödeme kanallarıyla tahsil edildiği.</p>
            </div>
            <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                @if(count($incomeByMethod) > 0)
                    <div id="incomeMethodChart" class="w-100" style="min-height: 280px;"></div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="ti ti-mood-empty fs-1 mb-2 d-block"></i> Veri bulunmuyor.
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Expense by Category -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4 h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold text-dark mb-0">Gider Kategori Dağılımı</h5>
                <p class="text-muted small mb-0">Harcamaların hangi operasyonel kategorilere bölündüğü.</p>
            </div>
            <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                @if(count($expenseByCategory) > 0)
                    <div id="expenseCategoryChart" class="w-100" style="min-height: 280px;"></div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="ti ti-mood-empty fs-1 mb-2 d-block"></i> Veri bulunmuyor.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Detailed Data Tables inside Tabs -->
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
        <ul class="nav nav-tabs border-bottom-0" id="financeTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-uppercase fs-7 pb-3 border-0 border-bottom border-2" id="incomes-tab" data-bs-toggle="tab" data-bs-target="#incomesPanel" type="button" role="tab" aria-controls="incomesPanel" aria-selected="true">
                    Gelir İşlemleri Listesi ({{ $incomes->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-uppercase fs-7 pb-3 border-0 border-bottom border-2 ms-3 text-secondary" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expensesPanel" type="button" role="tab" aria-controls="expensesPanel" aria-selected="false">
                    Gider İşlemleri Listesi ({{ $expenses->count() }})
                </button>
            </li>
        </ul>
    </div>
    
    <div class="card-body p-0">
        <div class="tab-content" id="financeTabContent">
            <!-- Incomes Tab -->
            <div class="tab-pane fade show active" id="incomesPanel" role="tabpanel" aria-labelledby="incomes-tab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Tarih</th>
                                <th>Açıklama</th>
                                <th>Ödeme Yöntemi</th>
                                <th class="text-end pe-4">Tutar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incomes as $inc)
                            <tr>
                                <td class="ps-4 text-secondary small">{{ \Carbon\Carbon::parse($inc->transaction_date)->format('d.m.Y H:i') }}</td>
                                <td class="fw-medium text-dark">{{ $inc->description ?? 'Randevu Geliri' }}</td>
                                <td>
                                    <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill">
                                        <i class="ti {{ $inc->payment_method ? $inc->payment_method->icon() : 'ti-credit-card' }} me-1"></i> 
                                        {{ $inc->payment_method ? $inc->payment_method->label() : 'Nakit' }}
                                    </span>
                                </td>
                                <td class="text-end text-success fw-bold pe-4">+₺{{ number_format($inc->amount, 2, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-5 text-muted"><i class="ti ti-mood-empty fs-1 d-block mb-1"></i>Kayıt bulunamadı.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Expenses Tab -->
            <div class="tab-pane fade" id="expensesPanel" role="tabpanel" aria-labelledby="expenses-tab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Tarih</th>
                                <th>Açıklama</th>
                                <th>Kategori</th>
                                <th>Ekleyen</th>
                                <th class="text-end pe-4">Tutar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $exp)
                            <tr>
                                <td class="ps-4 text-secondary small">{{ \Carbon\Carbon::parse($exp->expense_date)->format('d.m.Y') }}</td>
                                <td class="fw-medium text-dark">{{ $exp->description ?? 'Belirtilmemiş' }}</td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger px-2.5 py-1 rounded-pill">
                                        {{ $exp->category->name ?? 'Diğer' }}
                                    </span>
                                </td>
                                <td class="text-secondary small">{{ $exp->createdBy->full_name ?? 'Bilinmiyor' }}</td>
                                <td class="text-end text-danger fw-bold pe-4">-₺{{ number_format($exp->amount, 2, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted"><i class="ti ti-mood-empty fs-1 d-block mb-1"></i>Kayıt bulunamadı.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
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

        // Adjust Tab Styling on switch
        const tabElList = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabElList.forEach(tabEl => {
            tabEl.addEventListener('show.bs.tab', (event) => {
                event.target.classList.remove('text-secondary');
                event.relatedTarget.classList.add('text-secondary');
            });
        });

        // 1. Trend Chart
        const trendOptions = {
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false },
                fontFamily: 'Outfit, system-ui, -apple-system, sans-serif'
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            colors: ['#10b981', '#ef4444'],
            series: [
                { name: 'Gelir', data: @json($chartIncomeData) },
                { name: 'Gider', data: @json($chartExpenseData) }
            ],
            xaxis: {
                categories: @json($chartTimeline),
                labels: { style: { colors: '#94a3b8' } }
            },
            yaxis: {
                labels: {
                    style: { colors: '#94a3b8' },
                    formatter: (val) => '₺' + val.toLocaleString('tr-TR')
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.3,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            tooltip: { y: { formatter: (val) => '₺' + val.toLocaleString('tr-TR') } }
        };
        const trendChart = new ApexCharts(document.querySelector("#financialTrendChart"), trendOptions);
        trendChart.render();

        // 2. Income Method Chart
        @if(count($incomeByMethod) > 0)
            const incomeMethodOptions = {
                chart: { type: 'donut', height: 280, fontFamily: 'Outfit, system-ui, -apple-system, sans-serif' },
                labels: {!! json_encode(array_column($incomeByMethod, 'label')) !!},
                series: {!! json_encode(array_column($incomeByMethod, 'total')) !!},
                colors: ['#10b981', '#6366f1', '#06b6d4', '#f59e0b'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + '%' },
                tooltip: { y: { formatter: (val) => '₺' + val.toLocaleString('tr-TR') } }
            };
            const methodChart = new ApexCharts(document.querySelector("#incomeMethodChart"), incomeMethodOptions);
            methodChart.render();
        @endif

        // 3. Expense Category Chart
        @if(count($expenseByCategory) > 0)
            const expenseCategoryOptions = {
                chart: { type: 'donut', height: 280, fontFamily: 'Outfit, system-ui, -apple-system, sans-serif' },
                labels: {!! json_encode(array_column($expenseByCategory, 'label')) !!},
                series: {!! json_encode(array_column($expenseByCategory, 'total')) !!},
                colors: ['#ef4444', '#ec4899', '#f59e0b', '#8b5cf6', '#3b82f6'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + '%' },
                tooltip: { y: { formatter: (val) => '₺' + val.toLocaleString('tr-TR') } }
            };
            const categoryChart = new ApexCharts(document.querySelector("#expenseCategoryChart"), expenseCategoryOptions);
            categoryChart.render();
        @endif
    });
</script>
@endpush
@endsection
