@extends('layouts.app')
@section('title', 'Sayım Detayı - B&V Barber')
@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Sayım Detayı</h1>
                <p class="text-muted mb-0">Geçmiş tarihli stok sayım sonuçlarını inceleyin.</p>
            </div>
            <div>
                <a href="{{ route('stock-counts.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
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
                <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Genel Bilgiler</h5>
                
                <div class="mb-3">
                    <div class="text-secondary small fw-semibold">Sayım Tarihi</div>
                    <div class="fs-5 fw-bold text-dark">{{ \Carbon\Carbon::parse($stockCount->count_date)->format('d.m.Y') }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-secondary small fw-semibold">İşlemi Yapan</div>
                    <div class="text-dark">{{ $stockCount->creator ? $stockCount->creator->first_name . ' ' . $stockCount->creator->last_name : 'Sistem' }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-secondary small fw-semibold">Kayıt Zamanı</div>
                    <div class="text-dark">{{ $stockCount->created_at->format('d.m.Y H:i') }}</div>
                </div>

                @if($stockCount->notes)
                <div>
                    <div class="text-secondary small fw-semibold">Notlar</div>
                    <div class="text-dark fst-italic">{{ $stockCount->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-body p-0">
                <div class="p-4 border-bottom bg-light bg-opacity-50">
                    <h5 class="fw-bold mb-0 text-primary">Sayım Kalemleri ve Farklar</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0">Ürün</th>
                                <th class="py-3 border-0 text-center">Sistem Stoku</th>
                                <th class="py-3 border-0 text-center">Sayılan Miktar</th>
                                <th class="pe-4 py-3 border-0 text-center">Fark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockCount->items as $item)
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <div class="fw-semibold text-dark">{{ $item->product ? $item->product->name : 'Silinmiş Ürün' }}</div>
                                </td>
                                <td class="text-center text-secondary">
                                    {{ $item->system_quantity }}
                                </td>
                                <td class="text-center fw-bold text-primary">
                                    {{ $item->counted_quantity }}
                                </td>
                                <td class="pe-4 text-center">
                                    @if($item->difference > 0)
                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">+{{ $item->difference }} Fazla</span>
                                    @elseif($item->difference < 0)
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">{{ $item->difference }} Eksik</span>
                                    @else
                                        <span class="text-muted fw-bold">Eşit</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
