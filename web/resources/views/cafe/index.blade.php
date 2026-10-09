@extends('layouts.app')
@section('title', 'Cafe Bölümü Yönetimi - B&V Barber')

@push('styles')
<style>
    /* ── Cafe table list ─────────────────────────────────── */
    .cafe-img-thumb {
        width: 52px;
        height: 52px;
        object-fit: cover;
        border-radius: 10px;
        border: 2px solid #f0f0f0;
    }
    .cafe-img-placeholder {
        width: 52px;
        height: 52px;
        border-radius: 10px;
        background: linear-gradient(135deg, #fff3ef, #fde8df);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
    .action-icon {
        width: 34px; height: 34px;
        border-radius: 10px;
        border: 1px solid #e8e8e8;
        background: #fff;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.15s;
        text-decoration: none;
    }
    .action-icon:hover { background: #f5f5f5; }
    .action-icon.edit:hover  { border-color: #3b82f6; color: #3b82f6; }
    .action-icon.del:hover   { border-color: #ef4444; color: #ef4444; }
    /* Category filter pills */
    .category-pill {
        display: inline-block;
        padding: 6px 16px;
        border-radius: 30px;
        border: 1px solid #e0e0e0;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.15s;
        text-decoration: none;
        color: #555;
        background: #fff;
    }
    .category-pill:hover, .category-pill.active {
        background: #E66239;
        border-color: #E66239;
        color: #fff !important;
    }
    /* Image upload preview */
    .img-upload-area {
        border: 2px dashed #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.15s;
        background: #fafafa;
    }
    .img-upload-area:hover { border-color: #E66239; }
    .img-preview {
        max-height: 140px;
        object-fit: contain;
        border-radius: 8px;
        display: none;
    }
</style>
@endpush

@section('content')

{{-- ── Header ──────────────────────────────────────────── --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="fs-2">☕</span>
                    <h1 class="fs-3 fw-bold mb-0 text-dark">Cafe Bölümü</h1>
                </div>
                <p class="text-muted mb-0">Cafe menüsündeki tüm ürünleri görsel ve fiyatlarıyla yönetin.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm d-flex align-items-center gap-2"
                        data-bs-toggle="modal" data-bs-target="#cafeCategoriesModal">
                    <i class="ti ti-category fs-5"></i> Kategorileri Yönet
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm d-flex align-items-center gap-2"
                        data-bs-toggle="modal" data-bs-target="#addCafeModal" id="addCafeBtn">
                    <i class="ti ti-plus fs-5"></i> Yeni Ürün Ekle
                </button>
            </div>
        </div>
    </div>
</div>


@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" role="alert">
        <strong>Hata!</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ── Filter / Search ──────────────────────────────────── --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('cafe.index') }}" method="GET" class="d-flex flex-wrap gap-2 align-items-center">
            {{-- Kategori Pills --}}
            <a href="{{ route('cafe.index', array_merge(request()->except('category'), [])) }}"
               class="category-pill {{ !request('category') ? 'active' : '' }}">Tümü</a>
            @foreach($categories as $category)
                <a href="{{ route('cafe.index', array_merge(request()->except('category'), ['category' => $category->id])) }}"
                   class="category-pill {{ request('category') == $category->id ? 'active' : '' }}">{{ $category->name }}</a>
            @endforeach
            <div class="ms-auto d-flex gap-2">
                <input type="text" name="search" class="form-control border-0 bg-light rounded-3"
                       placeholder="Ürün ara..." value="{{ request('search') }}" style="width: 200px;">
                <select name="status" class="form-select border-0 bg-light rounded-3" style="width: 140px;">
                    <option value="">Tüm Durumlar</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Pasif</option>
                    <option value="featured" {{ request('status') === 'featured' ? 'selected' : '' }}>Öne Çıkan</option>
                </select>
                <button type="submit" class="btn btn-primary rounded-3 px-3">
                    <i class="ti ti-search"></i>
                </button>
                @if(request()->hasAny(['search','category','status']))
                    <a href="{{ route('cafe.index') }}" class="btn btn-light rounded-3 px-3">
                        <i class="ti ti-x"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Cafe Tablosu ────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-dark">
                <thead class="bg-light text-secondary">
                    <tr>
                        <th class="ps-4 py-3 border-0" style="min-width: 240px;">Ürün</th>
                        <th class="py-3 border-0">Kategori</th>
                        <th class="py-3 border-0 text-center">Öne Çıkan</th>
                        <th class="py-3 border-0 text-end">Fiyat</th>
                        <th class="py-3 border-0 text-center">Durum</th>
                        <th class="pe-4 py-3 border-0 text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cafeProducts as $item)
                    <tr class="border-bottom border-light" id="cafe-row-{{ $item->id }}">
                        {{-- Ürün Adı + Görsel --}}
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                @if($item->image_path)
                                    <img src="{{ asset('storage/' . $item->image_path) }}"
                                         class="cafe-img-thumb" alt="{{ $item->name }}">
                                @else
                                    <div class="cafe-img-placeholder">☕</div>
                                @endif
                                <div>
                                    <div class="fw-semibold text-dark">{{ $item->name }}</div>
                                    @if($item->description)
                                        <small class="text-muted" style="display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;">
                                            {{ $item->description }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        {{-- Kategori --}}
                        <td>
                            @if($item->cafeCategory)
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1" style="font-size:0.75rem;">
                                    {{ $item->cafeCategory->name }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        {{-- Öne Çıkan --}}
                        <td class="text-center">
                            @if($item->is_featured)
                                <span class="badge bg-warning text-dark rounded-pill" style="font-size:0.75rem;"><i class="ti ti-star-filled"></i> Evet</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        {{-- Fiyat --}}
                        <td class="text-end fw-bold" style="color:#E66239;">
                            {{ number_format($item->price, 2, ',', '.') }} ₺
                        </td>
                        {{-- Durum --}}
                        <td class="text-center">
                            <div class="form-check form-switch d-inline-flex justify-content-center">
                                <input class="form-check-input cafe-status-toggle"
                                       type="checkbox" role="switch"
                                       data-id="{{ $item->id }}"
                                       {{ $item->is_active ? 'checked' : '' }}
                                       style="cursor: pointer;">
                            </div>
                        </td>
                        {{-- İşlemler --}}
                        <td class="pe-4 text-end">
                            <div class="d-inline-flex gap-1">
                                {{-- Düzenle --}}
                                <button type="button"
                                        class="action-icon edit edit-cafe-btn"
                                        data-bs-toggle="modal" data-bs-target="#editCafeModal"
                                        data-id="{{ $item->id }}"
                                        data-name="{{ $item->name }}"
                                        data-category="{{ $item->cafe_category_id }}"
                                        data-description="{{ $item->description }}"
                                        data-ingredients="{{ $item->ingredients }}"
                                        data-price="{{ $item->price }}"
                                        data-active="{{ $item->is_active ? 1 : 0 }}"
                                        data-featured="{{ $item->is_featured ? 1 : 0 }}"
                                        data-sort="{{ $item->sort_order }}"
                                        data-image="{{ $item->image_path ? asset('storage/'.$item->image_path) : '' }}"
                                        title="Düzenle">
                                    <i class="ti ti-pencil" style="font-size:1rem;"></i>
                                </button>
                                {{-- Sil --}}
                                <button type="button"
                                        class="action-icon del delete-cafe-btn"
                                        data-id="{{ $item->id }}"
                                        data-name="{{ $item->name }}"
                                        title="Sil">
                                    <i class="ti ti-trash" style="font-size:1rem;"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="mb-3" style="font-size:3.5rem;">☕</div>
                            <h5 class="fw-semibold text-dark">Cafe ürünü bulunamadı</h5>
                            <p class="text-muted mb-3">Cafe menünüze ürün ekleyerek başlayın.</p>
                            <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addCafeModal">
                                <i class="ti ti-plus me-1"></i> İlk Ürünü Ekle
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($cafeProducts->hasPages())
        <div class="card-footer bg-white border-0 py-3 px-4">
            {{ $cafeProducts->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════
     MODAL: Yeni Cafe Ürünü Ekle
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="addCafeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #E66239, #d4522c);">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4">☕</span>
                    <h5 class="modal-title fw-bold text-white mb-0">Yeni Cafe Ürünü Ekle</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('cafe.store') }}" method="POST" enctype="multipart/form-data" id="addCafeForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Sol: Görsel --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small">Ürün Görseli</label>
                            <div class="img-upload-area" id="addImgDropArea" onclick="document.getElementById('addImageInput').click()">
                                <img id="addImgPreview" class="img-preview w-100 mb-2" src="" alt="">
                                <div id="addImgPlaceholder">
                                    <div style="font-size:2.5rem;">📷</div>
                                    <p class="text-muted small mb-0 mt-1">Tıkla veya sürükle & bırak<br><small>JPG, PNG, WEBP — maks. 2MB</small></p>
                                </div>
                            </div>
                            <input type="file" id="addImageInput" name="image" accept="image/*" class="d-none">
                        </div>
                        {{-- Sağ: Form --}}
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-secondary small">Ürün Adı *</label>
                                    <input type="text" name="name" class="form-control border-0 bg-light rounded-3"
                                           placeholder="Örn: Filtre Kahve, Soğuk Limonata..." required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Kategori</label>
                                    <select name="cafe_category_id" class="form-select border-0 bg-light rounded-3">
                                        <option value="">— Kategori Seç —</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Fiyat (₺) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light">₺</span>
                                        <input type="number" step="0.01" min="0" name="price"
                                               class="form-control border-0 bg-light" placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-secondary small">Açıklama</label>
                                    <textarea name="description" rows="2" class="form-control border-0 bg-light rounded-3"
                                              placeholder="Ürün hakkında kısa açıklama..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-secondary small">İçindekiler</label>
                                    <textarea name="ingredients" rows="2" class="form-control border-0 bg-light rounded-3"
                                              placeholder="Kafein, süt, şeker... (opsiyonel)"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-secondary small">Sıra No</label>
                                    <input type="number" name="sort_order" min="0" value="0"
                                           class="form-control border-0 bg-light rounded-3">
                                </div>
                                <div class="col-md-4 d-flex align-items-end pb-1">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="addIsActive" value="1" checked>
                                        <label class="form-check-label fw-semibold small" for="addIsActive">Satışta</label>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end pb-1">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_featured" id="addIsFeatured" value="1">
                                        <label class="form-check-label fw-semibold small" for="addIsFeatured">Öne Çıkar</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="ti ti-device-floppy me-1"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     MODAL: Cafe Ürünü Düzenle
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="editCafeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4">✏️</span>
                    <h5 class="modal-title fw-bold text-white mb-0">Cafe Ürünü Düzenle</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCafeForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Sol: Görsel --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small">Ürün Görseli</label>
                            <div class="img-upload-area" id="editImgDropArea" onclick="document.getElementById('editImageInput').click()">
                                <img id="editImgPreview" class="img-preview w-100 mb-2" src="" alt="">
                                <div id="editImgPlaceholder">
                                    <div style="font-size:2.5rem;">📷</div>
                                    <p class="text-muted small mb-0 mt-1">Tıkla veya sürükle & bırak<br><small>Değiştirmek için yeni görsel seç</small></p>
                                </div>
                            </div>
                            <input type="file" id="editImageInput" name="image" accept="image/*" class="d-none">
                        </div>
                        {{-- Sağ: Form --}}
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-secondary small">Ürün Adı *</label>
                                    <input type="text" name="name" id="editCafeName" class="form-control border-0 bg-light rounded-3" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Kategori</label>
                                    <select name="cafe_category_id" id="editCafeCategory" class="form-select border-0 bg-light rounded-3">
                                        <option value="">— Kategori Seç —</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-secondary small">Fiyat (₺) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light">₺</span>
                                        <input type="number" step="0.01" min="0" name="price" id="editCafePrice"
                                               class="form-control border-0 bg-light" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-secondary small">Açıklama</label>
                                    <textarea name="description" id="editCafeDescription" rows="2"
                                              class="form-control border-0 bg-light rounded-3"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-secondary small">İçindekiler</label>
                                    <textarea name="ingredients" id="editCafeIngredients" rows="2"
                                              class="form-control border-0 bg-light rounded-3"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-secondary small">Sıra No</label>
                                    <input type="number" name="sort_order" id="editCafeSortOrder" min="0"
                                           class="form-control border-0 bg-light rounded-3">
                                </div>
                                <div class="col-md-4 d-flex align-items-end pb-1">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="editCafeIsActive" value="1">
                                        <label class="form-check-label fw-semibold small" for="editCafeIsActive">Satışta</label>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end pb-1">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_featured" id="editCafeIsFeatured" value="1">
                                        <label class="form-check-label fw-semibold small" for="editCafeIsFeatured">Öne Çıkar</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="ti ti-device-floppy me-1"></i> Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Hidden delete form ───────────────────────────────── --}}
<form id="deleteCafeForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

{{-- ── Kategori Yönetim Modalı ──────────────────────────────── --}}
<div class="modal fade" id="cafeCategoriesModal" tabindex="-1" aria-labelledby="cafeCategoriesModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cafeCategoriesModalLabel">Kategori Yönetimi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCafeCategoryForm" class="mb-3" data-url="{{ route('cafe-categories.store') }}">
                    <div class="input-group">
                        <input type="text" id="newCafeCategoryName" class="form-control" placeholder="Yeni Kategori Adı" autocomplete="off">
                        <button class="btn btn-primary" type="submit">Ekle</button>
                    </div>
                </form>
                <ul class="list-group" id="cafeCategoriesList">
                    @forelse($categories as $cat)
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3" data-id="{{ $cat->id }}">
                        <span class="cat-text fw-medium">{{ $cat->name }}</span>
                        <input type="text" class="form-control form-control-sm cat-input d-none me-2" value="{{ $cat->name }}" style="max-width:60%;" aria-label="Kategori Adı">
                        <div class="d-inline-flex gap-1 align-items-center ms-2 flex-shrink-0">
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-circle p-2 border-0 cm-edit-btn" data-id="{{ $cat->id }}" title="Düzenle" aria-label="Düzenle">
                                <i class="ti ti-pencil fs-5"></i>
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm rounded-circle p-2 border-0 cm-save-btn d-none" data-id="{{ $cat->id }}" title="Kaydet" aria-label="Kaydet">
                                <i class="ti ti-check fs-5"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle p-2 border-0 cm-cancel-btn d-none" data-id="{{ $cat->id }}" title="İptal" aria-label="İptal">
                                <i class="ti ti-x fs-5"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-2 border-0 cm-delete-btn" data-id="{{ $cat->id }}" title="Sil" aria-label="Sil">
                                <i class="ti ti-trash fs-5"></i>
                            </button>
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted cm-empty">Kayıtlı kategori bulunmuyor. Yukarıdan ekleyebilirsiniz.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Image preview helper ──────────────────────────────────────
    function setupImagePreview(inputId, previewId, placeholderId) {
        const input       = document.getElementById(inputId);
        const preview     = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);

        if (!input) return;

        input.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
            };
            reader.readAsDataURL(file);
        });
    }

    setupImagePreview('addImageInput',  'addImgPreview',  'addImgPlaceholder');
    setupImagePreview('editImageInput', 'editImgPreview', 'editImgPlaceholder');

    // ── Edit Modal: veri doldur ───────────────────────────────────
    document.querySelectorAll('.edit-cafe-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const form = document.getElementById('editCafeForm');
            form.action = `/cafe/${this.dataset.id}`;

            document.getElementById('editCafeName').value        = this.dataset.name        || '';
            document.getElementById('editCafeCategory').value    = this.dataset.category    || '';
            document.getElementById('editCafeDescription').value = this.dataset.description || '';
            document.getElementById('editCafeIngredients').value = this.dataset.ingredients || '';
            document.getElementById('editCafePrice').value       = this.dataset.price       || '';
            document.getElementById('editCafeSortOrder').value   = this.dataset.sort        || 0;
            document.getElementById('editCafeIsActive').checked   = this.dataset.active  === '1';
            document.getElementById('editCafeIsFeatured').checked = this.dataset.featured === '1';

            // Mevcut görsel
            const preview     = document.getElementById('editImgPreview');
            const placeholder = document.getElementById('editImgPlaceholder');
            if (this.dataset.image) {
                preview.src = this.dataset.image;
                preview.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
            } else {
                preview.src = '';
                preview.style.display = 'none';
                if (placeholder) placeholder.style.display = 'block';
            }
        });
    });

    // ── Delete ────────────────────────────────────────────────────
    document.querySelectorAll('.delete-cafe-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const name = this.dataset.name;
            if (!confirm(`"${name}" ürününü silmek istediğinizden emin misiniz?`)) return;
            const form = document.getElementById('deleteCafeForm');
            form.action = `/cafe/${this.dataset.id}`;
            form.submit();
        });
    });

    // ── Status Toggle (AJAX) ──────────────────────────────────────
    document.querySelectorAll('.cafe-status-toggle').forEach(toggle => {
        toggle.addEventListener('change', function () {
            const id = this.dataset.id;
            fetch(`/cafe/${id}/toggle-status`, {
                method : 'PATCH',
                headers: {
                    'Content-Type' : 'application/json',
                    'X-CSRF-TOKEN' : '{{ csrf_token() }}',
                    'Accept'       : 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    this.checked = !this.checked;
                    alert('Durum güncellenirken hata oluştu.');
                }
            })
            .catch(() => {
                this.checked = !this.checked;
                alert('Durum güncellenirken hata oluştu.');
            });
        });
    });

    // ── Chevron animasyon (accordion) ─────────────────────────────
    const chevrons = document.querySelectorAll('[data-bs-toggle="collapse"]');
    chevrons.forEach(el => {
        const targetId = el.getAttribute('href') || el.getAttribute('data-bs-target');
        if (!targetId) return;
        const target = document.querySelector(targetId);
        if (!target) return;
        const chevron = el.querySelector('.ti-chevron-down');
        target.addEventListener('show.bs.collapse',  () => { if(chevron) chevron.style.transform = 'rotate(180deg)'; });
        target.addEventListener('hide.bs.collapse',  () => { if(chevron) chevron.style.transform = 'rotate(0deg)'; });
        // İlk yükleme
        if (target.classList.contains('show') && chevron) chevron.style.transform = 'rotate(180deg)';
    });
});
</script>
<script src="{{ asset('js/category-manager.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    CategoryManager.init({
        modalId:    'cafeCategoriesModal',
        listId:     'cafeCategoriesList',
        formId:     'addCafeCategoryForm',
        inputId:    'newCafeCategoryName',
        storeUrl:   '{{ route("cafe-categories.store") }}',
        updateUrl:  function (id) { return '/cafe-categories/' + id; },
        destroyUrl: function (id) { return '/cafe-categories/' + id; },
    });
});
</script>
@endpush
