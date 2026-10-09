@extends('layouts.app')
@section('title', 'Yeni Mal Alımı - B&V Barber')
@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Yeni Mal Alımı</h1>
                <p class="text-muted mb-0">Ürün alımlarını faturaya işleyip stokları güncelleyin.</p>
            </div>
            <div>
                <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm d-flex align-items-center gap-2">
                    <i class="ti ti-arrow-left fs-5"></i> Geri Dön
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <form action="{{ route('purchase-orders.store') }}" method="POST" id="purchaseForm">
                @csrf
                <div class="card-body p-4 p-md-5">
                    
                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Fatura Bilgileri</h5>
                    <div class="row g-4 mb-5">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Tedarikçi *</label>
                            <select name="supplier_id" class="form-select border-0 bg-light" required>
                                <option value="">Tedarikçi Seçiniz...</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Fatura / Belge No</label>
                            <input type="text" name="invoice_number" class="form-control border-0 bg-light" placeholder="OPS-12345">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Alım Tarihi *</label>
                            <input type="date" name="purchase_date" class="form-control border-0 bg-light" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary">Ödeme Yöntemi *</label>
                            <select name="payment_method" class="form-select border-0 bg-light" required>
                                <option value="cash">Nakit</option>
                                <option value="credit_card">Kredi Kartı</option>
                                <option value="bank_transfer">Banka Havalesi/EFT</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
                        <h5 class="fw-bold mb-0 text-primary">Alınan Ürün Kalemleri</h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#newProductModal">
                                <i class="ti ti-box"></i> Yeni Ürün Ekle
                            </button>
                            <button type="button" id="addItemBtn" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="ti ti-plus"></i> Kalem Ekle
                            </button>
                        </div>
                    </div>

                    <div id="itemsContainer">
                        <!-- Dynamic items will be added here -->
                    </div>

                    <div class="row mt-4 pt-3 border-top">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold text-secondary">Notlar</label>
                            <textarea name="notes" class="form-control border-0 bg-light" rows="3" placeholder="Fatura veya ürünlerle ilgili ek notlar..."></textarea>
                        </div>
                        <div class="col-md-4 d-flex flex-column justify-content-end align-items-end">
                            <div class="p-3 bg-light rounded-4 w-100 text-end">
                                <h6 class="text-secondary mb-1">Genel Toplam</h6>
                                <h3 class="fw-bold text-success mb-0" id="grandTotalDisplay">0,00 ₺</h3>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer bg-white border-0 p-4 p-md-5 text-end">
                    <button type="button" class="btn btn-light rounded-pill px-4 me-2" onclick="window.history.back()">İptal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                        <i class="ti ti-check me-2"></i> Faturayı Kaydet ve Stoklara İşle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Yeni Ürün Ekleme Modalı --}}
