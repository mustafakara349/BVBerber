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

    {{-- Pending Appointments Row --}}
    @if($pendingAppointments->isNotEmpty())
        <div class="row g-3 mt-1 mb-3">
            <div class="col-12">
                <div class="card border-warning border-opacity-50 shadow-sm">
                    <div
                        class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center px-4 py-3 border-bottom border-warning border-opacity-25">
                        <h4 class="mb-0 h5 text-warning-emphasis"><i class="ti ti-clock-exclamation me-2"></i>Onay Bekleyen
                            Randevular</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 border-top-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Tarih / Saat
                                        </th>
                                        <th class="text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Müşteri</th>
                                        <th class="text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Berber</th>
                                        <th class="text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Hizmet</th>
                                        <th class="text-end pe-4 text-uppercase text-muted fs-7 fw-semibold py-3 border-0">İşlem
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">
                                    @foreach($pendingAppointments as $apt)
                                        <tr>
                                            <td class="ps-4">
                                                <div
                                                    class="d-inline-flex align-items-center justify-content-center px-3 py-2 rounded-2 bg-dark text-white fw-bold shadow-sm">
                                                    {{ $apt->start_at->format('d.m.Y') }} <i
                                                        class="ti ti-clock ms-2 me-1 opacity-75"></i>
                                                    {{ $apt->start_at->format('H:i') }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm rounded-circle bg-warning-subtle text-warning me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm"
                                                        style="width: 40px; height: 40px;">
                                                        {{ mb_substr($apt->customer->first_name ?? 'M', 0, 1) }}{{ mb_substr($apt->customer->last_name ?? 'M', 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="d-block fw-bold text-dark">{{ $apt->customer->full_name ?? 'Bilinmeyen Müşteri' }}</span>
                                                        <small class="text-muted"><i
                                                                class="ti ti-phone me-1"></i>{{ $apt->customer->phone ?? 'Belirtilmemiş' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="icon-shape icon-sm bg-primary-subtle text-primary rounded-circle me-2">
                                                        <i class="ti ti-cut fs-5"></i>
                                                    </div>
                                                    <span
                                                        class="fw-medium text-dark">{{ $apt->employee->user->full_name ?? '-' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1" style="max-width: 250px;">
                                                    @foreach($apt->appointmentServices as $aps)
                                                        <span class="badge bg-light text-secondary border px-2 py-1"><i
                                                                class="ti ti-check me-1"></i>{{ $aps->service->name }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group shadow-sm">
                                                    <form action="{{ route('appointments.update-status', $apt) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="confirmed">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Onayla"
                                                            style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                                            <i class="ti ti-check me-1"></i>Onayla
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger btn-reject-trigger" 
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#rejectReasonModal"
                                                            data-url="{{ route('appointments.update-status', $apt) }}"
                                                            title="Reddet"
                                                            style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                                        <i class="ti ti-x me-1"></i>Reddet
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Revenue Widget Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-3 col-12">
            <div class="card p-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-2">
                <div class="d-flex gap-3">
                    <div class="icon-shape icon-md bg-primary text-white rounded-2">
                        <i class="ti ti-calendar-event fs-4"></i>
                    </div>
                    <div>
                        <h2 class="mb-3 fs-6">Bugünkü Randevular</h2>
                        <h3 class="fw-bold mb-0">{{ $widgets['appointments']['today_total'] ?? 0 }}</h3>
                        <p class="text-primary mb-0 small">{{ $widgets['appointments']['today_completed'] ?? 0 }} tamamlandı
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
                        <h3 class="fw-bold mb-0">₺{{ number_format($widgets['revenue']['daily'] ?? 0, 0, ',', '.') }}</h3>
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
                        <h3 class="fw-bold mb-0">₺{{ number_format($widgets['revenue']['daily_expense'] ?? 0, 0, ',', '.') }}</h3>
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
                        <h3 class="fw-bold mb-0">{{ $widgets['customers']['new_this_month'] ?? 0 }}</h3>
                        <p class="text-warning mb-0 small">Bu ay</p>
                    </div>
                </div>
            </div>
        </div>
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
                    <h4 class="mb-0 h5">Popüler Hizmetler</h4>
                    <a href="{{ route('services.index') }}" class="small text-primary text-decoration-underline">Tümü</a>
                </div>
                <ul class="list-group list-group-flush">
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

    {{-- Awaiting Action (Expired) Appointments Row --}}
    @if(isset($awaitingActionAppointments) && $awaitingActionAppointments->count() > 0)
    <div class="row g-3 mt-1 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden border-start border-warning border-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-1 bg-warning bg-opacity-20 rounded-2 text-warning">
                            <i class="ti ti-alert-triangle fs-4"></i>
                        </span>
                        <h4 class="mb-0 h5 text-dark fw-bold">⚠️ İşlem Bekleyen Randevular (Süresi Dolanlar)</h4>
                    </div>
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold">
                        {{ $awaitingActionAppointments->count() }} Randevu Eylem Bekliyor
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-dark">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="ps-4 py-3 text-uppercase fs-7 fw-bold border-0">Saat / Tarih</th>
                                    <th class="py-3 text-uppercase fs-7 fw-bold border-0">Müşteri</th>
                                    <th class="py-3 text-uppercase fs-7 fw-bold border-0">Berber</th>
                                    <th class="py-3 text-uppercase fs-7 fw-bold border-0">Hizmetler</th>
                                    <th class="py-3 text-uppercase fs-7 fw-bold border-0">Tutar</th>
                                    <th class="pe-4 py-3 text-end text-uppercase fs-7 fw-bold border-0">Hızlı Eylemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($awaitingActionAppointments as $apt)
                                <tr class="border-bottom border-light">
                                    <td class="ps-4">
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark fs-6">{{ $apt->start_at->format('H:i') }}</span>
                                            <span class="text-secondary small">{{ $apt->start_at->format('d.m.Y') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm rounded-circle bg-warning bg-opacity-15 text-warning me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px;">
                                                {{ mb_substr($apt->customer->first_name ?? 'M', 0, 1) }}{{ mb_substr($apt->customer->last_name ?? 'M', 0, 1) }}
                                            </div>
                                            <div>
                                                <span class="d-block fw-bold text-dark">{{ $apt->customer->full_name ?? 'Bilinmeyen Müşteri' }}</span>
                                                <small class="text-muted"><i class="ti ti-phone me-1"></i>{{ $apt->customer->phone ?? 'Belirtilmemiş' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-dark"><i class="ti ti-cut text-warning me-1"></i>{{ $apt->employee->user->full_name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1" style="max-width: 250px;">
                                            @foreach($apt->appointmentServices as $aps)
                                                <span class="badge bg-light text-secondary border px-2 py-1"><i class="ti ti-check me-1"></i>{{ $aps->service->name }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark fs-6">₺{{ number_format($apt->total_price, 2, ',', '.') }}</span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex gap-2 justify-content-end align-items-center">
                                            <!-- Completed & Pay Button -->
                                            <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#dashboardCompleteModal{{ $apt->id }}">
                                                <i class="ti ti-circle-check fs-5"></i> Tamamlandı & Ödeme Al
                                            </button>

                                            <!-- No Show Button -->
                                            <form action="{{ route('appointments.update-status', $apt) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="no_show">
                                                <button type="submit" class="btn btn-outline-warning btn-sm rounded-pill px-3 fw-bold" onclick="return confirm('Bu randevuyu \'Gelmedi (No Show)\' olarak işaretlemek istediğinize emin misiniz?')">
                                                    <i class="ti ti-user-off me-1"></i> Gelmedi
                                                </button>
                                            </form>

                                            <!-- Cancel Button -->
                                            <form action="{{ route('appointments.update-status', $apt) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold" onclick="return confirm('Bu randevuyu iptal etmek istediğinize emin misiniz?')">
                                                    <i class="ti ti-x me-1"></i> İptal
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Complete with Payment Modal for this specific appointment -->
                                <div class="modal fade" id="dashboardCompleteModal{{ $apt->id }}" tabindex="-1" aria-labelledby="dashboardCompleteModalLabel{{ $apt->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content border-0 rounded-4 shadow-lg">
                                            <div class="modal-header border-0 bg-success text-white py-3">
                                                <h5 class="modal-title fw-bold" id="dashboardCompleteModalLabel{{ $apt->id }}">Randevuyu Tamamla & Ödeme Kaydet</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
                                            </div>
                                            <form action="{{ route('appointments.complete-payment', $apt) }}" method="POST">
                                                @csrf
                                                <div class="modal-body p-4">
                                                    <div class="alert alert-success border-0 p-3 rounded-3 mb-3" role="alert">
                                                        <div class="d-flex justify-content-between fw-bold">
                                                            <span>Randevu Toplam Fiyatı:</span>
                                                            <span>₺{{ number_format($apt->total_price, 2, ',', '.') }}</span>
                                                        </div>
                                                        <div class="small text-success mt-1">
                                                            <span>Müşteri: {{ $apt->customer->full_name }}</span>
                                                        </div>
                                                    </div>

                                                    <!-- Amount -->
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold text-secondary">Ödenen Tutar (₺)</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text border-0 bg-light">₺</span>
                                                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control border-0 bg-light rounded-end-3" value="{{ $apt->total_price }}" required>
                                                        </div>
                                                    </div>

                                                    <!-- Paid At & Payment Method -->
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold text-secondary">Ödeme Yöntemi</label>
                                                            <select name="payment_method" class="form-select border-0 bg-light" required>
                                                                <option value="cash">Nakit (Kasa)</option>
                                                                <option value="credit_card">Kredi Kartı</option>
                                                                <option value="bank_transfer">Banka Transferi</option>
                                                                <option value="online">Online</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold text-secondary">Ödeme Tarihi</label>
                                                            <input type="date" name="paid_at" class="form-control border-0 bg-light" value="{{ date('Y-m-d') }}" required>
                                                        </div>
                                                    </div>

                                                    <!-- Reference Number -->
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold text-secondary">Referans / İşlem Kodu (İsteğe Bağlı)</label>
                                                        <input type="text" name="transaction_reference" class="form-control border-0 bg-light" placeholder="Havale referansı, fiş numarası vb...">
                                                    </div>

                                                    <!-- Completion note -->
                                                    <div class="mb-2">
                                                        <label class="form-label fw-semibold text-secondary">İşlem / Randevu Notu (İsteğe Bağlı)</label>
                                                        <textarea name="note" rows="2" class="form-control border-0 bg-light" placeholder="Randevu tamamlanmasına dair not ekleyin..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 p-4 pt-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                                                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">Tamamla ve Ödeme Al</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Today's Appointments Row --}}
    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                    <h4 class="mb-0 h5"><i class="ti ti-calendar-clock text-primary me-2"></i>Günün Randevuları</h4>
                    <a href="{{ route('appointments.index') }}" class="small text-primary text-decoration-underline">Tümünü
                        Gör</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-top-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Saat</th>
                                    <th class="text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Müşteri</th>
                                    <th class="text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Berber</th>
                                    <th class="text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Hizmet</th>
                                    <th class="text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Durum</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @php
                                    $barberNextCount = [];
                                @endphp
                                @forelse($todayAppointments as $apt)
                                    @php
                                        $isPast = $apt->start_at < now();
                                        $isNext = false;
                                        if (!$isPast && !isset($barberNextCount[$apt->employee_id])) {
                                            $isNext = true;
                                            $barberNextCount[$apt->employee_id] = true;
                                        }
                                        $bgClass = $isNext ? 'bg-primary bg-opacity-10' : ($isPast ? 'bg-light opacity-75' : '');

                                        $remainingTime = '';
                                        if (!$isPast && !$isNext) {
                                            $diff = now()->diff($apt->start_at);
                                            if ($diff->h > 0) {
                                                $remainingTime = $diff->h . ' sa ' . $diff->i . ' dk';
                                            } else {
                                                $remainingTime = $diff->i . ' dk';
                                            }
                                        }
                                    @endphp
                                    <tr class="{{ $bgClass }}" style="transition: all 0.2s ease;">
                                        <td class="ps-4">
                                            <div
                                                class="d-inline-flex align-items-center justify-content-center px-3 py-2 rounded-2 {{ $isPast ? 'bg-secondary text-white' : 'bg-dark text-white' }} fw-bold shadow-sm">
                                                <i class="ti ti-clock me-1 opacity-75"></i>
                                                {{ $apt->start_at->format('H:i') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm rounded-circle {{ $isPast ? 'bg-secondary-subtle text-secondary' : 'bg-primary-subtle text-primary' }} me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm"
                                                    style="width: 40px; height: 40px;">
                                                    {{ mb_substr($apt->customer->first_name ?? 'M', 0, 1) }}{{ mb_substr($apt->customer->last_name ?? 'M', 0, 1) }}
                                                </div>
                                                <div>
                                                    <span
                                                        class="d-block fw-bold text-dark">{{ $apt->customer->full_name ?? 'Bilinmeyen Müşteri' }}</span>
                                                    <small class="text-muted"><i
                                                            class="ti ti-phone me-1"></i>{{ $apt->customer->phone ?? 'Belirtilmemiş' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="icon-shape icon-sm bg-warning-subtle text-warning rounded-circle me-2">
                                                    <i class="ti ti-cut fs-5"></i>
                                                </div>
                                                <span
                                                    class="fw-medium text-dark">{{ $apt->employee->user->full_name ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1" style="max-width: 250px;">
                                                @foreach($apt->appointmentServices as $aps)
                                                    <span class="badge bg-light text-secondary border px-2 py-1"><i
                                                            class="ti ti-check me-1"></i>{{ $aps->service->name }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            @if($apt->status->value == 'completed')
                                                <span
                                                    class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill"><i
                                                        class="ti ti-circle-check me-1"></i>Tamamlandı</span>
                                            @elseif($apt->status->value == 'cancelled')
                                                <span
                                                    class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill"><i
                                                        class="ti ti-x me-1"></i>İptal</span>
                                            @else
                                                @if($isNext)
                                                    <span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm"><i
                                                            class="ti ti-player-play-filled me-1"></i>Sıradaki</span>
                                                @elseif($isPast)
                                                    <span
                                                        class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill"><i
                                                            class="ti ti-clock-check me-1"></i>Süresi Geçti</span>
                                                @else
                                                    <span
                                                        class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill"><i
                                                            class="ti ti-hourglass-empty me-1"></i>{{ $remainingTime }} kaldı</span>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Bugün için randevu bulunmuyor.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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

            // Reject Modal Trigger Handler
            document.querySelectorAll('.btn-reject-trigger').forEach(btn => {
                btn.addEventListener('click', function() {
                    const url = this.getAttribute('data-url');
                    const form = document.getElementById('rejectReasonForm');
                    if (form) {
                        form.action = url;
                    }
                    const textarea = document.getElementById('cancellation_reason_input');
                    if (textarea) textarea.value = '';
                });
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
                fetch(`{{ route('dashboard.appointment-stats') }}?period=${period}&start_date=${startDate}&end_date=${endDate}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('stat_total').textContent = data.total;
                        document.getElementById('stat_cancelled').textContent = data.cancelled;
                        document.getElementById('stat_no_show').textContent = data.no_show;
                        
                        const rateEl = document.getElementById('stat_cancellation_rate');
                        rateEl.textContent = '%' + data.cancellation_rate;
                        rateEl.className = `badge bg-${data.cancellation_rate > 10 ? 'danger' : 'success'}-subtle text-${data.cancellation_rate > 10 ? 'danger' : 'success'}`;
                        
                        document.getElementById('stat_avg_spending').textContent = '₺' + Number(data.avg_spending).toLocaleString('tr-TR', { maximumFractionDigits: 0 });
                    })
                    .catch(err => {
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
        });
    </script>
@endpush