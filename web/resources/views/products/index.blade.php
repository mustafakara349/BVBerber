@extends('layouts.app')
@section('title', 'Stok Yönetimi - Ürünler - B&V Barber')
@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Stok Yönetimi</h1>
                <p class="text-muted mb-0">Salonda satılan ürünlerinizi ve stok durumlarını yönetin.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="ti ti-plus fs-5"></i> Yeni Ürün Ekle
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Filter Panel -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4 bg-light bg-opacity-30">
                <form action="{{ route('products.index') }}" method="GET" class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label text-secondary fw-semibold small">Ürün Durumu</label>
                        <select name="status" class="form-select border-0 shadow-sm rounded-3">
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Tümü</option>
                            <option value="active" {{ request('status') === 'active' || !request('status') ? 'selected' : '' }}>Aktif Ürünler</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Pasif Ürünler</option>
                            <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Tükenenler (Stok <= 0)</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-secondary fw-semibold small">Arama</label>
                        <input type="text" name="search" class="form-control border-0 shadow-sm rounded-3" placeholder="Ürün adı, barkod, SKU..." value="{{ request('search') }}">
                    </div>

                    <div class="col-12 col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary rounded-3 w-100 py-2 shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="ti ti-search fs-5"></i> Ara
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0">Ürün Adı</th>
                                <th class="py-3 border-0">Barkod / SKU</th>
                                <th class="py-3 border-0 text-center">Stok</th>
                                <th class="py-3 border-0 text-end">Alış (₺)</th>
                                <th class="py-3 border-0 text-end">Satış (₺)</th>
                                <th class="py-3 border-0 text-center">Durum</th>
                                <th class="pe-4 py-3 border-0 text-end">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <div class="fw-semibold text-dark">{{ $product->name }}</div>
                                    <small class="text-secondary">{{ Str::limit($product->description, 30) }}</small>
                                </td>
                                <td>
                                    @if($product->barcode)
                                    <div class="small fw-semibold"><i class="ti ti-barcode"></i> {{ $product->barcode }}</div>
                                    @endif
                                    @if($product->sku)
                                    <div class="small text-secondary">SKU: {{ $product->sku }}</div>
                                    @endif
                                    @if(!$product->barcode && !$product->sku)
                                    <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($product->stock_quantity <= 0)
                                        <span class="badge bg-danger text-white rounded-pill px-3 py-2">Tükendi</span>
                                    @elseif($product->stock_quantity <= 5)
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">{{ $product->stock_quantity }} Adet (Kritik)</span>
                                    @else
                                        <span class="badge bg-success text-white rounded-pill px-3 py-2">{{ $product->stock_quantity }} Adet</span>
                                    @endif
                                </td>
                                <td class="text-end text-muted">
                                    {{ number_format($product->purchase_price, 2, ',', '.') }}
                                </td>
                                <td class="text-end fw-bold text-success">
                                    {{ number_format($product->sell_price, 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($product->is_active)
                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">Pasif</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-circle p-2 border-0 edit-product-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editProductModal"
                                            data-id="{{ $product->id }}"
                                            data-name="{{ $product->name }}"
                                            data-sku="{{ $product->sku }}"
                                            data-barcode="{{ $product->barcode }}"
                                            data-description="{{ $product->description }}"
                                            data-purchase="{{ $product->purchase_price }}"
                                            data-sell="{{ $product->sell_price }}"
                                            data-stock="{{ $product->stock_quantity }}"
                                            data-active="{{ $product->is_active ? 1 : 0 }}"
                                            title="Düzenle">
                                            <i class="ti ti-pencil fs-5"></i>
                                        </button>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu ürünü silmek istediğinize emin misiniz?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-2 border-0" title="Sil">
                                                <i class="ti ti-trash fs-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="ti ti-box fs-1 mb-2 d-block text-secondary opacity-50"></i>
                                    <h5>Ürün bulunamadı.</h5>
                                    <p class="small text-secondary mb-0">Satış yapmak için sisteme ürün ekleyin.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($products->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white py-3">
                <h5 class="modal-title fw-bold">Yeni Ürün Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Ürün Adı *</label>
                        <input type="text" name="name" class="form-control border-0 bg-light" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Barkod (Opsiyonel)</label>
                            <input type="text" name="barcode" class="form-control border-0 bg-light">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">SKU (Opsiyonel)</label>
                            <input type="text" name="sku" class="form-control border-0 bg-light">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Alış Fiyatı (₺)</label>
                            <input type="number" step="0.01" min="0" name="purchase_price" class="form-control border-0 bg-light" value="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Satış Fiyatı (₺) *</label>
                            <input type="number" step="0.01" min="0" name="sell_price" class="form-control border-0 bg-light" value="0.00" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Stok Miktarı *</label>
                            <input type="number" step="1" min="0" name="stock_quantity" class="form-control border-0 bg-light" value="0" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked value="1">
                                <label class="form-check-label fw-semibold" for="isActive">Satışa Açık</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary">Açıklama</label>
                        <textarea name="description" rows="2" class="form-control border-0 bg-light"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white py-3">
                <h5 class="modal-title fw-bold">Ürünü Düzenle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form id="editProductForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Ürün Adı *</label>
                        <input type="text" name="name" id="edit_name" class="form-control border-0 bg-light" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Barkod</label>
                            <input type="text" name="barcode" id="edit_barcode" class="form-control border-0 bg-light">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">SKU</label>
                            <input type="text" name="sku" id="edit_sku" class="form-control border-0 bg-light">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Alış Fiyatı (₺)</label>
                            <input type="number" step="0.01" min="0" name="purchase_price" id="edit_purchase" class="form-control border-0 bg-light" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Satış Fiyatı (₺) *</label>
                            <input type="number" step="0.01" min="0" name="sell_price" id="edit_sell" class="form-control border-0 bg-light" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Stok Miktarı *</label>
                            <input type="number" step="1" min="0" name="stock_quantity" id="edit_stock" class="form-control border-0 bg-light" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                                <label class="form-check-label fw-semibold" for="edit_is_active">Satışa Açık</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary">Açıklama</label>
                        <textarea name="description" id="edit_description" rows="2" class="form-control border-0 bg-light"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const editModal = document.getElementById('editProductModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                
                document.getElementById('editProductForm').action = `/products/${id}`;
                document.getElementById('edit_name').value = button.getAttribute('data-name');
                document.getElementById('edit_sku').value = button.getAttribute('data-sku');
                document.getElementById('edit_barcode').value = button.getAttribute('data-barcode');
                document.getElementById('edit_description').value = button.getAttribute('data-description');
                document.getElementById('edit_purchase').value = button.getAttribute('data-purchase');
                document.getElementById('edit_sell').value = button.getAttribute('data-sell');
                document.getElementById('edit_stock').value = button.getAttribute('data-stock');
                document.getElementById('edit_is_active').checked = button.getAttribute('data-active') === '1';
            });
        }
    });
</script>
@endpush
