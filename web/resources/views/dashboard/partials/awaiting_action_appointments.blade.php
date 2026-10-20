@if(isset($awaitingActionAppointments) && $awaitingActionAppointments->count() > 0)
    <div class="row g-3 mt-1 mb-4 db-animate-fade-up db-delay-5">
        <div class="col-12">
            <div class="db-table-card border-top border-warning border-3 mb-2">
                <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center px-4 py-3 border-bottom border-warning border-opacity-25">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 bg-warning-subtle text-warning-emphasis rounded-3 d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-alert-triangle fs-5"></i>
                        </span>
                        <div>
                            <h4 class="mb-0 h5 text-dark fw-bold">İşlem Bekleyen Randevular</h4>
                            <small class="text-secondary">Süresi dolmuş fakat ödemesi veya durumu sonuçlandırılmamış randevular</small>
                        </div>
                    </div>
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold shadow-sm">
                        {{ $awaitingActionAppointments->count() }} Eylem Bekliyor
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-dark">
                            <thead>
                                <tr>
                                    <th class="ps-4">Saat / Tarih</th>
                                    <th>Müşteri</th>
                                    <th>Berber</th>
                                    <th>Hizmetler</th>
                                    <th>Tutar</th>
                                    <th class="pe-4 text-end">Hızlı Eylemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($awaitingActionAppointments as $apt)
                                <tr class="dashboard-awaiting-row" data-appointment-id="{{ $apt->id }}">
                                    <td class="ps-4">
                                        <div class="db-time-tag db-time-primary">
                                            <span>{{ $apt->start_at->format('H:i') }}</span>
                                            <i class="ti ti-calendar opacity-75"></i>
                                            <span>{{ $apt->start_at->format('d.m.Y') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($apt->customer && $apt->customer->profile_photo_url)
                                                <img src="{{ $apt->customer->profile_photo_url }}" class="rounded-circle border border-warning border-opacity-25 shadow-sm me-3 object-fit-cover" style="width: 36px; height: 36px;" alt="{{ $apt->customer->full_name }}">
                                            @else
                                                <div class="avatar avatar-sm rounded-circle bg-warning-subtle text-warning-emphasis border border-warning border-opacity-25 me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                                    {{ mb_substr($apt->customer->first_name ?? 'M', 0, 1) }}{{ mb_substr($apt->customer->last_name ?? 'M', 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <span class="d-block fw-bold text-dark">{{ $apt->customer->full_name ?? 'Bilinmeyen Müşteri' }}</span>
                                                <small class="text-muted d-flex align-items-center gap-1">
                                                    <i class="ti ti-phone fs-7"></i>
                                                    {{ $apt->customer->phone ?? 'Belirtilmemiş' }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-xs rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 0.75rem;">
                                                <i class="ti ti-cut"></i>
                                            </div>
                                            <span class="fw-semibold text-dark">{{ $apt->employee->user->full_name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1" style="max-width: 260px;">
                                            @foreach($apt->appointmentServices as $aps)
                                                <span class="badge bg-light text-dark border px-2 py-1 rounded-2 fw-normal">
                                                    {{ $aps->service->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark fs-6 font-monospace">₺{{ number_format($apt->total_price, 2, ',', '.') }}</span>
                                        @if($apt->appliedCoupon)
                                            <a tabindex="0" class="text-info ms-1" role="button" data-bs-toggle="popover" data-bs-trigger="focus" title="Kupon Kullanıldı" data-bs-content="Kupon: {{ $apt->appliedCoupon->code }} | İndirim: ₺{{ number_format($apt->discount_amount, 2, ',', '.') }}"><i class="ti ti-info-circle fs-5"></i></a>
                                        @elseif($apt->appliedCampaign)
                                            <a tabindex="0" class="text-info ms-1" role="button" data-bs-toggle="popover" data-bs-trigger="focus" title="Kampanya Kullanıldı" data-bs-content="Kampanya: {{ $apt->appliedCampaign->title }} | İndirim: ₺{{ number_format($apt->discount_amount, 2, ',', '.') }}"><i class="ti ti-info-circle fs-5"></i></a>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex gap-2 justify-content-end align-items-center">
                                            <!-- Completed & Pay Button -->
                                            <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-semibold shadow-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#dashboardCompleteModal{{ $apt->id }}">
                                                <i class="ti ti-circle-check fs-6"></i> Tamamlandı & Öde
                                            </button>

                                            <!-- No Show Button -->
                                            <form action="{{ route('appointments.update-status', $apt) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="no_show">
                                                <button type="submit" class="btn btn-outline-warning btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1" onclick="return confirm('Bu randevuyu \'Gelmedi (No Show)\' olarak işaretlemek istediğinize emin misiniz?')">
                                                    <i class="ti ti-user-off fs-6"></i> Gelmedi
                                                </button>
                                            </form>

                                            <!-- Cancel Button -->
                                            <form action="{{ route('appointments.update-status', $apt) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1" onclick="return confirm('Bu randevuyu iptal etmek istediğinize emin misiniz?')">
                                                    <i class="ti ti-x fs-6"></i> İptal
                                                </button>
                                            </form>
                                        </div>
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

    <!-- Complete with Payment Modals for Awaiting Action Appointments (Outside Table) -->
    @foreach($awaitingActionAppointments as $apt)
        <div class="modal fade" id="dashboardCompleteModal{{ $apt->id }}" tabindex="-1" aria-labelledby="dashboardCompleteModalLabel{{ $apt->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered text-start">
                <div class="modal-content border-0 rounded-4 shadow-lg">
                    <div class="modal-header border-0 bg-success text-white py-3">
                        <h5 class="modal-title fw-bold" id="dashboardCompleteModalLabel{{ $apt->id }}">Randevuyu Tamamla & Ödeme Kaydet</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
                    </div>
                    <form action="{{ route('appointments.complete-payment', $apt) }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="alert alert-success border-0 p-3 rounded-3 mb-3" role="alert">
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Randevu Toplam Fiyatı:</span>
                                    <span>₺{{ number_format($apt->total_price, 2, ',', '.') }}</span>
                                </div>
                                <div class="small text-success mt-1">
                                    <span>Müşteri: {{ $apt->customer?->full_name }}</span>
                                </div>
                            </div>

                            <!-- Amount -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary">Ödenen Tutar (₺)</label>
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light">₺</span>
                                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control border-0 bg-light rounded-end-3" value="{{ $apt->total_price }}" required>
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
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary">Referans / İşlem Kodu (İsteğe Bağlı)</label>
                                <input type="text" name="transaction_reference" class="form-control border-0 bg-light" placeholder="Havale referansı, fiş numarası vb...">
                            </div>

                            <!-- Completion note -->
                            <div class="mb-2">
                                <label class="form-label fw-semibold text-secondary">İşlem / Randevu Notu (İsteğe Bağlı)</label>
                                <textarea name="note" rows="2" class="form-control border-0 bg-light" placeholder="Randevu tamamlanmasına dair not ekleyin..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                            <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">Tamamla ve Ödeme Al</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif
