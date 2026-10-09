@extends('layouts.app')
@section('title', 'Kasa İşlemi Detayı - B&V Barber')
@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('finance.transactions') }}" class="btn btn-light rounded-circle p-2 shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="ti ti-arrow-left fs-5"></i>
            </a>
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">İşlem Detayı</h1>
                <p class="text-muted mb-0">#TXN-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }} numaralı işlemin tüm detayları.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-4 pb-4 border-bottom">
                    <div>
                        <span class="text-secondary small fw-medium text-uppercase tracking-wider">İşlem Tipi</span>
                        <div class="mt-2">
                            @if($transaction->transaction_type->value === 'income')
                                <span class="badge bg-success-subtle text-success px-4 py-2 rounded-pill fw-bold fs-6">
                                    <i class="ti ti-arrow-up-right me-1"></i> Gelir
                                </span>
                            @elseif($transaction->transaction_type->value === 'expense')
                                <span class="badge bg-danger-subtle text-danger px-4 py-2 rounded-pill fw-bold fs-6">
                                    <i class="ti ti-arrow-down-left me-1"></i> Gider
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning px-4 py-2 rounded-pill fw-bold fs-6">
                                    <i class="ti ti-reload me-1"></i> İade
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="text-secondary small fw-medium text-uppercase tracking-wider">İşlem Tutarı</span>
                        <h2 class="fw-bold mb-0 mt-1 {{ $transaction->transaction_type->value === 'income' ? 'text-success' : 'text-danger' }}">
                            {{ $transaction->transaction_type->value === 'income' ? '+' : '-' }}₺{{ number_format($transaction->amount, 2, ',', '.') }}
                        </h2>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <span class="text-secondary small fw-semibold d-block mb-1">Kategori</span>
                            <span class="fw-bold text-dark fs-6">{{ $transaction->category ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <span class="text-secondary small fw-semibold d-block mb-1">Ödeme Yöntemi</span>
                            <span class="fw-bold text-dark fs-6"><i class="ti {{ $transaction->payment_method->icon() }} me-1"></i> {{ $transaction->payment_method->label() }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <span class="text-secondary small fw-semibold d-block mb-1">İşlem Tarihi</span>
                            <span class="fw-bold text-dark fs-6">{{ $transaction->transaction_date->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <span class="text-secondary small fw-semibold d-block mb-1">Ekleyen</span>
                            <span class="fw-bold text-dark fs-6"><i class="ti ti-user-circle"></i> {{ $transaction->createdBy->full_name ?? 'Sistem' }}</span>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2">Açıklama</h6>
                    <div class="p-3 bg-light rounded-3 text-secondary">
                        {{ $transaction->description ?? 'Bu işlem için açıklama girilmemiş.' }}
                    </div>
                </div>

                @if($transaction->reference)
                    <div class="mb-4 p-4 border rounded-3 bg-primary bg-opacity-10 border-primary border-opacity-25">
                        <h6 class="fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                            <i class="ti ti-link"></i> İlişkili Kayıt
                        </h6>
                        <p class="text-secondary mb-3">Bu kasa işlemi sistemdeki başka bir kayıtla ilişkilendirilmiştir.</p>
                        
                        @if(class_basename($transaction->reference_type) === 'Appointment')
                            <a href="{{ route('appointments.index') }}?search={{ $transaction->reference_id }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                <i class="ti ti-calendar-event me-2"></i> Randevu #{{ $transaction->reference_id }} Detayını Gör
                            </a>
                        @elseif(class_basename($transaction->reference_type) === 'Debt')
                            <span class="btn btn-primary rounded-pill px-4 shadow-sm">
                                <i class="ti ti-receipt me-2"></i> Borç / Alacak Kaydı (#{{ $transaction->reference_id }})
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        @if($transaction->document_path)
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="card-title fw-bold text-dark mb-0">Belge / Fiş</h6>
            </div>
            <div class="card-body p-0 position-relative bg-light text-center" style="min-height: 200px;">
                @if(Str::endsWith($transaction->document_path, '.pdf'))
                    <div class="d-flex flex-column align-items-center justify-content-center p-5">
                        <i class="ti ti-file-text text-danger opacity-50 mb-3" style="font-size: 60px;"></i>
                        <a href="{{ Storage::url($transaction->document_path) }}" target="_blank" class="btn btn-outline-primary rounded-pill px-4">PDF Belgesini Aç</a>
                    </div>
                @else
                    <img src="{{ Storage::url($transaction->document_path) }}" alt="Belge" class="img-fluid" style="max-height: 400px; object-fit: contain;">
                    <a href="{{ Storage::url($transaction->document_path) }}" target="_blank" class="position-absolute bottom-0 end-0 m-3 btn btn-dark btn-sm rounded-circle p-2 shadow" title="Tam Boyut">
                        <i class="ti ti-external-link"></i>
                    </a>
                @endif
            </div>
        </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 text-center">
                <h6 class="fw-bold text-dark mb-3">İşlemi Sil</h6>
                <p class="small text-secondary mb-3">Bu finansal işlemi silmek kasa bakiyenizi doğrudan etkiler. Bu işlem geri alınamaz.</p>
                <form action="{{ route('finance.transactions.destroy', $transaction) }}" method="POST" onsubmit="return confirm('Bu işlemi silmek istediğinize emin misiniz? Bu işlem kasa bakiyenizi doğrudan etkileyecektir.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger rounded-pill px-4 w-100">
                        <i class="ti ti-trash me-2"></i> İşlemi Tamamen Sil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
