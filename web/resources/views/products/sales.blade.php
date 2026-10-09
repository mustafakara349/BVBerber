@extends('layouts.app')
@section('title', 'Hızlı Satış - B&V Barber')

@section('content')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-control {
        border-radius: 0.375rem !important;
        border: 1px solid #dee2e6 !important;
        background-color: #f8f9fa !important;
        min-height: 38px;
        display: flex;
        align-items: center;
    }
</style>
@endpush

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Hızlı Ürün Satışı</h1>
                <p class="text-muted mb-0">Salonunuzdaki stok ürünlerini kasadan anında satın ve tahsilatı kaydedin.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newSaleModal">
                    <i class="ti ti-shopping-cart fs-5"></i> Yeni Satış
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
        <i class="ti ti-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
        <i class="ti ti-alert-circle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter Panel --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4 bg-light bg-opacity-30">
                <form action="{{ route('products.sales.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label text-secondary fw-semibold small">Müşteri</label>
                        <select name="customer_id" class="form-select border-0 shadow-sm rounded-3" id="filterCustomerSelect">
                            <option value="">Tüm Müşteriler</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label text-secondary fw-semibold small">Ürün</label>
                        <select name="product_id" class="form-select border-0 shadow-sm rounded-3">
                            <option value="">Tüm Ürünler</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label text-secondary fw-semibold small">Başlangıç</label>
                        <input type="date" name="date_from" class="form-control border-0 shadow-sm rounded-3" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label text-secondary fw-semibold small">Bitiş</label>
                        <input type="date" name="date_to" class="form-control border-0 shadow-sm rounded-3" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-3 w-100 py-2 shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="ti ti-filter fs-5"></i> Filtrele
                        </button>
                        @if(request()->hasAny(['customer_id','product_id','date_from','date_to']))
                            <a href="{{ route('products.sales.index') }}" class="btn btn-light rounded-3 py-2 shadow-sm" title="Filtreyi Temizle">
                                <i class="ti ti-x"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Sales History Table --}}
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-history text-primary fs-4"></i>
                    <h5 class="card-title fw-bold text-dark mb-0 fs-6">Son Ürün Satışları</h5>
                </div>
                @if($sales->total() > 0)
                    <span class="badge bg-primary-subtle text-primary rounded-pill fw-semibold">{{ $sales->total() }} kayıt</span>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0">Tarih</th>
                                <th class="py-3 border-0">Ürün</th>
                                <th class="py-3 border-0">Müşteri</th>
                                <th class="py-3 border-0 text-center">Adet</th>
                                <th class="py-3 border-0 text-end">Birim Fiyat</th>
                                <th class="py-3 border-0 text-end">Toplam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                            <tr class="border-bottom border-light" style="cursor:pointer;" onclick="if(!event.target.closest('.btn')){window.location='{{ route('products.sales.show', $sale->group_id) }}'}" title="Satış Detayını Gör">
                                <td class="ps-4 py-3">
                                    <span class="text-secondary small">{{ \Carbon\Carbon::parse($sale->sold_at)->format('d.m.Y H:i') }}</span>
                                    <div class="small text-muted">Satan: {{ $sale->seller->full_name ?? 'Sistem' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $sale->display_product_name }}</div>
                                    @if($sale->sale_code)
                                        <small class="text-muted">{{ $sale->sale_code }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($sale->customer)
                                        <div class="fw-medium text-dark">{{ $sale->customer->full_name ?? ($sale->customer->first_name . ' ' . $sale->customer->last_name) }}</div>
                                        <small class="text-muted">{{ $sale->customer->phone ?? '' }}</small>
                                    @else
                                        <span class="text-muted small">Kayıtsız Müşteri</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $sale->total_quantity }}</span>
                                </td>
                                <td class="text-end text-muted">
                                    @if($sale->display_unit_price)
                                        ₺{{ number_format($sale->display_unit_price, 2, ',', '.') }}
                                    @else
                                        Karma
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-success pe-4">
                                    ₺{{ number_format($sale->total_price, 2, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="ti ti-shopping-cart-x fs-1 mb-2 d-block text-secondary opacity-50"></i>
                                    <h5>Henüz satış yapılmamış.</h5>
                                    <p class="small mb-0">Filtrelerinizi değiştirmeyi deneyin.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($sales->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $sales->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ===================== NEW SALE MODAL ===================== --}}
<div class="modal fade" id="newSaleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white py-4 px-4 rounded-top-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="ti ti-shopping-cart me-2"></i>Yeni Ürün Satışı</h5>
                    <small class="opacity-75">Birden fazla ürün ekleyebilirsiniz.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="{{ route('products.sales.store') }}" method="POST" id="saleForm">
                @csrf
                <div class="modal-body p-4">

                    {{-- Customer Search (Select2) --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark mb-1">Müşteri <span class="text-muted fw-normal small">(Opsiyonel)</span></label>
                        <select name="customer_id" id="customerSelect" class="form-select select2-customer border-0" style="width: 100%;">
                            <option value="">Kayıtsız Müşteri / Hızlı Satış</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">
                                    {{ $customer->full_name ?? ($customer->first_name . ' ' . $customer->last_name) }}
                                    @if($customer->phone) ({{ $customer->phone }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Ödeme Yöntemi --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark mb-1">Ödeme Yöntemi <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <input type="radio" class="btn-check" name="payment_method" id="pay_cash" value="cash" checked>
                            <label class="btn btn-outline-dark px-3 py-2" for="pay_cash"><i class="ti ti-cash me-1"></i>Nakit</label>

                            <input type="radio" class="btn-check" name="payment_method" id="pay_card" value="credit_card">
                            <label class="btn btn-outline-dark px-3 py-2" for="pay_card"><i class="ti ti-credit-card me-1"></i>Kredi Kartı</label>

                            <input type="radio" class="btn-check" name="payment_method" id="pay_transfer" value="bank_transfer">
                            <label class="btn btn-outline-dark px-3 py-2" for="pay_transfer"><i class="ti ti-building-bank me-1"></i>Havale</label>
                        </div>
                    </div>

                    {{-- Sepet / Ürün Satırları --}}
                    <div class="border-top pt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="ti ti-list me-2 text-primary"></i>Ürünler</h6>
                            <button type="button" id="addProductRowBtn" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="ti ti-plus me-1"></i> Ürün Ekle
                            </button>
                        </div>

                        <div id="cartContainer">
                            {{-- Rows injected by JS --}}
                        </div>

                        {{-- Toplam --}}
                        <div class="alert alert-success bg-success bg-opacity-10 border-0 d-flex justify-content-between align-items-center mt-4 mb-0 rounded-3">
                            <span class="fw-semibold text-success">Toplam Tutar:</span>
                            <span class="fs-4 fw-bold text-success" id="grandTotalDisplay">₺0,00</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm text-white fw-bold">
                        <i class="ti ti-check me-2"></i>Satışı Tamamla
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== SALE DETAIL MODAL ===================== --}}
<div class="modal fade" id="saleDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-dark text-white py-4 px-4 rounded-top-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="ti ti-receipt me-2"></i>Satış Detayı</h5>
                    <small class="opacity-75" id="saleDetailCode"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="saleDetailBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">Yükleniyor...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ============== CUSTOMER SEARCH ==============
    if(document.getElementById('customerSelect')) {
        new TomSelect('#customerSelect', {
            create: false,
            sortField: { field: "text", direction: "asc" },
            placeholder: 'Müşteri ara (İsim veya telefon)...',
            allowEmptyOption: true
        });
    }

    // ============== CART LOGIC ==============
    const cartContainer = document.getElementById('cartContainer');
    const addProductRowBtn = document.getElementById('addProductRowBtn');
    const grandTotalDisplay = document.getElementById('grandTotalDisplay');

    @php
        $productsJson = $products->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'sell_price' => $p->sell_price,
                'stock_quantity' => $p->stock_quantity,
            ];
        })->values();
    @endphp
    const allProducts = @json($productsJson);

    let rowIndex = 0;

    function buildProductOptions(excludeIds = []) {
        let html = '<option value="">Ürün seçin...</option>';
        allProducts.forEach(p => {
            const disabled = excludeIds.includes(p.id) ? 'disabled' : '';
            const sku = p.sku ? ` [${p.sku}]` : '';
            html += `<option value="${p.id}" data-price="${p.sell_price}" data-stock="${p.stock_quantity}" ${disabled}>
                ${p.name}${sku} — Stok: ${p.stock_quantity} | ₺${parseFloat(p.sell_price).toLocaleString('tr-TR', {minimumFractionDigits: 2})}
            </option>`;
        });
        return html;
    }

    function getSelectedProductIds(excludeRow = null) {
        const ids = [];
        cartContainer.querySelectorAll('.cart-product-select').forEach(sel => {
            if (sel.closest('.cart-row') !== excludeRow && sel.value) {
                ids.push(parseInt(sel.value));
            }
        });
        return ids;
    }

    function recalculateTotal() {
        let total = 0;
        cartContainer.querySelectorAll('.cart-row').forEach(row => {
            const price = parseFloat(row.querySelector('.cart-price-display').dataset.price || 0);
            const qty = parseInt(row.querySelector('.cart-qty').value || 0);
            total += price * qty;
        });
        grandTotalDisplay.textContent = '₺' + total.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function addProductRow() {
        const excludedIds = getSelectedProductIds();
        const idx = rowIndex++;

        const rowHtml = `
        <div class="cart-row row g-2 align-items-end mb-2 bg-light bg-opacity-75 p-2 rounded-3" data-index="${idx}">
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small mb-1">Ürün *</label>
                <select name="items[${idx}][product_id]" class="form-select form-select-sm border-0 shadow-sm cart-product-select" required style="height:36px;">
                    ${buildProductOptions(excludedIds)}
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold text-secondary small mb-1">Adet *</label>
                <input type="number" name="items[${idx}][quantity]" class="form-control form-control-sm border-0 shadow-sm cart-qty" value="1" min="1" required style="height:36px;">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold text-secondary small mb-1">Tutar</label>
                <div class="form-control-plaintext form-control-sm pb-0 pt-1 fw-bold text-success ps-2 cart-line-total">—</div>
                <div class="cart-price-display d-none" data-price="0"></div>
            </div>
            <div class="col-md-1 text-end pb-1">
                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-1 border-0 remove-cart-row" title="Satırı Kaldır" style="width:32px;height:32px;">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        </div>`;

        cartContainer.insertAdjacentHTML('beforeend', rowHtml);
        const newRow = cartContainer.lastElementChild;

        const productSelect = newRow.querySelector('.cart-product-select');
        const qtyInput = newRow.querySelector('.cart-qty');
        const lineTotal = newRow.querySelector('.cart-line-total');
        const priceDisplay = newRow.querySelector('.cart-price-display');
        const removeBtn = newRow.querySelector('.remove-cart-row');

        function updateLineTotal() {
            const price = parseFloat(priceDisplay.dataset.price || 0);
            const qty = parseInt(qtyInput.value || 0);
            const total = price * qty;
            lineTotal.textContent = total > 0
                ? '₺' + total.toLocaleString('tr-TR', {minimumFractionDigits: 2})
                : '—';
            recalculateTotal();
        }

        productSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt.value) {
                const price = parseFloat(opt.dataset.price);
                const stock = parseInt(opt.dataset.stock);
                priceDisplay.dataset.price = price;
                qtyInput.max = stock;
                if (parseInt(qtyInput.value) > stock) qtyInput.value = stock;
                updateLineTotal();
            } else {
                priceDisplay.dataset.price = 0;
                lineTotal.textContent = '—';
                recalculateTotal();
            }
            // Refresh options in other rows to mark this product as excluded
            refreshAllSelectOptions();
        });

        qtyInput.addEventListener('input', function() {
            const opt = productSelect.options[productSelect.selectedIndex];
            if (opt && opt.value) {
                const stock = parseInt(opt.dataset.stock);
                if (parseInt(this.value) > stock) {
                    this.value = stock;
                }
            }
            updateLineTotal();
        });

        removeBtn.addEventListener('click', function() {
            newRow.remove();
            refreshAllSelectOptions();
            recalculateTotal();
        });
    }

    function refreshAllSelectOptions() {
        cartContainer.querySelectorAll('.cart-row').forEach(row => {
            const sel = row.querySelector('.cart-product-select');
            const currentVal = sel.value;
            const excludedIds = getSelectedProductIds(row);
            sel.innerHTML = buildProductOptions(excludedIds);
            if (currentVal) sel.value = currentVal;
        });
    }

    addProductRowBtn.addEventListener('click', addProductRow);

    // Validate before submit
    document.getElementById('saleForm').addEventListener('submit', function(e) {
        const rows = cartContainer.querySelectorAll('.cart-row');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Lütfen en az bir ürün ekleyin!');
            return;
        }
        let allSelected = true;
        rows.forEach(row => {
            const sel = row.querySelector('.cart-product-select');
            if (!sel.value) allSelected = false;
        });
        if (!allSelected) {
            e.preventDefault();
            alert('Lütfen tüm satırlarda ürün seçin veya boş satırları kaldırın!');
        }
    });

    // Start with 1 row
    addProductRow();

    // ============== SALE DETAIL MODAL ==============
    document.querySelectorAll('.detail-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const saleId = this.dataset.saleId;
            const body = document.getElementById('saleDetailBody');
            const codeEl = document.getElementById('saleDetailCode');
            body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="text-muted mt-2">Yükleniyor...</p></div>';
            codeEl.textContent = '';

            fetch(`/products-sales/${saleId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                const { sale, product, customer, seller } = data;

                const paymentLabels = { cash: 'Nakit', credit_card: 'Kredi Kartı', bank_transfer: 'Havale/EFT' };
                const customerHtml = customer
                    ? `<div class="fw-semibold text-dark">${customer.full_name || (customer.first_name + ' ' + customer.last_name)}</div>
                       <small class="text-muted">${customer.phone ?? ''}</small>
                       <small class="text-muted d-block">${customer.email ?? ''}</small>`
                    : `<span class="text-muted small">Kayıtsız Müşteri / Hızlı Satış</span>`;

                const productHtml = product
                    ? `<div class="fw-semibold text-dark">${product.name}</div>
                       ${product.sku ? `<small class="text-muted">SKU: ${product.sku}</small>` : ''}
                       ${product.barcode ? `<small class="text-muted d-block">Barkod: ${product.barcode}</small>` : ''}`
                    : `<span class="text-danger small">Silinmiş Ürün</span>`;

                const sellerName = seller ? (seller.full_name || (seller.first_name + ' ' + seller.last_name)) : 'Sistem';

                codeEl.textContent = `#${sale.sale_code ?? sale.id}`;

                body.innerHTML = `
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                    <i class="ti ti-package"></i>
                                </div>
                                <span class="fw-bold text-dark">Ürün Bilgileri</span>
                            </div>
                            <div class="bg-light rounded-3 p-3 h-100">
                                ${productHtml}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                    <i class="ti ti-user-circle"></i>
                                </div>
                                <span class="fw-bold text-dark">Müşteri Bilgileri</span>
                            </div>
                            <div class="bg-light rounded-3 p-3 h-100">
                                ${customerHtml}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                    <i class="ti ti-user-edit"></i>
                                </div>
                                <span class="fw-bold text-dark">Satışı Yapan Personel</span>
                            </div>
                            <div class="bg-light rounded-3 p-3 h-100">
                                <div class="fw-semibold text-dark">${sellerName}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                    <i class="ti ti-calendar"></i>
                                </div>
                                <span class="fw-bold text-dark">Tarih & Saat</span>
                            </div>
                            <div class="bg-light rounded-3 p-3 h-100 d-flex flex-column justify-content-center">
                                <div class="fw-semibold text-dark">${new Date(sale.sold_at).toLocaleDateString('tr-TR', {day:'2-digit',month:'2-digit',year:'numeric'})}</div>
                                <small class="text-muted">${new Date(sale.sold_at).toLocaleTimeString('tr-TR', {hour:'2-digit',minute:'2-digit'})}</small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 border-light">

                    <div class="row g-3">
                        <div class="col-4 text-center">
                            <div class="text-muted small mb-1 fw-semibold">Satılan Adet</div>
                            <div class="fs-4 fw-bold text-dark">${sale.quantity}</div>
                        </div>
                        <div class="col-4 text-center border-start border-end">
                            <div class="text-muted small mb-1 fw-semibold">Birim Fiyat</div>
                            <div class="fs-4 fw-bold text-dark">₺${parseFloat(sale.unit_price).toLocaleString('tr-TR', {minimumFractionDigits:2})}</div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="text-muted small mb-1 fw-semibold">Genel Toplam</div>
                            <div class="fs-3 fw-bold text-success">₺${parseFloat(sale.total_price).toLocaleString('tr-TR', {minimumFractionDigits:2})}</div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded-3 d-flex justify-content-between align-items-center border">
                        <span class="text-muted small fw-semibold">Ödeme Yöntemi</span>
                        <span class="badge bg-dark text-white rounded-pill px-3 py-2">${paymentLabels[sale.payment_method] ?? sale.payment_method}</span>
                    </div>

                    <div class="mt-3 text-center">
                        <a href="{{ url('finance/transactions') }}?search=${sale.sale_code}" class="btn btn-outline-info rounded-pill px-4 py-2 w-100 fw-semibold text-decoration-none shadow-sm">
                            <i class="ti ti-external-link me-1"></i> İlgili Kasa Hareketini Gör
                        </a>
                    </div>
                `;
            })
            .catch(() => {
                body.innerHTML = '<div class="alert alert-danger">Detaylar yüklenirken hata oluştu.</div>';
            });
        });
    });
});
</script>
@endpush
