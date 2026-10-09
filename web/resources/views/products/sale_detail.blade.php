@extends('layouts.app')
@section('title', 'Satış Detayı - B&V Barber')

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Satış Detayı</h1>
                <p class="text-muted mb-0">Ürün satışına ait tüm bilgileri detaylıca inceleyin.</p>
            </div>
            <div>
                <a href="javascript:history.back()" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm fw-semibold">
                    <i class="ti ti-arrow-left me-1"></i> Geri Dön
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header border-0 bg-primary text-white py-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fw-bold mb-0"><i class="ti ti-receipt me-2"></i>Satış Fişi</h5>
                    <small class="opacity-75">Satış Kodu: {{ $productSale->sale_code ?? '#' . $productSale->id }}</small>
                </div>
                <div class="text-end">
                    <div class="fs-5 fw-bold">₺{{ number_format($productSale->total_price, 2, ',', '.') }}</div>
                    <small class="opacity-75">{{ $productSale->sold_at->format('d.m.Y H:i') }}</small>
                </div>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <div class="row g-4 mb-4">
                    {{-- Ürün Bilgileri --}}
                    <div class="col-md-6 d-flex flex-column">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                <i class="ti ti-package"></i>
                            </div>
                            <span class="fw-bold text-dark">Ürün Bilgileri</span>
                        </div>
                        <div class="bg-light rounded-3 p-3 flex-grow-1">
                            @foreach($saleItems as $item)
                                <div class="d-flex justify-content-between align-items-center border-bottom border-secondary-subtle pb-2 mb-2 {{ $loop->last ? 'border-0 pb-0 mb-0' : '' }}">
                                    <div>
                                        @if($item->product)
                                            <div class="fw-semibold text-dark fs-6">{{ $item->product->name }}</div>
                                            @if($item->product->sku)
                                                <small class="text-muted d-block mt-1">SKU: {{ $item->product->sku }}</small>
                                            @endif
                                        @else
                                            <span class="text-danger fw-semibold">Silinmiş Ürün</span>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-success">₺{{ number_format($item->total_price, 2, ',', '.') }}</div>
                                        <small class="text-muted">{{ $item->quantity }} x ₺{{ number_format($item->unit_price, 2, ',', '.') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Müşteri Bilgileri --}}
                    <div class="col-md-6 d-flex flex-column">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                <i class="ti ti-user-circle"></i>
                            </div>
                            <span class="fw-bold text-dark">Müşteri Bilgileri</span>
                        </div>
                        <div class="bg-light rounded-3 p-3 flex-grow-1">
                            @if($productSale->customer)
                                <div class="fw-semibold text-dark fs-6">{{ $productSale->customer->full_name ?? ($productSale->customer->first_name . ' ' . $productSale->customer->last_name) }}</div>
                                @if($productSale->customer->phone)
                                    <small class="text-muted d-block mt-1"><i class="ti ti-phone me-1"></i>{{ $productSale->customer->phone }}</small>
                                @endif
                                @if($productSale->customer->email)
                                    <small class="text-muted d-block"><i class="ti ti-mail me-1"></i>{{ $productSale->customer->email }}</small>
                                @endif
                            @else
                                <span class="text-muted fw-semibold">Kayıtsız Müşteri / Hızlı Satış</span>
                            @endif
                        </div>
                    </div>

                    {{-- Personel Bilgileri --}}
                    <div class="col-md-6 d-flex flex-column">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                <i class="ti ti-user-edit"></i>
                            </div>
                            <span class="fw-bold text-dark">İşlemi Yapan Personel</span>
                        </div>
                        <div class="bg-light rounded-3 p-3 flex-grow-1 d-flex flex-column justify-content-center">
                            <div class="fw-semibold text-dark fs-6">
                                {{ $productSale->seller ? ($productSale->seller->full_name ?? ($productSale->seller->first_name . ' ' . $productSale->seller->last_name)) : 'Sistem' }}
                            </div>
                            <small class="text-muted mt-1"><i class="ti ti-clock me-1"></i>{{ \Carbon\Carbon::parse($productSale->sold_at)->format('d.m.Y - H:i') }}</small>
                        </div>
                    </div>

                    {{-- Ödeme/Kasa İşlemi --}}
                    <div class="col-md-6 d-flex flex-column">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                <i class="ti ti-wallet"></i>
                            </div>
                            <span class="fw-bold text-dark">Ödeme & Kasa</span>
                        </div>
                        <div class="bg-light rounded-3 p-3 flex-grow-1 d-flex flex-column align-items-start">
                            <div class="d-flex justify-content-between w-100 mb-2">
                                <span class="text-secondary small fw-semibold">Ödeme Yöntemi:</span>
                                <span class="badge bg-primary text-white rounded-pill px-3 py-2">
                                    @switch($productSale->payment_method)
                                        @case('cash') Nakit @break
                                        @case('credit_card') Kredi Kartı @break
                                        @case('bank_transfer') Havale / EFT @break
                                        @default {{ $productSale->payment_method }}
                                    @endswitch
                                </span>
                            </div>
                            <a href="{{ route('finance.transactions') }}?search={{ $productSale->sale_code }}" class="btn btn-sm btn-outline-info rounded-pill mt-auto w-100 text-decoration-none">
                                <i class="ti ti-external-link me-1"></i> İlgili Kasa Hareketini Gör
                            </a>
                        </div>
                    </div>
                </div>

                <hr class="border-light my-4">

                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="text-muted small mb-1 fw-semibold">Toplam Adet</div>
                        <div class="fs-4 fw-bold text-dark">{{ $saleItems->sum('quantity') }}</div>
                    </div>
                    <div class="col-4 border-start border-end">
                        <div class="text-muted small mb-1 fw-semibold">Kalem Sayısı</div>
                        <div class="fs-4 fw-bold text-dark">{{ $saleItems->count() }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small mb-1 fw-semibold">Genel Toplam</div>
                        <div class="fs-3 fw-bold text-success">₺{{ number_format($saleItems->sum('total_price'), 2, ',', '.') }}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
