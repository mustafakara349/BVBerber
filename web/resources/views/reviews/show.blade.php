@extends('layouts.app')
@section('title', 'Değerlendirme #' . $review->id . ' - B&V Barber')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h1 class="fs-3 mb-0">Değerlendirme #{{ $review->id }}</h1>
                    @if($review->rating <= 2)
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-semibold">
                            <i class="ti ti-shield-alert me-1"></i> Şikayet / Düşük Puan
                        </span>
                    @elseif($review->rating == 3)
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1.5 rounded-pill fw-semibold">
                            <i class="ti ti-alert-circle me-1"></i> 3 Yıldız / Orta Deneyim
                        </span>
                    @elseif($review->redirected_to_google_at)
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold">
                            <i class="ti ti-brand-google me-1"></i> Google'a Yönlendirildi
                        </span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold">
                            <i class="ti ti-star-filled text-success me-1"></i> {{ $review->rating }} Yıldız
                        </span>
                    @endif
                </div>
                <p class="text-muted mb-0">Müşterinin hizmet sonrası bıraktığı geri bildirim ve ilişkili randevu detayları.</p>
            </div>
            <div>
                <a href="{{ route('reviews.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-medium">
                    <i class="ti ti-arrow-left me-1"></i> Değerlendirmelere Dön
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Sol Kolon: Değerlendirme, NPS ve Hizmetler -->
    <div class="col-lg-7">
        <!-- Değerlendirme Kartı -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Verilen Puan</span>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->rating)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="currentColor" class="{{ $review->rating >= 4 ? 'text-success' : ($review->rating == 3 ? 'text-warning' : 'text-danger') }}">
                                            <path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                                        </svg>
                                    @endif
                                @endfor
                            </div>
                            <span class="fs-4 fw-bold text-dark">{{ $review->rating }} <span class="fs-6 text-muted fw-normal">/ 5 Yıldız</span></span>
                        </div>
                    </div>
                    <div class="text-end text-muted small">
                        <i class="ti ti-clock me-1"></i>
                        {{ $review->created_at ? $review->created_at->format('d.m.Y H:i') : '-' }}
                    </div>
                </div>

                <!-- Müşteri Yorumu -->
                <div class="mt-4">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-2">Müşteri Yorumu</span>
                    @if($review->comment)
                        <div class="p-4 rounded-3 bg-light border-start border-4 {{ $review->rating >= 4 ? 'border-success' : ($review->rating == 3 ? 'border-warning' : 'border-danger') }}">
                            <p class="fs-6 text-dark mb-0 fw-medium text-break lh-base" style="white-space: pre-wrap;">{{ $review->comment }}</p>
                        </div>
                    @else
                        <div class="p-3.5 px-4 rounded-3 bg-light text-muted fst-italic small">
                            <i class="ti ti-info-circle me-1"></i> Müşteri metinli bir yorum yazmadı, yalnızca puan verdi.
                        </div>
                    @endif
                </div>

                <!-- NPS & Google Haritalar Durumu -->
                <div class="mt-4 pt-3 border-top">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-2">Google Haritalar & İtibar Durumu</span>
                    
                    @if($review->redirected_to_google_at)
                        <div class="alert alert-success border-0 rounded-3 d-flex align-items-start gap-3 mb-0 p-3">
                            <i class="ti ti-brand-google fs-3 mt-1 text-success"></i>
                            <div>
                                <div class="fw-bold text-success mb-1">Müşteri Google Haritalar'a Yönlendirildi</div>
                                <div class="small text-dark">
                                    Müşteri yüksek puan verdikten sonra sunulan Google değerlendirme butonuna tıklayarak doğrudan işletmenizin Google Haritalar yorum ekranına geçti.
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="ti ti-calendar-time me-1"></i> Yönlendirme Tarihi: {{ $review->redirected_to_google_at->format('d.m.Y H:i') }}
                                </div>
                            </div>
                        </div>
                    @elseif($review->prompted_for_google)
                        <div class="alert alert-info border-0 rounded-3 d-flex align-items-start gap-3 mb-0 p-3">
                            <i class="ti ti-eye fs-3 mt-1 text-info"></i>
                            <div>
                                <div class="fw-bold text-info mb-1">Google Daveti Gösterildi</div>
                                <div class="small text-dark">
                                    Müşteriye 4-5 yıldız sonrası Google Haritalar'da yorum yapma daveti gösterildi; ancak henüz Google butonuna tıklamadan ekranı kapattı veya 'Şimdilik Geç' dedi.
                                </div>
                            </div>
                        </div>
                    @elseif($review->rating == 3)
                        <div class="alert alert-warning border-0 rounded-3 d-flex align-items-start gap-3 mb-0 p-3">
                            <i class="ti ti-alert-circle fs-3 mt-1 text-warning"></i>
                            <div>
                                <div class="fw-bold text-warning-emphasis mb-1">Orta Deneyim / Dahili Tutuldu</div>
                                <div class="small text-dark">
                                    Müşteri 3 yıldız verdiği için Google Haritalar'a yönlendirilmemiş, işletme içi değerlendirme olarak saklanmıştır.
                                </div>
                            </div>
                        </div>
                    @elseif($review->rating <= 2)
                        <div class="alert alert-danger border-0 rounded-3 d-flex align-items-start gap-3 mb-0 p-3">
                            <i class="ti ti-shield-alert fs-3 mt-1 text-danger"></i>
                            <div>
                                <div class="fw-bold text-danger mb-1">Şikayet / Kötü Deneyim Kalkanı (İçeride Tutuldu)</div>
                                <div class="small text-dark">
                                    Müşteri düşük puan verdiği için <strong>Google Haritalar linki kesinlikle sunulmadı</strong>. Bu sayede Google puanınız korundu. Geri bildirim yöneticilere uyarı olarak iletildi.
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-light border rounded-3 d-flex align-items-center gap-2 mb-0 p-3 text-secondary small">
                            <i class="ti ti-info-circle fs-4"></i>
                            <div>Dahili değerlendirme olarak kaydedildi.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Alınan Hizmetler ve Ödeme Detayları -->
        @if($review->appointment)
        @php $appt = $review->appointment; @endphp
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-transparent p-4 border-0 pb-0">
                <h5 class="fw-bold mb-0">Randevuda Alınan Hizmetler</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive mb-3">
                    <table class="table table-borderless align-middle mb-0">
                        <thead class="text-secondary small text-uppercase">
                            <tr class="border-bottom">
                                <th class="ps-0">Hizmet Adı</th>
                                <th>Kategori</th>
                                <th class="text-center">Süre</th>
                                <th class="text-end pe-0">Fiyat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appt->appointmentServices as $as)
                            <tr class="border-bottom">
                                <td class="ps-0 fw-semibold text-dark">{{ $as->service?->name ?? 'Hizmet' }}</td>
                                <td class="text-secondary small">{{ $as->service?->category?->name ?? '-' }}</td>
                                <td class="text-center text-muted small">{{ $as->service?->duration_minutes ?? $appt->total_duration }} dk</td>
                                <td class="text-end pe-0 fw-semibold">₺{{ number_format((float) ($as->price ?? $as->service?->price ?? 0), 2, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-muted text-center py-3">Hizmet detayı bulunamadı.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Finansal Özet -->
                <div class="bg-light rounded-3 p-3 mt-3">
                    <div class="d-flex justify-content-between text-secondary small mb-1">
                        <span>Ara Toplam:</span>
                        <span>₺{{ number_format((float) $appt->subtotal, 2, ',', '.') }}</span>
                    </div>
                    @if($appt->discount_amount > 0)
                    <div class="d-flex justify-content-between text-success small mb-1">
                        <span>
                            İndirim
                            @if($appt->campaign)
                                ({{ $appt->campaign->name }})
                            @elseif($appt->coupon)
                                (Kupon: {{ $appt->coupon->code }})
                            @endif:
                        </span>
                        <span>-₺{{ number_format((float) $appt->discount_amount, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                        <span class="fw-bold text-dark fs-6">Toplam Tutar:</span>
                        <span class="fw-bold text-dark fs-5">₺{{ number_format((float) $appt->total_price, 2, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top small">
                        <span class="text-secondary">Ödeme Durumu / Yöntemi:</span>
                        <div>
                            @if($appt->payment_status)
                                <span class="badge bg-{{ $appt->payment_status->color() }}-subtle text-{{ $appt->payment_status->color() }} border border-{{ $appt->payment_status->color() }}-subtle rounded-pill me-1">
                                    {{ $appt->payment_status->label() }}
                                </span>
                            @endif
                            @if($appt->payment_method)
                                <span class="badge bg-light text-dark border rounded-pill">
                                    {{ $appt->payment_method->label() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sağ Kolon: Müşteri, Berber ve Randevu Kartları -->
    <div class="col-lg-5">
        <!-- Müşteri Bilgileri Kartı -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-transparent p-4 border-0 pb-0">
                <h5 class="fw-bold mb-0">Müşteri Bilgileri</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    @if($review->customer?->profile_photo_url)
                        <img src="{{ $review->customer->profile_photo_url }}" class="rounded-circle me-3 object-fit-cover shadow-sm" width="56" height="56" alt="{{ $review->customer->full_name }}">
                    @else
                        <div class="avatar bg-primary-subtle text-primary me-3 rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 56px; height: 56px; font-size: 18px;">
                            {{ mb_substr($review->customer?->first_name ?? 'M', 0, 1) }}{{ mb_substr($review->customer?->last_name ?? 'U', 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h6 class="fw-bold text-dark mb-1">{{ $review->customer?->full_name ?? 'Müşteri' }}</h6>
                        <span class="badge bg-light text-secondary border rounded-pill small">
                            {{ $review->customer?->is_guest ? 'Misafir Müşteri' : 'Kayıtlı Müşteri' }}
                        </span>
                    </div>
                </div>

                <div class="bg-light rounded-3 p-3 small text-secondary">
                    @if($review->customer?->phone)
                    <div class="d-flex align-items-center justify-content-between py-2 {{ $review->customer?->email || $review->customer?->created_at ? 'border-bottom' : '' }}" style="border-color: rgba(0,0,0,0.06) !important;">
                        <span class="text-muted"><i class="ti ti-phone me-1"></i> Telefon:</span>
                        <a href="tel:{{ $review->customer->phone }}" class="fw-semibold text-dark text-decoration-none">
                            {{ $review->customer->phone }}
                        </a>
                    </div>
                    @endif

                    @if($review->customer?->email)
                    <div class="d-flex align-items-center justify-content-between py-2 {{ $review->customer?->created_at ? 'border-bottom' : '' }}" style="border-color: rgba(0,0,0,0.06) !important;">
                        <span class="text-muted"><i class="ti ti-mail me-1"></i> E-Posta:</span>
                        <a href="mailto:{{ $review->customer->email }}" class="fw-semibold text-dark text-decoration-none">
                            {{ $review->customer->email }}
                        </a>
                    </div>
                    @endif

                    @if($review->customer?->created_at)
                    <div class="d-flex align-items-center justify-content-between py-2">
                        <span class="text-muted"><i class="ti ti-calendar me-1"></i> Kayıt Tarihi:</span>
                        <span class="fw-medium text-dark">{{ $review->customer->created_at->format('d.m.Y') }}</span>
                    </div>
                    @endif
                </div>

                @if($review->customer)
                <div class="mt-3">
                    <a href="{{ route('customers.show', $review->customer) }}" class="btn btn-light border rounded-pill w-100 fw-semibold text-secondary btn-sm py-2">
                        <i class="ti ti-user me-1"></i> Müşteri Profilini Aç
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Hizmet Veren Berber Kartı -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-transparent p-4 border-0 pb-0">
                <h5 class="fw-bold mb-0">Hizmet Veren Berber</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    @if($review->employee?->user?->profile_photo_url)
                        <img src="{{ $review->employee->user->profile_photo_url }}" class="rounded-circle me-3 object-fit-cover shadow-sm" width="50" height="50" alt="{{ $review->employee->full_name }}">
                    @else
                        <div class="avatar bg-warning-subtle text-dark me-3 rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 50px; height: 50px; font-size: 16px;">
                            {{ mb_substr($review->employee?->full_name ?? 'B', 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h6 class="fw-bold text-dark mb-0">{{ $review->employee?->full_name ?? $review->employee?->user?->full_name ?? 'Personel' }}</h6>
                        <span class="text-secondary small">{{ $review->employee?->title ?? 'Berber' }}</span>
                        @if($review->employee?->user?->phone)
                            <div class="small text-muted mt-0.5">
                                <i class="ti ti-phone me-1"></i> {{ $review->employee->user->phone }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- İlişkili Randevu Kartı -->
        @if($review->appointment)
        @php $appt = $review->appointment; @endphp
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-transparent p-4 border-0 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Randevu Özeti</h5>
                <span class="badge bg-{{ $appt->status->color() }}-subtle text-{{ $appt->status->color() }} border border-{{ $appt->status->color() }}-subtle rounded-pill">
                    {{ $appt->status->label() }}
                </span>
            </div>
            <div class="card-body p-4">
                <div class="d-flex flex-column gap-2 small">
                    <div class="d-flex justify-content-between py-1.5 border-bottom">
                        <span class="text-muted">Randevu Kodu:</span>
                        <span class="fw-bold text-dark">#{{ $appt->appointment_code }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1.5 border-bottom">
                        <span class="text-muted">Tarih & Saat:</span>
                        <span class="fw-semibold text-dark">{{ $appt->start_at->format('d.m.Y H:i') }} - {{ $appt->end_at->format('H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1.5 border-bottom">
                        <span class="text-muted">Toplam Süre:</span>
                        <span class="fw-semibold text-dark">{{ $appt->total_duration }} dakika</span>
                    </div>
                    <div class="d-flex justify-content-between py-1.5 border-bottom">
                        <span class="text-muted">Şube:</span>
                        <span class="fw-semibold text-dark">{{ $appt->branch?->name ?? 'Merkez Şube' }}</span>
                    </div>

                    @if($appt->customer_note)
                    <div class="py-1.5 border-bottom">
                        <span class="text-muted d-block mb-1">Müşteri Notu:</span>
                        <span class="fst-italic text-dark bg-light p-2 rounded-2 d-block">{{ $appt->customer_note }}</span>
                    </div>
                    @endif

                    @if($appt->internal_note)
                    <div class="py-1.5">
                        <span class="text-muted d-block mb-1">Personel Notu:</span>
                        <span class="fst-italic text-dark bg-light p-2 rounded-2 d-block">{{ $appt->internal_note }}</span>
                    </div>
                    @endif
                </div>

                <div class="mt-4">
                    <a href="{{ route('appointments.show', $appt) }}" class="btn btn-primary rounded-pill w-100 fw-semibold py-2">
                        <i class="ti ti-calendar-event me-1"></i> Randevu Detayına Git
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
