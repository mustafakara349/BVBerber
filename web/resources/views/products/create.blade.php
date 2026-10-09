@extends('layouts.app')
@section('title', 'Yeni Ürün Ekle - B&V Barber')

@push('styles')
<style>
    .form-tab-nav .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6b7280;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 12px 20px;
        transition: all 0.15s;
        background: none;
    }
    .form-tab-nav .nav-link:hover   { color: #E66239; }
    .form-tab-nav .nav-link.active  { color: #E66239; border-bottom-color: #E66239; background: none; }
    .img-upload-box {
        border: 2px dashed #d1d5db;
        border-radius: 16px;
        padding: 32px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #fafbfc;
    }
    .img-upload-box:hover  { border-color: #E66239; background: #fff9f7; }
    .img-preview-area      { display: none; }
    .img-preview-area img  { max-height: 200px; object-fit: contain; border-radius: 12px; }
    .step-indicator {
        display: flex; gap: 8px; align-items: center;
        color: #9ca3af; font-size: 0.8rem;
    }
    .step-indicator .dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #e5e7eb;
        transition: background 0.2s;
    }
    .step-indicator .dot.active { background: #E66239; }
</style>
@endpush

@section('content')

{{-- Breadcrumb --}}
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none text-muted">Satış Ürünleri</a></li>
        <li class="breadcrumb-item active text-dark fw-semibold">Yeni Ürün Ekle</li>
    </ol>
</nav>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            {{-- Card Header --}}
            <div class="card-header border-0 p-4 pb-0" style="background: linear-gradient(135deg, #E66239 0%, #d4522c 100%);">
                <div class="d-flex align-items-center gap-3 pb-4">
                    <div class="rounded-3 p-2 bg-white bg-opacity-25">
                        <i class="ti ti-box text-white fs-4"></i>
                    </div>
                    <div>
                        <h2 class="fw-bold text-white mb-0 fs-5">Yeni Ürün Ekle</h2>
                        <p class="text-white-50 small mb-0">Tüm bilgileri eksiksiz doldurun.</p>
                    </div>
                </div>
                {{-- Tab Nav --}}
                <ul class="nav form-tab-nav" id="productTabs" role="tablist"
                    style="border-bottom: none;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-white" id="tab-basic" data-bs-toggle="tab"
                                data-bs-target="#pane-basic" type="button"
                                style="border-bottom-color: white;" role="tab">
                            <i class="ti ti-info-circle me-1"></i> Temel Bilgiler
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-white-50" id="tab-stock" data-bs-toggle="tab"
                                data-bs-target="#pane-stock" type="button" role="tab">
                            <i class="ti ti-package me-1"></i> Stok & Fiyat
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-white-50" id="tab-detail" data-bs-toggle="tab"
                                data-bs-target="#pane-detail" type="button" role="tab">
                            <i class="ti ti-photo me-1"></i> Detay & Görsel
                        </button>
                    </li>
                </ul>
            </div>

            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" id="createProductForm">
                @csrf
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger border-0 rounded-3 mb-4">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="tab-content" id="productTabContent">

                        {{-- ── TAB 1: Temel Bilgiler ───────────────────────── --}}
                        <div class="tab-pane fade show active" id="pane-basic" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Ürün Adı <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control border-0 bg-light rounded-3 @error('name') is-invalid @enderror"
                                           placeholder="Örn: Argan Yağı Şampuanı" value="{{ old('name') }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Kategori</label>
                                    <select name="product_category_id" class="form-select border-0 bg-light rounded-3">
                                        <option value="">— Kategori Seç —</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('product_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">SKU (İç Kod)</label>
                                    <input type="text" name="sku" class="form-control border-0 bg-light rounded-3"
                                           placeholder="Örn: SKU-001" value="{{ old('sku') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Barkod</label>
                                    <input type="text" name="barcode" class="form-control border-0 bg-light rounded-3"
                                           placeholder="Barkod no" value="{{ old('barcode') }}">
                                </div>
                            </div>
                        </div>

                        {{-- ── TAB 2: Stok & Fiyat ────────────────────────── --}}
                        <div class="tab-pane fade" id="pane-stock" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Alış Fiyatı (₺) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light">₺</span>
                                        <input type="number" step="0.01" min="0" name="purchase_price"
                                               class="form-control border-0 bg-light @error('purchase_price') is-invalid @enderror"
                                               value="{{ old('purchase_price', '0.00') }}" required>
                                        @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Satış Fiyatı (₺) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light">₺</span>
                                        <input type="number" step="0.01" min="0" name="sell_price"
                                               class="form-control border-0 bg-light @error('sell_price') is-invalid @enderror"
                                               value="{{ old('sell_price', '0.00') }}" required>
                                        @error('sell_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Stok Miktarı <span class="text-danger">*</span></label>
                                    <input type="number" step="1" min="0" name="stock_quantity"
                                           class="form-control border-0 bg-light rounded-3 @error('stock_quantity') is-invalid @enderror"
                                           value="{{ old('stock_quantity', 0) }}" required>
                                    @error('stock_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Kritik Stok Limiti</label>
                                    <input type="number" step="1" min="0" name="critical_stock"
                                           class="form-control border-0 bg-light rounded-3"
                                           value="{{ old('critical_stock', 5) }}"
                                           title="Bu miktarın altına düştüğünde uyarı verilir.">
                                    <small class="text-muted">Bu miktarın altı kırmızıyla gösterilir.</small>
                                </div>
                                <div class="col-md-4 d-flex align-items-center pt-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="isActive">Satışa Açık</label>
                                    </div>
                                </div>
                                {{-- Kâr hesaplayıcı --}}
                                <div class="col-12">
                                    <div class="alert alert-light border rounded-3 d-flex align-items-center gap-3 py-2" id="profitAlert" style="display:none!important;">
                                        <i class="ti ti-calculator text-primary fs-5"></i>
                                        <span class="small">Tahmini Kâr Marjı: <strong id="profitMargin" class="text-success">—</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── TAB 3: Detay & Görsel ──────────────────────── --}}
                        <div class="tab-pane fade" id="pane-detail" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Ürün Görseli</label>
                                    <div class="img-upload-box" id="imgDropBox" onclick="document.getElementById('imageInput').click()">
                                        <div class="mb-2 text-muted">
                                            <i class="ti ti-photo" style="font-size: 2.5rem;"></i>
                                        </div>
                                        <p class="text-muted mb-1 fw-semibold">Görsel Yükle</p>
                                        <small class="text-muted">JPG, PNG, WEBP — Maks 2MB</small>
                                    </div>
                                    <div class="img-preview-area mt-2 text-center" id="imgPreviewArea">
                                        <img id="imgPreview" src="" alt="Önizleme" class="w-100">
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 rounded-pill"
                                                onclick="clearImage()">
                                            <i class="ti ti-x me-1"></i> Görseli Kaldır
                                        </button>
                                    </div>
                                    <input type="file" id="imageInput" name="image" accept="image/*" class="d-none">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label fw-semibold">Ürün Açıklaması</label>
                                    <textarea name="description" rows="5" class="form-control border-0 bg-light rounded-3"
                                              placeholder="Ürün hakkında detaylı açıklama, kullanım alanı, özellikler...">{{ old('description') }}</textarea>
                                    <small class="text-muted">Bu açıklama liste ekranında kısa kırpılarak gösterilir.</small>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /tab-content --}}
                </div>

                <div class="card-footer bg-white border-0 px-4 pb-4 pt-0 d-flex justify-content-between align-items-center">
                    <a href="{{ route('products.index') }}" class="btn btn-light rounded-pill px-4">
                        <i class="ti ti-arrow-left me-1"></i> Geri
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                        <i class="ti ti-device-floppy me-1"></i> Ürünü Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Yardım / İpuçları ───────────────────────────────────── --}}
    <div class="col-12 col-lg-4 mt-4 mt-lg-0">
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-3">
            <h6 class="fw-bold text-dark mb-3"><i class="ti ti-bulb text-warning me-2"></i> İpuçları</h6>
            <ul class="list-unstyled small text-muted mb-0" style="line-height: 2;">
                <li><i class="ti ti-box text-primary me-1"></i> <strong>SKU:</strong> Kendi iç kodlama sisteminiz için kullanın.</li>
                <li><i class="ti ti-barcode text-primary me-1"></i> <strong>Barkod:</strong> Ürün kutusundaki barkodu girin.</li>
                <li><i class="ti ti-alert-triangle text-warning me-1"></i> <strong>Kritik Stok:</strong> Ürün bu miktarın altına düştüğünde listede sarı uyarı gösterilir.</li>
                <li><i class="ti ti-photo text-info me-1"></i> <strong>Görsel:</strong> Netliği yüksek bir kare görsel yükleyin.</li>
            </ul>
        </div>
        <div class="card border-0 rounded-4 p-4" style="background: linear-gradient(135deg, #eff6ff, #dbeafe);">
            <h6 class="fw-bold text-primary mb-2"><i class="ti ti-calculator me-1"></i> Kâr Hesaplama</h6>
            <p class="small text-muted mb-0">Alış ve satış fiyatını girdikten sonra "Stok & Fiyat" sekmesinde otomatik kâr marjını görebilirsiniz.</p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Tab active state for header styling ────────────────────────
    const tabBtns = document.querySelectorAll('#productTabs .nav-link');
    tabBtns.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function () {
            tabBtns.forEach(b => {
                b.style.borderBottomColor = '';
                b.classList.add('text-white-50');
                b.classList.remove('text-white');
            });
            this.style.borderBottomColor = 'white';
            this.classList.remove('text-white-50');
            this.classList.add('text-white');
        });
    });

    // ── Image preview ────────────────────────────────────────────────
    const imageInput = document.getElementById('imageInput');
    const imgPreview = document.getElementById('imgPreview');
    const previewArea = document.getElementById('imgPreviewArea');
    const dropBox     = document.getElementById('imgDropBox');

    imageInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            imgPreview.src = e.target.result;
            previewArea.style.display = 'block';
            dropBox.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });

    // Sürükle bırak
    ['dragenter','dragover'].forEach(ev => {
        dropBox.addEventListener(ev, e => { e.preventDefault(); dropBox.style.borderColor = '#E66239'; });
    });
    dropBox.addEventListener('dragleave', () => { dropBox.style.borderColor = ''; });
    dropBox.addEventListener('drop', e => {
        e.preventDefault();
        dropBox.style.borderColor = '';
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            imageInput.files = files;
            imageInput.dispatchEvent(new Event('change'));
        }
    });

    // ── Kâr Hesaplayıcı ─────────────────────────────────────────────
    const purchaseInput = document.querySelector('[name=purchase_price]');
    const sellInput     = document.querySelector('[name=sell_price]');
    const profitAlert   = document.getElementById('profitAlert');
    const profitMargin  = document.getElementById('profitMargin');

    function calcProfit() {
        const purchase = parseFloat(purchaseInput?.value) || 0;
        const sell     = parseFloat(sellInput?.value) || 0;
        if (purchase > 0 && sell > 0) {
            const margin = ((sell - purchase) / purchase * 100).toFixed(1);
            const profit = (sell - purchase).toFixed(2);
            profitMargin.textContent = `%${margin} (${profit} ₺)`;
            profitMargin.className = margin >= 0 ? 'text-success' : 'text-danger';
            profitAlert.style.removeProperty('display');
        }
    }

    purchaseInput?.addEventListener('input', calcProfit);
    sellInput?.addEventListener('input', calcProfit);
});

function clearImage() {
    document.getElementById('imageInput').value = '';
    document.getElementById('imgPreview').src = '';
    document.getElementById('imgPreviewArea').style.display = 'none';
    document.getElementById('imgDropBox').style.display = 'block';
}
</script>
@endpush
