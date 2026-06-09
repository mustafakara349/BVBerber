@extends('layouts.app')
@section('title', 'Borç Takibi - B&V Barber')
@section('content')

<!-- Modern Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Borç Takibi</h1>
                <p class="text-muted mb-0">Müşterilerinizin ödenmemiş randevu ödemelerini ve manuel borç kayıtlarını yönetin.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addDebtModal">
                    <i class="ti ti-plus fs-5"></i> Yeni Borç Ekle
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <!-- Total Debt Card -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute end-0 bottom-0 opacity-10 mb-n3 me-n2">
                    <i class="ti ti-file-invoice" style="font-size: 110px; line-height: 1;"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-2 rounded-3" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="ti ti-file-text fs-4 text-white"></i>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 text-white" style="background-color: rgba(255, 255, 255, 0.25);">Filtrelenmiş Toplam</span>
                </div>
                <h6 class="text-white text-opacity-75 small mb-1 fw-medium text-uppercase tracking-wider">Toplam Borç</h6>
                <h3 class="fs-2 fw-bold mb-0">₺{{ number_format($totalDebt, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- Total Paid Card -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute end-0 bottom-0 opacity-10 mb-n3 me-n2">
                    <i class="ti ti-checkbox" style="font-size: 110px; line-height: 1;"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-2 rounded-3" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="ti ti-circle-check fs-4 text-white"></i>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 text-white" style="background-color: rgba(255, 255, 255, 0.25);">Bugün Tahsil Edilen</span>
                </div>
                <h6 class="text-white text-opacity-75 small mb-1 fw-medium text-uppercase tracking-wider">Bugün Tahsil Edilen</h6>
                <h3 class="fs-2 fw-bold mb-0">₺{{ number_format($todayPaid, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- Remaining Debt Card -->
    <div class="col-12 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute end-0 bottom-0 opacity-10 mb-n3 me-n2">
                    <i class="ti ti-alert-triangle" style="font-size: 110px; line-height: 1;"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-2 rounded-3" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="ti ti-alert-circle fs-4 text-white"></i>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 text-white" style="background-color: rgba(255, 255, 255, 0.25);">Kalan Bakiye</span>
                </div>
                <h6 class="text-white text-opacity-75 small mb-1 fw-medium text-uppercase tracking-wider">Kalan Net Borç</h6>
                <h3 class="fs-2 fw-bold mb-0">₺{{ number_format($remainingDebt, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Filter Panel -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-filter text-primary fs-4"></i>
                    <h5 class="card-title fw-bold text-dark mb-0 fs-6">Gelişmiş Filtreleme</h5>
                </div>
                @if(request()->anyFilled(['status', 'type', 'search']))
                    <a href="{{ route('finance.debts.index') }}" class="btn btn-light rounded-pill btn-sm text-secondary d-flex align-items-center gap-1">
                        <i class="ti ti-rotate"></i> Filtreleri Temizle
                    </a>
                @endif
            </div>
            <div class="card-body p-4 bg-light bg-opacity-30">
                <form action="{{ route('finance.debts.index') }}" method="GET" class="row g-3">
                    <!-- Status Filter -->
                    <div class="col-12 col-md-3">
                        <label class="form-label text-secondary fw-semibold small">Borç Durumu</label>
                        <select name="status" class="form-select border-0 shadow-sm rounded-3">
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Tüm Borçlar (Ödenenler Dahil)</option>
                            <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>Aktif Borçlar (Ödenmemiş & Kısmi)</option>
                            <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Ödenmemiş</option>
                            <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Kısmi Ödenmiş</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Tamamen Ödenmiş</option>
                        </select>
                    </div>

                    <!-- Type Filter -->
                    <div class="col-12 col-md-3">
                        <label class="form-label text-secondary fw-semibold small">Borç Kaynağı</label>
                        <select name="type" class="form-select border-0 shadow-sm rounded-3">
                            <option value="all" {{ request('type') === 'all' || !request('type') ? 'selected' : '' }}>Tüm Kaynaklar</option>
                            <option value="appointment" {{ request('type') === 'appointment' ? 'selected' : '' }}>Randevu Borçları</option>
                            <option value="manual" {{ request('type') === 'manual' ? 'selected' : '' }}>Manuel Borçlar</option>
                        </select>
                    </div>

                    <!-- Search Filter -->
                    <div class="col-12 col-md-4">
                        <label class="form-label text-secondary fw-semibold small">Arama</label>
                        <input type="text" name="search" class="form-control border-0 shadow-sm rounded-3" placeholder="Müşteri adı, telefon, randevu kodu..." value="{{ request('search') }}">
                    </div>

                    <!-- Search Button -->
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

<!-- Debts Table List -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0">Müşteri</th>
                                <th class="py-3 border-0">Borç Kaynağı</th>
                                <th class="py-3 border-0">Borç Tarihi</th>
                                <th class="py-3 border-0">Vade Tarihi</th>
                                <th class="py-3 border-0">Toplam Tutar</th>
                                <th class="py-3 border-0">Ödenen</th>
                                <th class="py-3 border-0">Kalan Borç</th>
                                <th class="py-3 border-0">Durum</th>
                                <th class="pe-4 py-3 text-end border-0">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debts as $debt)
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-6 me-3" style="width: 40px; height: 40px;">
                                            {{ mb_substr($debt->customer->first_name ?? 'M', 0, 1) }}{{ mb_substr($debt->customer->last_name ?? 'B', 0, 1) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold text-dark">{{ $debt->customer->full_name ?? 'Bilinmeyen Müşteri' }}</h6>
                                            <span class="text-secondary small">{{ $debt->customer->phone ?? 'Telefon Yok' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($debt->appointment)
                                        <a href="{{ route('appointments.show', $debt->appointment) }}" class="text-primary fw-semibold small text-decoration-none d-inline-flex align-items-center gap-1">
                                            <i class="ti ti-calendar-event"></i> Randevu #{{ $debt->appointment->appointment_code }}
                                        </a>
                                    @else
                                        <span class="text-secondary small d-inline-flex align-items-center gap-1">
                                            <i class="ti ti-file-text"></i> Manuel: {{ Str::limit($debt->description, 25) }}
                                        </span>
                                    @endif
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
                                <td class="fw-semibold">
                                    ₺{{ number_format($debt->amount, 2, ',', '.') }}
                                </td>
                                <td class="text-success small">
                                    ₺{{ number_format($debt->paid_amount, 2, ',', '.') }}
                                </td>
                                <td class="fw-bold {{ $debt->status === 'paid' ? 'text-secondary' : 'text-danger' }}">
                                    ₺{{ number_format($debt->remaining_amount, 2, ',', '.') }}
                                </td>
                                <td>
                                    @if($debt->status === 'unpaid')
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-bold">Ödenmedi</span>
                                    @elseif($debt->status === 'partial')
                                        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fw-bold">Kısmi Ödeme</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold">Ödendi</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-2">
                                        @if($debt->status !== 'paid')
                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 d-flex align-items-center gap-1 pay-debt-btn" 
                                                data-bs-toggle="modal"
                                                data-bs-target="#payDebtModal"
                                                data-id="{{ $debt->id }}" 
                                                data-customer="{{ $debt->customer->full_name ?? 'Müşteri' }}" 
                                                data-remaining="{{ $debt->remaining_amount }}" 
                                                data-source="{{ $debt->appointment ? 'Randevu #' . $debt->appointment->appointment_code : 'Manuel Borç' }}">
                                            <i class="ti ti-cash fs-6"></i> Ödeme Al
                                        </button>
                                        @endif

                                        @if(!$debt->appointment)
                                        <form action="{{ route('finance.debts.destroy', $debt) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu borç kaydını tamamen silmek istediğinize emin misiniz?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-2 border-0" title="Borcu Sil">
                                                <i class="ti ti-trash fs-5"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="ti ti-mood-empty fs-1 mb-2 d-block text-secondary opacity-50"></i>
                                    <h5>Borç kaydı bulunamadı.</h5>
                                    <p class="small text-secondary mb-0">Tüm borçlar ödenmiş olabilir veya filtre kriterleriniz çok dar olabilir.</p>
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

<!-- Add Manual Debt Modal -->
<div class="modal fade" id="addDebtModal" tabindex="-1" aria-labelledby="addDebtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="addDebtModalLabel">Yeni Borç Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="{{ route('finance.debts.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <!-- Customer Select -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Müşteri Seçin</label>
                        <select name="customer_id" class="form-select border-0 bg-light select-picker-custom" required>
                            <option value="">Müşteri Seçiniz...</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->id }}">{{ $cust->full_name }} ({{ $cust->phone ?? 'Telefon Yok' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Amount -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Borç Tutarı (₺)</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light">₺</span>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control border-0 bg-light rounded-end-3" placeholder="0,00" required>
                        </div>
                    </div>

                    <!-- Due Date -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Vade Tarihi (İsteğe Bağlı)</label>
                        <input type="date" name="due_date" class="form-control border-0 bg-light">
                    </div>

                    <!-- Description -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary">Açıklama</label>
                        <textarea name="description" rows="3" class="form-control border-0 bg-light" placeholder="Borç nedenini açıklayın (örn: Kozmetik ürün satışı, ek hizmet vb.)..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Borcu Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Pay Debt Modal -->
<div class="modal fade" id="payDebtModal" tabindex="-1" aria-labelledby="payDebtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-success text-white py-3">
                <h5 class="modal-title fw-bold" id="payDebtModalLabel">Borç Ödemesi Tahsil Et</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form id="payDebtForm" action="" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <!-- Debt Info Display -->
                    <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success rounded-3 mb-3 small d-flex flex-column gap-1">
                        <div><strong>Müşteri:</strong> <span id="payCustomerName">-</span></div>
                        <div><strong>Borç Kaynağı:</strong> <span id="paySource">-</span></div>
                        <div><strong>Kalan Toplam Borç:</strong> <span class="fw-bold">₺<span id="payRemainingText">0,00</span></span></div>
                    </div>

                    <!-- Payment Amount -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Ödenen Tutar (₺)</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light">₺</span>
                            <input type="number" step="0.01" min="0.01" id="payAmountInput" name="amount" class="form-control border-0 bg-light rounded-end-3" placeholder="0,00" required>
                        </div>
                        <small class="text-muted d-block mt-1">Gerekirse kısmi ödeme tahsil edebilirsiniz.</small>
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
                        <label class="form-label fw-semibold text-secondary">Ödeme Tarihi</label>
                        <input type="datetime-local" name="paid_at" class="form-control border-0 bg-light" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>

                    <!-- Reference Number -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary">Referans / İşlem Kodu (İsteğe Bağlı)</label>
                        <input type="text" name="transaction_reference" class="form-control border-0 bg-light" placeholder="Dekont no, slip no vb...">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm text-white">Ödemeyi Kaydet</button>
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
                // Button that triggered the modal
                const button = event.relatedTarget;
                
                // Extract info from data-bs-* attributes
                const debtId = button.getAttribute('data-id');
                const customerName = button.getAttribute('data-customer');
                const remaining = parseFloat(button.getAttribute('data-remaining'));
                const source = button.getAttribute('data-source');

                // Get modal elements
                const payForm = document.getElementById('payDebtForm');
                const customerNameSpan = document.getElementById('payCustomerName');
                const sourceSpan = document.getElementById('paySource');
                const remainingSpan = document.getElementById('payRemainingText');
                const amountInput = document.getElementById('payAmountInput');

                // Set Form Action Route URL
                payForm.action = `/finance/debts/${debtId}/pay`;

                // Set Modal Information Fields
                customerNameSpan.textContent = customerName;
                sourceSpan.textContent = source;
                remainingSpan.textContent = remaining.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                
                // Set amount defaults
                amountInput.value = remaining.toFixed(2);
                amountInput.max = remaining.toFixed(2);
            });
        }
    });
</script>
@endpush
