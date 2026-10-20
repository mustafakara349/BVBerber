@extends('layouts.app')
@section('title', 'Ödemeler - B&V Barber')
@section('content')

<x-page-header title="Ödemeler (Bizim Borçlarımız)" description="Tedarikçi borçlarını, masrafları ve işletmenizin yapması gereken ödemeleri yönetin.">
    <x-btn-primary icon="ti ti-plus" data-bs-toggle="modal" data-bs-target="#addDebtModal">
        Yeni Borç Kaydı Ekle
    </x-btn-primary>
</x-page-header>

<!-- Navigation Tabs -->
<div class="mb-4">
    <ul class="nav nav-pills p-1 bg-white rounded-pill d-inline-flex border shadow-sm">
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 text-secondary" href="{{ route('finance.receivables.index') }}">
                <i class="ti ti-arrow-down-right me-1"></i> Alacaklar (Müşteriler)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active rounded-pill px-4" href="{{ route('finance.payables.index') }}">
                <i class="ti ti-arrow-up-right me-1"></i> Ödemeler (Tedarikçiler/Giderler)
            </a>
        </li>
    </ul>
</div>


<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <!-- Total Debt Card -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute end-0 bottom-0 opacity-10 mb-n3 me-n2">
                    <i class="ti ti-file-invoice" style="font-size: 110px; line-height: 1;"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-2 rounded-3" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="ti ti-file-text fs-4 text-white"></i>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 text-white" style="background-color: rgba(255, 255, 255, 0.25);">Filtrelenmiş Toplam</span>
                </div>
                <h6 class="text-white text-opacity-75 small mb-1 fw-medium text-uppercase tracking-wider">Toplam Borç</h6>
                <h3 class="fs-2 fw-bold mb-0">₺{{ number_format($totalDebt, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- Total Paid Card -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute end-0 bottom-0 opacity-10 mb-n3 me-n2">
                    <i class="ti ti-checkbox" style="font-size: 110px; line-height: 1;"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-2 rounded-3" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="ti ti-circle-check fs-4 text-white"></i>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 text-white" style="background-color: rgba(255, 255, 255, 0.25);">Filtrelenmiş Ödenen</span>
                </div>
                <h6 class="text-white text-opacity-75 small mb-1 fw-medium text-uppercase tracking-wider">Toplam Ödenen</h6>
                <h3 class="fs-2 fw-bold mb-0">₺{{ number_format($totalDebt - $remainingDebt, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- Remaining Debt Card -->
    <div class="col-12 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute end-0 bottom-0 opacity-10 mb-n3 me-n2">
                    <i class="ti ti-alert-triangle" style="font-size: 110px; line-height: 1;"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-2 rounded-3" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="ti ti-alert-circle fs-4 text-white"></i>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 text-white" style="background-color: rgba(255, 255, 255, 0.25);">Kalan Bakiye</span>
                </div>
                <h6 class="text-white text-opacity-75 small mb-1 fw-medium text-uppercase tracking-wider">Kalan Net Borç</h6>
                <h3 class="fs-2 fw-bold mb-0">₺{{ number_format($remainingDebt, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <form action="{{ route('finance.payables.index') }}" method="GET" class="row g-2 align-items-end">
                <!-- Search Filter -->
                <div class="col-12 col-md-5 col-lg-5">
                    <label class="form-label text-secondary small fw-medium mb-1">Arama</label>
                    <x-search-input name="search" placeholder="Müşteri adı, telefon, randevu kodu..." :value="request('search')" :resetUrl="route('finance.payables.index')" />
                </div>

                <!-- Status Filter -->
                <div class="col-12 col-md-4 col-lg-4">
                    <x-form-select name="status" label="Ödeme Durumu" :pill="true">
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Tüm Borçlar (Ödenenler Dahil)</option>
                        <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>Aktif Borçlar (Ödenmemiş & Kısmi)</option>
                        <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Ödenmemiş</option>
                        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Kısmi Ödenmiş</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Tamamen Ödenmiş</option>
                    </x-form-select>
                </div>

                <!-- Search Button -->
                <div class="col-12 col-md-3 col-lg-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-1 fw-medium" style="height: 38px;">
                        <i class="ti ti-filter"></i> Filtrele
                    </button>
                    @if(request()->anyFilled(['status', 'search']))
                        <a href="{{ route('finance.payables.index') }}" class="btn btn-light rounded-pill px-2 shadow-sm d-inline-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="height: 38px; width: 38px;" title="Filtreleri Temizle">
                            <i class="ti ti-rotate"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Debts Table List -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0">Alacaklı (Tedarikçi/Kişi)</th>
                                <th class="py-3 border-0">Kayıt Sayısı</th>
                                <th class="py-3 border-0">Son İşlem Tarihi</th>
                                <th class="py-3 border-0">Toplam Tutar</th>
                                <th class="py-3 border-0">Ödenen</th>
                                <th class="py-3 border-0">Kalan Bakiye</th>
                                <th class="pe-4 py-3 text-end border-0">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debts as $debt)
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center fw-bold fs-6 me-3" style="width: 40px; height: 40px;">
                                            {{ mb_substr($debt->counterparty_name ?? 'B', 0, 1) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold text-dark">{{ $debt->counterparty_name ?? 'Bilinmeyen Alacaklı' }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $debt->debt_count }} Kayıt</span>
                                </td>
                                <td>
                                    <span class="text-secondary small">{{ \Carbon\Carbon::parse($debt->last_debt_date)->format('d.m.Y H:i') }}</span>
                                </td>
                                <td class="fw-semibold">
                                    ₺{{ number_format($debt->total_amount, 2, ',', '.') }}
                                </td>
                                <td class="text-success small">
                                    ₺{{ number_format($debt->total_paid, 2, ',', '.') }}
                                </td>
                                <td class="fw-bold {{ $debt->remaining_amount <= 0 ? 'text-secondary' : 'text-danger' }}">
                                    ₺{{ number_format($debt->remaining_amount, 2, ',', '.') }}
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('finance.debts.details', ['type' => 'payable', 'counterparty_name' => $debt->counterparty_name]) }}" class="btn btn-sm btn-danger rounded-pill px-3 d-flex align-items-center gap-1 d-inline-flex">
                                        <i class="ti ti-eye fs-6"></i> Detay / Ödeme
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <x-empty-state 
                                colspan="7" 
                                title="Borç kaydı bulunamadı" 
                                description="Tüm borçlar ödenmiş olabilir veya filtre kriterleriniz çok dar olabilir." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($debts->hasPages())
                <div class="card-footer bg-white border-top border-light-subtle py-3 px-4">
                    {{ $debts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Manual Debt Modal -->
<div class="modal fade" id="addDebtModal" tabindex="-1" aria-labelledby="addDebtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="addDebtModalLabel">Yeni Borç/Gider Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="{{ route('finance.debts.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="payable">
                <div class="modal-body p-4">
                    <!-- Counterparty -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Alacaklı (Tedarikçi/Kurum/Kişi)</label>
                        <input type="text" name="counterparty_name" class="form-control border-0 bg-light" placeholder="Örn: Toptancı A, Elektrik İdaresi" required>
                    </div>

                    <!-- Amount -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Borç Tutarı (₺)</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light">₺</span>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control border-0 bg-light rounded-end-3" placeholder="0,00" required>
                        </div>
                    </div>

                    <!-- Due Date -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Vade Tarihi (İsteğe Bağlı)</label>
                        <input type="date" name="due_date" class="form-control border-0 bg-light">
                    </div>

                    <!-- Description -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary">Açıklama</label>
                        <textarea name="description" rows="3" class="form-control border-0 bg-light" placeholder="Borç nedenini açıklayın (örn: Kozmetik ürün satışı, ek hizmet vb.)..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Borcu Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
