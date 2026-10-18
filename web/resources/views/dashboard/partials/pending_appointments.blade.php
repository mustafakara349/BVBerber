@if($pendingAppointments->isNotEmpty())
    <div class="row g-3 mt-1 mb-3">
        <div class="col-12">
            <div class="card border-warning border-opacity-50 shadow-sm">
                <div
                    class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center px-4 py-3 border-bottom border-warning border-opacity-25 flex-wrap gap-2">
                    <h4 class="mb-0 h5 text-warning-emphasis"><i class="ti ti-clock-exclamation me-2"></i>Onay Bekleyen
                        Randevular</h4>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold">
                            {{ $pendingAppointments->count() }} Randevu Onay Bekliyor
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
                        <table class="table table-hover align-middle mb-0 border-top-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Tarih / Saat
                                    </th>
                                    <th class="text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Müşteri</th>
                                    <th class="text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Berber</th>
                                    <th class="text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Hizmet</th>
                                    <th class="text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Tutar</th>
                                    <th class="text-end pe-4 text-uppercase text-muted fs-7 fw-semibold py-3 border-0">İşlem
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @foreach($pendingAppointments as $apt)
                                    <tr data-appointment-id="{{ $apt->id }}" class="dashboard-pending-row">
                                        <td class="ps-4">
                                            <div
                                                class="d-inline-flex align-items-center justify-content-center px-3 py-2 rounded-2 bg-dark text-white fw-bold shadow-sm">
                                                {{ $apt->start_at->format('d.m.Y') }} <i
                                                    class="ti ti-clock ms-2 me-1 opacity-75"></i>
                                                {{ $apt->start_at->format('H:i') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($apt->customer && $apt->customer->profile_photo_url)
                                                    <img src="{{ $apt->customer->profile_photo_url }}" class="rounded-circle border border-warning border-opacity-25 shadow-sm me-3 object-fit-cover" style="width: 32px; height: 32px;" alt="{{ $apt->customer->full_name }}">
                                                @else
                                                    <div class="avatar avatar-sm rounded-circle bg-warning-subtle text-warning border border-warning border-opacity-25 me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                                        {{ mb_substr($apt->customer->first_name ?? 'M', 0, 1) }}{{ mb_substr($apt->customer->last_name ?? 'M', 0, 1) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <span
                                                        class="d-block fw-bold text-dark">{{ $apt->customer->full_name ?? 'Bilinmeyen Müşteri' }}</span>
                                                    <small class="text-muted"><i
                                                            class="ti ti-phone me-1"></i>{{ $apt->customer->phone ?? 'Belirtilmemiş' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium text-dark">{{ $apt->employee->user->full_name ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1" style="max-width: 250px;">
                                                @foreach($apt->appointmentServices as $aps)
                                                    <span class="badge bg-light text-secondary border px-2 py-1"><i
                                                            class="ti ti-check me-1"></i>{{ $aps->service->name }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark fs-6">₺{{ number_format($apt->total_price, 2, ',', '.') }}</span>
                                            @if($apt->appliedCoupon)
                                                <a tabindex="0" class="text-info ms-1" role="button" data-bs-toggle="popover" data-bs-trigger="focus" title="Kupon Kullanıldı" data-bs-content="Kupon: {{ $apt->appliedCoupon->code }} | İndirim: ₺{{ number_format($apt->discount_amount, 2, ',', '.') }}"><i class="ti ti-info-circle fs-5"></i></a>
                                            @elseif($apt->appliedCampaign)
                                                <a tabindex="0" class="text-info ms-1" role="button" data-bs-toggle="popover" data-bs-trigger="focus" title="Kampanya Kullanıldı" data-bs-content="Kampanya: {{ $apt->appliedCampaign->title }} | İndirim: ₺{{ number_format($apt->discount_amount, 2, ',', '.') }}"><i class="ti ti-info-circle fs-5"></i></a>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex gap-2 justify-content-end align-items-center">
                                                <form action="{{ route('appointments.update-status', $apt) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="confirmed">
                                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm" title="Onayla">
                                                        <i class="ti ti-check me-1"></i>Onayla
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-sm btn-reject-trigger" 
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#rejectReasonModal"
                                                        data-url="{{ route('appointments.update-status', $apt) }}"
                                                        title="Reddet">
                                                    <i class="ti ti-x me-1"></i>Reddet
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
