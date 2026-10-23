@if($pendingAppointments->isNotEmpty())
    <div class="row g-3 mt-1 mb-4 db-animate-fade-up db-delay-1">
        <div class="col-12">
            <div class="db-table-card border-top border-warning border-3 mb-2">
                <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center px-4 py-3 border-bottom border-warning border-opacity-25 flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 bg-warning-subtle text-warning-emphasis rounded-3 d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-clock-exclamation fs-5"></i>
                        </span>
                        <div>
                            <h4 class="mb-0 h5 fw-bold text-dark">Onay Bekleyen Randevular</h4>
                            <small class="text-secondary">Onayınızı bekleyen yeni müşteri randevuları</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold shadow-sm">
                            {{ $pendingAppointments->count() }} Randevu Bekliyor
                        </span>
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-1 btn-bulk-approve"
                                data-bs-toggle="modal" data-bs-target="#bulkApproveModal"
                                data-count="{{ $pendingAppointments->count() }}"
                                data-ids="{{ $pendingAppointments->pluck('id')->join(',') }}"
                                title="Onay bekleyen tüm randevuları tek seferde onayla">
                            <i class="ti ti-checks"></i> Tümünü Onayla
                        </button>
                        <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-1 btn-bulk-reject"
                                data-bs-toggle="modal" data-bs-target="#bulkRejectModal"
                                data-count="{{ $pendingAppointments->count() }}"
                                data-ids="{{ $pendingAppointments->pluck('id')->join(',') }}"
                                title="Onay bekleyen tüm randevuları tek seferde reddet">
                            <i class="ti ti-x"></i> Tümünü Reddet
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-dark">
                            <thead>
                                <tr>
                                    <th class="ps-4">Tarih / Saat</th>
                                    <th>Müşteri</th>
                                    <th>Berber</th>
                                    <th>Hizmet</th>
                                    <th>Tutar</th>
                                    <th class="pe-4 text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingAppointments as $apt)
                                    <tr data-appointment-id="{{ $apt->id }}" data-href="{{ route('appointments.show', $apt) }}" class="dashboard-pending-row clickable-row" title="Randevu Detayını Gör">
                                        <td class="ps-4">
                                            <div class="db-time-tag db-time-primary">
                                                <span>{{ $apt->start_at->format('d.m.Y') }}</span>
                                                <i class="ti ti-clock opacity-75"></i>
                                                <span>{{ $apt->start_at->format('H:i') }}</span>
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
                                        <td class="text-end pe-4">
                                            <div class="d-flex gap-2 justify-content-end align-items-center">
                                                <form action="{{ route('appointments.update-status', $apt) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="confirmed">
                                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold shadow-sm d-inline-flex align-items-center gap-1" title="Onayla">
                                                        <i class="ti ti-check fs-6"></i> Onayla
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold btn-reject-trigger d-inline-flex align-items-center gap-1" 
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#rejectReasonModal"
                                                        data-url="{{ route('appointments.update-status', $apt) }}"
                                                        title="Reddet">
                                                    <i class="ti ti-x fs-6"></i> Reddet
                                                </button>
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
@endif

