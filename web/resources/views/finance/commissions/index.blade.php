@extends('layouts.app')
@section('title', 'Personel Prim ve Hakedişleri - B&V Barber')
@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Prim ve Maaş Hakedişleri</h1>
                <p class="text-muted mb-0">Personelinizin aylık baz maaş ve randevu primlerini takip edin.</p>
            </div>
            
            <form action="{{ route('finance.commissions.index') }}" method="GET" class="d-flex align-items-center gap-2 bg-white p-2 rounded-pill shadow-sm">
                <select name="month" class="form-select border-0 bg-transparent fw-semibold text-primary" onchange="this.form.submit()">
                    @foreach($months as $val => $label)
                        <option value="{{ $val }}" {{ $monthInput === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
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
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="ti ti-receipt-off fs-1 mb-2 d-block text-secondary opacity-50"></i>
                                    <h5>Bu aya ait prim/hakediş verisi bulunamadı.</h5>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
