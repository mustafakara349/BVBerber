@extends('layouts.app')
@section('title', 'Müşteri Değerlendirmeleri - B&V Barber')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h1 class="fs-3 mb-1">Müşteri Değerlendirmeleri</h1>
            <p class="text-muted">Müşterilerinizin hizmet sonrası salonunuza ve berberlerinize bıraktığı puanlar ile geri bildirimler.</p>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Star Average -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-4 h-100 text-center p-4">
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <h1 class="display-3 fw-bold text-dark mb-1">{{ $stats['avg'] }}</h1>
                <div class="text-warning mb-2 fs-4">
                    @php $fullStars = floor($stats['avg']); @endphp
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $fullStars)
                            <i class="ti ti-star-filled"></i>
                        @elseif($i == $fullStars + 1 && $stats['avg'] - $fullStars >= 0.5)
                            <i class="ti ti-star-half-filled"></i>
                        @else
                            <i class="ti ti-star"></i>
                        @endif
                    @endfor
                </div>
                <span class="text-secondary small fw-semibold text-uppercase">Ortalama Puan</span>
                <span class="text-muted small mt-1">Toplam {{ $stats['total'] }} Değerlendirme</span>
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
            <div class="col-md-4">
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
            
            <div class="col-md-4">
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
            
            <div class="col-md-4 d-flex gap-2">
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
                        <th class="text-center">Tarih</th>
                        <th class="text-end pe-4">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $rev)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                @if($rev->customer?->profile_photo)
                                    <img src="{{ asset($rev->customer->profile_photo) }}" class="rounded-circle me-3 object-fit-cover shadow-sm" width="38" height="38" alt="{{ $rev->customer->full_name }}">
                                @else
                                    <div class="avatar bg-primary-subtle text-primary me-3 rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 13px;">
                                        {{ mb_substr($rev->customer?->first_name ?? 'M', 0, 1) }}{{ mb_substr($rev->customer?->last_name ?? 'U', 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-semibold text-dark">{{ $rev->customer?->full_name ?? 'Müşteri' }}</div>
                                    <div class="text-secondary small">{{ $rev->customer?->phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $rev->employee?->user?->full_name ?? 'Berber' }}</div>
                            <div class="text-secondary small text-truncate" style="max-width: 150px;">
                                @if($rev->appointment)
                                    @foreach($rev->appointment->services as $srv)
                                        {{ $srv->name }},
                                    @endforeach
                                @else
                                    Genel Hizmet
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning-subtle px-2.5 py-1 rounded-pill">
                                <i class="ti ti-star-filled text-warning me-1"></i> {{ $rev->rating }}
                            </span>
                        </td>
                        <td class="text-dark py-3" style="max-width: 350px;">
                            <div class="text-wrap text-break fw-medium">{{ $rev->comment ?? '-' }}</div>
                        </td>
                        <td class="text-center text-secondary small">
                            {{ $rev->created_at ? $rev->created_at->format('d.m.Y H:i') : '-' }}
                        </td>
                        <td class="text-end pe-4">
                            <form method="POST" action="{{ route('reviews.destroy', $rev->id) }}" onsubmit="return confirm('Bu değerlendirmeyi silmek istediğinize emin misiniz? (Yorum silinecek fakat ortalama puanı etkilemeye devam edecektir)');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger border-0 rounded-circle btn-sm" title="Yorumu Kaldır (Modere Et)">
                                    <i class="ti ti-trash fs-5"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
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
@endsection
