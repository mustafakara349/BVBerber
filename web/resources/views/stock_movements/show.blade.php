@extends('layouts.app')
@section('title', 'Stok Hareketi Detayı - B&V Barber')

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Stok Hareketi Detayı</h1>
                <p class="text-muted mb-0">Hareketin tüm izleme bilgilerini inceleyin.</p>
            </div>
            <a href="{{ route('stock-movements.index') }}" class="btn btn-light rounded-pill px-4 shadow-sm fw-semibold flex-shrink-0">
                <i class="ti ti-arrow-left me-1"></i> Geri Dön
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Left column: Movement core info --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3
                        @switch($stockMovement->movement_type)
                            @case('purchase') bg-success-subtle text-success @break
                            @case('sale') bg-primary-subtle text-primary @break
                            @case('adjustment') bg-warning-subtle text-warning @break
                            @case('damage') bg-danger-subtle text-danger @break
                            @default bg-secondary-subtle text-secondary
                        @endswitch"
                        style="width:52px;height:52px;">
                        @switch($stockMovement->movement_type)
                            @case('purchase') <i class="ti ti-arrow-down fs-3"></i> @break
                            @case('sale') <i class="ti ti-arrow-up fs-3"></i> @break
                            @case('adjustment') <i class="ti ti-refresh fs-3"></i> @break
                            @case('damage') <i class="ti ti-trash fs-3"></i> @break
                            @default <i class="ti ti-transfer fs-3"></i>
                        @endswitch
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Hareket Bilgileri</h5>
                        <small class="text-muted">#{{ $stockMovement->id }}</small>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-secondary small fw-semibold mb-1">Hareket Türü</div>
                    @switch($stockMovement->movement_type)
                        @case('purchase')
                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fs-6">Mal Alımı</span>
                            @break
                        @case('sale')
                            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fs-6">Satış</span>
                            @break
                        @case('adjustment')
                            <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fs-6">Düzeltme/Sayım</span>
                            @break
                        @case('damage')
                            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fs-6">Fire/Hasar</span>
                            @break
                        @case('consumption')
                            <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill fs-6">Hizmet Tüketimi</span>
                            @break
                        @default
                            <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fs-6">{{ $stockMovement->movement_type }}</span>
                    @endswitch
                </div>

                <hr class="border-light">

                <div class="mb-3">
                    <div class="text-secondary small fw-semibold mb-1">Tarih ve Saat</div>
                    <div class="fw-semibold text-dark">{{ $stockMovement->created_at->format('d.m.Y') }}</div>
                    <small class="text-muted">{{ $stockMovement->created_at->format('H:i:s') }}</small>
                </div>

                <div class="mb-3">
                    <div class="text-secondary small fw-semibold mb-1">İşlemi Yapan Personel</div>
                    @if($stockMovement->creator)
                        <div class="fw-semibold text-dark">{{ $stockMovement->creator->first_name }} {{ $stockMovement->creator->last_name }}</div>
                        <small class="text-muted">{{ $stockMovement->creator->email }}</small>
                    @else
                        <span class="text-muted small">Sistem (Otomatik)</span>
                    @endif
                </div>

                @if($stockMovement->notes)
                <div class="mb-3">
                    <div class="text-secondary small fw-semibold mb-1">Notlar / Açıklama</div>
                    <div class="text-dark fst-italic bg-light p-2 rounded-3 small">{{ $stockMovement->notes }}</div>
                </div>
                @endif

                @if($stockMovement->reference_type)
                <div>
                    <div class="text-secondary small fw-semibold mb-1">Referans</div>
                    @php
                        $refLink = null;
                        if ($stockMovement->reference_type === 'product_sales') {
                            $refLink = route('products.sales.show', $stockMovement->reference_id);
                        } elseif ($stockMovement->reference_type === 'purchase_orders') {
                            $refLink = route('purchase-orders.show', $stockMovement->reference_id);
                        } elseif ($stockMovement->reference_type === 'stock_counts') {
                            $refLink = route('stock-counts.show', $stockMovement->reference_id);
                        }
                    @endphp
                    @if($refLink)
                        <a href="{{ $refLink }}" class="badge bg-primary text-white px-3 py-2 rounded-pill text-decoration-none fw-semibold">
                            <i class="ti ti-external-link me-1"></i>{{ $stockMovement->reference_type }} #{{ $stockMovement->reference_id }}
                        </a>
                    @else
                        <code class="text-primary small">{{ $stockMovement->reference_type }} #{{ $stockMovement->reference_id }}</code>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Middle column: Stock change --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="ti ti-chart-bar"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark">Stok Değişimi</h5>
                </div>

                <div class="text-center py-3">
                    <div class="fs-6 text-muted mb-1">Önceki Stok</div>
                    <div class="display-6 fw-bold text-secondary">{{ $stockMovement->before_stock }}</div>
                </div>

                <div class="text-center py-2">
                    @if($stockMovement->quantity > 0)
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fs-5 fw-bold">
                            <i class="ti ti-arrow-down me-1"></i>+{{ $stockMovement->quantity }}
                        </span>
                        <div class="text-muted small mt-1">stok girişi</div>
                    @elseif($stockMovement->quantity < 0)
                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fs-5 fw-bold">
                            <i class="ti ti-arrow-up me-1"></i>{{ $stockMovement->quantity }}
                        </span>
                        <div class="text-muted small mt-1">stok çıkışı</div>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fs-5">0</span>
                        <div class="text-muted small mt-1">değişim yok</div>
                    @endif
                </div>

                <div class="text-center py-3">
                    <div class="fs-6 text-muted mb-1">Sonraki Stok</div>
                    <div class="display-6 fw-bold text-dark">{{ $stockMovement->after_stock }}</div>
                </div>

                @if($stockMovement->unit_cost)
                <hr class="border-light">
                <div class="text-center">
                    <div class="text-muted small mb-1">Birim Maliyet / Fiyat</div>
                    <div class="fs-5 fw-bold text-dark">₺{{ number_format($stockMovement->unit_cost, 2, ',', '.') }}</div>
                    <div class="text-muted small mt-1">
                        Toplam: ₺{{ number_format($stockMovement->unit_cost * abs($stockMovement->quantity), 2, ',', '.') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right column: Product info + optional sale info --}}
    <div class="col-md-4">
        {{-- Product Card --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="ti ti-box"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark">Ürün Bilgileri</h5>
                </div>

                @if($stockMovement->product)
                    <div class="fw-bold text-dark fs-6 mb-1">{{ $stockMovement->product->name }}</div>
                    @if($stockMovement->product->sku)
                        <div class="text-muted small"><i class="ti ti-barcode me-1"></i>SKU: {{ $stockMovement->product->sku }}</div>
                    @endif
                    @if($stockMovement->product->barcode)
                        <div class="text-muted small"><i class="ti ti-scan me-1"></i>Barkod: {{ $stockMovement->product->barcode }}</div>
                    @endif
                    @if($stockMovement->product->productCategory)
                        <div class="text-muted small mt-1"><i class="ti ti-folder me-1"></i>{{ $stockMovement->product->productCategory->name }}</div>
                    @endif
                    <hr class="border-light my-3">
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Mevcut Stok</span>
                        <span class="fw-bold text-dark">{{ $stockMovement->product->stock_quantity }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mt-1">
                        <span class="text-muted">Satış Fiyatı</span>
                        <span class="fw-bold text-success">₺{{ number_format($stockMovement->product->sell_price, 2, ',', '.') }}</span>
                    </div>
                @else
                    <span class="text-danger small">Ürün silinmiş ya da bulunamadı.</span>
                @endif
            </div>
        </div>

        {{-- Referenced Sale (if exists) --}}
        @if($referencedSale)
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="ti ti-receipt"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark">İlgili Satış</h5>
                </div>

                <div class="mb-2">
                    <div class="text-muted small">Satış Kodu</div>
                    <a href="{{ route('products.sales.show', $referencedSale->id) }}" class="badge bg-primary text-white px-3 py-2 rounded-pill text-decoration-none fw-semibold">
                        <i class="ti ti-external-link me-1"></i>{{ $referencedSale->sale_code }}
                    </a>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Müşteri</div>
                    <div class="fw-semibold text-dark">{{ $referencedSale->customer ? $referencedSale->customer->full_name : 'Kayıtsız Müşteri' }}</div>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Satan Personel</div>
                    <div class="fw-semibold text-dark">{{ $referencedSale->seller ? $referencedSale->seller->full_name : 'Sistem' }}</div>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Ödeme Yöntemi</div>
                    <span class="badge bg-dark text-white rounded-pill px-3">
                        @switch($referencedSale->payment_method)
                            @case('cash') Nakit @break
                            @case('credit_card') Kredi Kartı @break
                            @case('bank_transfer') Havale/EFT @break
                            @default {{ $referencedSale->payment_method }}
                        @endswitch
                    </span>
                </div>
                <hr class="border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Toplam Tutar</span>
                    <span class="fs-5 fw-bold text-success">₺{{ number_format($referencedSale->total_price, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
