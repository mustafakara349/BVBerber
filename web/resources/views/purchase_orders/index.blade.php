@extends('layouts.app')
@section('title', 'Mal Alımları - Stok & Tedarik - B&V Barber')
@section('content')

<x-page-header title="Mal Alımları" description="Tedarikçilerden aldığınız ürünleri faturalandırın ve stoklara ekleyin.">
    <x-btn-primary href="{{ route('purchase-orders.create') }}" icon="ti ti-plus">
        Yeni Mal Alımı
    </x-btn-primary>
</x-page-header>

{{-- Filters --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
            <form action="{{ route('purchase-orders.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-5 col-lg-5">
                    <label class="form-label text-secondary small fw-medium mb-1">Fatura Numarası</label>
                    <x-search-input name="invoice_number" placeholder="Fatura veya fiş no..." :value="request('invoice_number')" :resetUrl="route('purchase-orders.index')" />
                </div>

                <div class="col-12 col-md-4 col-lg-4">
                    <x-form-select name="supplier_id" label="Tedarikçi" :pill="true">
                        <option value="">Tüm Tedarikçiler</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </x-form-select>
                </div>

                <div class="col-12 col-md-3 col-lg-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-1 fw-medium" style="height: 38px;">
                        <i class="ti ti-filter"></i> Filtrele
                    </button>
                    @if(request()->anyFilled(['supplier_id', 'invoice_number']))
                        <a href="{{ route('purchase-orders.index') }}" class="btn btn-light rounded-pill px-2 shadow-sm d-inline-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="height: 38px; width: 38px;" title="Filtreleri Temizle">
                            <i class="ti ti-rotate"></i>
                        </a>
                    @endif
                </div>
            </form>
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
                                <th class="py-3 border-0 text-center pe-4">İşleyen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr class="border-bottom border-light" style="cursor:pointer;" onclick="window.location='{{ route('purchase-orders.show', $order) }}'" title="Detayı Görüntüle">
                                <td class="ps-4 py-3">
                                    <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($order->purchase_date)->format('d.m.Y') }}</div>
                                    <div class="text-secondary small">{{ \Carbon\Carbon::parse($order->purchase_date)->format('H:i') }}</div>
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
                                <td class="text-center pe-4">
                                    <div class="small fw-semibold">{{ $order->creator ? $order->creator->first_name : 'Sistem' }}</div>
                                </td>
                            </tr>
                            @empty
                            <x-empty-state icon="ti ti-file-invoice" title="Kayıt bulunamadı" message="Mal alımı yapabilmek için 'Yeni Mal Alımı' butonuna tıklayın veya filtrelerinizi değiştirin." colspan="5" />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($orders->hasPages())
                <div class="card-footer bg-white border-top border-light-subtle py-3 px-4">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
