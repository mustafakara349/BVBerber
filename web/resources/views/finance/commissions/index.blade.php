@extends('layouts.app')
@section('title', 'Personel Prim ve Hakedişleri - B&V Barber')
@section('content')

<x-page-header title="Prim ve Maaş Hakedişleri" description="Personelinizin aylık baz maaş ve randevu primlerini takip edin." />

<!-- Filter Form -->
<div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
    <form method="GET" action="{{ route('finance.commissions.index') }}" class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label text-secondary small fw-medium mb-1">Personel Ara</label>
            <x-search-input 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Personel adı ile ara..." 
                :pill="true" />
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label text-secondary small fw-medium mb-1">Dönem (Ay)</label>
            <x-form-select name="month" :pill="true">
                @foreach($months as $val => $label)
                    <option value="{{ $val }}" {{ $monthInput === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </x-form-select>
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label text-secondary small fw-medium mb-1">Maaş Tipi</label>
            <x-form-select name="salary_type" :pill="true">
                <option value="">Tüm Maaş Tipleri</option>
                @foreach($salaryTypes as $type)
                    <option value="{{ $type->value }}" {{ request('salary_type') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                @endforeach
            </x-form-select>
        </div>

        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary rounded-pill px-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-1 fw-medium" style="height: 38px;">
                <i class="ti ti-filter"></i> Filtrele
            </button>
            @if(request()->hasAny(['search', 'salary_type']) || (request()->filled('month') && request('month') !== now()->format('Y-m')))
                <a href="{{ route('finance.commissions.index') }}" class="btn btn-light rounded-pill px-2 shadow-sm d-inline-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="height: 38px; width: 38px;" title="Filtreleri Temizle">
                    <i class="ti ti-rotate"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bg-primary-subtle p-3 rounded-3 me-3 text-primary">
                    <i class="ti ti-wallet fs-2"></i>
                </div>
                <div>
                    <h6 class="text-secondary small mb-1 fw-medium text-uppercase tracking-wider">Toplam Maaş Ödemesi</h6>
                    <h3 class="fs-3 fw-bold mb-0 text-dark">₺{{ number_format(collect($commissions)->sum('base_salary'), 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-4">
        <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bg-warning-subtle p-3 rounded-3 me-3 text-warning">
                    <i class="ti ti-percentage fs-2"></i>
                </div>
                <div>
                    <h6 class="text-secondary small mb-1 fw-medium text-uppercase tracking-wider">Toplam Prim Dağıtımı</h6>
                    <h3 class="fs-3 fw-bold mb-0 text-dark">₺{{ number_format(collect($commissions)->sum('commission_earned'), 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card border-0 rounded-4 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="p-3 rounded-3 me-3" style="background-color: rgba(255, 255, 255, 0.2);">
                    <i class="ti ti-cash-banknote fs-2 text-white"></i>
                </div>
                <div>
                    <h6 class="text-white text-opacity-75 small mb-1 fw-medium text-uppercase tracking-wider">Aylık Toplam Hakediş</h6>
                    <h3 class="fs-3 fw-bold mb-0">₺{{ number_format(collect($commissions)->sum('total_earnings'), 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Commissions Table -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0">Personel</th>
                                <th class="py-3 border-0">Maaş Tipi</th>
                                <th class="py-3 border-0 text-end">Ciro (Ürettiği)</th>
                                <th class="py-3 border-0 text-center">Prim Oranı</th>
                                <th class="py-3 border-0 text-end">Prim Kazancı</th>
                                <th class="py-3 border-0 text-end">Baz Maaş</th>
                                <th class="pe-4 py-3 border-0 text-end">Toplam Hakediş</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($commissions as $row)
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-6 me-3" style="width: 40px; height: 40px;">
                                            {{ mb_substr($row['employee']->user->first_name ?? 'P', 0, 1) }}{{ mb_substr($row['employee']->user->last_name ?? '', 0, 1) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold text-dark">{{ $row['employee']->full_name }}</h6>
                                            <span class="text-secondary small">{{ $row['employee']->title ?? 'Kuaför' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-medium">{{ $row['salary_type'] }}</span>
                                </td>
                                <td class="text-end text-muted">
                                    ₺{{ number_format($row['total_revenue'], 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($row['commission_rate'] > 0)
                                        <span class="badge bg-warning-subtle text-warning fw-bold">%{{ $row['commission_rate'] }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end text-success fw-medium">
                                    ₺{{ number_format($row['commission_earned'], 2, ',', '.') }}
                                </td>
                                <td class="text-end text-muted">
                                    ₺{{ number_format($row['base_salary'], 2, ',', '.') }}
                                </td>
                                <td class="pe-4 text-end">
                                    <span class="fs-5 fw-bold text-dark">₺{{ number_format($row['total_earnings'], 2, ',', '.') }}</span>
                                </td>
                            </tr>
                            @empty
                            <x-empty-state 
                                colspan="7" 
                                icon="ti ti-receipt-off" 
                                title="Hakediş kaydı bulunamadı" 
                                description="{{ request()->hasAny(['search', 'salary_type']) ? 'Arama kriterlerinize uygun personel hakediş verisi bulunamadı.' : 'Seçilen döneme ait hakediş veya prim kaydı mevcut değil.' }}" />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
