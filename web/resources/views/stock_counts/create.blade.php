@extends('layouts.app')
@section('title', 'Yeni Stok Sayımı - B&V Barber')
@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Yeni Stok Sayımı</h1>
                <p class="text-muted mb-0">Fiziksel sayım sonuçlarını girin, sistem farkları otomatik düzeltsin.</p>
            </div>
            <div>
                <a href="{{ route('stock-counts.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm d-flex align-items-center gap-2">
                    <i class="ti ti-arrow-left fs-5"></i> Geri Dön
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <form action="{{ route('stock-counts.store') }}" method="POST" id="countForm">
                @csrf
                <div class="card-body p-4 p-md-5">
                    
                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Genel Bilgiler</h5>
                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">Sayım Tarihi *</label>
                            <input type="date" name="count_date" class="form-control border-0 bg-light" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold text-secondary">Notlar</label>
                            <input type="text" name="notes" class="form-control border-0 bg-light" placeholder="Aylık olağan sayım, eksikler giderildi vs.">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
                        <h5 class="fw-bold mb-0 text-primary">Sayım Kalemleri</h5>
                        <button type="button" id="addItemBtn" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="ti ti-plus"></i> Satır Ekle
                        </button>
                    </div>

                    <div id="itemsContainer">
                        <!-- Dynamic items -->
                    </div>

                </div>
                <div class="card-footer bg-white border-0 p-4 p-md-5 text-end">
                    <button type="button" class="btn btn-light rounded-pill px-4 me-2">İptal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                        <i class="ti ti-check me-2"></i> Sayımı Kaydet ve Farkları Uygula
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
        let itemIndex = 0;

        const products = @json($products);

        function createItemRow() {
            let options = '<option value="">Ürün Seçiniz...</option>';
            products.forEach(p => {
                options += `<option value="${p.id}" data-stock="${p.stock_quantity}">${p.name} (Sistem: ${p.stock_quantity})</option>`;
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
                        <label class="form-label fw-semibold text-secondary small">Sistemdeki Stok</label>
                        <div class="form-control-plaintext fw-bold text-secondary pb-0 ps-2 item-system-stock">-</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-primary small">Sayılan Gerçek Miktar *</label>
                        <input type="number" name="items[${itemIndex}][counted_quantity]" class="form-control border-0 shadow-sm item-counted" value="0" min="0" required>
                    </div>
                    <div class="col-md-1 text-center">
                        <label class="form-label fw-semibold text-secondary small">Fark</label>
                        <div class="form-control-plaintext fw-bold pb-0 item-diff">-</div>
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

            const select = newRow.querySelector('.item-select');
            const systemStockDisplay = newRow.querySelector('.item-system-stock');
            const countedInput = newRow.querySelector('.item-counted');
            const diffDisplay = newRow.querySelector('.item-diff');
            const removeBtn = newRow.querySelector('.remove-item-btn');

            function updateDiff() {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const systemStock = parseInt(selectedOption.getAttribute('data-stock'), 10);
                    const countedStock = parseInt(countedInput.value, 10) || 0;
                    const diff = countedStock - systemStock;
                    
                    if (diff > 0) {
                        diffDisplay.innerHTML = `<span class="text-success">+${diff}</span>`;
                    } else if (diff < 0) {
                        diffDisplay.innerHTML = `<span class="text-danger">${diff}</span>`;
                    } else {
                        diffDisplay.innerHTML = `<span class="text-muted">0</span>`;
                    }
                }
            }

            select.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const stock = selectedOption.getAttribute('data-stock');
                    systemStockDisplay.textContent = stock;
                    countedInput.value = stock; // Default to system stock to speed up entry
                    updateDiff();
                } else {
                    systemStockDisplay.textContent = '-';
                    diffDisplay.textContent = '-';
                }
            });

            countedInput.addEventListener('input', updateDiff);

            removeBtn.addEventListener('click', function() {
                newRow.remove();
            });

            itemIndex++;
        }

        addItemBtn.addEventListener('click', createItemRow);

        document.getElementById('countForm').addEventListener('submit', function(e) {
            const rows = itemsContainer.querySelectorAll('.item-row');
            if (rows.length === 0) {
                e.preventDefault();
                alert('Lütfen sayıma en az bir ürün ekleyin!');
            }
        });

        // Add a blank row initially
        createItemRow();
    });
</script>
@endpush
