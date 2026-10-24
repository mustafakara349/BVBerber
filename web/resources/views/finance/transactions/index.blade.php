@extends('layouts.app')
@section('title', 'Finansal İşlemler - B&V Barber')
@section('content')

<x-page-header title="Finansal İşlemler" description="İşletmenizin gelir, gider ve genel kasa hareketlerini tek bir yerden yönetin.">
    <form action="{{ route('finance.transactions') }}" method="GET" id="dateFilterForm" class="d-inline-block">
        @if(request('transaction_type'))
            <input type="hidden" name="transaction_type" value="{{ request('transaction_type') }}">
        @endif
        @if(request('payment_method'))
            <input type="hidden" name="payment_method" value="{{ request('payment_method') }}">
        @endif
        @if(request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
        @endif
        <select name="date_filter" id="top_date_filter_select" class="form-select form-select-pill bg-white shadow-sm" style="min-width: 140px; cursor: pointer;">
            <option value="monthly" {{ $dateFilter === 'monthly' ? 'selected' : '' }}>Bu Ay</option>
            <option value="daily" {{ $dateFilter === 'daily' ? 'selected' : '' }}>Bugün</option>
            <option value="yearly" {{ $dateFilter === 'yearly' ? 'selected' : '' }}>Bu Yıl</option>
            <option value="all" {{ $dateFilter === 'all' ? 'selected' : '' }}>Tüm Zamanlar</option>
            @if($dateFilter === 'custom')
                <option value="custom" selected>Özel Aralık</option>
            @endif
        </select>
    </form>
    <x-btn-primary icon="ti ti-plus" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
        Yeni Kasa İşlemi Ekle
    </x-btn-primary>
</x-page-header>

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

{{-- Filters --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <form action="{{ route('finance.transactions') }}" method="GET" id="bottomFilterForm" class="row g-2 align-items-end">
                <input type="hidden" name="date_filter" id="bottom_date_filter" value="{{ $dateFilter }}">
                <!-- Type Filter -->
                <div class="col-12 col-md-3">
                    <x-form-select name="transaction_type" label="İşlem Tipi" :pill="true">
                        <option value="">Tüm İşlem Tipleri</option>
                        <option value="income" {{ request('transaction_type') === 'income' ? 'selected' : '' }}>Gelir (+)</option>
                        <option value="expense" {{ request('transaction_type') === 'expense' ? 'selected' : '' }}>Gider (-)</option>
                        <option value="refund" {{ request('transaction_type') === 'refund' ? 'selected' : '' }}>İade (↺)</option>
                    </x-form-select>
                </div>

                <!-- Payment Method Filter -->
                <div class="col-12 col-md-3">
                    <x-form-select name="payment_method" label="Ödeme Yöntemi" :pill="true">
                        <option value="">Tüm Ödeme Yöntemleri</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Nakit</option>
                        <option value="credit_card" {{ request('payment_method') === 'credit_card' ? 'selected' : '' }}>Kredi Kartı</option>
                        <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Banka Transferi</option>
                        <option value="online" {{ request('payment_method') === 'online' ? 'selected' : '' }}>Online</option>
                    </x-form-select>
                </div>

                <!-- Start Date Filter -->
                <div class="col-6 col-md-2">
                    <x-date-input name="start_date" label="Başlangıç Tarihi" :value="request('start_date')" :pill="true" />
                </div>

                <!-- End Date Filter -->
                <div class="col-6 col-md-2">
                    <x-date-input name="end_date" label="Bitiş Tarihi" :value="request('end_date')" :pill="true" />
                </div>

                <!-- Search Submit Button -->
                <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-1 fw-medium" style="height: 38px;">
                        <i class="ti ti-filter"></i> Filtrele
                    </button>
                    @if(request()->anyFilled(['transaction_type', 'payment_method', 'start_date', 'end_date', 'search']) || in_array(request('date_filter'), ['daily', 'yearly', 'all', 'custom']))
                        <a href="{{ route('finance.transactions') }}" class="btn btn-light rounded-pill px-2 shadow-sm d-inline-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="height: 38px; width: 38px;" title="Filtreleri Temizle">
                            <i class="ti ti-rotate"></i>
                        </a>
                    @endif
                </div>
            </form>
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
                            <tr class="border-bottom border-light hover-bg-light transition-all clickable-row" data-href="{{ route('finance.transactions.show', $transaction) }}" title="İşlem Detayını Gör">
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
                                    <div class="d-flex flex-column">
                                        <span class="text-dark fw-medium">{{ $transaction->category ?? '-' }}</span>
                                        <span class="text-secondary text-truncate small" style="max-width: 150px;" title="{{ $transaction->description }}">
                                            {{ $transaction->description ?? 'Açıklama yok' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary small d-flex align-items-center gap-1">
                                        <i class="ti ti-user-circle"></i> {{ $transaction->createdBy->full_name ?? 'Sistem' }}
                                    </span>
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
                            <x-empty-state 
                                colspan="8" 
                                title="Filtreye uygun finansal işlem bulunamadı" 
                                description="Filtre kriterlerinizi değiştirmeyi veya yeni kasa işlemi eklemeyi deneyin." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($transactions->hasPages())
                <div class="card-footer bg-white border-top border-light-subtle py-3 px-4">
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
            <form action="{{ route('finance.transactions.store') }}" method="POST" enctype="multipart/form-data">
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
                    
                    <!-- Dynamic Debt Integration -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" id="debt_label">İlişkili Alacak / Müşteri Borcu (Opsiyonel)</label>
                        <select name="debt_id" id="debt_select" class="form-select border-0 bg-light">
                            <option value="">Seçilmedi (Serbest Gelir)</option>
                        </select>
                        <div class="form-text" id="debt_help_text">Bize borcu olan bir müşteriden alacak tahsilatı yapıyorsanız listeden seçin.</div>
                    </div>

                    <!-- Category & Amount Row -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-secondary">Kategori</label>
                            <input type="text" name="category" id="transaction_category" class="form-control border-0 bg-light rounded-3" placeholder="Örn: Maaş, Fatura, Satış" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-secondary">Tutar (₺)</label>
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light">₺</span>
                                <input type="number" step="0.01" min="0.01" name="amount" id="transaction_amount" class="form-control border-0 bg-light rounded-end-3" placeholder="0,00" required>
                            </div>
                        </div>
                    </div>

                    <!-- Date & Payment Method Row -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-secondary">İşlem Tarihi</label>
                            <input type="datetime-local" name="transaction_date" class="form-control border-0 bg-light" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-secondary">Ödeme Yöntemi</label>
                            <select name="payment_method" class="form-select border-0 bg-light" required>
                                <option value="cash">Nakit</option>
                                <option value="credit_card">Kredi Kartı</option>
                                <option value="bank_transfer">Banka Transferi</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Açıklama</label>
                        <textarea name="description" id="transaction_description" rows="2" class="form-control border-0 bg-light" placeholder="İşlem açıklaması yazın..."></textarea>
                    </div>

                    <!-- Document Upload -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary">Belge / Fiş Görseli</label>
                        <input type="file" name="document_path" class="form-control border-0 bg-light" accept=".jpg,.jpeg,.png,.pdf">
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

@php
    $receivablesData = $receivables->map(function($d) {
        $name = $d->counterparty_name ?: ($d->customer?->full_name ?: 'Müşteri');
        return [
            'id' => $d->id,
            'name' => $name,
            'remaining' => (float)$d->remaining_amount,
            'formatted_remaining' => number_format($d->remaining_amount, 2, ',', '.'),
            'category' => 'Alacak Tahsilatı',
            'description' => 'Tahsilat - ' . $name,
        ];
    })->values();

    $payablesData = $payables->map(function($d) {
        $name = $d->counterparty_name ?: ($d->customer?->full_name ?: 'Tedarikçi / Borçlu');
        return [
            'id' => $d->id,
            'name' => $name,
            'remaining' => (float)$d->remaining_amount,
            'formatted_remaining' => number_format($d->remaining_amount, 2, ',', '.'),
            'category' => 'Borç Ödemesi',
            'description' => 'Ödeme - ' . $name,
        ];
    })->values();
@endphp

@push('scripts')
<script nonce="{{ $cspNonce }}">
    const receivablesData = @json($receivablesData);
    const payablesData = @json($payablesData);

    function updateDebtDropdown() {
        const typeIncomeRadio = document.getElementById('type_income');
        const isIncome = typeIncomeRadio ? typeIncomeRadio.checked : true;
        const debtSelect = document.getElementById('debt_select');
        const debtLabel = document.getElementById('debt_label');
        const debtHelpText = document.getElementById('debt_help_text');

        if (!debtSelect) return;

        debtSelect.innerHTML = '';

        if (isIncome) {
            if (debtLabel) debtLabel.textContent = 'İlişkili Alacak / Müşteri Borcu (Opsiyonel)';
            if (debtHelpText) debtHelpText.textContent = 'Bize borcu olan bir müşteriden alacak tahsilatı yapıyorsanız listeden seçin. Kasa girişi (+) olarak kaydedilir.';

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = 'Seçilmedi (Serbest Gelir)';
            debtSelect.appendChild(defaultOpt);

            if (receivablesData.length > 0) {
                const optgroup = document.createElement('optgroup');
                optgroup.label = 'Alacaklarımız (Bize Borcu Olanlar)';
                receivablesData.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = `${item.name} - Kalan: ₺${item.formatted_remaining}`;
                    opt.dataset.remaining = item.remaining;
                    opt.dataset.category = item.category;
                    opt.dataset.description = item.description;
                    optgroup.appendChild(opt);
                });
                debtSelect.appendChild(optgroup);
            }
        } else {
            if (debtLabel) debtLabel.textContent = 'İlişkili Borcumuz / Ödeme (Opsiyonel)';
            if (debtHelpText) debtHelpText.textContent = 'Bizim yapacağımız bir borç/fatura ödemesi ise listeden seçin. Kasa çıkışı (-) olarak kaydedilir.';

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = 'Seçilmedi (Serbest Gider)';
            debtSelect.appendChild(defaultOpt);

            if (payablesData.length > 0) {
                const optgroup = document.createElement('optgroup');
                optgroup.label = 'Borçlarımız (Bizim Ödemelerimiz)';
                payablesData.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = `${item.name} - Kalan: ₺${item.formatted_remaining}`;
                    opt.dataset.remaining = item.remaining;
                    opt.dataset.category = item.category;
                    opt.dataset.description = item.description;
                    optgroup.appendChild(opt);
                });
                debtSelect.appendChild(optgroup);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // İlk yüklemede borç açılır listesini hazırla
        updateDebtDropdown();

        const typeIncome = document.getElementById('type_income');
        const typeExpense = document.getElementById('type_expense');
        const debtSelect = document.getElementById('debt_select');
        const categoryInput = document.getElementById('transaction_category');
        const amountInput = document.getElementById('transaction_amount');
        const descInput = document.getElementById('transaction_description');

        if (typeIncome) {
            typeIncome.addEventListener('change', () => {
                updateDebtDropdown();
            });
        }

        if (typeExpense) {
            typeExpense.addEventListener('change', () => {
                updateDebtDropdown();
            });
        }

        // Modal her açıldığında tipi ve listeyi tazele
        const addModal = document.getElementById('addTransactionModal');
        if (addModal) {
            addModal.addEventListener('show.bs.modal', () => {
                updateDebtDropdown();
            });
        }

        // Borç veya Alacak seçildiğinde tutar, kategori ve açıklamayı otomatik doldur
        if (debtSelect) {
            debtSelect.addEventListener('change', function() {
                const selectedOpt = this.options[this.selectedIndex];
                if (selectedOpt && selectedOpt.value) {
                    if (selectedOpt.dataset.remaining && amountInput) {
                        amountInput.value = selectedOpt.dataset.remaining;
                    }
                    if (selectedOpt.dataset.category && categoryInput) {
                        categoryInput.value = selectedOpt.dataset.category;
                    }
                    if (selectedOpt.dataset.description && descInput) {
                        descInput.value = selectedOpt.dataset.description;
                    }
                }
            });
        }

        // Üst Tarih Filtresi (Bugün, Bu Ay, Bu Yıl, Tüm Zamanlar)
        const topDateSelect = document.getElementById('top_date_filter_select');
        const dateFilterForm = document.getElementById('dateFilterForm');
        if (topDateSelect && dateFilterForm) {
            topDateSelect.addEventListener('change', () => {
                dateFilterForm.submit();
            });
        }

        // Alt Filtre Formu (Tarih aralığı girildiğinde date_filter'ı custom olarak işaretle)
        const bottomFilterForm = document.getElementById('bottomFilterForm');
        if (bottomFilterForm) {
            bottomFilterForm.addEventListener('submit', () => {
                const startVal = document.querySelector('input[name="start_date"]')?.value;
                const endVal = document.querySelector('input[name="end_date"]')?.value;
                const hiddenDateFilter = document.getElementById('bottom_date_filter');
                if (startVal || endVal) {
                    if (hiddenDateFilter) hiddenDateFilter.value = 'custom';
                }
            });
        }

        // Satır tıklama yönlendirmesi
        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', function(e) {
                if (e.target.closest('button, form, a, input, select, label')) return;
                const href = this.getAttribute('data-href');
                if (href) window.location.href = href;
            });
        });
    });
</script>
@endpush
