<div class="row g-3 mt-1 mb-4 db-animate-fade-up db-delay-6">
    <div class="col-12">
        <div class="db-table-card mb-2">
            <div class="card-header bg-white d-flex justify-content-between align-items-center px-4 py-3 border-bottom border-light-subtle">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 bg-primary-subtle text-primary rounded-3 d-inline-flex align-items-center justify-content-center">
                        <i class="ti ti-calendar-clock fs-5"></i>
                    </span>
                    <div>
                        <h4 class="mb-0 h5 fw-bold text-dark">Günün Randevuları</h4>
                        <small class="text-secondary">Bugün için planlanan tüm randevu akışı</small>
                    </div>
                </div>
                <a href="{{ route('appointments.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1">
                    Tümünü Gör <i class="ti ti-arrow-right fs-6"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead>
                            <tr>
                                <th class="ps-4">Saat</th>
                                <th>Müşteri</th>
                                <th>Berber</th>
                                <th>Hizmet</th>
                                <th class="pe-4 text-end">Durum</th>
                            </tr>
                        </thead>
                        <tbody>
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
                                <tr class="{{ $bgClass }} dashboard-today-row clickable-row" data-appointment-id="{{ $apt->id }}" data-href="{{ route('appointments.show', $apt) }}" title="Randevu Detayını Gör" style="transition: all 0.2s ease;">
                                    <td class="ps-4">
                                        <div class="db-time-tag {{ $isPast ? 'db-time-past' : 'db-time-primary' }}">
                                            <i class="ti ti-clock opacity-75"></i>
                                            <span>{{ $apt->start_at->format('H:i') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($apt->customer && $apt->customer->profile_photo_url)
                                                <img src="{{ $apt->customer->profile_photo_url }}" class="rounded-circle border border-primary border-opacity-25 shadow-sm me-3 object-fit-cover" style="width: 36px; height: 36px;" alt="{{ $apt->customer->full_name }}">
                                            @else
                                                <div class="avatar avatar-sm rounded-circle {{ $isPast ? 'bg-secondary-subtle text-secondary' : 'bg-primary-subtle text-primary' }} me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm"
                                                    style="width: 36px; height: 36px; font-size: 0.85rem;">
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
                                            <div class="avatar avatar-xs rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.8rem;">
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
                                    <td class="pe-4 text-end">
                                        @if($apt->status->value == 'completed')
                                            <span class="db-badge-pill bg-success-subtle text-success border border-success-subtle">
                                                <i class="ti ti-circle-check fs-6"></i> Tamamlandı
                                            </span>
                                        @elseif($apt->status->value == 'cancelled')
                                            <span class="db-badge-pill bg-danger-subtle text-danger border border-danger-subtle">
                                                <i class="ti ti-x fs-6"></i> İptal
                                            </span>
                                        @else
                                            @if($isNext)
                                                <span class="db-badge-pill bg-primary text-white shadow-sm">
                                                    <i class="ti ti-player-play-filled fs-7"></i> Sıradaki
                                                </span>
                                            @elseif($isPast)
                                                <span class="db-badge-pill bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                    <i class="ti ti-clock-check fs-6"></i> Süresi Geçti
                                                </span>
                                            @else
                                                <span class="db-badge-pill bg-info-subtle text-info border border-info-subtle">
                                                    <i class="ti ti-hourglass-empty fs-6"></i> {{ $remainingTime }} kaldı
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state colspan="5" title="Bugün için randevu bulunmuyor" description="Bugün planlanmış herhangi bir randevu kaydı mevcut değil." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