<div class="modal fade" id="newProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-success text-white py-4 px-4 rounded-top-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="ti ti-box me-2"></i>Hızlı Yeni Ürün Ekle</h5>
                    <small class="opacity-75">Tedarikçiden gelen ve sistemde olmayan yeni bir ürünü hemen oluşturun.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form id="newProductForm">
                @csrf
                <div class="modal-body p-4">
                    <div id="newProductAlert" class="alert alert-danger d-none rounded-3 border-0"></div>
                    
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Ürün Adı *</label>
                            <input type="text" name="name" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Kategori</label>
                            <select name="product_category_id" class="form-select bg-light border-0">
                                <option value="">Seçiniz...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Barkod</label>
                            <input type="text" name="barcode" class="form-control bg-light border-0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">SKU / Stok Kodu</label>
                            <input type="text" name="sku" class="form-control bg-light border-0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Alış Fiyatı (₺) *</label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Satış Fiyatı (₺) *</label>
                            <input type="number" step="0.01" name="sell_price" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Mevcut Stok <small class="text-muted">(Genelde 0)</small></label>
                            <input type="number" name="stock_quantity" class="form-control bg-light border-0" value="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm text-white" id="saveProductBtn">
                        <i class="ti ti-check me-2"></i>Ürünü Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const itemsContainer = document.getElementById('itemsContainer');
        const addItemBtn = document.getElementById('addItemBtn');
        const grandTotalDisplay = document.getElementById('grandTotalDisplay');
        let itemIndex = 0;

        let products = @json($products);

        function formatCurrency(amount) {
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(amount);
        }

        function calculateGrandTotal() {
            let total = 0;
            const rows = itemsContainer.querySelectorAll('.item-row');
            rows.forEach(row => {
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const rowTotal = qty * price;
                row.querySelector('.item-total').textContent = formatCurrency(rowTotal);
                total += rowTotal;
            });
            grandTotalDisplay.textContent = formatCurrency(total);
        }

        // Build product options
        function buildOptions() {
            let options = '<option value="">Ürün Seçiniz...</option>';
            products.forEach(p => {
                const sku = p.sku ? ` [SKU: ${p.sku}]` : '';
                const barcode = p.barcode ? ` | Barkod: ${p.barcode}` : '';
                options += `<option value="${p.id}" data-price="${p.purchase_price}" data-sku="${p.sku ?? ''}" data-barcode="${p.barcode ?? ''}" data-stock="${p.stock_quantity}">${p.name}${sku}${barcode} — Sistem Stoku: ${p.stock_quantity}</option>`;
            });
            return options;
        }

        function createItemRow(preselectProductId = null) {
            const rowHtml = `
                <div class="row g-3 mb-3 align-items-start item-row bg-light bg-opacity-50 p-3 rounded-3" data-index="${itemIndex}">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-secondary small">Ürün *</label>
                        <select name="items[${itemIndex}][product_id]" class="form-select border-0 shadow-sm item-select" required>
                            ${buildOptions()}
                        </select>
                        
                        <div class="item-product-detail mt-2 d-none">
                            <div class="bg-white border rounded-3 p-2 small">
                                <span class="item-sku text-muted"></span>
                                <span class="item-barcode text-muted ms-2"></span>
                                <span class="item-stock text-primary fw-bold ms-2"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold text-secondary small">Alınan Miktar *</label>
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control border-0 shadow-sm item-qty" value="1" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold text-secondary small">Birim Alış (₺) *</label>
                        <input type="number" step="0.01" name="items[${itemIndex}][unit_price]" class="form-control border-0 shadow-sm item-price" value="0.00" min="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Satır Toplamı</label>
                        <div class="form-control-plaintext fw-bold text-dark item-total pb-0 fs-5">0,00 ₺</div>
                    </div>
                    <div class="col-md-1 text-end">
                        <label class="form-label fw-semibold text-secondary small d-block">&nbsp;</label>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-2 border-0 remove-item-btn" title="Satırı Sil">
                            <i class="ti ti-trash fs-5"></i>
                        </button>
                    </div>
                </div>
            `;
            
            itemsContainer.insertAdjacentHTML('beforeend', rowHtml);
            const newRow = itemsContainer.lastElementChild;

            // Add event listeners
            const select = newRow.querySelector('.item-select');
            const qty = newRow.querySelector('.item-qty');
            const price = newRow.querySelector('.item-price');
            const removeBtn = newRow.querySelector('.remove-item-btn');
            const detailPanel = newRow.querySelector('.item-product-detail');

            select.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const defaultPrice = selectedOption.getAttribute('data-price');
                    const sku = selectedOption.getAttribute('data-sku');
                    const barcode = selectedOption.getAttribute('data-barcode');
                    const stock = selectedOption.getAttribute('data-stock');
                    
                    price.value = defaultPrice;
                    
                    detailPanel.classList.remove('d-none');
                    newRow.querySelector('.item-sku').textContent = sku ? `SKU: ${sku}` : '';
                    newRow.querySelector('.item-barcode').textContent = barcode ? `Barkod: ${barcode}` : '';
                    newRow.querySelector('.item-stock').textContent = `Mevcut Stok: ${stock}`;
                } else {
                    detailPanel.classList.add('d-none');
                }
                calculateGrandTotal();
            });

            qty.addEventListener('input', calculateGrandTotal);
            price.addEventListener('input', calculateGrandTotal);

            removeBtn.addEventListener('click', function() {
                newRow.remove();
                calculateGrandTotal();
            });

            // Preselect if given
            if (preselectProductId) {
                select.value = preselectProductId;
                // Dispatch change event to trigger price update and detail panel
                select.dispatchEvent(new Event('change'));
            }

            itemIndex++;
        }

        addItemBtn.addEventListener('click', () => createItemRow());

        // Refresh all selects when a new product is added globally
        function refreshAllSelects() {
            const options = buildOptions();
            itemsContainer.querySelectorAll('.item-row').forEach(row => {
                const sel = row.querySelector('.item-select');
                const val = sel.value;
                sel.innerHTML = options;
                if(val) sel.value = val;
            });
        }

        // --- NEW PRODUCT AJAX SUBMIT ---
        const newProductForm = document.getElementById('newProductForm');
        const alertBox = document.getElementById('newProductAlert');
        const saveBtn = document.getElementById('saveProductBtn');
        const modalEl = document.getElementById('newProductModal');
        const bsModal = new bootstrap.Modal(modalEl);

        newProductForm.addEventListener('submit', function(e) {
            e.preventDefault();
            alertBox.classList.add('d-none');
            
            const formData = new FormData(this);
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Kaydediliyor...';

            fetch('{{ route('products.store') }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.product) {
                    // Ürünü global listeye ekle
                    products.push(data.product);
                    
                    // Tüm selectleri güncelle
                    refreshAllSelects();

                    // Formu sıfırla ve modalı kapat
                    newProductForm.reset();
                    bsModal.hide();

                    // Yeni satır açıp bu ürünü seçtir
                    createItemRow(data.product.id);
                } else if (data.errors) {
                    let errs = Object.values(data.errors).map(e => e.join('<br>')).join('<br>');
                    alertBox.innerHTML = errs;
                    alertBox.classList.remove('d-none');
                }
            })
            .catch(error => {
                alertBox.innerHTML = 'Ürün kaydedilirken bir hata oluştu. Lütfen tüm zorunlu alanları doldurun.';
                alertBox.classList.remove('d-none');
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="ti ti-check me-2"></i>Ürünü Kaydet';
            });
        });

        // Form submission validation
        document.getElementById('purchaseForm').addEventListener('submit', function(e) {
            const rows = itemsContainer.querySelectorAll('.item-row');
            if (rows.length === 0) {
                e.preventDefault();
                alert('Lütfen faturaya en az bir ürün kalemi ekleyin!');
            }
        });

        // Initialize with one row
        createItemRow();
    });
</script>
@endpush
