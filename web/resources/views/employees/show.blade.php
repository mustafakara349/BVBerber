@extends('layouts.app')
@section('title', 'Çalışan Detayı - B&V Barber')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fs-3 mb-1">Çalışan İstatistikleri</h1>
                <p class="text-muted">{{ $employee->user->full_name }} detayları ve performans verileri.</p>
            </div>
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Geri Dön
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Profil & Kimlik Kartı -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow-sm border-0 rounded-3 h-100">
            <div class="card-body text-center p-4">
                @if($employee->user->profile_photo)
                    <img src="{{ asset($employee->user->profile_photo) }}" class="rounded-circle mb-3 object-fit-cover shadow-sm" width="120" height="120" alt="{{ $employee->user->full_name }}">
                @else
                    <div class="avatar bg-primary-subtle text-primary rounded-circle mb-3 d-flex align-items-center justify-content-center mx-auto fw-bold shadow-sm" style="width: 120px; height: 120px; font-size: 2.5rem;">
                        {{ mb_substr($employee->user->first_name, 0, 1) }}{{ mb_substr($employee->user->last_name, 0, 1) }}
                    </div>
                @endif
                <h4 class="mb-1 fw-bold">{{ $employee->user->full_name }}</h4>
                <p class="text-muted mb-3">{{ $employee->title ?? 'Unvan Belirtilmemiş' }}</p>
                
                <div class="d-flex justify-content-center gap-2 mb-4">
                    <span class="badge bg-light text-dark border"><i class="ti ti-id-badge me-1"></i>{{ $employee->employee_code }}</span>
                    @if($employee->is_active)
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Pasif</span>
                    @endif
                    <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $employee->user->role->name ?? 'Belirsiz' }}</span>
                </div>

                <div class="text-start border-top pt-4">
                    <div class="mb-3 d-flex align-items-center">
                        <div class="bg-light rounded p-2 me-3 text-secondary"><i class="ti ti-mail fs-5"></i></div>
                        <div>
                            <small class="d-block text-muted">E-posta</small>
                            <span class="fw-medium">{{ $employee->user->email }}</span>
                        </div>
                    </div>
                    <div class="mb-3 d-flex align-items-center">
                        <div class="bg-light rounded p-2 me-3 text-secondary"><i class="ti ti-phone fs-5"></i></div>
                        <div>
                            <small class="d-block text-muted">Telefon</small>
                            <span class="fw-medium">{{ $employee->user->phone ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="mb-3 d-flex align-items-center">
                        <div class="bg-light rounded p-2 me-3 text-secondary"><i class="ti ti-calendar fs-5"></i></div>
                        <div>
                            <small class="d-block text-muted">İşe Başlama</small>
                            <span class="fw-medium">{{ $employee->hire_date ? $employee->hire_date->format('d.m.Y') : '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-top p-3 text-center">
                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary rounded-pill px-4 w-100">
                    <i class="ti ti-pencil me-1"></i> Profili Düzenle
                </a>
            </div>
        </div>
    </div>

    <!-- İstatistikler -->
    <div class="col-xl-8 col-lg-7">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 bg-success bg-opacity-10 rounded-3">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="bg-success text-white rounded p-3 me-3"><i class="ti ti-cash fs-3"></i></div>
                        <div>
                            <h3 class="mb-0 fw-bold">₺{{ number_format($totalRevenue, 2, ',', '.') }}</h3>
                            <span class="text-success fw-medium">Üretilen Toplam Ciro</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0 bg-primary bg-opacity-10 rounded-3">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="bg-primary text-white rounded p-3 me-3"><i class="ti ti-calendar-check fs-3"></i></div>
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $completedAppointments }}</h3>
                            <span class="text-primary fw-medium">Tamamlanan Randevu</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="mb-0">Maaş ve Hak Ediş Detayları</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4 text-center">
                    <div class="col-md-4 border-end">
                        <span class="d-block text-muted mb-2">Maaş Tipi</span>
                        <h6 class="fw-bold mb-0">
                            @if($employee->salary_type->value == 'fixed') Sabit Maaş @endif
                            @if($employee->salary_type->value == 'commission') Prim Usulü @endif
                            @if($employee->salary_type->value == 'fixed_plus_commission') Maaş + Prim @endif
                            @if($employee->salary_type->value == 'hourly') Saatlik Ücret @endif
                        </h6>
                    </div>
                    <div class="col-md-4 border-end">
                        <span class="d-block text-muted mb-2">Sabit Tutar / Saatlik</span>
                        <h6 class="fw-bold mb-0">₺{{ number_format($employee->salary_amount, 2, ',', '.') }}</h6>
                    </div>
                    <div class="col-md-4">
                        <span class="d-block text-muted mb-2">Prim Oranı</span>
                        <h6 class="fw-bold mb-0">%{{ $employee->commission_rate }}</h6>
                    </div>
                </div>
                
                @if($employee->salary_type->value == 'commission' || $employee->salary_type->value == 'fixed_plus_commission')
                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Tahmini Prim Hak Edişi (Ciro Üzerinden)</span>
                        <h5 class="fw-bold text-success mb-0">₺{{ number_format($totalRevenue * ($employee->commission_rate / 100), 2, ',', '.') }}</h5>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-3 mt-4">
            <div class="card-header bg-transparent border-bottom py-3">
                <h5 class="mb-0">Son Randevuları</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Müşteri</th>
                                <th>Tarih</th>
                                <th>Hizmetler</th>
                                <th>Tutar</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->appointments->sortByDesc('start_at')->take(5) as $apt)
                            <tr>
                                <td class="ps-4">{{ $apt->customer->full_name ?? 'Bilinmiyor' }}</td>
                                <td>{{ $apt->start_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border" title="{{ $apt->appointmentServices->pluck('service.name')->join(', ') }}">
                                        {{ $apt->appointmentServices->count() }} Hizmet
                                    </span>
                                </td>
                                <td>₺{{ number_format($apt->total_price, 2) }}</td>
                                <td>
                                    @if($apt->status->value == 'completed')
                                        <span class="badge bg-success-subtle text-success">Tamamlandı</span>
                                    @elseif($apt->status->value == 'cancelled')
                                        <span class="badge bg-danger-subtle text-danger">İptal</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">{{ $apt->status->value }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">Geçmiş randevu bulunmuyor.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
