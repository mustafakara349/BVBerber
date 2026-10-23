@extends('layouts.app')

@section('title', 'Dashboard - B&V Barber')

@section('content')
    <div class="row align-items-center mb-4 db-animate-fade-up">
        <div class="col-12 col-md-7">
            <h1 class="h3 fw-bold text-dark mb-1">Dashboard</h1>
            <p class="text-secondary mb-0">Merhaba {{ auth()->user()->first_name }}, işte bugünkü özet.</p>
        </div>
        <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0">
            <div class="d-inline-flex align-items-center gap-2 bg-white px-3 py-2 rounded-pill border border-light-subtle shadow-sm small text-secondary">
                <i class="ti ti-calendar text-primary"></i>
                <span class="fw-medium">{{ now()->translatedFormat('d F Y, l') }}</span>
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
    <div class="row g-3 mb-4 db-stagger-grid db-stagger-delay-2">
        <div class="col-lg-4 col-12">
            <div class="db-finance-card h-100">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <span class="db-finance-label d-block mb-1">Aylık Net Kâr</span>
                        <div class="db-finance-value" id="stat_monthly_profit">
                            @if(($widgets['financial']['profit'] ?? 0) < 0)
                                -₺{{ number_format(abs($widgets['financial']['profit']), 2, ',', '.') }}
                            @else
                                ₺{{ number_format($widgets['financial']['profit'] ?? 0, 2, ',', '.') }}
                            @endif
                        </div>
                    </div>
                    <div class="db-finance-icon bg-primary-subtle text-primary">
                        <i class="ti ti-layers-subtract"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-end align-items-center pt-2">
                    <a href="{{ route('finance.transactions') }}" class="db-finance-btn">
                        Detay <i class="ti ti-arrow-right fs-7"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="db-finance-card h-100">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <span class="db-finance-label d-block mb-1">Aylık Toplam Gelir</span>
                        <div class="db-finance-value text-success" id="stat_monthly_income">₺{{ number_format($widgets['financial']['income'] ?? 0, 2, ',', '.') }}</div>
                    </div>
                    <div class="db-finance-icon bg-success-subtle text-success">
                        <i class="ti ti-trending-up"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-end align-items-center pt-2">
                    <a href="{{ route('finance.transactions') }}" class="db-finance-btn">
                        Detay <i class="ti ti-arrow-right fs-7"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="db-finance-card h-100">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <span class="db-finance-label d-block mb-1">Aylık Toplam Gider</span>
                        <div class="db-finance-value text-danger" id="stat_monthly_expense">₺{{ number_format($widgets['financial']['expense'] ?? 0, 2, ',', '.') }}</div>
                    </div>
                    <div class="db-finance-icon bg-danger-subtle text-danger">
                        <i class="ti ti-trending-down"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-end align-items-center pt-2">
                    <a href="{{ route('finance.expenses') }}" class="db-finance-btn">
                        Detay <i class="ti ti-arrow-right fs-7"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-4 db-stagger-grid db-stagger-delay-3">
        <div class="col-12 col-lg-8">
            <div class="db-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 bg-primary-subtle text-primary rounded-3 d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-chart-bar fs-5"></i>
                        </span>
                        <div>
                            <h3 class="h5 mb-0 fw-bold text-dark">Gelir Grafiği</h3>
                            <small class="text-secondary">Dönemsel ciro ve kazanç analizi</small>
                        </div>
                    </div>
                    <div>
                        <div class="db-period-pills" role="group">
                            <button type="button" class="btn btn-period" data-period="day">Bugün</button>
                            <button type="button" class="btn btn-period active" data-period="month">Bu Ay</button>
                            <button type="button" class="btn btn-period" data-period="year">Bu Yıl</button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div id="revenueChart"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="db-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 bg-primary-subtle text-primary rounded-3 d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-users-group fs-5"></i>
                        </span>
                        <div>
                            <h3 class="h5 mb-0 fw-bold text-dark">Müşteri Dağılımı</h3>
                            <small class="text-secondary">Cinsiyet ve kitle profili</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="customer-distribution-container">
                        <!-- Modern Horizontal Stacked Bar -->
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
                                <div class="db-distribution-card h-100">
                                    <span class="text-secondary small d-block mb-1">Toplam</span>
                                    <span class="fs-5 fw-bold text-dark">{{ $widgets['customers']['total'] ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="db-distribution-card h-100">
                                    <span class="text-primary small d-block mb-1 fw-semibold">Erkek</span>
                                    <span class="fs-6 fw-bold text-primary">{{ $maleCount }} <span class="small fw-normal text-muted" style="font-size: 11px;">({{ round($malePct) }}%)</span></span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="db-distribution-card h-100">
                                    <span class="small d-block mb-1 fw-semibold" style="color: #ea580c !important;">Kadın</span>
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
    <div class="row g-3 mb-4 db-stagger-grid db-stagger-delay-4">
        {{-- Top Barbers --}}
        <div class="col-lg-4">
            <div class="db-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center px-4 py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-1.5 bg-warning-subtle text-warning-emphasis rounded-3 d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-award fs-5"></i>
                        </span>
                        <h4 class="mb-0 h5 fw-bold text-dark">En İyi Berberler</h4>
                    </div>
                    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold">Tümü</a>
                </div>
                <ul class="list-group list-group-flush db-list-group">
                    @forelse($barbers as $barber)
                        <li class="list-group-item d-flex align-items-center gap-3">
                            @if($barber['photo'])
                                <img src="{{ $barber['photo'] }}" class="avatar avatar-sm rounded-circle object-fit-cover shadow-sm" style="width: 38px; height: 38px;" alt="{{ $barber['name'] }}">
                            @else
                                <div class="avatar avatar-sm rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                    <span>{{ substr($barber['name'], 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <p class="mb-0 fw-semibold text-dark">{{ $barber['name'] }}</p>
                                <div class="d-flex align-items-center gap-2 text-muted">
                                    <small class="fw-bold text-dark">₺{{ number_format($barber['revenue'], 0, ',', '.') }}</small>
                                    <small>•</small>
                                    <small>{{ $barber['completed_appointments'] }} randevu</small>
                                </div>
                            </div>
                            <span class="badge bg-warning-subtle text-warning-emphasis px-2.5 py-1 rounded-pill fw-bold border border-warning border-opacity-25 shadow-sm">
                                <i class="ti ti-star-filled me-1 text-warning"></i>{{ $barber['rating'] }}
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
            <div class="db-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center px-4 py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-1.5 bg-primary-subtle text-primary rounded-3 d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-scissors fs-5"></i>
                        </span>
                        <h4 class="mb-0 h5 fw-bold text-dark" style="font-size: 1.05rem !important;">Popüler Hizmetler</h4>
                    </div>
                    <select class="form-select form-select-sm w-auto rounded-pill" id="topServicesStatsPeriod">
                        <option value="day">Günlük</option>
                        <option value="month" selected>Aylık</option>
                        <option value="year">Yıllık</option>
                    </select>
                </div>
                <ul class="list-group list-group-flush db-list-group" id="topServicesList">
                    @forelse($topServices as $service)
                        <li class="list-group-item d-flex align-items-center gap-3">
                            <div class="avatar avatar-sm rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; font-size: 1.1rem;">
                                <i class="ti ti-cut"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-0 fw-semibold text-dark">{{ $service['name'] }}</p>
                                <div class="d-flex align-items-center gap-2 text-muted">
                                    <small class="fw-bold text-dark">₺{{ number_format($service['total_revenue'], 0, ',', '.') }}</small>
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
            <div class="db-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center px-4 py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-1.5 bg-info-subtle text-info rounded-3 d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-chart-pie fs-5"></i>
                        </span>
                        <h4 class="mb-0 h5 fw-bold text-dark">Randevu Özeti</h4>
                    </div>
                    <select class="form-select form-select-sm w-auto rounded-pill" id="appointmentStatsPeriod">
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
                            <input type="date" id="statsStartDate" class="form-control form-control-sm rounded-pill">
                        </div>
                        <div class="col-5">
                            <label class="form-label small text-secondary mb-1 fw-semibold">Bitiş</label>
                            <input type="date" id="statsEndDate" class="form-control form-control-sm rounded-pill">
                        </div>
                        <div class="col-2">
                            <button type="button" id="btnApplyStatsFilter" class="btn btn-sm btn-primary w-100 rounded-pill" style="padding: 0.35rem 0;" title="Filtrele">
                                <i class="ti ti-filter"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <ul class="list-group list-group-flush db-list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-secondary fw-medium">Toplam Randevu</span>
                        <a href="{{ route('appointments.index') }}" class="fw-bold text-dark text-decoration-none fs-6" id="stat_total">{{ $widgets['appointments']['month_total'] ?? 0 }}</a>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-secondary fw-medium">İptal Edilen</span>
                        <a href="{{ route('appointments.index', ['status' => 'cancelled']) }}" class="fw-bold text-danger text-decoration-none fs-6" id="stat_cancelled">{{ $widgets['appointments']['month_cancelled'] ?? 0 }}</a>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-secondary fw-medium">Gelmeyen</span>
                        <a href="{{ route('appointments.index', ['status' => 'no_show']) }}" class="fw-bold text-warning text-decoration-none fs-6" id="stat_no_show">{{ $widgets['appointments']['month_no_show'] ?? 0 }}</a>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-secondary fw-medium">İptal Oranı</span>
                        <span id="stat_cancellation_rate"
                            class="badge bg-{{ ($widgets['appointments']['cancellation_rate'] ?? 0) > 10 ? 'danger' : 'success' }}-subtle text-{{ ($widgets['appointments']['cancellation_rate'] ?? 0) > 10 ? 'danger' : 'success' }} px-2.5 py-1.5 rounded-pill fw-bold fs-7">
                            %{{ $widgets['appointments']['cancellation_rate'] ?? 0 }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-secondary fw-medium">Kişi Başı Ortalama Harcama</span>
                        <span class="fw-bold text-dark fs-6" id="stat_avg_spending">₺{{ number_format($widgets['customers']['avg_spending'] ?? 0, 0, ',', '.') }}</span>
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
@endsection

@push('modals')
    <!-- Rejection Reason Modal -->
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg text-start rounded-4 overflow-hidden">
                <div class="modal-header bg-danger text-white border-0 py-3 px-4">
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
                            <label for="cancellation_reason_input" class="form-label fw-semibold text-dark">Reddetme Nedeni <span class="text-danger">*</span></label>
                            <textarea class="form-control border bg-light rounded-3" id="cancellation_reason_input" name="cancellation_reason" rows="3" required placeholder="Örn: Berber o saatte müsait değil, acil durum vb."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-3 fw-semibold text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">Reddet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Approve Modal -->
    <div class="modal fade" id="bulkApproveModal" tabindex="-1" aria-labelledby="bulkApproveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 12px;">
                <div class="modal-header bg-success text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="bulkApproveModalLabel"><i class="ti ti-checks me-1"></i> Tüm Randevuları Onayla</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <form id="bulkApproveForm" method="POST" action="{{ route('appointments.bulk-status') }}">
                    @csrf
                    <input type="hidden" name="status" value="confirmed">
                    <input type="hidden" name="appointment_ids" id="bulkApproveIds" value="{{ isset($pendingAppointments) ? $pendingAppointments->pluck('id')->join(',') : '' }}">
                    <div class="modal-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-success-subtle text-success p-3 d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="ti ti-checks fs-2"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Toplu Onaylama İşlemi</h6>
                                <p class="text-secondary small mb-0">Onay bekleyen tüm randevular sisteme onaylı olarak kaydedilecektir.</p>
                            </div>
                        </div>
                        <div class="alert alert-light border small text-muted mb-0">
                            Onay bekleyen toplam <strong class="text-dark" id="bulkApproveCountBadge">{{ isset($pendingAppointments) ? $pendingAppointments->count() : 0 }}</strong> adet randevuyu tek seferde onaylamak istediğinize emin misiniz?
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3">
                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Vazgeç</button>
                        <button type="submit" class="btn btn-success px-4 fw-bold" id="btnBulkApproveSubmit">
                            <i class="ti ti-check me-1"></i> Evet, Tümünü Onayla
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Reject Modal -->
    <div class="modal fade" id="bulkRejectModal" tabindex="-1" aria-labelledby="bulkRejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 12px;">
                <div class="modal-header bg-danger text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="bulkRejectModalLabel"><i class="ti ti-x me-1"></i> Tüm Randevuları Reddet</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <form id="bulkRejectForm" method="POST" action="{{ route('appointments.bulk-status') }}">
                    @csrf
                    <input type="hidden" name="status" value="rejected">
                    <input type="hidden" name="appointment_ids" id="bulkRejectIds" value="{{ isset($pendingAppointments) ? $pendingAppointments->pluck('id')->join(',') : '' }}">
                    <div class="modal-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-danger-subtle text-danger p-3 d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="ti ti-alert-triangle fs-2"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-danger mb-1">Toplu İptal / Reddetme</h6>
                                <p class="text-secondary small mb-0">Onay bekleyen tüm randevular iptal edilecektir.</p>
                            </div>
                        </div>
                        <p class="text-secondary small mb-3">
                            Onay bekleyen toplam <strong class="text-dark" id="bulkRejectCountBadge">{{ isset($pendingAppointments) ? $pendingAppointments->count() : 0 }}</strong> adet randevuyu reddetmek üzeresiniz. Lütfen müşterilere bildirilecek ortak reddetme nedenini yazınız.
                        </p>
                        <div class="mb-2">
                            <label for="bulk_cancellation_reason_input" class="form-label fw-semibold">Reddetme Nedeni <span class="text-danger">*</span></label>
                            <textarea class="form-control border bg-light" id="bulk_cancellation_reason_input" name="cancellation_reason" rows="3" required placeholder="Örn: Teknik arıza / Salon tadilatı nedeniyle randevular iptal edildi."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3">
                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Vazgeç</button>
                        <button type="submit" class="btn btn-danger px-4 fw-bold" id="btnBulkRejectSubmit">
                            <i class="ti ti-x me-1"></i> Tümünü Reddet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Complete with Payment Modal for Awaiting Action Appointments -->
    <div class="modal fade" id="dashboardCompletePaymentModal" tabindex="-1" aria-labelledby="dashboardCompletePaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered text-start">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-0 bg-success text-white py-3 px-4">
                    <h5 class="modal-title fw-bold" id="dashboardCompletePaymentModalLabel"><i class="ti ti-circle-check me-1"></i> Randevuyu Tamamla & Ödeme Kaydet</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <form id="dashboardCompletePaymentForm" action="" method="POST">
                    @csrf
                    <div class="modal-body p-4 text-dark">
                        <div class="alert alert-success border-0 p-3 rounded-3 mb-3" role="alert">
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Randevu Toplam Fiyatı:</span>
                                <span id="dashboardCompleteModalTotalPrice">₺0,00</span>
                            </div>
                            <div class="small text-success mt-1">
                                <span>Müşteri: <strong id="dashboardCompleteModalCustomerName">-</strong></span>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary">Ödenen Tutar (₺) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text border bg-light">₺</span>
                                <input type="number" step="0.01" min="0.01" name="amount" id="dashboardCompleteModalAmountInput" class="form-control border bg-light rounded-end-3" value="" required>
                            </div>
                        </div>

                        <!-- Paid At & Payment Method -->
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold text-secondary">Ödeme Yöntemi <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select border bg-light" required>
                                    <option value="cash" selected>Nakit (Kasa)</option>
                                    <option value="credit_card">Kredi Kartı</option>
                                    <option value="bank_transfer">Banka Transferi</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold text-secondary">Ödeme Tarihi <span class="text-danger">*</span></label>
                                <input type="date" name="paid_at" id="dashboardCompleteModalPaidAt" class="form-control border bg-light" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Reference Number -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary">Referans / İşlem Kodu (İsteğe Bağlı)</label>
                            <input type="text" name="transaction_reference" id="dashboardCompleteModalRef" class="form-control border bg-light" placeholder="Havale referansı, fiş numarası vb...">
                        </div>

                        <!-- Completion note -->
                        <div class="mb-2">
                            <label class="form-label fw-semibold text-secondary">İşlem / Randevu Notu (İsteğe Bağlı)</label>
                            <textarea name="note" id="dashboardCompleteModalNote" rows="2" class="form-control border bg-light" placeholder="Randevu tamamlanmasına dair not ekleyin..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">Tamamla ve Ödeme Al</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script nonce="{{ $cspNonce }}">
        document.addEventListener('DOMContentLoaded', () => {
            const chartDataAll = @json($revenueChart);

            // Reject Modal Trigger Handler (Event Delegation for live elements)
            document.addEventListener('click', function(e) {
                // Complete with Payment Modal Trigger Handler (Event Delegation)
                const completeBtn = e.target.closest('.btn-complete-payment-trigger');
                if (completeBtn) {
                    const actionUrl = completeBtn.getAttribute('data-action');
                    const price = completeBtn.getAttribute('data-price');
                    const priceFormatted = completeBtn.getAttribute('data-price-formatted');
                    const customer = completeBtn.getAttribute('data-customer');

                    const form = document.getElementById('dashboardCompletePaymentForm');
                    if (form && actionUrl) form.action = actionUrl;

                    const priceEl = document.getElementById('dashboardCompleteModalTotalPrice');
                    if (priceEl && priceFormatted) priceEl.textContent = priceFormatted;

                    const customerEl = document.getElementById('dashboardCompleteModalCustomerName');
                    if (customerEl && customer) customerEl.textContent = customer;

                    const amountInput = document.getElementById('dashboardCompleteModalAmountInput');
                    if (amountInput && price) amountInput.value = price;

                    const refInput = document.getElementById('dashboardCompleteModalRef');
                    if (refInput) refInput.value = '';

                    const noteInput = document.getElementById('dashboardCompleteModalNote');
                    if (noteInput) noteInput.value = '';

                    const paidAtInput = document.getElementById('dashboardCompleteModalPaidAt');
                    if (paidAtInput) {
                        const today = new Date().toISOString().split('T')[0];
                        paidAtInput.value = today;
                    }
                }

                const btn = e.target.closest('.btn-reject-trigger');
                if (btn) {
                    const url = btn.getAttribute('data-url');
                    const form = document.getElementById('rejectReasonForm');
                    if (form && url) {
                        form.action = url;
                    }
                    const textarea = document.getElementById('cancellation_reason_input');
                    if (textarea) textarea.value = '';
                }

                // Bulk Trigger Handlers (Event Delegation)
                const approveBtn = e.target.closest('.btn-bulk-approve');
                if (approveBtn) {
                    const count = approveBtn.getAttribute('data-count');
                    const ids = approveBtn.getAttribute('data-ids');
                    const countEl = document.getElementById('bulkApproveCountBadge');
                    const idsInput = document.getElementById('bulkApproveIds');
                    if (countEl && count) countEl.textContent = count;
                    if (idsInput && ids) idsInput.value = ids;
                }

                const rejectBtn = e.target.closest('.btn-bulk-reject');
                if (rejectBtn) {
                    const count = rejectBtn.getAttribute('data-count');
                    const ids = rejectBtn.getAttribute('data-ids');
                    const countEl = document.getElementById('bulkRejectCountBadge');
                    const idsInput = document.getElementById('bulkRejectIds');
                    const textarea = document.getElementById('bulk_cancellation_reason_input');
                    if (countEl && count) countEl.textContent = count;
                    if (idsInput && ids) idsInput.value = ids;
                    if (textarea) textarea.value = '';
                }
            });

            // Prevent double submits on bulk forms
            const bulkApproveForm = document.getElementById('bulkApproveForm');
            if (bulkApproveForm) {
                bulkApproveForm.addEventListener('submit', function() {
                    const btn = document.getElementById('btnBulkApproveSubmit');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Onaylanıyor...';
                    }
                });
            }

            const bulkRejectForm = document.getElementById('bulkRejectForm');
            if (bulkRejectForm) {
                bulkRejectForm.addEventListener('submit', function() {
                    const btn = document.getElementById('btnBulkRejectSubmit');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Reddediliyor...';
                    }
                });
            }

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

            function escapeHtml(str) {
                if (str === null || str === undefined) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

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
                                    <div class="avatar avatar-sm rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; font-size: 1.1rem;">
                                        <i class="ti ti-cut"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold text-dark">${escapeHtml(service.name)}</p>
                                        <div class="d-flex align-items-center gap-2 text-muted">
                                            <small class="fw-bold text-dark">₺${formattedRevenue}</small>
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
                    if (data.widgets_data) {
                        const elTotal = document.getElementById('widgetTodayAppointmentsTotal');
                        if (elTotal) elTotal.textContent = data.widgets_data.today_total;
                        const elComp = document.getElementById('widgetTodayAppointmentsCompleted');
                        if (elComp) elComp.textContent = data.widgets_data.today_completed;
                        const elRev = document.getElementById('widgetDailyRevenue');
                        if (elRev) elRev.textContent = '₺' + data.widgets_data.daily_revenue;
                        const elExp = document.getElementById('widgetDailyExpense');
                        if (elExp) elExp.textContent = '₺' + data.widgets_data.daily_expense;
                        const elCust = document.getElementById('widgetNewCustomers');
                        if (elCust) elCust.textContent = data.widgets_data.new_customers;

                        // Aylık Finansal Kartlar
                        const elProfit = document.getElementById('stat_monthly_profit');
                        if (elProfit && data.widgets_data.monthly_profit !== undefined) {
                            const val = data.widgets_data.monthly_profit;
                            elProfit.textContent = val.startsWith('-') ? '-₺' + val.substring(1) : '₺' + val;
                        }
                        const elIncome = document.getElementById('stat_monthly_income');
                        if (elIncome && data.widgets_data.monthly_income !== undefined) {
                            elIncome.textContent = '₺' + data.widgets_data.monthly_income;
                        }
                        const elExpense = document.getElementById('stat_monthly_expense');
                        if (elExpense && data.widgets_data.monthly_expense !== undefined) {
                            elExpense.textContent = '₺' + data.widgets_data.monthly_expense;
                        }
                    } else {
                        const widgetsEl = document.getElementById('revenueWidgetsSection');
                        if (widgetsEl && typeof data.widgets_html === 'string') {
                            widgetsEl.innerHTML = data.widgets_html;
                        }
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
            // Sayfa ilk açılışındaki fetchFeed olayında veriler zaten sunucudan taze geldiğinden tekrar çekilmez.
            document.addEventListener('admin-notifications:update', function (e) {
                if (e.detail && e.detail.isNew) {
                    scheduleLiveRefresh();
                }
            });

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