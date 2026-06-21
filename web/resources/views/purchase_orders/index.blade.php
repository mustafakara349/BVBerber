@extends('layouts.app')
@section('title', 'Mal Alımları - Stok & Tedarik - B&V Barber')
@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Mal Alımları</h1>
                <p class="text-muted mb-0">Tedarikçilerden aldığınız ürünleri faturalandırın ve stoklara ekleyin.</p>
            </div>
            <div>
                <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm d-flex align-items-center gap-2">
                    <i class="ti ti-plus fs-5"></i> Yeni Mal Alımı
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Filter Panel -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4 bg-light bg-opacity-30">
                <form action="{{ route('purchase-orders.index') }}" method="GET" class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label text-secondary fw-semibold small">Tedarikçi</label>
                        <select name="supplier_id" class="form-select border-0 shadow-sm rounded-3">
                            <option value="">Tümü</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-secondary fw-semibold small">Fatura Numarası</label>
                        <input type="text" name="invoice_number" class="form-control border-0 shadow-sm rounded-3" placeholder="Fatura veya fiş no..." value="{{ request('invoice_number') }}">
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

<!-- Orders Table -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0">Alım Tarihi</th>
                                <th class="py-3 border-0">Fatura No</th>
                                <th class="py-3 border-0">Tedarikçi</th>
                                <th class="py-3 border-0 text-end">Toplam Tutar</th>
                                <th class="py-3 border-0 text-center">İşleyen</th>
                                <th class="pe-4 py-3 border-0 text-end">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($order->purchase_date)->format('d.m.Y') }}</div>
                                </td>
                                <td>
                                    @if($order->invoice_number)
                                        <div class="fw-semibold text-primary">#{{ $order->invoice_number }}</div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $order->supplier ? $order->supplier->name : 'Bilinmeyen Tedarikçi' }}</div>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    {{ number_format($order->total_amount, 2, ',', '.') }} ₺
                                </td>
                                <td class="text-center">
                                    <div class="small fw-semibold">{{ $order->creator ? $order->creator->first_name : 'Sistem' }}</div>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('purchase-orders.show', $order) }}" class="btn btn-outline-info btn-sm rounded-circle p-2 border-0" title="Detayları Gör">
                                        <i class="ti ti-eye fs-5"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="ti ti-file-invoice fs-1 mb-2 d-block text-secondary opacity-50"></i>
                                    <h5>Kayıt bulunamadı.</h5>
                                    <p class="small text-secondary mb-0">Mal alımı yapabilmek için "Yeni Mal Alımı" butonuna tıklayın.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($orders->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
