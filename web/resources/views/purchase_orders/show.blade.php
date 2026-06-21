@extends('layouts.app')
@section('title', 'Fatura Detayı - Mal Alımları - B&V Barber')
@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Fatura Detayı</h1>
                <p class="text-muted mb-0">Mal alım işleminin detaylarını inceleyin.</p>
            </div>
            <div>
                <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                    Geri Dön
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Özet Bilgiler</h5>
                
                <div class="mb-3">
                    <div class="text-secondary small fw-semibold">Fatura / Belge No</div>
                    <div class="fs-5 fw-bold text-dark">{{ $purchaseOrder->invoice_number ?? 'Belirtilmedi' }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-secondary small fw-semibold">Tedarikçi</div>
                    <div class="fw-semibold text-dark">{{ $purchaseOrder->supplier ? $purchaseOrder->supplier->name : 'Bilinmiyor' }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-secondary small fw-semibold">Alım Tarihi</div>
                    <div class="text-dark">{{ \Carbon\Carbon::parse($purchaseOrder->purchase_date)->format('d.m.Y') }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-secondary small fw-semibold">İşlemi Yapan</div>
                    <div class="text-dark">{{ $purchaseOrder->creator ? $purchaseOrder->creator->first_name . ' ' . $purchaseOrder->creator->last_name : 'Sistem' }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-secondary small fw-semibold">Kayıt Zamanı</div>
                    <div class="text-dark">{{ $purchaseOrder->created_at->format('d.m.Y H:i') }}</div>
                </div>

                @if($purchaseOrder->notes)
                <div>
                    <div class="text-secondary small fw-semibold">Notlar</div>
                    <div class="text-dark fst-italic">{{ $purchaseOrder->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-body p-0">
                <div class="p-4 border-bottom bg-light bg-opacity-50">
                    <h5 class="fw-bold mb-0 text-primary">Fatura Kalemleri</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0">Ürün</th>
                                <th class="py-3 border-0 text-center">Miktar</th>
                                <th class="py-3 border-0 text-end">Birim Fiyat</th>
                                <th class="pe-4 py-3 border-0 text-end">Toplam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $item)
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <div class="fw-semibold text-dark">{{ $item->product ? $item->product->name : 'Silinmiş Ürün' }}</div>
                                </td>
                                <td class="text-center fw-semibold">
                                    {{ $item->quantity }}
                                </td>
                                <td class="text-end text-muted">
                                    {{ number_format($item->unit_price, 2, ',', '.') }} ₺
                                </td>
                                <td class="pe-4 text-end fw-bold text-dark">
                                    {{ number_format($item->total_price, 2, ',', '.') }} ₺
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light bg-opacity-50">
                            <tr>
                                <td colspan="3" class="text-end py-4 fw-bold text-secondary">GENEL TOPLAM:</td>
                                <td class="pe-4 text-end py-4 fs-4 fw-bold text-success">{{ number_format($purchaseOrder->total_amount, 2, ',', '.') }} ₺</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
