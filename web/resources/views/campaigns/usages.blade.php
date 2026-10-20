@extends('layouts.app')
@section('title', $campaign->title . ' - Kullanım Geçmişi')
@section('content')
<x-page-header title="{{ $campaign->title }} - Kullanım Geçmişi" description="Kampanyanın detaylı kullanım geçmişini inceliyorsunuz.">
    <x-btn-outline href="{{ route('campaigns.index') }}" icon="ti ti-arrow-left">
        Kampanyalara Dön
    </x-btn-outline>
</x-page-header>

<!-- Filter Form -->
<div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
    <form method="GET" action="{{ route('campaigns.usages', $campaign) }}" class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label text-secondary small fw-medium mb-1">Müşteri Ara</label>
            <x-search-input 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Müşteri adı veya telefon..." 
                :pill="true" />
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label text-secondary small fw-medium mb-1">Başlangıç Tarihi</label>
            <x-date-input 
                name="date_from" 
                value="{{ request('date_from') }}" 
                :pill="true" />
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label text-secondary small fw-medium mb-1">Bitiş Tarihi</label>
            <x-date-input 
                name="date_to" 
                value="{{ request('date_to') }}" 
                :pill="true" />
        </div>

        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary rounded-pill px-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-1 fw-medium" style="height: 38px;">
                <i class="ti ti-filter"></i> Filtrele
            </button>
            @if(request()->hasAny(['search', 'date_from', 'date_to']))
                <a href="{{ route('campaigns.usages', $campaign) }}" class="btn btn-light rounded-pill px-2 shadow-sm d-inline-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="height: 38px; width: 38px;" title="Filtreleri Temizle">
                    <i class="ti ti-rotate"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom border-light-subtle pt-4 pb-3 px-4">
        <h5 class="mb-0 fw-bold"><i class="ti ti-history me-2 text-secondary"></i>Kullanım Kayıtları</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-dark">
                <thead class="bg-light text-secondary">
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
                    <x-empty-state 
                        colspan="4" 
                        title="Kullanım bulunamadı" 
                        description="{{ request()->hasAny(['search', 'date_from', 'date_to']) ? 'Arama kriterlerinize uygun kullanım kaydı bulunamadı.' : 'Bu kampanya henüz hiçbir müşteri tarafından kullanılmamış.' }}" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($usages->hasPages())
    <div class="card-footer bg-white border-top border-light-subtle py-3 px-4">
        {{ $usages->links() }}
    </div>
    @endif
</div>
@endsection
