@extends('layouts.app')
@section('title', 'Müşteri Değerlendirmeleri - B&V Barber')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                <h1 class="fs-3 mb-1">Müşteri Değerlendirmeleri</h1>
                <p class="text-muted">Müşterilerinizin hizmet sonrası salonunuza ve berberlerinize bıraktığı puanlar ile
                    geri bildirimler.</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Star Average -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100 text-center p-4">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <h1 class="display-3 fw-bold text-dark mb-1">{{ $stats['avg'] }}</h1>
                    <div class="d-flex align-items-center justify-content-center gap-1 mb-2">
                        @php 
                                                                                    $avg = (float) $stats['avg'];
                            $full = floor($avg);
                            $hasHalf = ($avg - $full) >= 0.3 && ($avg - $full) <= 0.7;
                        @endphp
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $full || ($i == $full + 1 && ($avg - $full) > 0.7))
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                                    fill="currentColor" class="text-warning">
                                    <path
                                        d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                                </svg>
                            @elseif($i == $full + 1 && $hasHalf)
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                                    <path
                                        d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z"
                                        fill="none" stroke="#d1d5db" stroke-width="1.8" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253v15.497z"
                                        fill="currentColor" class="text-warning" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                                    stroke="#d1d5db" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                                </svg>
                            @endif
                        @endfor
                    </div>
                    <span class="text-secondary small fw-semibold text-uppercase">Ortalama Puan</span>
                    <span class="text-muted small mt-1">Toplam {{ $stats['total'] }} Değerlendirme</span>

                    <!-- NPS & Google Özeti -->
                    <div class="d-flex gap-2 mt-3 pt-3 border-top w-100 justify-content-center">
                        <div
                            class="px-2.5 py-1.5 rounded-3 bg-danger-subtle text-danger small fw-semibold text-center flex-fill">
                            <div class="fs-5 fw-bold">{{ $stats['low_rating_count'] }}</div>
                            <div style="font-size: 11px;">Düşük Puan (1-3★)</div>
                        </div>
                        <div
                            class="px-2.5 py-1.5 rounded-3 bg-success-subtle text-success small fw-semibold text-center flex-fill">
                            <div class="fs-5 fw-bold">{{ $stats['google_redirected_count'] }}</div>
                            <div style="font-size: 11px;">Google'a Giden</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rating breakdown bars -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4 h-100 p-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3 text-secondary text-uppercase small">Puan Dağılımı</h6>
                    <div class="d-flex flex-column gap-2.5">
                        @foreach([5, 4, 3, 2, 1] as $star)
                            @php 
                                                                                                                                                                        $count = $stats['stars'][$star] ?? 0;
                                $pct = $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0;
                            @endphp
                            <div class="d-flex align-items-center">
                                <span class="small text-secondary fw-bold" style="width: 50px;">{{ $star }} Yıldız</span>
                                <div class="progress flex-grow-1 mx-3" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="small text-muted" style="width: 40px;">{{ $count }} Adet</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reviews.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">Berber Filtresi</label>
                    <select name="employee_id" class="form-select border-0 bg-light rounded-3">
                        <option value="">Tüm Berberler</option>
                        @foreach($barbers as $barber)
                            <option value="{{ $barber->id }}" {{ request('employee_id') == $barber->id ? 'selected' : '' }}>
                                {{ $barber->user->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">Puan Derecesi</label>
                    <select name="rating" class="form-select border-0 bg-light rounded-3">
                        <option value="">Tüm Puanlar</option>
                        <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Yıldız</option>
                        <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Yıldız</option>
                        <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Yıldız</option>
                        <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Yıldız</option>
                        <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Yıldız</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">Yönlendirme Durumu</label>
                    <select name="nps_status" class="form-select border-0 bg-light rounded-3">
                        <option value="">Tümü</option>
                        <option value="low_rating" {{ request('nps_status') == 'low_rating' ? 'selected' : '' }}>⚠️ Şikayetler
                            (1-3 Yıldız)</option>
                        <option value="google_redirected" {{ request('nps_status') == 'google_redirected' ? 'selected' : '' }}>📍 Google'a Yönlendirilenler</option>
                        <option value="promoter" {{ request('nps_status') == 'promoter' ? 'selected' : '' }}>🌟 Memnun
                            Müşteriler (4-5 Yıldız)</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-3 w-100 fw-semibold py-2">
                        <i class="ti ti-filter me-1"></i> Filtrele
                    </button>
                    <a href="{{ route('reviews.index') }}" class="btn btn-light border rounded-3 w-100 fw-semibold py-2">
                        Sıfırla
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reviews Table List -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Müşteri</th>
                            <th>Hizmet Veren</th>
                            <th class="text-center">Puan</th>
                            <th>Müşteri Yorumu</th>
                            <th class="text-center">Durum / Yönlendirme</th>
                            <th class="text-center">Tarih</th>
                            <th class="text-end pe-4">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $rev)
                            <tr data-href="{{ route('reviews.show', $rev) }}" style="cursor: pointer;" class="review-row">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        @if($rev->customer?->profile_photo_url)
                                            <img src="{{ $rev->customer->profile_photo_url }}"
                                                class="rounded-circle me-3 object-fit-cover shadow-sm" width="38" height="38"
                                                alt="{{ $rev->customer->full_name }}">
                                        @else
                                            <div class="avatar bg-primary-subtle text-primary me-3 rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm"
                                                style="width: 38px; height: 38px; font-size: 13px;">
                                                {{ mb_substr($rev->customer?->first_name ?? 'M', 0, 1) }}{{ mb_substr($rev->customer?->last_name ?? 'U', 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $rev->customer?->full_name ?? 'Müşteri' }}
                                            </div>
                                            <div class="text-secondary small">{{ $rev->customer?->phone }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="fw-semibold text-dark">{{ $rev->employee?->user?->full_name ?? $rev->employee?->full_name ?? 'Berber' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($rev->rating >= 4)
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold"
                                            style="font-size: 12px;">
                                            {{ $rev->rating }} Yıldız
                                        </span>
                                    @elseif($rev->rating == 3)
                                        <span
                                            class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1.5 rounded-pill fw-semibold"
                                            style="font-size: 12px;">
                                            3 Yıldız
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-semibold"
                                            style="font-size: 12px;">
                                            {{ $rev->rating }} Yıldız
                                        </span>
                                    @endif
                                </td>
                                <td class="text-dark py-3" style="max-width: 350px;">
                                    <div class="text-wrap text-break fw-medium">{{ $rev->comment ?? '-' }}</div>
                                </td>
                                <td class="text-center">
                                    @if($rev->redirected_to_google_at)
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1"
                                            title="{{ $rev->redirected_to_google_at->format('d.m.Y H:i') }}">
                                            <i class="ti ti-brand-google me-1"></i> Google'a Gitti
                                        </span>
                                    @elseif($rev->prompted_for_google)
                                        <span
                                            class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1">
                                            <i class="ti ti-eye me-1"></i> Davet Edildi
                                        </span>
                                    @elseif($rev->rating <= 2)
                                        <span
                                            class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                                            <i class="ti ti-shield-alert me-1"></i> Şikayet / Dahili
                                        </span>
                                    @elseif($rev->rating == 3)
                                        <span
                                            class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">
                                            <i class="ti ti-alert-circle me-1"></i> Orta / Dahili
                                        </span>
                                    @else
                                        <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">
                                            Dahili
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center text-secondary small">
                                    {{ $rev->created_at ? $rev->created_at->format('d.m.Y H:i') : '-' }}
                                </td>
                                <td class="text-end pe-4" onclick="event.stopPropagation();">
                                    <a href="{{ route('reviews.show', $rev) }}"
                                        class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-medium">
                                        <i class="ti ti-eye me-1"></i> İncele
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="ti ti-mood-empty fs-1 d-block mb-1"></i>
                                    Kriterlere uygun değerlendirme bulunamadı.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @push('styles')
        <style>
            .review-row {
                transition: background-color 0.15s ease-in-out;
            }

            .review-row:hover {
                background-color: rgba(13, 110, 253, 0.04) !important;
            }
        </style>
    @endpush
@endsection