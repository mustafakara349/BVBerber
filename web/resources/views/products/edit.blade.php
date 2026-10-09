@extends('layouts.app')
@section('title', 'Ürün Düzenle - ' . $product->name . ' - B&V Barber')

@push('styles')
<style>
    .form-tab-nav .nav-link {
        border: none; border-bottom: 3px solid transparent;
        color: #6b7280; font-weight: 600; font-size: 0.9rem;
        padding: 12px 20px; transition: all 0.15s; background: none;
    }
    .form-tab-nav .nav-link:hover   { color: #3b82f6; }
    .form-tab-nav .nav-link.active  { color: #3b82f6; border-bottom-color: #3b82f6; background: none; }
    .img-upload-box {
        border: 2px dashed #d1d5db; border-radius: 16px;
        padding: 24px 16px; text-align: center; cursor: pointer;
        transition: all 0.2s; background: #fafbfc;
    }
    .img-upload-box:hover { border-color: #3b82f6; background: #eff6ff; }
    .current-img { max-height: 180px; object-fit: contain; border-radius: 12px; }
</style>
@endpush

@section('content')

{{-- Breadcrumb --}}
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none text-muted">Satış Ürünleri</a></li>
        <li class="breadcrumb-item"><a href="{{ route('products.show', $product) }}" class="text-decoration-none text-muted">{{ Str::limit($product->name, 30) }}</a></li>
        <li class="breadcrumb-item active text-dark fw-semibold">Düzenle</li>
    </ol>
</nav>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            {{-- Header --}}
            <div class="card-header border-0 p-4 pb-0" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <div class="d-flex align-items-center gap-3 pb-4">
                    <div class="rounded-3 p-2 bg-white bg-opacity-25">
                        <i class="ti ti-pencil text-white fs-4"></i>
                    </div>
                    <div>
                        <h2 class="fw-bold text-white mb-0 fs-5">Ürünü Düzenle</h2>
                        <p class="text-white-50 small mb-0">{{ $product->name }}</p>
                    </div>
                </div>
                <ul class="nav form-tab-nav" id="editProductTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active text-white" data-bs-toggle="tab" data-bs-target="#epane-basic" type="button">
                            <i class="ti ti-info-circle me-1"></i> Temel Bilgiler
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link text-white-50" data-bs-toggle="tab" data-bs-target="#epane-stock" type="button">
                            <i class="ti ti-package me-1"></i> Stok & Fiyat
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link text-white-50" data-bs-toggle="tab" data-bs-target="#epane-detail" type="button">
                            <i class="ti ti-photo me-1"></i> Detay & Görsel
                        </button>
                    </li>
                </ul>
            </div>

            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger border-0 rounded-3 mb-4">
                            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <div class="tab-content">

                        {{-- TAB 1 --}}
                        <div class="tab-pane fade show active" id="epane-basic">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Ürün Adı <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control border-0 bg-light rounded-3"
                                           value="{{ old('name', $product->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Kategori</label>
                                    <select name="product_category_id" class="form-select border-0 bg-light rounded-3">
                                        <option value="">— Kategori Seç —</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('product_category_id', $product->product_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">SKU</label>
                                    <input type="text" name="sku" class="form-control border-0 bg-light rounded-3"
                                           value="{{ old('sku', $product->sku) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Barkod</label>
                                    <input type="text" name="barcode" class="form-control border-0 bg-light rounded-3"
                                           value="{{ old('barcode', $product->barcode) }}">
                                </div>
                            </div>
                        </div>

                        {{-- TAB 2 --}}
                        <div class="tab-pane fade" id="epane-stock">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Alış Fiyatı (₺) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light">₺</span>
                                        <input type="number" step="0.01" min="0" name="purchase_price"
                                               class="form-control border-0 bg-light"
                                               value="{{ old('purchase_price', $product->purchase_price) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Satış Fiyatı (₺) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light">₺</span>
                                        <input type="number" step="0.01" min="0" name="sell_price"
                                               class="form-control border-0 bg-light"
                                               value="{{ old('sell_price', $product->sell_price) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Stok Miktarı <span class="text-danger">*</span></label>
                                    <input type="number" step="1" min="0" name="stock_quantity"
                                           class="form-control border-0 bg-light rounded-3"
                                           value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Kritik Stok Limiti</label>
                                    <input type="number" step="1" min="0" name="critical_stock"
                                           class="form-control border-0 bg-light rounded-3"
                                           value="{{ old('critical_stock', $product->critical_stock) }}">
                                </div>
                                <div class="col-md-4 d-flex align-items-center pt-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive" value="1"
                                               {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="editIsActive">Satışa Açık</label>
                                    </div>
                                </div>
                                {{-- Stok istatistikleri --}}
                                <div class="col-12">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <div class="rounded-3 p-3 text-center" style="background:#f0fdf4;">
                                                <div class="fw-bold text-success fs-5">{{ number_format($product->average_cost_price ?? 0, 2, ',', '.') }} ₺</div>
                                                <small class="text-muted">Ortalama Maliyet</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="rounded-3 p-3 text-center" style="background:#eff6ff;">
                                                <div class="fw-bold text-primary fs-5">{{ number_format($product->last_purchase_price ?? 0, 2, ',', '.') }} ₺</div>
                                                <small class="text-muted">Son Alış Fiyatı</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="rounded-3 p-3 text-center" style="background:#fef9c3;">
                                                <div class="fw-bold text-warning fs-5">{{ $product->stock_quantity }} Adet</div>
                                                <small class="text-muted">Mevcut Stok</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 3 --}}
                        <div class="tab-pane fade" id="epane-detail">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Ürün Görseli</label>
                                    @if($product->image)
                                        <div class="text-center mb-3 p-3 bg-light rounded-3">
                                            <img src="{{ asset('storage/'.$product->image) }}" class="current-img mb-2 w-100" alt="{{ $product->name }}">
                                            <small class="text-muted d-block">Mevcut görsel. Değiştirmek için yeni dosya seçin.</small>
                                        </div>
                                    @endif
                                    <div class="img-upload-box" onclick="document.getElementById('editImageInput').click()">
                                        <div class="mb-2 text-muted">
                                            <i class="ti ti-photo" style="font-size: 2.5rem;"></i>
                                        </div>
                                        <p class="text-muted small mb-0 mt-1">{{ $product->image ? 'Yeni görsel yükle (opsiyonel)' : 'Görsel yükle' }}</p>
                                        <small class="text-muted">JPG, PNG, WEBP — Maks 2MB</small>
                                    </div>
                                    <div class="text-center mt-2 d-none" id="editImgPreviewWrap">
                                        <img id="editImgPreview" src="" alt="" class="w-100 rounded-3" style="max-height:150px;object-fit:contain;">
                                        <small class="text-success d-block mt-1">✓ Yeni görsel seçildi</small>
                                    </div>
                                    <input type="file" id="editImageInput" name="image" accept="image/*" class="d-none">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label fw-semibold">Ürün Açıklaması</label>
                                    <textarea name="description" rows="6" class="form-control border-0 bg-light rounded-3"
                                              placeholder="Detaylı açıklama, kullanım bilgisi...">{{ old('description', $product->description) }}</textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card-footer bg-white border-0 px-4 pb-4 pt-0 d-flex justify-content-between">
                    <a href="{{ route('products.show', $product) }}" class="btn btn-light rounded-pill px-4">
                        <i class="ti ti-arrow-left me-1"></i> Geri
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                        <i class="ti ti-device-floppy me-1"></i> Değişiklikleri Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sağ Panel: Özet --}}
    <div class="col-12 col-lg-4 mt-4 mt-lg-0">
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-3">
            <h6 class="fw-bold text-dark mb-3"><i class="ti ti-info-circle text-primary me-2"></i> Ürün Özeti</h6>
            <dl class="row small mb-0">
                <dt class="col-5 text-muted">Oluşturma</dt>
                <dd class="col-7 fw-semibold">{{ $product->created_at->format('d.m.Y') }}</dd>
                <dt class="col-5 text-muted">Son Güncelleme</dt>
                <dd class="col-7 fw-semibold">{{ $product->updated_at->format('d.m.Y H:i') }}</dd>
                <dt class="col-5 text-muted">Stok Durumu</dt>
                <dd class="col-7">
                    @if($product->stock_quantity <= 0)
                        <span class="badge bg-danger-subtle text-danger rounded-pill">Tükendi</span>
                    @elseif($product->stock_quantity <= ($product->critical_stock ?? 5))
                        <span class="badge bg-warning-subtle text-warning rounded-pill">Kritik</span>
                    @else
                        <span class="badge bg-success-subtle text-success rounded-pill">Yeterli</span>
                    @endif
                </dd>
            </dl>
        </div>
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h6 class="fw-bold text-dark mb-3"><i class="ti ti-tools text-secondary me-2"></i> Hızlı İşlemler</h6>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('products.show', $product) }}" class="btn btn-light rounded-3 text-start">
                    <i class="ti ti-eye text-primary me-2"></i> Ürün Detayı
                </a>
                <form action="{{ route('products.destroy', $product) }}" method="POST"
                      onsubmit="return confirm('Bu ürünü silmek istediğinizden emin misiniz?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-light text-danger rounded-3 w-100 text-start">
                        <i class="ti ti-trash me-2"></i> Ürünü Sil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('editImageInput').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('editImgPreview').src = e.target.result;
            document.getElementById('editImgPreviewWrap').classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endpush
