@extends('layouts.app')

@section('title', 'Dashboard - B&V Barber')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="mb-6">
                <h1 class="fs-3 mb-1">Dashboard</h1>
                <p>Merhaba {{ auth()->user()->first_name }}, işte bugünkü özet.</p>
            </div>
        </div>
    </div>

    {{-- Pending Appointments Section (Live Container) --}}
    <div id="pendingAppointmentsSection">
        @include('dashboard.partials.pending_appointments')
    </div>

    {{-- Revenue Widget Cards (Live Container) --}}
    <div id="revenueWidgetsSection">
        @include('dashboard.partials.revenue_widgets')
    </div>

    {{-- Financial Summary Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-4 col-12">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between border-bottom pb-5 mb-3">
                        <div>
                            <h3 class="fw-bold h4">₺{{ number_format($widgets['financial']['profit'] ?? 0, 0, ',', '.') }}
                            </h3>
                            <span>Aylık Kâr</span>
                        </div>
                        <div><i class="ti ti-layers-subtract fs-1 text-primary"></i></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center small">
                        <div class="text-muted">Gelir - Gider</div>
                        <div><a href="{{ route('finance.transactions') }}"
                                class="link-primary text-decoration-underline">Detay</a></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between border-bottom pb-5 mb-3">
                        <div>
                            <h3 class="fw-bold h4">₺{{ number_format($widgets['financial']['income'] ?? 0, 0, ',', '.') }}
                            </h3>
                            <span>Aylık Gelir</span>
                        </div>
                        <div><i class="ti ti-trending-up fs-1 text-success"></i></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center small">
                        <div class="text-muted"><span class="text-success">Toplam gelir</span> bu ay</div>
                        <div><a href="{{ route('finance.transactions') }}"
                                class="link-primary text-decoration-underline">Detay</a></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between border-bottom pb-5 mb-3">
                        <div>
                            <h3 class="fw-bold h4">₺{{ number_format($widgets['financial']['expense'] ?? 0, 0, ',', '.') }}
                            </h3>
                            <span>Aylık Gider</span>
                        </div>
                        <div><i class="ti ti-trending-down fs-1 text-danger"></i></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center small">
                        <div class="text-muted"><span class="text-danger">Toplam gider</span> bu ay</div>
                        <div><a href="{{ route('finance.expenses') }}"
                                class="link-primary text-decoration-underline">Detay</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent px-4 py-3">
                    <h3 class="h5 mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="ti ti-chart-bar text-primary fs-4"></i> Gelir Grafiği
                    </h3>
                    <div>
                        <div class="btn-group p-1 bg-light rounded-pill border border-light" role="group">
                            <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold border-0 btn-period" data-period="day" style="font-size: 12px; transition: all 0.2s ease;">Bugün</button>
                            <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold border-0 btn-period active bg-white text-dark shadow-sm" data-period="month" style="font-size: 12px; transition: all 0.2s ease;">Bu Ay</button>
                            <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold border-0 btn-period" data-period="year" style="font-size: 12px; transition: all 0.2s ease;">Bu Yıl</button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div id="revenueChart"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent px-4 py-3">
                    <h3 class="h5 mb-0">Müşteri Dağılımı</h3>
                </div>
                <div class="card-body p-4">
                    <div class="customer-distribution-container">
                        <!-- Beautiful Modern Horizontal Stacked Bar -->
                        <div class="gender-bar-wrapper position-relative overflow-hidden rounded-pill bg-light d-flex" style="height: 38px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);">
                            @php
                                $maleCount = $widgets['customers']['genders']['male'] ?? 0;
                                $femaleCount = $widgets['customers']['genders']['female'] ?? 0;
                                $otherCount = $widgets['customers']['genders']['other'] ?? 0;
                                $totalGenders = $maleCount + $femaleCount + $otherCount;
                                
                                $malePct = $totalGenders > 0 ? ($maleCount / $totalGenders) * 100 : 50;
                                $femalePct = $totalGenders > 0 ? ($femaleCount / $totalGenders) * 100 : 50;
                                $otherPct = $totalGenders > 0 ? ($otherCount / $totalGenders) * 100 : 0;
                            @endphp
                            
                            @if($maleCount > 0 || $totalGenders == 0)
                                <div class="gender-segment male-segment d-flex align-items-center justify-content-center text-white fw-bold transition-all" 
                                     style="width: {{ $malePct }}%; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); transition: width 0.6s ease; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                    <span class="d-flex align-items-center gap-1">
                                        <i class="ti ti-man fs-5"></i>
                                        {{ $maleCount }}
                                    </span>
                                </div>
                            @endif
                            
                            @if($otherCount > 0)
                                <div class="gender-segment other-segment d-flex align-items-center justify-content-center text-white fw-bold transition-all" 
                                     style="width: {{ $otherPct }}%; background: linear-gradient(135deg, #10b981 0%, #047857 100%); transition: width 0.6s ease; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                    <span class="d-flex align-items-center gap-1">
                                        {{ $otherCount }}
                                    </span>
                                </div>
                            @endif

                            @if($femaleCount > 0 || $totalGenders == 0)
                                <div class="gender-segment female-segment d-flex align-items-center justify-content-center text-white fw-bold transition-all" 
                                     style="width: {{ $femalePct }}%; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); transition: width 0.6s ease; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                    <span class="d-flex align-items-center gap-1">
                                        <i class="ti ti-woman fs-5"></i>
                                        {{ $femaleCount }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Modern Info Cards Below the Bar -->
                        <div class="row g-2 mt-4">
                            <div class="col-4">
                                <div class="p-3 rounded-3 bg-light border border-white text-center h-100" style="box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                                    <span class="text-secondary small d-block mb-1">Toplam</span>
                                    <span class="fs-5 fw-bold text-dark">{{ $widgets['customers']['total'] ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3 bg-light border border-white text-center h-100" style="box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                                    <span class="text-primary small d-block mb-1">Erkek</span>
                                    <span class="fs-6 fw-bold text-primary">{{ $maleCount }} <span class="small fw-normal text-muted" style="font-size: 11px;">({{ round($malePct) }}%)</span></span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3 bg-light border border-white text-center h-100" style="box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                                    <span class="text-warning small d-block mb-1" style="color: #ea580c !important;">Kadın</span>
                                    <span class="fs-6 fw-bold" style="color: #ea580c;">{{ $femaleCount }} <span class="small fw-normal text-muted" style="font-size: 11px;">({{ round($femalePct) }}%)</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="row g-3">
        {{-- Top Barbers --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center px-4 py-3">
                    <h4 class="mb-0 h5">En İyi Berberler</h4>
                    <a href="{{ route('employees.index') }}" class="small text-primary text-decoration-underline">Tümü</a>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse($barbers as $barber)
                        <li class="list-group-item d-flex align-items-center gap-3">
                            @if($barber['photo'])
                                <img src="{{ $barber['photo'] }}" class="avatar avatar-sm rounded-circle" alt="">
                            @else
                                <div class="avatar avatar-sm rounded-circle avatar-primary">
                                    <span class="avatar-initials">{{ substr($barber['name'], 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <p class="mb-1">{{ $barber['name'] }}</p>
                                <div class="d-flex align-items-center gap-2 text-muted">
                                    <small class="fw-semibold">₺{{ number_format($barber['revenue'], 0, ',', '.') }}</small>
                                    <small>•</small>
                                    <small>{{ $barber['completed_appointments'] }} randevu</small>
                                </div>
                            </div>
                            <span class="badge bg-warning-subtle text-warning border border-warning">
                                <i class="ti ti-star-filled me-1"></i>{{ $barber['rating'] }}
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-4">Henüz veri yok</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Top Services --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center px-4 py-3">
                    <h4 class="mb-0 h5" style="font-size: 1.1rem !important;">En Çok Tercih Edilen Hizmetler</h4>
                    <select class="form-select form-select-sm w-auto" id="topServicesStatsPeriod">
                        <option value="day">Günlük</option>
                        <option value="month" selected>Aylık</option>
                        <option value="year">Yıllık</option>
                    </select>
                </div>
                <ul class="list-group list-group-flush" id="topServicesList">
                    @forelse($topServices as $service)
                        <li class="list-group-item d-flex align-items-center gap-3">
                            <div class="icon-shape icon-sm bg-primary bg-opacity-10 text-primary rounded-2">
                                <i class="ti ti-cut"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1">{{ $service['name'] }}</p>
                                <div class="d-flex align-items-center gap-2 text-muted">
                                    <small
                                        class="fw-semibold">₺{{ number_format($service['total_revenue'], 0, ',', '.') }}</small>
                                    <small>•</small>
                                    <small>{{ $service['usage_count'] }} kullanım</small>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-4">Henüz veri yok</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Appointment Stats --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center px-4 py-3">
                    <h4 class="mb-0 h5">Randevu İstatistikleri</h4>
                    <select class="form-select form-select-sm w-auto" id="appointmentStatsPeriod">
                        <option value="today">Bugün</option>
                        <option value="month" selected>Bu Ay</option>
                        <option value="year">Bu Yıl</option>
                        <option value="custom">Özel Tarih</option>
                    </select>
                </div>
                <div id="customDateInputs" class="d-none border-bottom p-3 bg-light">
                    <div class="row g-2 align-items-end">
                        <div class="col-5">
                            <label class="form-label small text-secondary mb-1 fw-semibold">Başlangıç</label>
                            <input type="date" id="statsStartDate" class="form-control form-control-sm rounded-2">
                        </div>
                        <div class="col-5">
                            <label class="form-label small text-secondary mb-1 fw-semibold">Bitiş</label>
                            <input type="date" id="statsEndDate" class="form-control form-control-sm rounded-2">
                        </div>
                        <div class="col-2">
                            <button type="button" id="btnApplyStatsFilter" class="btn btn-sm btn-primary w-100 rounded-2" style="padding: 0.35rem 0;" title="Filtrele">
                                <i class="ti ti-filter"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Toplam Randevu</span>
                        <a href="{{ route('appointments.index') }}" class="fw-bold text-dark text-decoration-underline" id="stat_total">{{ $widgets['appointments']['month_total'] ?? 0 }}</a>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>İptal Edilen</span>
                        <a href="{{ route('appointments.index', ['status' => 'cancelled']) }}" class="fw-bold text-danger text-decoration-underline" id="stat_cancelled">{{ $widgets['appointments']['month_cancelled'] ?? 0 }}</a>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Gelmeyen</span>
                        <a href="{{ route('appointments.index', ['status' => 'no_show']) }}" class="fw-bold text-warning text-decoration-underline" id="stat_no_show">{{ $widgets['appointments']['month_no_show'] ?? 0 }}</a>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>İptal Oranı</span>
                        <span id="stat_cancellation_rate"
                            class="badge bg-{{ ($widgets['appointments']['cancellation_rate'] ?? 0) > 10 ? 'danger' : 'success' }}-subtle text-{{ ($widgets['appointments']['cancellation_rate'] ?? 0) > 10 ? 'danger' : 'success' }}">
                            %{{ $widgets['appointments']['cancellation_rate'] ?? 0 }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Ort. Müşteri Harcaması</span>
                        <span class="fw-bold" id="stat_avg_spending">₺{{ number_format($widgets['customers']['avg_spending'] ?? 0, 0, ',', '.') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Awaiting Action (Expired) Appointments Row (Live Container) --}}
    <div id="awaitingActionSection">
        @include('dashboard.partials.awaiting_action_appointments')
    </div>


    {{-- Today's Appointments Row (Live Container) --}}
    <div id="todayAppointmentsSection">
        @include('dashboard.partials.today_appointments')
    </div>

    <!-- Rejection Reason Modal -->
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 12px;">
                <div class="modal-header bg-danger text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="rejectReasonModalLabel"><i class="ti ti-x me-1"></i> Randevuyu Reddet</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <form id="rejectReasonForm" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="rejected">
                    <div class="modal-body p-4">
                        <p class="text-secondary small mb-3">Bu randevuyu reddetmek istediğinize emin misiniz? Lütfen reddetme nedenini aşağıya yazınız. Bu işlem geri alınamaz.</p>
                        <div class="mb-3">
                            <label for="cancellation_reason_input" class="form-label fw-semibold">Reddetme Nedeni <span class="text-danger">*</span></label>
                            <textarea class="form-control border bg-light" id="cancellation_reason_input" name="cancellation_reason" rows="3" required placeholder="Örn: Berber o saatte müsait değil, elektrik kesintisi vb."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3">
                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Vazgeç</button>
                        <button type="submit" class="btn btn-danger px-4 fw-bold">Reddet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chartDataAll = @json($revenueChart);

            // Reject Modal Trigger Handler (Event Delegation for live elements)
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-reject-trigger');
                if (!btn) return;
                const url = btn.getAttribute('data-url');
                const form = document.getElementById('rejectReasonForm');
                if (form && url) {
                    form.action = url;
                }
                const textarea = document.getElementById('cancellation_reason_input');
                if (textarea) textarea.value = '';
            });

            // Render Revenue Chart
            try {
                if (document.getElementById('revenueChart') && typeof ApexCharts !== 'undefined') {
                    let currentPeriod = 'month';

                    const options = {
                        chart: { 
                            type: 'area', 
                            height: 350, 
                            toolbar: { show: false },
                            fontFamily: 'Outfit, system-ui, -apple-system, sans-serif',
                            dropShadow: {
                                enabled: true,
                                color: '#E66239',
                                top: 12,
                                left: 0,
                                blur: 8,
                                opacity: 0.18
                            }
                        },
                        colors: ['#E66239'],
                        stroke: { width: 3.5, curve: 'smooth', lineCap: 'round' },
                        markers: { 
                            size: 0, 
                            strokeColors: '#fff',
                            strokeWidth: 2,
                            hover: { size: 6 } 
                        },
                        grid: {
                            borderColor: '#f1f5f9',
                            strokeDashArray: 4,
                            xaxis: { lines: { show: false } },
                            yaxis: { lines: { show: true } },
                            padding: { top: 0, right: 10, bottom: 0, left: 10 }
                        },
                        series: [{ name: 'Gelir', data: chartDataAll[currentPeriod].map(d => d.revenue) }],
                        fill: {
                            type: 'gradient',
                            gradient: { 
                                shadeIntensity: 1, 
                                opacityFrom: 0.35, 
                                opacityTo: 0.02, 
                                stops: [0, 90, 100] 
                            }
                        },
                        xaxis: { 
                            categories: chartDataAll[currentPeriod].map(d => d.label),
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: {
                                style: {
                                    colors: '#64748b',
                                    fontSize: '12px',
                                    fontWeight: 500
                                }
                            }
                        },
                        yaxis: {
                            tickAmount: 5,
                            labels: { 
                                style: {
                                    colors: '#64748b',
                                    fontSize: '12px',
                                    fontWeight: 500
                                },
                                formatter: (val) => '₺' + val.toLocaleString('tr-TR', { maximumFractionDigits: 0 }) 
                            }
                        },
                        tooltip: {
                            theme: 'dark',
                            y: { 
                                formatter: (val) => '₺' + val.toLocaleString('tr-TR', { maximumFractionDigits: 0 }) 
                            }
                        },
                    };
                    const chart = new ApexCharts(document.querySelector('#revenueChart'), options);
                    chart.render();

                    // Add click event listeners to custom period buttons
                    document.querySelectorAll('.btn-period').forEach(btn => {
                        btn.addEventListener('click', function() {
                            // Toggle active styling
                            document.querySelectorAll('.btn-period').forEach(b => {
                                b.classList.remove('active', 'bg-white', 'text-dark', 'shadow-sm');
                            });
                            this.classList.add('active', 'bg-white', 'text-dark', 'shadow-sm');

                            // Update chart period
                            currentPeriod = this.getAttribute('data-period');

                            // Update chart view
                            chart.updateOptions({
                                xaxis: { categories: chartDataAll[currentPeriod].map(d => d.label) }
                            });
                            chart.updateSeries([{
                                name: 'Gelir',
                                data: chartDataAll[currentPeriod].map(d => d.revenue)
                            }]);
                        });
                    });
                }
            } catch (e) {
                console.error("Revenue Chart render error:", e);
            }


            // Appointment Stats Filter and AJAX Fetch
            const appointmentPeriodSelect = document.getElementById('appointmentStatsPeriod');
            const customDateInputs = document.getElementById('customDateInputs');
            const startDateInput = document.getElementById('statsStartDate');
            const endDateInput = document.getElementById('statsEndDate');
            const btnApplyStatsFilter = document.getElementById('btnApplyStatsFilter');

            function fetchAppointmentStats(period, startDate = '', endDate = '') {
                const statIds = ['stat_total', 'stat_cancelled', 'stat_no_show', 'stat_cancellation_rate', 'stat_avg_spending'];
                statIds.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.classList.add('skeleton-shimmer');
                });

                fetch(`{{ route('dashboard.appointment-stats') }}?period=${period}&start_date=${startDate}&end_date=${endDate}`)
                    .then(response => response.json())
                    .then(data => {
                        statIds.forEach(id => {
                            const el = document.getElementById(id);
                            if (el) el.classList.remove('skeleton-shimmer');
                        });

                        document.getElementById('stat_total').textContent = data.total;
                        document.getElementById('stat_cancelled').textContent = data.cancelled;
                        document.getElementById('stat_no_show').textContent = data.no_show;
                        
                        const rateEl = document.getElementById('stat_cancellation_rate');
                        rateEl.textContent = '%' + data.cancellation_rate;
                        rateEl.className = `badge bg-${data.cancellation_rate > 10 ? 'danger' : 'success'}-subtle text-${data.cancellation_rate > 10 ? 'danger' : 'success'}`;
                        
                        document.getElementById('stat_avg_spending').textContent = '₺' + Number(data.avg_spending).toLocaleString('tr-TR', { maximumFractionDigits: 0 });
                    })
                    .catch(err => {
                        statIds.forEach(id => {
                            const el = document.getElementById(id);
                            if (el) el.classList.remove('skeleton-shimmer');
                        });
                        console.error("Error fetching stats:", err);
                    });
            }

            if (appointmentPeriodSelect) {
                appointmentPeriodSelect.addEventListener('change', function(e) {
                    const period = e.target.value;
                    if (period === 'custom') {
                        customDateInputs.classList.remove('d-none');
                    } else {
                        customDateInputs.classList.add('d-none');
                        fetchAppointmentStats(period);
                    }
                });

                if (btnApplyStatsFilter) {
                    btnApplyStatsFilter.addEventListener('click', function() {
                        if (startDateInput.value && endDateInput.value) {
                            fetchAppointmentStats('custom', startDateInput.value, endDateInput.value);
                        } else {
                            alert('Lütfen filtreleme için hem başlangıç hem de bitiş tarihini seçiniz.');
                        }
                    });
                }
            }

            // Top Services Filter and AJAX Fetch
            const topServicesPeriodSelect = document.getElementById('topServicesStatsPeriod');
            const topServicesList = document.getElementById('topServicesList');

            function fetchTopServices(period) {
                topServicesList.innerHTML = `
                    <li class="list-group-item d-flex align-items-center gap-3">
                        <div class="skeleton-icon sm"></div>
                        <div class="flex-grow-1">
                            <div class="skeleton-text sm w-75 mb-1"></div>
                            <div class="skeleton-text xs w-50 mb-0"></div>
                        </div>
                    </li>
                    <li class="list-group-item d-flex align-items-center gap-3">
                        <div class="skeleton-icon sm"></div>
                        <div class="flex-grow-1">
                            <div class="skeleton-text sm w-50 mb-1"></div>
                            <div class="skeleton-text xs w-25 mb-0"></div>
                        </div>
                    </li>
                    <li class="list-group-item d-flex align-items-center gap-3">
                        <div class="skeleton-icon sm"></div>
                        <div class="flex-grow-1">
                            <div class="skeleton-text sm w-60 mb-1"></div>
                            <div class="skeleton-text xs w-40 mb-0"></div>
                        </div>
                    </li>
                `;

                fetch(`{{ route('dashboard.top-services') }}?period=${period}`)
                    .then(response => response.json())
                    .then(data => {
                        topServicesList.innerHTML = '';
                        if (data.length === 0) {
                            topServicesList.innerHTML = '<li class="list-group-item text-center text-muted py-4">Henüz veri yok</li>';
                            return;
                        }

                        data.forEach(service => {
                            const formattedRevenue = Number(service.total_revenue).toLocaleString('tr-TR', { maximumFractionDigits: 0 });
                            const html = `
                                <li class="list-group-item d-flex align-items-center gap-3">
                                    <div class="icon-shape icon-sm bg-primary bg-opacity-10 text-primary rounded-2">
                                        <i class="ti ti-cut"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1">${service.name}</p>
                                        <div class="d-flex align-items-center gap-2 text-muted">
                                            <small class="fw-semibold">₺${formattedRevenue}</small>
                                            <small>•</small>
                                            <small>${service.usage_count} kullanım</small>
                                        </div>
                                    </div>
                                </li>
                            `;
                            topServicesList.insertAdjacentHTML('beforeend', html);
                        });
                    })
                    .catch(err => {
                        console.error("Error fetching top services:", err);
                    });
            }

            if (topServicesPeriodSelect) {
                topServicesPeriodSelect.addEventListener('change', function(e) {
                    fetchTopServices(e.target.value);
                });
            }

            // -------------------------------------------------------------
            // Dashboard Canlı Senkronizasyon (Live Real-time Sync)
            // -------------------------------------------------------------
            let isRefreshingLive = false;
            let refreshDebounceTimer = null;

            async function refreshDashboardLiveSections() {
                if (isRefreshingLive) return;
                // Kullanıcı herhangi bir modal açıkken DOM'un üzerine yazılmasını engelle
                if (document.querySelector('.modal.show')) return;
                isRefreshingLive = true;

                try {
                    const response = await fetch('{{ route('dashboard.live-sections') }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        cache: 'no-store'
                    });

                    if (!response.ok) return;
                    const data = await response.json();

                    // 1. Onay Bekleyen Randevular
                    const pendingEl = document.getElementById('pendingAppointmentsSection');
                    if (pendingEl && typeof data.pending_html === 'string') {
                        pendingEl.innerHTML = data.pending_html;
                    }

                    // 2. İşlem / Süresi Dolan Randevular
                    const awaitingEl = document.getElementById('awaitingActionSection');
                    if (awaitingEl && typeof data.awaiting_html === 'string') {
                        awaitingEl.innerHTML = data.awaiting_html;
                    }

                    // 3. Günün Randevuları
                    const todayEl = document.getElementById('todayAppointmentsSection');
                    if (todayEl && typeof data.today_html === 'string') {
                        todayEl.innerHTML = data.today_html;
                    }

                    // 4. Gelir & Sayaç Widget'ları
                    const widgetsEl = document.getElementById('revenueWidgetsSection');
                    if (widgetsEl && typeof data.widgets_html === 'string') {
                        widgetsEl.innerHTML = data.widgets_html;
                    }

                    // Dinamik eklenen içeriklerdeki Bootstrap popover'ları yeniden etkinleştir
                    const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
                    [...popovers].forEach(p => {
                        if (window.bootstrap && bootstrap.Popover) {
                            bootstrap.Popover.getOrCreateInstance(p);
                        }
                    });

                } catch (err) {
                    console.warn('[Dashboard] Canlı senkronizasyon hatası:', err);
                } finally {
                    isRefreshingLive = false;
                }
            }

            function scheduleLiveRefresh() {
                clearTimeout(refreshDebounceTimer);
                refreshDebounceTimer = setTimeout(refreshDashboardLiveSections, 200);
            }

            // A) Topbar'ın WebSocket'ten aldığı bildirim güncelleme olayını (CustomEvent) anında yakala
            document.addEventListener('admin-notifications:update', scheduleLiveRefresh);

            // B) Echo üzerinden personelin kendi kanalını doğrudan da dinle
            function setupDashboardEcho() {
                const userId = '{{ auth()->id() }}';
                if (window.Echo && userId) {
                    window.Echo.private('admin.notifications.' + userId)
                        .listen('.notification.created', scheduleLiveRefresh);
                }
            }
            if (window.Echo) setupDashboardEcho();
            else document.addEventListener('echo:ready', setupDashboardEcho, { once: true });
        });
    </script>
@endpush

@push('styles')
<style>
    @keyframes sn-row-highlight {
        0% { background-color: rgba(255, 193, 7, 0.4); }
        50% { background-color: rgba(255, 193, 7, 0.15); }
        100% { background-color: transparent; }
    }
    .dashboard-pending-row, .dashboard-awaiting-row, .dashboard-today-row {
        animation: sn-row-highlight 2.2s ease-out;
    }
</style>
@endpush