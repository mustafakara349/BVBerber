@extends('layouts.app')
@section('title', 'Finansal İşlemler - B&V Barber')
@section('content')

<!-- Modern and Premium Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Finansal İşlemler</h1>
                <p class="text-muted mb-0">İşletmenizin gelir, gider ve genel kasa hareketlerini tek bir yerden yönetin.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                    <i class="ti ti-plus fs-5"></i> Yeni Kasa İşlemi Ekle
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Summary Cards with Vibrant Gradients -->
<div class="row g-4 mb-4">
    <!-- Total Income Card -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute end-0 bottom-0 opacity-10 mb-n3 me-n2">
                    <i class="ti ti-circle-arrow-up-right" style="font-size: 110px; line-height: 1;"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-2 rounded-3" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="ti ti-trending-up fs-4 text-white"></i>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 text-white" style="background-color: rgba(255, 255, 255, 0.25);">Filtrelenmiş Gelir</span>
                </div>
                <h6 class="text-white text-opacity-75 small mb-1 fw-medium text-uppercase tracking-wider">Toplam Gelir</h6>
                <h3 class="fs-2 fw-bold mb-0">₺{{ number_format($totalIncome, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- Total Expense Card -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute end-0 bottom-0 opacity-10 mb-n3 me-n2">
                    <i class="ti ti-circle-arrow-down-left" style="font-size: 110px; line-height: 1;"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-2 rounded-3" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="ti ti-trending-down fs-4 text-white"></i>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 text-white" style="background-color: rgba(255, 255, 255, 0.25);">Filtrelenmiş Gider</span>
                </div>
                <h6 class="text-white text-opacity-75 small mb-1 fw-medium text-uppercase tracking-wider">Toplam Gider</h6>
                <h3 class="fs-2 fw-bold mb-0">₺{{ number_format($totalExpense, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- Net Balance Card -->
    <div class="col-12 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute end-0 bottom-0 opacity-10 mb-n3 me-n2">
                    <i class="ti ti-wallet" style="font-size: 110px; line-height: 1;"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-2 rounded-3" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="ti ti-wallet fs-4 text-white"></i>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 text-white" style="background-color: rgba(255, 255, 255, 0.25);">Bakiye</span>
                </div>
                <h6 class="text-white text-opacity-75 small mb-1 fw-medium text-uppercase tracking-wider">Net Kasa Durumu</h6>
                <h3 class="fs-2 fw-bold mb-0">
                    @if($netBalance < 0)
                        -₺{{ number_format(abs($netBalance), 2, ',', '.') }}
                    @else
                        ₺{{ number_format($netBalance, 2, ',', '.') }}
                    @endif
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- Alert Messages -->
@if(session('success'))
<div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="ti ti-circle-check fs-4"></i>
    <div>{{ session('success') }}</div>
</div>
@endif

<!-- Advanced Filter Panel -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-filter text-primary fs-4"></i>
                    <h5 class="card-title fw-bold text-dark mb-0 fs-6">Gelişmiş Filtreleme</h5>
                </div>
                @if(request()->anyFilled(['transaction_type', 'payment_method', 'start_date', 'end_date']))
                    <a href="{{ route('finance.transactions') }}" class="btn btn-light rounded-pill btn-sm text-secondary d-flex align-items-center gap-1">
                        <i class="ti ti-rotate"></i> Filtreleri Temizle
                    </a>
                @endif
            </div>
            <div class="card-body p-4 bg-light bg-opacity-30">
                <form action="{{ route('finance.transactions') }}" method="GET" class="row g-3">
                    <!-- Type Filter -->
                    <div class="col-12 col-md-3">
                        <label class="form-label text-secondary fw-semibold small">İşlem Tipi</label>
                        <select name="transaction_type" class="form-select border-0 shadow-sm rounded-3">
                            <option value="">Tüm İşlem Tipleri</option>
                            <option value="income" {{ request('transaction_type') === 'income' ? 'selected' : '' }}>Gelir (+)</option>
                            <option value="expense" {{ request('transaction_type') === 'expense' ? 'selected' : '' }}>Gider (-)</option>
                            <option value="refund" {{ request('transaction_type') === 'refund' ? 'selected' : '' }}>İade (↺)</option>
                        </select>
                    </div>

                    <!-- Payment Method Filter -->
                    <div class="col-12 col-md-3">
                        <label class="form-label text-secondary fw-semibold small">Ödeme Yöntemi</label>
                        <select name="payment_method" class="form-select border-0 shadow-sm rounded-3">
                            <option value="">Tüm Ödeme Yöntemleri</option>
                            <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Nakit</option>
                            <option value="credit_card" {{ request('payment_method') === 'credit_card' ? 'selected' : '' }}>Kredi Kartı</option>
                            <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Banka Transferi</option>
                            <option value="online" {{ request('payment_method') === 'online' ? 'selected' : '' }}>Online</option>
                        </select>
                    </div>

                    <!-- Start Date Filter -->
                    <div class="col-12 col-md-2">
                        <label class="form-label text-secondary fw-semibold small">Başlangıç Tarihi</label>
                        <input type="date" name="start_date" class="form-control border-0 shadow-sm rounded-3" value="{{ request('start_date') }}">
                    </div>

                    <!-- End Date Filter -->
                    <div class="col-12 col-md-2">
                        <label class="form-label text-secondary fw-semibold small">Bitiş Tarihi</label>
                        <input type="date" name="end_date" class="form-control border-0 shadow-sm rounded-3" value="{{ request('end_date') }}">
                    </div>

                    <!-- Search Submit Button -->
                    <div class="col-12 col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary rounded-3 w-100 py-2 shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="ti ti-search fs-5"></i> Filtrele
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Transactions Table List -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0">İşlem No</th>
                                <th class="py-3 border-0">Tarih</th>
                                <th class="py-3 border-0">İşlem Tipi</th>
                                <th class="py-3 border-0">Ödeme Yöntemi</th>
                                <th class="py-3 border-0">Tutar</th>
                                <th class="py-3 border-0">Açıklama</th>
                                <th class="py-3 border-0">Ekleyen / Randevu</th>
                                <th class="pe-4 py-3 text-end border-0">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3 fw-semibold">
                                    #TXN-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-dark">{{ $transaction->transaction_date->format('d.m.Y') }}</span>
                                        <span class="text-secondary small">{{ $transaction->transaction_date->format('H:i') }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($transaction->transaction_type->value === 'income')
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold">
                                            <i class="ti ti-arrow-up-right me-1"></i> Gelir
                                        </span>
                                    @elseif($transaction->transaction_type->value === 'expense')
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-bold">
                                            <i class="ti ti-arrow-down-left me-1"></i> Gider
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fw-bold">
                                            <i class="ti ti-reload me-1"></i> İade
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                                        <i class="ti {{ $transaction->payment_method->icon() }} me-1"></i> {{ $transaction->payment_method->label() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold fs-6 {{ $transaction->transaction_type->value === 'income' ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->transaction_type->value === 'income' ? '+' : '-' }}₺{{ number_format($transaction->amount, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-secondary text-wrap" style="max-width: 250px; display: inline-block;">
                                        {{ $transaction->description ?? 'Açıklama bulunmuyor.' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        @if($transaction->appointment)
                                            <a href="{{ route('appointments.index') }}?search={{ $transaction->appointment->id }}" class="text-primary fw-semibold small text-decoration-none d-flex align-items-center gap-1">
                                                <i class="ti ti-calendar-event"></i> Randevu #{{ $transaction->appointment->id }}
                                            </a>
                                        @else
                                            <span class="text-secondary small d-flex align-items-center gap-1">
                                                <i class="ti ti-user-circle"></i> {{ $transaction->createdBy->full_name ?? 'Sistem' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="pe-4 text-end">
                                    <form action="{{ route('finance.transactions.destroy', $transaction) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Bu işlemi silmek istediğinize emin misiniz? Bu işlem kasa bakiyenizi doğrudan etkileyecektir.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-2 border-0" title="İşlemi Sil">
                                            <i class="ti ti-trash fs-5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ti ti-mood-empty fs-1 mb-2 d-block text-secondary opacity-50"></i>
                                    <h5>Filtreye uygun finansal işlem bulunamadı.</h5>
                                    <p class="small text-secondary mb-0">Filtre kriterlerinizi değiştirmeyi veya yeni kasa işlemi eklemeyi deneyin.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($transactions->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Elegant Add Transaction Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="addTransactionModalLabel">Yeni Kasa İşlemi Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="{{ route('finance.transactions.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <!-- Type Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">İşlem Tipi</label>
                        <div class="d-flex gap-3">
                            <input type="radio" class="btn-check" name="transaction_type" id="type_income" value="income" checked>
                            <label class="btn btn-outline-success rounded-pill px-4 flex-fill" for="type_income">
                                <i class="ti ti-arrow-up-right me-1"></i> Gelir
                            </label>
                            
                            <input type="radio" class="btn-check" name="transaction_type" id="type_expense" value="expense">
                            <label class="btn btn-outline-danger rounded-pill px-4 flex-fill" for="type_expense">
                                <i class="ti ti-arrow-down-left me-1"></i> Gider
                            </label>
                        </div>
                    </div>

                    <!-- Amount & Date Row -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-secondary">Tutar (₺)</label>
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light">₺</span>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control border-0 bg-light rounded-end-3" placeholder="0,00" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-secondary">İşlem Tarihi</label>
                            <input type="datetime-local" name="transaction_date" class="form-control border-0 bg-light" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Ödeme Yöntemi</label>
                        <select name="payment_method" class="form-select border-0 bg-light" required>
                            <option value="cash">Nakit</option>
                            <option value="credit_card">Kredi Kartı</option>
                            <option value="bank_transfer">Banka Transferi</option>
                            <option value="online">Online</option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary">Açıklama</label>
                        <textarea name="description" rows="3" class="form-control border-0 bg-light" placeholder="İşlem açıklaması yazın (örn: Şampuan satışı, Ofis mutfak masrafları)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">İşlemi Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
