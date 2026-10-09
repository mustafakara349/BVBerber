@extends('layouts.app')
@section('title', 'Müşteri Detayı - B&V Barber')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fs-3 mb-1 fw-bold">Müşteri Profili</h1>
                <p class="text-muted">{{ $customer->full_name }} detayları, harcama istatistikleri ve randevu geçmişi.</p>
            </div>
            <a href="{{ route('customers.index') }}" class="btn btn-light rounded-pill border shadow-sm">
                <i class="ti ti-arrow-left me-1"></i> Geri Dön
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Sol Taraf: Profil ve Bilgiler -->
    <div class="col-xl-4 col-lg-4">
        <div class="card shadow-sm border-0 rounded-4 mb-4 text-center">
            <div class="card-body p-4">
                @if($customer->profile_photo_url)
                    <img src="{{ $customer->profile_photo_url }}" class="rounded-circle mb-3 object-fit-cover shadow-sm border border-4 border-white" width="140" height="140" alt="{{ $customer->full_name }}">
                @else
                    <div class="avatar bg-primary-subtle text-primary rounded-circle mb-3 d-flex align-items-center justify-content-center mx-auto fw-bold shadow-sm border border-4 border-white" style="width: 140px; height: 140px; font-size: 3.5rem;">
                        {{ mb_substr($customer->first_name, 0, 1) }}{{ mb_substr($customer->last_name, 0, 1) }}
                    </div>
                @endif
                <h4 class="mb-1 fw-bold">{{ $customer->full_name }}</h4>
                <p class="text-muted mb-3">Müşteri</p>
                
                <div class="d-flex justify-content-center gap-2 mb-4">
                    @if($customer->status->value == 'active')
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill">Aktif Kullanıcı</span>
                    @elseif($customer->status->value == 'inactive')
                        <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2 rounded-pill">Pasif</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 rounded-pill">Engelli</span>
                    @endif
                </div>

                <div class="text-start border-top pt-4">
                    <div class="mb-3 d-flex align-items-center">
                        <div class="bg-light rounded p-2 me-3 text-secondary"><i class="ti ti-mail fs-5"></i></div>
                        <div>
                            <small class="d-block text-muted">E-posta</small>
                            <span class="fw-bold text-dark">{{ $customer->email }}</span>
                        </div>
                    </div>
                    <div class="mb-3 d-flex align-items-center">
                        <div class="bg-light rounded p-2 me-3 text-secondary"><i class="ti ti-phone fs-5"></i></div>
                        <div>
                            <small class="d-block text-muted">Telefon</small>
                            <span class="fw-bold text-dark">{{ $customer->phone ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="mb-3 d-flex align-items-center">
                        <div class="bg-light rounded p-2 me-3 text-secondary"><i class="ti ti-calendar fs-5"></i></div>
                        <div>
                            <small class="d-block text-muted">Kayıt Tarihi</small>
                            <span class="fw-bold text-dark">{{ $customer->created_at->format('d.m.Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light border-top p-3 d-flex flex-column gap-2 text-center rounded-bottom-4">
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary rounded-pill px-4 w-100 shadow-sm fw-bold">
                    <i class="ti ti-pencil me-1"></i> Profili Düzenle
                </a>
                <a href="{{ route('customers.loyalty.show', $customer) }}" class="btn btn-outline-warning rounded-pill px-4 w-100 shadow-sm fw-bold">
                    <i class="ti ti-star fs-5 me-1"></i> Sadakat Kartı
                </a>
            </div>
        </div>
    </div>

    <!-- Sağ Taraf: İstatistikler ve Tablo -->
    <div class="col-xl-8 col-lg-8">
        <!-- Harcama İstatistikleri -->
        <h5 class="fw-bold mb-3"><i class="ti ti-chart-bar me-2 text-primary"></i>Harcama İstatistikleri</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4 col-12">
                <div class="card shadow-sm border-0 bg-success bg-opacity-10 rounded-4 h-100">
                    <div class="card-body p-3 text-center">
                        <div class="bg-success text-white rounded-circle d-inline-flex p-3 mb-2 shadow-sm"><i class="ti ti-cash fs-4"></i></div>
                        <h4 class="mb-0 fw-bold text-success">₺{{ number_format($totalSpent, 0, ',', '.') }}</h4>
                        <span class="text-muted small fw-medium">Toplam Harcama</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="card shadow-sm border-0 bg-info bg-opacity-10 rounded-4 h-100">
                    <div class="card-body p-3 text-center">
                        <div class="bg-info text-white rounded-circle d-inline-flex p-3 mb-2 shadow-sm"><i class="ti ti-calendar-stats fs-4"></i></div>
                        <h4 class="mb-0 fw-bold text-info">₺{{ number_format($thisMonthSpent, 0, ',', '.') }}</h4>
                        <span class="text-muted small fw-medium">Bu Ay Harcama</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="card shadow-sm border-0 bg-warning bg-opacity-10 rounded-4 h-100">
                    <div class="card-body p-3 text-center">
                        <div class="bg-warning text-white rounded-circle d-inline-flex p-3 mb-2 shadow-sm"><i class="ti ti-calendar-star fs-4"></i></div>
                        <h4 class="mb-0 fw-bold text-warning">₺{{ number_format($thisYearSpent, 0, ',', '.') }}</h4>
                        <span class="text-muted small fw-medium">Bu Yıl Harcama</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Randevu İstatistikleri -->
        <h5 class="fw-bold mb-3"><i class="ti ti-calendar-check me-2 text-primary"></i>Randevu İstatistikleri</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-6">
                <div class="card shadow-sm border-0 bg-primary bg-opacity-10 rounded-4 h-100">
                    <div class="card-body p-3 text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex p-3 mb-2 shadow-sm"><i class="ti ti-calendar-check fs-4"></i></div>
                        <h4 class="mb-0 fw-bold text-primary">{{ $completedAppointments }}</h4>
                        <span class="text-muted small fw-medium">Toplam Tamamlanan</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-6">
                <div class="card shadow-sm border-0 bg-secondary bg-opacity-10 rounded-4 h-100">
                    <div class="card-body p-3 text-center">
                        <div class="bg-secondary text-white rounded-circle d-inline-flex p-3 mb-2 shadow-sm"><i class="ti ti-calendar fs-4"></i></div>
                        <h4 class="mb-0 fw-bold text-secondary">{{ $thisMonthAppointments }}</h4>
                        <span class="text-muted small fw-medium">Bu Ay Tamamlanan</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Randevu Geçmişi Tablosu -->
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="ti ti-history me-2 text-primary"></i>Geçmiş Randevular</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Tarih / Saat</th>
                                <th>Berber</th>
                                <th>Hizmetler</th>
                                <th>Tutar</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $apt)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $apt->start_at->format('d.m.Y') }}</div>
                                    <small class="text-muted">{{ $apt->start_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="icon-shape icon-sm bg-warning-subtle text-warning rounded-circle">
                                            <i class="ti ti-cut fs-5"></i>
                                        </div>
                                        <span class="fw-medium text-dark">{{ $apt->employee->user->full_name ?? 'Bilinmiyor' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border border-secondary shadow-sm" title="{{ $apt->appointmentServices->pluck('service.name')->join(', ') }}">
                                        {{ $apt->appointmentServices->count() }} Hizmet
                                    </span>
                                </td>
                                <td class="fw-bold text-dark">₺{{ number_format($apt->total_price, 2, ',', '.') }}</td>
                                <td>
                                    @if($apt->status->value == 'completed')
                                        <span class="badge bg-success-subtle text-success border border-success">Tamamlandı</span>
                                    @elseif($apt->status->value == 'cancelled' || $apt->status->value == 'rejected')
                                        <span class="badge bg-danger-subtle text-danger border border-danger">İptal/Red</span>
                                    @elseif($apt->status->value == 'pending')
                                        <span class="badge bg-warning-subtle text-warning border border-warning">Bekliyor</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary">{{ $apt->status->label() }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="ti ti-calendar-off fs-1 d-block mb-2"></i>
                                    Bu müşterinin geçmiş randevusu bulunmuyor.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($appointments->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">
                {{ $appointments->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
