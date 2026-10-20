@extends('layouts.app')
@section('title', 'Sadakat Programı - ' . $customer->full_name . ' - B&V Barber')
@section('content')

<x-page-header title="{{ $customer->full_name }} - Sadakat Programı" description="Müşterinin puanlarını görüntüleyin veya manuel işlem yapın.">
    <x-btn-outline href="{{ route('customers.show', $customer) }}" icon="ti ti-arrow-left">
        Müşteri Profiline Dön
    </x-btn-outline>
</x-page-header>

<div class="row g-4">
    <!-- Balance Card -->
    <div class="col-12 col-md-4">
        <div class="card border-0 rounded-4 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute end-0 bottom-0 opacity-10 mb-n3 me-n2">
                    <i class="ti ti-star" style="font-size: 110px; line-height: 1;"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-2 rounded-3" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="ti ti-star fs-4 text-white"></i>
                    </div>
                </div>
                <h6 class="text-white text-opacity-75 small mb-1 fw-medium text-uppercase tracking-wider">Mevcut Bakiye</h6>
                <h3 class="fs-1 fw-bold mb-0">{{ number_format($account->points_balance ?? 0, 0, ',', '.') }} <small class="fs-5">Puan</small></h3>
                <div class="mt-4 pt-3 border-top border-white border-opacity-25 d-flex justify-content-between">
                    <div>
                        <div class="small text-white text-opacity-75">Toplam Kazanılan</div>
                        <div class="fw-semibold">{{ number_format($account->total_earned ?? 0, 0, ',', '.') }} Puan</div>
                    </div>
                    <div class="text-end">
                        <div class="small text-white text-opacity-75">Toplam Harcanan</div>
                        <div class="fw-semibold">{{ number_format($account->total_spent ?? 0, 0, ',', '.') }} Puan</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Adjust Form -->
        <div class="card border-0 rounded-4 shadow-sm mt-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title fw-bold text-dark mb-0 fs-6">Manuel Puan İşlemi</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('customers.loyalty.store', $customer) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">İşlem Miktarı</label>
                        <div class="input-group">
                            <input type="number" name="points" class="form-control bg-light border-0" placeholder="Örn: 500 veya -200" required>
                            <span class="input-group-text bg-light border-0">Puan</span>
                        </div>
                        <small class="text-muted mt-1 d-block">Puan eklemek için pozitif (örn: 100), silmek için negatif (örn: -50) değer girin.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Açıklama</label>
                        <textarea name="description" rows="2" class="form-control bg-light border-0" placeholder="İşlem nedeni..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm">İşlemi Kaydet</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="col-12 col-md-8">
        <!-- Filter Form -->
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-3">
            <form method="GET" action="{{ route('customers.loyalty.show', $customer) }}" class="row g-2 align-items-end">
                <div class="col-12 col-sm-4">
                    <label class="form-label text-secondary small fw-medium mb-1">İşlem Türü</label>
                    <x-form-select name="type" :pill="true">
                        <option value="">Tüm İşlem Türleri</option>
                        <option value="earn" {{ request('type') === 'earn' ? 'selected' : '' }}>Kazanım</option>
                        <option value="spend" {{ request('type') === 'spend' ? 'selected' : '' }}>Harcama</option>
                        <option value="manual" {{ request('type') === 'manual' ? 'selected' : '' }}>Manuel</option>
                    </x-form-select>
                </div>

                <div class="col-12 col-sm-3">
                    <label class="form-label text-secondary small fw-medium mb-1">Başlangıç</label>
                    <x-date-input 
                        name="date_from" 
                        value="{{ request('date_from') }}" 
                        :pill="true" />
                </div>

                <div class="col-12 col-sm-3">
                    <label class="form-label text-secondary small fw-medium mb-1">Bitiş</label>
                    <x-date-input 
                        name="date_to" 
                        value="{{ request('date_to') }}" 
                        :pill="true" />
                </div>

                <div class="col-12 col-sm-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-2 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-1 fw-medium" style="height: 38px;">
                        <i class="ti ti-filter"></i> Filtrele
                    </button>
                    @if(request()->hasAny(['type', 'date_from', 'date_to']))
                        <a href="{{ route('customers.loyalty.show', $customer) }}" class="btn btn-light rounded-pill px-2 shadow-sm d-inline-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="height: 38px; width: 38px;" title="Filtreleri Temizle">
                            <i class="ti ti-rotate"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-2">
                <i class="ti ti-history text-primary fs-4"></i>
                <h5 class="card-title fw-bold text-dark mb-0 fs-6">Puan Hareketleri Geçmişi</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0">Tarih</th>
                                <th class="py-3 border-0">İşlem Türü</th>
                                <th class="py-3 border-0 text-end">Puan</th>
                                <th class="pe-4 py-3 border-0">Açıklama</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $txn)
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <span class="text-secondary small">{{ $txn->created_at->format('d.m.Y H:i') }}</span>
                                </td>
                                <td>
                                    @if($txn->type === 'earn')
                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Kazanım</span>
                                    @elseif($txn->type === 'spend')
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">Harcama</span>
                                    @elseif($txn->type === 'manual')
                                        <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill">Manuel</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">{{ ucfirst($txn->type) }}</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold {{ in_array($txn->type, ['earn', 'manual']) && $txn->points > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $txn->points > 0 && in_array($txn->type, ['earn', 'manual']) ? '+' : '-' }}{{ abs($txn->points) }}
                                </td>
                                <td class="pe-4 text-muted small">
                                    {{ $txn->description }}
                                </td>
                            </tr>
                            @empty
                            <x-empty-state 
                                icon="ti ti-star-off" 
                                title="Puan hareketi bulunamadı" 
                                description="{{ request()->hasAny(['type', 'date_from', 'date_to']) ? 'Arama kriterlerinize uygun puan hareketi bulunamadı.' : 'Bu müşteriye ait henüz bir puan kazanımı veya harcaması gerçekleşmemiştir.' }}" 
                                colspan="4" />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($transactions->hasPages())
                <div class="card-footer bg-white border-top border-light-subtle py-3 px-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
