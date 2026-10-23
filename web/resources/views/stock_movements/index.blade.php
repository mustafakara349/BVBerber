@extends('layouts.app')
@section('title', 'Stok Hareketleri - Stok & Tedarik - B&V Barber')
@section('content')

<x-page-header title="Stok Hareketleri" description="Tüm ürün giriş, çıkış ve düzeltme loglarını inceleyin.">
    <x-btn-outline href="{{ route('products.index') }}" icon="ti ti-arrow-left">
        Ürünlere Dön
    </x-btn-outline>
</x-page-header>

{{-- Filters --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
            <form action="{{ route('stock-movements.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <x-form-select name="type" label="Hareket Türü" :pill="true">
                        <option value="all">Tüm Hareketler</option>
                        <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>Mal Alımı</option>
                        <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Satış</option>
                        <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Düzeltme/Sayım</option>
                        <option value="damage" {{ request('type') === 'damage' ? 'selected' : '' }}>Hasar/Fire</option>
                        <option value="consumption" {{ request('type') === 'consumption' ? 'selected' : '' }}>Hizmet Tüketimi</option>
                    </x-form-select>
                </div>

                <div class="col-12 col-md-3">
                    <x-form-select name="product_id" label="Ürün" :pill="true">
                        <option value="">Tüm Ürünler</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </x-form-select>
                </div>

                <div class="col-6 col-md-2">
                    <x-date-input name="date_from" label="Başlangıç" :value="request('date_from')" :pill="true" />
                </div>

                <div class="col-6 col-md-2">
                    <x-date-input name="date_to" label="Bitiş" :value="request('date_to')" :pill="true" />
                </div>

                <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-1 fw-medium" style="height: 38px;">
                        <i class="ti ti-filter"></i> Filtrele
                    </button>
                    @if(request()->hasAny(['type', 'product_id', 'date_from', 'date_to']) && (request('type') !== 'all' || request('product_id') || request('date_from') || request('date_to')))
                        <a href="{{ route('stock-movements.index') }}" class="btn btn-light rounded-pill px-2 shadow-sm d-inline-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="height: 38px; width: 38px;" title="Filtreleri Temizle">
                            <i class="ti ti-rotate"></i>
                        </a>
                    @endif
                </div>
            </form>
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
                            <tr class="border-bottom border-light clickable-row" data-href="{{ route('stock-movements.show', $movement) }}" title="Detayı Görüntüle">
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
                            <x-empty-state icon="ti ti-transfer-in" title="Stok hareketi bulunamadı" message="Belirtilen filtrelere uygun hareket log kaydı bulunmamaktadır." colspan="8" />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($movements->hasPages())
                <div class="card-footer bg-white border-top border-light-subtle py-3 px-4">
                    {{ $movements->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
