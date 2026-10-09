@extends('layouts.app')
@section('title', 'Borç Detayları - B&V Barber')
@section('content')

<!-- Modern Header & Return Link -->
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ $type === 'receivable' ? route('finance.receivables.index') : route('finance.payables.index') }}" class="btn btn-light rounded-pill px-3 shadow-sm mb-3 d-inline-flex align-items-center gap-2">
            <i class="ti ti-arrow-left"></i> Geri Dön
        </a>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">
                    {{ $debts->first()?->customer->full_name ?? $counterpartyName ?? 'Bilinmeyen Kişi' }} 
                    <span class="fs-5 text-muted fw-normal">({{ $type === 'receivable' ? 'Alacak Detayları' : 'Ödeme Detayları' }})</span>
                </h1>
                <p class="text-muted mb-0">Kişiye ait tüm geçmiş ve mevcut borç/alacak işlemleri.</p>
            </div>
            <div>
                <button type="button" class="btn btn-{{ $type === 'receivable' ? 'primary' : 'danger' }} rounded-pill px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addDebtModal">
                    <i class="ti ti-plus fs-5"></i> Yeni Kayıt Ekle
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm bg-light">
            <div class="card-body p-4">
                <h6 class="text-muted small mb-1 fw-medium text-uppercase">Toplam Tutar</h6>
                <h3 class="fs-3 fw-bold mb-0 text-dark">₺{{ number_format($totalDebt, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm bg-light">
            <div class="card-body p-4">
                <h6 class="text-muted small mb-1 fw-medium text-uppercase">{{ $type === 'receivable' ? 'Tahsil Edilen' : 'Ödenen' }}</h6>
                <h3 class="fs-3 fw-bold mb-0 text-success">₺{{ number_format($totalPaid, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, {{ $type === 'receivable' ? '#f59e0b, #d97706' : '#ef4444, #dc2626' }});">
            <div class="card-body p-4">
                <h6 class="text-white text-opacity-75 small mb-1 fw-medium text-uppercase">Kalan Bakiye</h6>
                <h3 class="fs-3 fw-bold mb-0">₺{{ number_format($remainingDebt, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Debts Table List -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0">Kayıt Türü & Kaynağı</th>
                                <th class="py-3 border-0">Kayıt Tarihi</th>
                                <th class="py-3 border-0">Vade Tarihi</th>
                                <th class="py-3 border-0">Kalan Bakiye</th>
                                <th class="py-3 border-0">Durum</th>
                                <th class="pe-4 py-3 text-end border-0">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debts as $debt)
                            @php
                                $allPayments = collect();
                                if ($debt->appointment && $debt->appointment->payments) {
                                    foreach($debt->appointment->payments as $pmt) {
                                        $allPayments->push([
                                            'date' => $pmt->paid_at ?? $pmt->created_at ?? $debt->created_at,
                                            'amount' => $pmt->amount,
                                            'method' => $pmt->payment_method->value ?? $pmt->payment_method,
                                            'reference' => $pmt->transaction_reference,
                                        ]);
                                    }
                                } elseif ($debt->transactions) {
                                    foreach($debt->transactions as $tx) {
                                        $allPayments->push([
                                            'date' => $tx->transaction_date ?? $tx->created_at,
                                            'amount' => $tx->amount,
                                            'method' => $tx->payment_method->value ?? $tx->payment_method,
                                            'reference' => $tx->transaction_reference,
                                        ]);
                                    }
                                }
                                $allPayments = $allPayments->sortByDesc('date');
                            @endphp
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <div class="d-flex flex-column">
                                        @if($debt->appointment)
                                            <a href="{{ route('appointments.show', $debt->appointment) }}" class="text-primary fw-bold small text-decoration-none d-inline-flex align-items-center gap-1 mb-1">
                                                <i class="ti ti-calendar-event"></i> Randevu #{{ $debt->appointment->appointment_code }}
                                            </a>
                                        @else
                                            <span class="text-dark fw-bold small d-inline-flex align-items-center gap-1 mb-1">
                                                <i class="ti ti-file-text text-secondary"></i> {{ Str::limit($debt->description, 35) }}
                                            </span>
                                        @endif
                                        <span class="text-secondary small">Asıl Tutar: ₺{{ number_format($debt->amount, 2, ',', '.') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary small">{{ $debt->created_at->format('d.m.Y H:i') }}</span>
                                </td>
                                <td>
                                    @if($debt->due_date)
                                        <span class="fw-semibold {{ $debt->status !== 'paid' && $debt->due_date->isPast() ? 'text-danger' : 'text-dark' }} small">
                                            {{ $debt->due_date->format('d.m.Y') }}
                                            @if($debt->status !== 'paid' && $debt->due_date->isPast())
                                                <span class="badge bg-danger-subtle text-danger ms-1 small" style="font-size: 0.65rem;">Gecikti</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-secondary small">-</span>
                                    @endif
                                </td>
                                <td class="fw-bold fs-6 {{ $debt->status === 'paid' ? 'text-secondary' : 'text-danger' }}">
                                    ₺{{ number_format($debt->remaining_amount, 2, ',', '.') }}
                                </td>
                                <td>
                                    @if($debt->status === 'unpaid')
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-bold">Ödenmedi</span>
                                    @elseif($debt->status === 'partial')
                                        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fw-bold">Kısmi Ödeme</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold">Ödendi (Sıfırlandı)</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-2">
                                        @if($allPayments->count() > 0)
                                        <button class="btn btn-sm btn-light border rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#transactions-{{ $debt->id }}" aria-expanded="false" title="İşlem Geçmişi">
                                            <i class="ti ti-history fs-5"></i>
                                        </button>
                                        @endif

                                        @if($debt->status !== 'paid')
                                        <button type="button" class="btn btn-sm btn-{{ $type === 'receivable' ? 'success' : 'danger' }} rounded-pill px-3 d-flex align-items-center gap-1 pay-debt-btn" 
                                                data-bs-toggle="modal"
                                                data-bs-target="#payDebtModal"
                                                data-id="{{ $debt->id }}" 
                                                data-customer="{{ $debt->customer->full_name ?? $debt->counterparty_name ?? 'Kişi' }}" 
                                                data-remaining="{{ $debt->remaining_amount }}" 
                                                data-source="{{ $debt->appointment ? 'Randevu #' . $debt->appointment->appointment_code : 'Manuel Kayıt' }}">
                                            <i class="ti ti-{{ $type === 'receivable' ? 'cash' : 'credit-card' }} fs-6"></i> 
                                            {{ $type === 'receivable' ? 'Tahsil Et' : 'Ödeme Yap' }}
                                        </button>
                                        @endif

                                        @if(!$debt->appointment)
                                        <form action="{{ route('finance.debts.destroy', $debt) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu kaydı tamamen silmek istediğinize emin misiniz?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-2 border-0" title="Sil">
                                                <i class="ti ti-trash fs-5"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @if($allPayments->count() > 0)
                            <tr class="collapse bg-light" id="transactions-{{ $debt->id }}">
                                <td colspan="6" class="p-3">
                                    <div class="card border border-light shadow-sm mb-0">
                                        <div class="card-header bg-white border-bottom py-2 d-flex align-items-center">
                                            <i class="ti ti-receipt text-secondary me-2"></i>
                                            <span class="fw-bold text-dark small">Bu Kayda Ait Ödeme / Tahsilat İşlemleri</span>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover align-middle mb-0">
                                                <thead class="bg-light text-secondary">
                                                    <tr>
                                                        <th class="ps-3 py-2 border-0 small">İşlem Tarihi</th>
                                                        <th class="py-2 border-0 small">Ödeme Yöntemi</th>
                                                        <th class="py-2 border-0 small">Referans Kodu</th>
                                                        <th class="pe-3 py-2 border-0 text-end small">İşlem Tutarı</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($allPayments as $tx)
                                                    <tr class="border-bottom border-light">
                                                        <td class="ps-3 py-2 text-dark small">{{ $tx['date']->format('d.m.Y H:i') }}</td>
                                                        <td class="py-2 text-dark small">
                                                            @php
                                                                $pm = $tx['method'];
                                                                $methods = [
                                                                    'cash' => 'Nakit',
                                                                    'credit_card' => 'Kredi Kartı',
                                                                    'bank_transfer' => 'Banka Transferi',
                                                                    'online' => 'Online',
                                                                ];
                                                            @endphp
                                                            {{ $methods[$pm] ?? ucfirst($pm) }}
                                                        </td>
                                                        <td class="py-2 text-secondary small">{{ $tx['reference'] ?? '-' }}</td>
                                                        <td class="pe-3 py-2 text-{{ $type === 'receivable' ? 'success' : 'danger' }} fw-bold text-end small">
                                                            ₺{{ number_format($tx['amount'], 2, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ti ti-mood-empty fs-1 mb-2 d-block text-secondary opacity-50"></i>
                                    <h5>Kayıt bulunamadı.</h5>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($debts->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $debts->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Pay Debt Modal -->
<div class="modal fade" id="payDebtModal" tabindex="-1" aria-labelledby="payDebtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-{{ $type === 'receivable' ? 'success' : 'danger' }} text-white py-3">
                <h5 class="modal-title fw-bold" id="payDebtModalLabel">{{ $type === 'receivable' ? 'Tahsilat Yap' : 'Ödeme Yap' }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form id="payDebtForm" action="" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <!-- Debt Info Display -->
                    <div class="alert alert-{{ $type === 'receivable' ? 'success' : 'danger' }} border-0 bg-{{ $type === 'receivable' ? 'success' : 'danger' }} bg-opacity-10 text-{{ $type === 'receivable' ? 'success' : 'danger' }} rounded-3 mb-3 small d-flex flex-column gap-1">
                        <div><strong>Kişi:</strong> <span id="payCustomerName">-</span></div>
                        <div><strong>Kayıt Kaynağı:</strong> <span id="paySource">-</span></div>
                        <div><strong>Kalan Tutar:</strong> <span class="fw-bold">₺<span id="payRemainingText">0,00</span></span></div>
                    </div>

                    <!-- Payment Amount -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">İşlem Tutarı (₺)</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light">₺</span>
                            <input type="number" step="0.01" min="0.01" id="payAmountInput" name="amount" class="form-control border-0 bg-light rounded-end-3" placeholder="0,00" required>
                        </div>
                        <small class="text-muted d-block mt-1">Gerekirse kısmi işlem yapabilirsiniz.</small>
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

                    <!-- Paid At Date -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Tarih</label>
                        <input type="datetime-local" name="paid_at" class="form-control border-0 bg-light" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>

                    <!-- Reference Number -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary">Referans Kodu (İsteğe Bağlı)</label>
                        <input type="text" name="transaction_reference" class="form-control border-0 bg-light" placeholder="Dekont no, slip no vb...">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-{{ $type === 'receivable' ? 'success' : 'danger' }} rounded-pill px-4 shadow-sm text-white">İşlemi Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Manual Debt Modal -->
<div class="modal fade" id="addDebtModal" tabindex="-1" aria-labelledby="addDebtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-{{ $type === 'receivable' ? 'primary' : 'danger' }} text-white py-3">
                <h5 class="modal-title fw-bold" id="addDebtModalLabel">Yeni {{ $type === 'receivable' ? 'Alacak' : 'Borç' }} Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="{{ route('finance.debts.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                @if($customerId)
                    <input type="hidden" name="customer_id" value="{{ $customerId }}">
                @endif
                @if($counterpartyName)
                    <input type="hidden" name="counterparty_name" value="{{ $counterpartyName }}">
                @endif
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Kişi</label>
                        <input type="text" class="form-control border-0 bg-light" value="{{ $debts->first()?->customer->full_name ?? $counterpartyName ?? '' }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Tutar (₺)</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light">₺</span>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control border-0 bg-light rounded-end-3" placeholder="0,00" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Vade Tarihi (İsteğe Bağlı)</label>
                        <input type="date" name="due_date" class="form-control border-0 bg-light">
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary">Açıklama</label>
                        <textarea name="description" rows="3" class="form-control border-0 bg-light" placeholder="Nedenini açıklayın..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-{{ $type === 'receivable' ? 'primary' : 'danger' }} rounded-pill px-4 shadow-sm">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const payDebtModal = document.getElementById('payDebtModal');
        if (payDebtModal) {
            payDebtModal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                const debtId = button.getAttribute('data-id');
                const customerName = button.getAttribute('data-customer');
                const remaining = parseFloat(button.getAttribute('data-remaining'));
                const source = button.getAttribute('data-source');

                const payForm = document.getElementById('payDebtForm');
                const customerNameSpan = document.getElementById('payCustomerName');
                const sourceSpan = document.getElementById('paySource');
                const remainingSpan = document.getElementById('payRemainingText');
                const amountInput = document.getElementById('payAmountInput');

                payForm.action = `/finance/debts/${debtId}/pay`;
                customerNameSpan.textContent = customerName;
                sourceSpan.textContent = source;
                remainingSpan.textContent = remaining.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                
                amountInput.value = remaining.toFixed(2);
                amountInput.max = remaining.toFixed(2);
            });
        }
    });
</script>
@endpush
