@extends('layouts.app')

@section('title', 'Randevu Detay - B&V Barber')

@section('content')

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="ti ti-circle-check fs-4"></i>
    <div>{{ session('success') }}</div>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4" role="alert">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fs-3 mb-1">Randevu #{{ $appointment->appointment_code }}</h1>
                <p class="mb-0">Randevu detayları</p>
            </div>
            <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i>Geri
            </a>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Main Info --}}
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header bg-transparent px-4 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 h5">Randevu Bilgileri</h4>
                    <span class="badge bg-{{ $appointment->status->color() }}-subtle text-{{ $appointment->status->color() }} border border-{{ $appointment->status->color() }} fs-6">
                        {{ $appointment->status->label() }}
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Tarih</small>
                        <span class="fw-semibold">{{ $appointment->start_at->format('d.m.Y') }}</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Saat</small>
                        <span class="fw-semibold">{{ $appointment->start_at->format('H:i') }} - {{ $appointment->end_at->format('H:i') }}</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Süre</small>
                        <span>{{ $appointment->total_duration }} dakika</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Kaynak</small>
                        <span>{{ $appointment->source?->label() }}</span>
                    </div>
                    @if($appointment->cancellation_reason)
                    <div class="col-12 mt-3 pt-3 border-top">
                        <small class="text-danger d-block fw-bold"><i class="ti ti-alert-triangle me-1"></i>Randevu Red/İptal Nedeni</small>
                        <span class="text-dark bg-danger-subtle px-3 py-2 rounded d-block mt-1 fw-semibold" style="border-left: 4px solid #dc3545; font-size: 13px;">
                            {{ $appointment->cancellation_reason }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Services --}}
        <div class="card mb-3">
            <div class="card-header bg-transparent px-4 py-3">
                <h4 class="mb-0 h5">Hizmetler</h4>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr><th>Hizmet</th><th>Süre</th><th>Birim Fiyat</th><th>Adet</th><th>Toplam</th></tr>
                    </thead>
                    <tbody>
                        @foreach($appointment->appointmentServices as $as)
                        <tr>
                            <td>{{ $as->service?->name }}</td>
                            <td>{{ $as->duration_minutes }} dk</td>
                            <td>₺{{ number_format($as->unit_price, 0, ',', '.') }}</td>
                            <td>{{ $as->quantity }}</td>
                            <td class="fw-semibold">₺{{ number_format($as->total_price, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-semibold">Toplam:</td>
                            <td class="fw-bold fs-5 text-primary">₺{{ number_format($appointment->total_price, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Status Logs --}}
        <div class="card">
            <div class="card-header bg-transparent px-4 py-3">
                <h4 class="mb-0 h5">Durum Geçmişi</h4>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($appointment->statusLogs->sortByDesc('created_at') as $log)
                <li class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <div>
                            @if($log->old_status)
                                <span class="badge bg-secondary-subtle text-secondary">{{ $log->old_status }}</span>
                                <i class="ti ti-arrow-right mx-1"></i>
                            @endif
                            <span class="badge bg-primary-subtle text-primary">{{ $log->new_status }}</span>
                            @if($log->note)
                                <small class="text-muted ms-2">{{ $log->note }}</small>
                            @endif
                        </div>
                        <small class="text-muted">
                            {{ $log->changedBy?->full_name ?? 'Sistem' }} •
                            {{ $log->created_at->format('d.m.Y H:i') }}
                        </small>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        {{-- Customer --}}
        <div class="card mb-3">
            <div class="card-header bg-transparent px-4 py-3">
                <h4 class="mb-0 h5">Müşteri</h4>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    @if($appointment->customer?->profile_photo)
                        <img src="{{ $appointment->customer->profile_photo }}" class="avatar avatar-lg rounded-circle" alt="">
                    @else
                        <div class="avatar avatar-lg rounded-circle avatar-primary">
                            <span class="avatar-initials">{{ substr($appointment->customer?->first_name ?? '?', 0, 1) }}</span>
                        </div>
                    @endif
                    <div>
                        <h5 class="mb-0">{{ $appointment->customer?->full_name }}</h5>
                        <small class="text-muted">{{ $appointment->customer?->phone }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Barber --}}
        <div class="card mb-3">
            <div class="card-header bg-transparent px-4 py-3">
                <h4 class="mb-0 h5">Berber</h4>
            </div>
            <div class="card-body p-4">
                <h5 class="mb-1">{{ $appointment->employee?->user?->full_name }}</h5>
                <small class="text-muted">{{ $appointment->employee?->title }}</small>
            </div>
        </div>

        {{-- Payment Info & History --}}
        <div class="card mb-3 shadow-sm border-0 rounded-3">
            <div class="card-header bg-transparent px-4 py-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0 h5 text-dark fw-bold">Ödeme Bilgisi</h4>
                @php
                    $totalPaid = $appointment->payments->sum('amount');
                    $remaining = $appointment->total_price - $totalPaid;
                @endphp
                <span class="badge bg-{{ $appointment->payment_status->color() }}-subtle text-{{ $appointment->payment_status->color() }} border border-{{ $appointment->payment_status->color() }} px-3 py-2 rounded-pill fw-bold">
                    {{ $appointment->payment_status->label() }}
                </span>
            </div>
            <div class="card-body p-4">
                <div class="d-flex flex-column gap-2 mb-3 bg-light p-3 rounded-3 border border-white">
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary small">Toplam Tutar:</span>
                        <span class="fw-bold text-dark">₺{{ number_format($appointment->total_price, 2, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary small">Toplam Ödenen:</span>
                        <span class="fw-bold text-success">₺{{ number_format($totalPaid, 2, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2">
                        <span class="text-secondary small fw-semibold">Kalan Tutar:</span>
                        <span class="fw-bold {{ $remaining > 0 ? 'text-danger' : 'text-success' }}">₺{{ number_format($remaining, 2, ',', '.') }}</span>
                    </div>
                </div>

                @if($appointment->payments->count() > 0)
                    <h6 class="fw-bold text-dark border-top pt-3 mb-2 small">Ödeme Geçmişi</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 13px;">
                            <thead>
                                <tr class="text-secondary border-bottom">
                                    <th class="ps-0 py-1">Tarih</th>
                                    <th class="py-1 text-center">Yöntem</th>
                                    <th class="py-1 text-end">Tutar</th>
                                    <th class="pe-0 py-1 text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($appointment->payments as $payment)
                                <tr class="border-bottom border-light">
                                    <td class="ps-0 py-2 text-dark fw-medium">{{ $payment->paid_at->format('d.m.Y') }}</td>
                                    <td class="py-2 text-center">
                                        <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill" style="font-size: 10px;">
                                            {{ $payment->payment_method->label() }}
                                        </span>
                                    </td>
                                    <td class="py-2 text-end fw-bold text-dark">₺{{ number_format($payment->amount, 2, ',', '.') }}</td>
                                    <td class="pe-0 py-2 text-end">
                                        <form action="{{ route('appointments.payments.destroy', [$appointment, $payment]) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Bu ödemeyi silmek istediğinize emin misiniz? Bu işlem kasadan geliri de silecektir.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-0 border-0" title="Ödemeyi Sil">
                                                <i class="ti ti-trash" style="font-size: 14px;"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if($remaining > 0 && $appointment->status->value === 'completed')
                    <button type="button" class="btn btn-success btn-sm w-100 d-flex align-items-center justify-content-center gap-1 shadow-sm py-2 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#receivePaymentModal">
                        <i class="ti ti-wallet fs-5"></i> Ödeme Kaydet (Ödeme Al)
                    </button>
                @elseif($appointment->status->value !== 'completed')
                    <div class="alert alert-info border-0 p-2 mb-0 small text-center" role="alert" style="border-radius: 12px;">
                        <i class="ti ti-info-circle me-1"></i> Ödeme kaydedebilmek için önce randevu durumunu "Tamamlandı" olarak güncelleyin.
                    </div>
                @endif
            </div>
        </div>

        {{-- Status Update --}}
        <div class="card mb-3 shadow-sm border-0 rounded-3">
            <div class="card-header bg-transparent px-4 py-3">
                <h4 class="mb-0 h5 text-dark fw-bold">Durumu Güncelle</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('appointments.update-status', $appointment) }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <select name="status" class="form-select border-0 bg-light rounded-3">
                            @foreach(\App\Enums\AppointmentStatus::cases() as $s)
                                <option value="{{ $s->value }}" {{ $appointment->status === $s ? 'selected' : '' }}>{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <textarea name="note" class="form-control border-0 bg-light rounded-3" rows="2" placeholder="Durum değişikliğine dair not girin (opsiyonel)"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100 py-2 rounded-pill fw-bold"><i class="ti ti-device-floppy me-1"></i> Durumu Güncelle</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
@if($remaining > 0 && $appointment->status->value === 'completed')
<div class="modal fade" id="receivePaymentModal" tabindex="-1" aria-labelledby="receivePaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-success text-white py-3">
                <h5 class="modal-title fw-bold" id="receivePaymentModalLabel">Ödeme Al / Kaydet</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="{{ route('appointments.payments.store', $appointment) }}" method="POST">
                @csrf
                <div class="modal-body p-4 text-dark">
                    <div class="alert alert-success border-0 p-3 rounded-3 mb-3" role="alert">
                        <div class="d-flex justify-content-between fw-bold mb-1">
                            <span>Randevu Toplam Fiyatı:</span>
                            <span>₺{{ number_format($appointment->total_price, 2, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between small text-success">
                            <span>Kalan Unpaid Bakiye:</span>
                            <span>₺{{ number_format($remaining, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Amount -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Ödenen Tutar (₺)</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light">₺</span>
                            <input type="number" step="0.01" min="0.01" max="{{ $remaining }}" name="amount" class="form-control border-0 bg-light rounded-end-3" value="{{ $remaining }}" required>
                        </div>
                    </div>

                    <!-- Paid At & Payment Method -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-secondary">Ödeme Yöntemi</label>
                            <select name="payment_method" class="form-select border-0 bg-light" required>
                                <option value="cash">Nakit (Kasa)</option>
                                <option value="credit_card">Kredi Kartı</option>
                                <option value="bank_transfer">Banka Transferi</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-secondary">Ödeme Tarihi</label>
                            <input type="date" name="paid_at" class="form-control border-0 bg-light" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <!-- Reference Number -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary">Referans / İşlem Kodu (İsteğe Bağlı)</label>
                        <input type="text" name="transaction_reference" class="form-control border-0 bg-light" placeholder="Havale referansı, fiş numarası vb...">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">Ödemeyi Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
