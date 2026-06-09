@extends('layouts.app')
@section('title', 'Hızlı Satış - B&V Barber')
@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Hızlı Ürün Satışı</h1>
                <p class="text-muted mb-0">Salondaki stok ürünlerinizi kasadan anında satın ve tahsilatı kaydedin.</p>
            </div>
            <div>
                <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newSaleModal">
                    <i class="ti ti-shopping-cart fs-5"></i> Yeni Satış
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Sales History Table -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-2">
                <i class="ti ti-history text-primary fs-4"></i>
                <h5 class="card-title fw-bold text-dark mb-0 fs-6">Son Ürün Satışları</h5>
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
                                <th class="pe-4 py-3 border-0 text-end">Toplam Tutar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <span class="text-secondary small">{{ $sale->sold_at->format('d.m.Y H:i') }}</span>
                                    <div class="small text-muted">Satan: {{ $sale->seller->full_name ?? 'Sistem' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $sale->product->name ?? 'Silinmiş Ürün' }}</div>
                                </td>
                                <td>
                                    @if($sale->customer)
                                        <div class="fw-medium text-dark">{{ $sale->customer->full_name }}</div>
                                    @else
                                        <span class="text-muted small">Kayıtsız Müşteri</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $sale->quantity }}</span>
                                </td>
                                <td class="text-end text-muted">
                                    ₺{{ number_format($sale->unit_price, 2, ',', '.') }}
                                </td>
                                <td class="pe-4 text-end fw-bold text-success">
                                    ₺{{ number_format($sale->total_price, 2, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="ti ti-shopping-cart-x fs-1 mb-2 d-block text-secondary opacity-50"></i>
                                    <h5>Henüz satış yapılmamış.</h5>
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

<!-- New Sale Modal -->
<div class="modal fade" id="newSaleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-success text-white py-3">
                <h5 class="modal-title fw-bold">Yeni Ürün Satışı</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="{{ route('products.sales.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Ürün Seçin *</label>
                        <select name="product_id" id="product_select" class="form-select border-0 bg-light" required onchange="updatePrice()">
                            <option value="">Seçiniz...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->sell_price }}" data-stock="{{ $product->stock_quantity }}">
                                    {{ $product->name }} (Stok: {{ $product->stock_quantity }} | ₺{{ number_format($product->sell_price, 2, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Adet *</label>
                        <input type="number" name="quantity" id="quantity_input" class="form-control border-0 bg-light" value="1" min="1" required onchange="updatePrice()" onkeyup="updatePrice()">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Müşteri Seçin (Opsiyonel)</label>
                        <select name="customer_id" class="form-select border-0 bg-light">
                            <option value="">Kayıtsız Müşteri / Hızlı Satış</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->full_name }} ({{ $customer->phone }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Ödeme Yöntemi *</label>
                        <select name="payment_method" class="form-select border-0 bg-light" required>
                            <option value="cash">Nakit</option>
                            <option value="credit_card">Kredi Kartı</option>
                            <option value="bank_transfer">Havale / EFT</option>
                        </select>
                        <small class="text-muted d-block mt-1">Bu satış kasaya <strong>Gelir</strong> olarak işlenecektir.</small>
                    </div>

                    <div class="alert alert-success bg-success bg-opacity-10 border-0 d-flex justify-content-between align-items-center mt-4 mb-0">
                        <span class="fw-semibold text-success">Toplam Tutar:</span>
                        <span class="fs-4 fw-bold text-success" id="total_price_display">₺0,00</span>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm text-white">Satışı Tamamla</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function updatePrice() {
        const select = document.getElementById('product_select');
        const quantity = parseInt(document.getElementById('quantity_input').value) || 0;
        const display = document.getElementById('total_price_display');
        
        if (select.selectedIndex > 0) {
            const option = select.options[select.selectedIndex];
            const price = parseFloat(option.getAttribute('data-price'));
            const stock = parseInt(option.getAttribute('data-stock'));
            
            if (quantity > stock) {
                alert(`Uyarı: Bu üründen stokta sadece ${stock} adet var.`);
                document.getElementById('quantity_input').value = stock;
            }
            
            const total = price * document.getElementById('quantity_input').value;
            display.innerText = '₺' + total.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } else {
            display.innerText = '₺0,00';
        }
    }
</script>
@endpush
