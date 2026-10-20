@extends('layouts.app')
@section('title', 'Ürünler - B&V Barber')

@push('styles')
<style>
    .product-img-thumb {
        width: 52px;
        height: 52px;
        object-fit: cover;
        border-radius: 10px;
        border: 2px solid #f0f0f0;
    }
    .product-img-placeholder {
        width: 52px;
        height: 52px;
        border-radius: 10px;
        background: linear-gradient(135deg, #f0f4ff, #e0e9ff);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
    .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.78rem;
        font-weight: 600;
        border-radius: 20px;
        padding: 4px 10px;
    }
    .stock-ok      { background: #dcfce7; color: #16a34a; }
    .stock-low     { background: #fef9c3; color: #a16207; }
    .stock-empty   { background: #fee2e2; color: #dc2626; }
    .filter-card   { background: #fafbfc; border: 1px solid #f0f0f0; }
    .action-icon   {
        width: 34px; height: 34px;
        border-radius: 10px;
        border: 1px solid #e8e8e8;
        background: #fff;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.15s;
    }
    .action-icon:hover { background: #f5f5f5; }
    .action-icon.edit:hover  { border-color: #3b82f6; color: #3b82f6; }
    .action-icon.del:hover   { border-color: #ef4444; color: #ef4444; }
    .action-icon.view:hover  { border-color: #8b5cf6; color: #8b5cf6; }
</style>
@endpush

@section('content')

<x-page-header title="Ürünler" description="Salonda sattığınız ürünleri detaylı bilgileriyle yönetin.">
    <x-btn-outline icon="ti ti-category" data-bs-toggle="modal" data-bs-target="#productCategoriesModal">
        Kategorileri Yönet
    </x-btn-outline>
    <x-btn-primary href="{{ route('products.create') }}" icon="ti ti-plus" id="addProductBtn">
        Yeni Ürün Ekle
    </x-btn-primary>
</x-page-header>



{{-- ── İstatistik kartları ─────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-2" style="background:#eff6ff;"><i class="ti ti-box text-primary fs-4"></i></div>
                <div>
                    <div class="fw-bold fs-5">{{ $products->total() }}</div>
                    <div class="text-muted small">Toplam Ürün</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-2" style="background:#f0fdf4;"><i class="ti ti-circle-check text-success fs-4"></i></div>
                <div>
                    <div class="fw-bold fs-5">{{ $products->getCollection()->where('is_active', true)->count() }}</div>
                    <div class="text-muted small">Aktif</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-2" style="background:#fef9c3;"><i class="ti ti-alert-triangle text-warning fs-4"></i></div>
                <div>
                    <div class="fw-bold fs-5">{{ $products->getCollection()->filter(fn($p) => $p->stock_quantity > 0 && $p->stock_quantity <= ($p->critical_stock ?? 5))->count() }}</div>
                    <div class="text-muted small">Kritik Stok</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-2" style="background:#fee2e2;"><i class="ti ti-circle-x text-danger fs-4"></i></div>
                <div>
                    <div class="fw-bold fs-5">{{ $products->getCollection()->where('stock_quantity', '<=', 0)->count() }}</div>
                    <div class="text-muted small">Stok Yok</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <form action="{{ route('products.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label text-secondary small fw-medium mb-1">Arama</label>
                    <x-search-input name="search" placeholder="Ürün adı, barkod, SKU..." :value="request('search')" :resetUrl="route('products.index')" />
                </div>
                <div class="col-6 col-md-3">
                    <x-form-select name="category" label="Kategori" :pill="true">
                        <option value="">Tüm Kategoriler</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </x-form-select>
                </div>
                <div class="col-6 col-md-3">
                    <x-form-select name="status" label="Durum" :pill="true">
                        <option value="">Tüm Durumlar</option>
                        <option value="active"       {{ request('status') === 'active'       ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive"     {{ request('status') === 'inactive'     ? 'selected' : '' }}>Pasif</option>
                        <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Stok Yok</option>
                        <option value="low_stock"    {{ request('status') === 'low_stock'    ? 'selected' : '' }}>Kritik Stok</option>
                    </x-form-select>
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-1 fw-medium" style="height: 38px;">
                        <i class="ti ti-filter"></i> Filtrele
                    </button>
                    @if(request()->anyFilled(['search', 'category', 'status']))
                        <a href="{{ route('products.index') }}" class="btn btn-light rounded-pill px-2 shadow-sm d-inline-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="height: 38px; width: 38px;" title="Filtreleri Temizle">
                            <i class="ti ti-rotate"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Ürün Tablosu ────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-dark">
                <thead class="bg-light text-secondary">
                    <tr>
                        <th class="ps-4 py-3 border-0" style="min-width: 240px;">Ürün</th>
                        <th class="py-3 border-0">Kategori</th>
                        <th class="py-3 border-0">Barkod / SKU</th>
                        <th class="py-3 border-0 text-center">Stok</th>
                        <th class="py-3 border-0 text-end">Alış</th>
                        <th class="py-3 border-0 text-end">Satış</th>
                        <th class="py-3 border-0 text-center">Durum</th>
                        <th class="pe-4 py-3 border-0 text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr class="border-bottom border-light" id="product-row-{{ $product->id }}" style="cursor:pointer;" onclick="if(!event.target.closest('.product-status-toggle, .btn-group, .action-icon, form')){window.location='{{ route('products.show', $product) }}'}" title="Ürün Detayını Görüntüle">
                        {{-- Ürün Adı + Görsel --}}
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}"
                                         class="product-img-thumb" alt="{{ $product->name }}">
                                @else
                                    <div class="product-img-placeholder"><i class="ti ti-package text-muted fs-4"></i></div>
                                @endif
                                <div>
                                    <div class="fw-semibold text-dark">{{ $product->name }}</div>
                                    @if($product->description)
                                        <small class="text-muted">{{ Str::limit($product->description, 40) }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        {{-- Kategori --}}
                        <td>
                            @if($product->productCategory)
                                <span class="badge bg-info-subtle text-info rounded-pill px-2">
                                    {{ $product->productCategory->name }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        {{-- Barkod / SKU --}}
                        <td>
                            @if($product->barcode)
                                <div class="small fw-semibold"><i class="ti ti-barcode text-muted"></i> {{ $product->barcode }}</div>
                            @endif
                            @if($product->sku)
                                <div class="small text-muted">SKU: {{ $product->sku }}</div>
                            @endif
                            @if(!$product->barcode && !$product->sku)
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        {{-- Stok --}}
                        <td class="text-center">
                            @if($product->stock_quantity <= 0)
                                <span class="stock-badge stock-empty"><i class="ti ti-circle-x"></i> Tükendi</span>
                            @elseif($product->stock_quantity <= ($product->critical_stock ?? 5))
                                <span class="stock-badge stock-low"><i class="ti ti-alert-triangle"></i> {{ $product->stock_quantity }} Adet</span>
                            @else
                                <span class="stock-badge stock-ok"><i class="ti ti-circle-check"></i> {{ $product->stock_quantity }} Adet</span>
                            @endif
                        </td>
                        {{-- Fiyatlar --}}
                        <td class="text-end text-muted small">
                            {{ number_format($product->purchase_price, 2, ',', '.') }} ₺
                        </td>
                        <td class="text-end fw-bold" style="color:#16a34a;">
                            {{ number_format($product->sell_price, 2, ',', '.') }} ₺
                        </td>
                        {{-- Durum --}}
                        <td class="text-center">
                            <div class="form-check form-switch d-inline-flex justify-content-center">
                                <input class="form-check-input product-status-toggle"
                                       type="checkbox" role="switch"
                                       data-id="{{ $product->id }}"
                                       {{ $product->is_active ? 'checked' : '' }}
                                       style="cursor: pointer;">
                            </div>
                        </td>
                        {{-- İşlemler --}}
                        <td class="pe-4 text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('products.edit', $product) }}" class="action-icon edit" title="Düzenle">
                                    <i class="ti ti-pencil" style="font-size:1rem;"></i>
                                </a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline delete-product-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-icon del" title="Sil">
                                        <i class="ti ti-trash" style="font-size:1rem;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state icon="ti ti-package" title="Ürün bulunamadı" message="Arama kriterlerinizi değiştirin veya yeni ürün ekleyin." colspan="8">
                        <x-btn-primary href="{{ route('products.create') }}" icon="ti ti-plus">
                            Yeni Ürün Ekle
                        </x-btn-primary>
                    </x-empty-state>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
        <div class="card-footer bg-white border-top border-light-subtle py-3 px-4">
            {{ $products->links() }}
        </div>
    @endif
</div>

{{-- ── Kategori Yönetim Modalı ──────────────────────────────── --}}
<div class="modal fade" id="productCategoriesModal" tabindex="-1" aria-labelledby="productCategoriesModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productCategoriesModalLabel">Kategori Yönetimi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addProductCategoryForm" class="mb-3" data-url="{{ route('product-categories.store') }}">
                    <div class="input-group">
                        <input type="text" id="newProductCategoryName" class="form-control" placeholder="Yeni Kategori Adı" autocomplete="off">
                        <button class="btn btn-primary" type="submit">Ekle</button>
                    </div>
                </form>
                <ul class="list-group" id="productCategoriesList">
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
<script nonce="{{ $cspNonce }}">
document.addEventListener('DOMContentLoaded', function () {

    // ── Status Toggle (AJAX) ──────────────────────────────────────
    document.querySelectorAll('.product-status-toggle').forEach(toggle => {
        toggle.addEventListener('change', function () {
            const id = this.dataset.id;
            fetch(`/products/${id}/toggle-status`, {
                method : 'PATCH',
                headers: {
                    'Content-Type' : 'application/json',
                    'X-CSRF-TOKEN' : '{{ csrf_token() }}',
                    'Accept'       : 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) this.checked = !this.checked;
            })
            .catch(() => { this.checked = !this.checked; });
        });
    });

    // ── Delete Confirm ────────────────────────────────────────────
    document.querySelectorAll('.delete-product-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (confirm('Bu ürünü silmek istediğinizden emin misiniz?')) {
                this.submit();
            }
        });
    });
});
</script>
<script nonce="{{ $cspNonce }}" src="{{ asset('js/category-manager.js') }}"></script>
<script nonce="{{ $cspNonce }}">
document.addEventListener('DOMContentLoaded', function () {
    CategoryManager.init({
        modalId:    'productCategoriesModal',
        listId:     'productCategoriesList',
        formId:     'addProductCategoryForm',
        inputId:    'newProductCategoryName',
        storeUrl:   '{{ route("product-categories.store") }}',
        updateUrl:  function (id) { return '/product-categories/' + id; },
        destroyUrl: function (id) { return '/product-categories/' + id; },
    });
});
</script>
@endpush
