@extends('layouts.app')
@section('title', 'Kupon Kodu - Kullanım Geçmişi')
@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('campaigns.index') }}#couponsPanel" class="btn btn-sm btn-light rounded-pill px-3 mb-2" onclick="setTimeout(()=>document.getElementById('coupons-tab').click(), 100)">
                    <i class="ti ti-arrow-left me-1"></i> Kuponlara Dön
                </a>
                <h1 class="fs-3 mb-1">
                    <i class="ti ti-ticket text-primary me-2"></i> 
                    <span class="font-monospace">{{ $coupon->code }}</span>
                </h1>
                <p class="text-muted mb-0">
                    @if($coupon->title) {{ $coupon->title }} - @endif
                    Kuponunun detaylı kullanım geçmişini inceliyorsunuz.
                </p>
            </div>
            <div>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fs-6">
                    Kullanım Durumu: {{ $coupon->used_count }} / {{ $coupon->usage_limit }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-white border-bottom pt-4 pb-3 px-4 rounded-top-4">
        <h5 class="mb-0 fw-bold"><i class="ti ti-history me-2 text-secondary"></i>Kullanım Kayıtları</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tarih / Saat</th>
                        <th>Müşteri Bilgileri</th>
                        <th>Randevu Detayı</th>
                        <th>İşlemi Yapan Personel</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usages as $usage)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold text-dark">{{ $usage->used_at ? $usage->used_at->format('d.m.Y') : $usage->created_at->format('d.m.Y') }}</div>
                            <div class="text-secondary small">{{ $usage->used_at ? $usage->used_at->format('H:i') : $usage->created_at->format('H:i') }}</div>
                        </td>
                        <td>
                            @if($usage->customer)
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 35px; height: 35px;">
                                    {{ mb_substr($usage->customer->first_name, 0, 1) }}{{ mb_substr($usage->customer->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $usage->customer->first_name }} {{ $usage->customer->last_name }}</div>
                                    <div class="text-secondary small"><i class="ti ti-phone me-1"></i>{{ $usage->customer->phone ?? '-' }}</div>
                                </div>
                            </div>
                            @else
                            <div class="text-muted fst-italic">Silinmiş Müşteri</div>
                            @endif
                        </td>
                        <td>
                            @if($usage->appointment)
                                <div class="fw-bold font-monospace text-primary mb-1">
                                    #{{ $usage->appointment->appointment_code }}
                                </div>
                                <div class="text-secondary small">
                                    <span class="badge bg-light text-dark border me-1">
                                        ₺{{ number_format($usage->appointment->total_price, 2, ',', '.') }}
                                    </span>
                                    <span class="badge bg-{{ $usage->appointment->status->color() }} bg-opacity-10 text-{{ $usage->appointment->status->color() }}">
                                        {{ $usage->appointment->status->label() }}
                                    </span>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($usage->appointment && $usage->appointment->employee)
                                <div class="d-flex align-items-center gap-2">
                                    @if($usage->appointment->employee->avatar)
                                        <img src="{{ Storage::url($usage->appointment->employee->avatar) }}" alt="Avatar" class="rounded-circle object-fit-cover" width="32" height="32">
                                    @else
                                        <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">
                                            {{ mb_substr($usage->appointment->employee->first_name, 0, 1) }}
                                        </div>
                                    @endif
                                    <span class="fw-medium text-dark">{{ $usage->appointment->employee->first_name }} {{ $usage->appointment->employee->last_name }}</span>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="ti ti-info-circle fs-1 d-block mb-3 text-secondary opacity-50"></i>
                            <h5 class="fw-medium">Kullanım Bulunamadı</h5>
                            <p class="mb-0 small">Bu kupon kodu henüz hiçbir müşteri tarafından kullanılmamış.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($usages->hasPages())
    <div class="card-footer bg-white border-top py-3 px-4 rounded-bottom-4">
        {{ $usages->links() }}
    </div>
    @endif
</div>
@endsection
