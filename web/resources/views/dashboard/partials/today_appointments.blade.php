<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                <h4 class="mb-0 h5"><i class="ti ti-calendar-clock text-primary me-2"></i>Günün Randevuları</h4>
                <a href="{{ route('appointments.index') }}" class="small text-primary text-decoration-underline">Tümünü
                    Gör</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 border-top-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Saat</th>
                                <th class="text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Müşteri</th>
                                <th class="text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Berber</th>
                                <th class="text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Hizmet</th>
                                <th class="text-uppercase text-muted fs-7 fw-semibold py-3 border-0">Durum</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @php
                                $barberNextCount = [];
                            @endphp
                            @forelse($todayAppointments as $apt)
                                @php
                                    $isPast = $apt->start_at < now();
                                    $isNext = false;
                                    if (!$isPast && !isset($barberNextCount[$apt->employee_id])) {
                                        $isNext = true;
                                        $barberNextCount[$apt->employee_id] = true;
                                    }
                                    $bgClass = $isNext ? 'bg-primary bg-opacity-10' : ($isPast ? 'bg-light opacity-75' : '');

                                    $remainingTime = '';
                                    if (!$isPast && !$isNext) {
                                        $diff = now()->diff($apt->start_at);
                                        if ($diff->h > 0) {
                                            $remainingTime = $diff->h . ' sa ' . $diff->i . ' dk';
                                        } else {
                                            $remainingTime = $diff->i . ' dk';
                                        }
                                    }
                                @endphp
                                <tr class="{{ $bgClass }} dashboard-today-row" data-appointment-id="{{ $apt->id }}" style="transition: all 0.2s ease;">
                                    <td class="ps-4">
                                        <div
                                            class="d-inline-flex align-items-center justify-content-center px-3 py-2 rounded-2 {{ $isPast ? 'bg-secondary text-white' : 'bg-dark text-white' }} fw-bold shadow-sm">
                                            <i class="ti ti-clock me-1 opacity-75"></i>
                                            {{ $apt->start_at->format('H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm rounded-circle {{ $isPast ? 'bg-secondary-subtle text-secondary' : 'bg-primary-subtle text-primary' }} me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm"
                                                style="width: 40px; height: 40px;">
                                                {{ mb_substr($apt->customer->first_name ?? 'M', 0, 1) }}{{ mb_substr($apt->customer->last_name ?? 'M', 0, 1) }}
                                            </div>
                                            <div>
                                                <span
                                                    class="d-block fw-bold text-dark">{{ $apt->customer->full_name ?? 'Bilinmeyen Müşteri' }}</span>
                                                <small class="text-muted"><i
                                                        class="ti ti-phone me-1"></i>{{ $apt->customer->phone ?? 'Belirtilmemiş' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div
                                                class="icon-shape icon-sm bg-warning-subtle text-warning rounded-circle me-2">
                                                <i class="ti ti-cut fs-5"></i>
                                            </div>
                                            <span
                                                class="fw-medium text-dark">{{ $apt->employee->user->full_name ?? '-' }}</span>
                                        </div>
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
                                        @if($apt->status->value == 'completed')
                                            <span
                                                class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill"><i
                                                    class="ti ti-circle-check me-1"></i>Tamamlandı</span>
                                        @elseif($apt->status->value == 'cancelled')
                                            <span
                                                class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill"><i
                                                    class="ti ti-x me-1"></i>İptal</span>
                                        @else
                                            @if($isNext)
                                                <span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm"><i
                                                        class="ti ti-player-play-filled me-1"></i>Sıradaki</span>
                                            @elseif($isPast)
                                                <span
                                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill"><i
                                                        class="ti ti-clock-check me-1"></i>Süresi Geçti</span>
                                            @else
                                                <span
                                                    class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill"><i
                                                        class="ti ti-hourglass-empty me-1"></i>{{ $remainingTime }} kaldı</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Bugün için randevu bulunmuyor.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
