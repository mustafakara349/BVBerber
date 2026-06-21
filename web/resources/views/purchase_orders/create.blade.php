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
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">Tedarikçi *</label>
                            <select name="supplier_id" class="form-select border-0 bg-light" required>
                                <option value="">Tedarikçi Seçiniz...</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">Fatura / Belge No</label>
                            <input type="text" name="invoice_number" class="form-control border-0 bg-light" placeholder="OPS-12345">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">Alım Tarihi *</label>
                            <input type="date" name="purchase_date" class="form-control border-0 bg-light" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
                        <h5 class="fw-bold mb-0 text-primary">Ürün Kalemleri</h5>
                        <button type="button" id="addItemBtn" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="ti ti-plus"></i> Kalem Ekle
                        </button>
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
                    <button type="button" class="btn btn-light rounded-pill px-4 me-2">İptal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                        <i class="ti ti-check me-2"></i> Faturayı Kaydet ve Stoklara İşle
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

        const products = @json($products);

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

        function createItemRow() {
            let options = '<option value="">Ürün Seçiniz...</option>';
            products.forEach(p => {
                options += `<option value="${p.id}" data-price="${p.purchase_price}">${p.name} (Stok: ${p.stock_quantity})</option>`;
            });

            const rowHtml = `
                <div class="row g-3 mb-3 align-items-end item-row bg-light bg-opacity-50 p-3 rounded-3" data-index="${itemIndex}">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold text-secondary small">Ürün *</label>
                        <select name="items[${itemIndex}][product_id]" class="form-select border-0 shadow-sm item-select" required>
                            ${options}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold text-secondary small">Miktar *</label>
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control border-0 shadow-sm item-qty" value="1" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold text-secondary small">Birim Fiyat (₺) *</label>
                        <input type="number" step="0.01" name="items[${itemIndex}][unit_price]" class="form-control border-0 shadow-sm item-price" value="0.00" min="0" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold text-secondary small">Toplam</label>
                        <div class="form-control-plaintext fw-bold text-dark item-total pb-0">0,00 ₺</div>
                    </div>
                    <div class="col-md-1 text-end">
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

            select.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const defaultPrice = selectedOption.getAttribute('data-price');
                    price.value = defaultPrice;
                }
                calculateGrandTotal();
            });

            qty.addEventListener('input', calculateGrandTotal);
            price.addEventListener('input', calculateGrandTotal);

            removeBtn.addEventListener('click', function() {
                newRow.remove();
                calculateGrandTotal();
            });

            itemIndex++;
        }

        addItemBtn.addEventListener('click', createItemRow);

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
