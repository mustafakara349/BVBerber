@extends('layouts.app')
@section('title', 'Stok Hareketleri - Stok & Tedarik - B&V Barber')
@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Stok Hareketleri</h1>
                <p class="text-muted mb-0">Tüm ürün giriş, çıkış ve düzeltme loglarını inceleyin.</p>
            </div>
            <div>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                    Ürünlere Dön
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Filter Panel -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4 bg-light bg-opacity-30">
                <form action="{{ route('stock-movements.index') }}" method="GET" class="row g-3">
                    <div class="col-12 col-md-3">
                        <label class="form-label text-secondary fw-semibold small">Hareket Türü</label>
                        <select name="type" class="form-select border-0 shadow-sm rounded-3">
                            <option value="all">Tümü</option>
                            <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>Mal Alımı</option>
                            <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Satış</option>
                            <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Düzeltme/Sayım</option>
                            <option value="damage" {{ request('type') === 'damage' ? 'selected' : '' }}>Hasar/Fire</option>
                            <option value="consumption" {{ request('type') === 'consumption' ? 'selected' : '' }}>Hizmet Tüketimi</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label text-secondary fw-semibold small">Ürün</label>
                        <select name="product_id" class="form-select border-0 shadow-sm rounded-3">
                            <option value="">Tüm Ürünler</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label text-secondary fw-semibold small">Başlangıç</label>
                        <input type="date" name="date_from" class="form-control border-0 shadow-sm rounded-3" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label text-secondary fw-semibold small">Bitiş</label>
                        <input type="date" name="date_to" class="form-control border-0 shadow-sm rounded-3" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-12 col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary rounded-3 w-100 py-2 shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="ti ti-filter fs-5"></i> Filtrele
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Movements Table -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0">Tarih</th>
                                <th class="py-3 border-0">Ürün</th>
                                <th class="py-3 border-0">Tür</th>
                                <th class="py-3 border-0 text-center">Değişim</th>
                                <th class="py-3 border-0 text-center">Yeni Stok</th>
                                <th class="py-3 border-0 text-end">Birim Maliyet (₺)</th>
                                <th class="py-3 border-0">Not / Referans</th>
                                <th class="pe-4 py-3 border-0">Personel</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $movement)
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <div class="fw-semibold text-dark">{{ $movement->created_at->format('d.m.Y') }}</div>
                                    <small class="text-secondary">{{ $movement->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    @if($movement->product)
                                        <div class="fw-semibold text-dark">{{ $movement->product->name }}</div>
                                        <small class="text-secondary">SKU: {{ $movement->product->sku ?? '-' }}</small>
                                    @else
                                        <span class="text-danger small">Silinmiş Ürün</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($movement->movement_type)
                                        @case('purchase')
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill"><i class="ti ti-arrow-down"></i> Mal Alımı</span>
                                            @break
                                        @case('sale')
                                            <span class="badge bg-primary-subtle text-primary px-2 py-1 rounded-pill"><i class="ti ti-arrow-up"></i> Satış</span>
                                            @break
                                        @case('adjustment')
                                            <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill"><i class="ti ti-refresh"></i> Düzeltme</span>
                                            @break
                                        @case('damage')
                                            <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill"><i class="ti ti-trash"></i> Fire/Hasar</span>
                                            @break
                                        @case('consumption')
                                            <span class="badge bg-info-subtle text-info px-2 py-1 rounded-pill"><i class="ti ti-scissors"></i> Tüketim</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">{{ $movement->movement_type }}</span>
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    @if($movement->quantity > 0)
                                        <span class="text-success fw-bold">+{{ $movement->quantity }}</span>
                                    @elseif($movement->quantity < 0)
                                        <span class="text-danger fw-bold">{{ $movement->quantity }}</span>
                                    @else
                                        <span class="text-muted fw-bold">0</span>
                                    @endif
                                </td>
                                <td class="text-center fw-semibold text-dark">
                                    {{ $movement->after_stock }}
                                </td>
                                <td class="text-end text-muted">
                                    {{ $movement->unit_cost ? number_format($movement->unit_cost, 2, ',', '.') : '-' }}
                                </td>
                                <td>
                                    @if($movement->notes)
                                        <div class="small text-dark">{{ Str::limit($movement->notes, 30) }}</div>
                                    @endif
                                    @if($movement->reference_type)
                                        <small class="text-secondary opacity-75">Ref: {{ $movement->reference_type }} #{{ $movement->reference_id }}</small>
                                    @endif
                                    @if(!$movement->notes && !$movement->reference_type)
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="pe-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 28px; height: 28px; font-size: 11px;">
                                            {{ $movement->creator ? strtoupper(substr($movement->creator->first_name, 0, 1) . substr($movement->creator->last_name, 0, 1)) : 'S' }}
                                        </div>
                                        <div class="small fw-semibold">{{ $movement->creator ? $movement->creator->first_name : 'Sistem' }}</div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ti ti-transfer-in fs-1 mb-2 d-block text-secondary opacity-50"></i>
                                    <h5>Stok hareketi bulunamadı.</h5>
                                    <p class="small text-secondary mb-0">Belirtilen filtrelere uygun log kaydı yoktur.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($movements->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $movements->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
