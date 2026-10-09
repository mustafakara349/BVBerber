@extends('layouts.app')
@section('title', $product->name . ' - Ürün Detayı - B&V Barber')

@section('content')

{{-- Breadcrumb --}}
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none text-muted">Satış Ürünleri</a></li>
        <li class="breadcrumb-item active text-dark fw-semibold">{{ Str::limit($product->name, 40) }}</li>
    </ol>
</nav>

<div class="row g-4">

    {{-- ── Sol: Görsel & temel info ──────────────────────────────── --}}
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            {{-- Görsel --}}
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" class="card-img-top"
                     style="height: 260px; object-fit: cover;" alt="{{ $product->name }}">
            @else
                <div class="d-flex align-items-center justify-content-center"
                     style="height: 260px; background: linear-gradient(135deg, #f0f4ff, #dbeafe);">
                    <i class="ti ti-package text-muted" style="font-size: 5rem;"></i>
                </div>
            @endif

            <div class="card-body p-4">
                {{-- Durum --}}
                <div class="d-flex gap-2 mb-3">
                    @if($product->is_active)
                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">✓ Satışta</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">Pasif</span>
                    @endif
                    @if($product->productCategory)
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">{{ $product->productCategory->name }}</span>
                    @endif
                </div>

                <h1 class="fs-4 fw-bold text-dark mb-1">{{ $product->name }}</h1>

                @if($product->description)
                    <p class="text-muted small mb-3">{{ $product->description }}</p>
                @endif

                <hr class="my-3">

                <dl class="row small mb-0">
                    @if($product->sku)
                        <dt class="col-5 text-muted">SKU</dt>
                        <dd class="col-7 fw-semibold">{{ $product->sku }}</dd>
                    @endif
                    @if($product->barcode)
                        <dt class="col-5 text-muted">Barkod</dt>
                        <dd class="col-7 fw-semibold"><i class="ti ti-barcode text-muted"></i> {{ $product->barcode }}</dd>
                    @endif
                    <dt class="col-5 text-muted">Oluşturulma</dt>
                    <dd class="col-7">{{ $product->created_at->format('d.m.Y') }}</dd>
                    <dt class="col-5 text-muted">Güncelleme</dt>
                    <dd class="col-7">{{ $product->updated_at->format('d.m.Y H:i') }}</dd>
                </dl>
            </div>

            <div class="card-footer bg-white border-0 px-4 pb-4 d-flex gap-2">
                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary rounded-pill flex-fill">
                    <i class="ti ti-pencil me-1"></i> Düzenle
                </a>
                <form action="{{ route('products.destroy', $product) }}" method="POST"
                      onsubmit="return confirm('Bu ürünü silmek istediğinizden emin misiniz?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger rounded-pill px-3">
                        <i class="ti ti-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Sağ: Detay kartları ─────────────────────────────────────── --}}
    <div class="col-12 col-lg-8">
        <div class="row g-4">

            {{-- Fiyat Kartları --}}
            <div class="col-12">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100"
                             style="background: linear-gradient(135deg, #eff6ff, #dbeafe);">
                            <div class="text-primary mb-2"><i class="ti ti-receipt fs-3"></i></div>
                            <div class="fs-4 fw-bold text-primary">{{ number_format($product->purchase_price, 2, ',', '.') }} ₺</div>
                            <small class="text-muted">Alış Fiyatı</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100"
                             style="background: linear-gradient(135deg, #f0fdf4, #dcfce7);">
                            <div class="text-success mb-2"><i class="ti ti-tag fs-3"></i></div>
                            <div class="fs-4 fw-bold text-success">{{ number_format($product->sell_price, 2, ',', '.') }} ₺</div>
                            <small class="text-muted">Satış Fiyatı</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        @php
                            $margin = $product->purchase_price > 0
                                ? (($product->sell_price - $product->purchase_price) / $product->purchase_price) * 100
                                : 0;
                        @endphp
                        <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100"
                             style="background: linear-gradient(135deg, #fefce8, #fef9c3);">
                            <div class="text-warning mb-2"><i class="ti ti-trending-up fs-3"></i></div>
                            <div class="fs-4 fw-bold text-warning">%{{ number_format($margin, 1, ',', '.') }}</div>
                            <small class="text-muted">Kâr Marjı</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stok Bilgileri --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h6 class="fw-bold text-dark mb-4"><i class="ti ti-package text-primary me-2"></i> Stok Bilgileri</h6>
                    <div class="row g-3">
                        <div class="col-md-4 text-center">
                            @php
                                $stockClass = $product->stock_quantity <= 0
                                    ? 'text-danger' : ($product->stock_quantity <= ($product->critical_stock ?? 5)
                                    ? 'text-warning' : 'text-success');
                            @endphp
                            <div class="fs-1 fw-bold {{ $stockClass }}">{{ $product->stock_quantity }}</div>
                            <small class="text-muted">Mevcut Stok</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="fs-1 fw-bold text-secondary">{{ $product->critical_stock ?? 5 }}</div>
                            <small class="text-muted">Kritik Limit</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="fs-4 fw-bold text-info">{{ number_format($product->average_cost_price ?? 0, 2, ',', '.') }} ₺</div>
                            <small class="text-muted">Ortalama Maliyet</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">Stok doluluk oranı</span>
                            @php
                                $maxStock = max($product->stock_quantity, ($product->critical_stock ?? 5) * 3, 1);
                                $pct      = min(100, round($product->stock_quantity / $maxStock * 100));
                                $barColor = $product->stock_quantity <= 0 ? 'bg-danger' : ($product->stock_quantity <= ($product->critical_stock ?? 5) ? 'bg-warning' : 'bg-success');
                            @endphp
                            <span class="fw-semibold">{{ $pct }}%</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 8px;">
                            <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Son Hareketler (varsa) --}}
            @if($product->stockMovements && $product->stockMovements->count() > 0)
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="ti ti-history text-secondary me-2"></i> Son Stok Hareketleri</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="bg-light text-muted text-uppercase" style="font-size:0.75rem;">
                                <tr>
                                    <th class="ps-3 py-2 border-0">Tarih</th>
                                    <th class="py-2 border-0">Tür</th>
                                    <th class="py-2 border-0 text-center">Miktar</th>
                                    <th class="py-2 border-0">Açıklama</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->stockMovements->take(5) as $mov)
                                <tr class="border-bottom border-light">
                                    <td class="ps-3 py-2 small text-muted">{{ $mov->created_at->format('d.m.Y') }}</td>
                                    <td class="py-2">
                                        @if(in_array($mov->type, ['purchase','manual_in','adjustment_in']))
                                            <span class="badge bg-success-subtle text-success rounded-pill small">Giriş</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger rounded-pill small">Çıkış</span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-center fw-semibold">{{ $mov->quantity }}</td>
                                    <td class="py-2 small text-muted">{{ Str::limit($mov->notes, 40) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

@endsection
