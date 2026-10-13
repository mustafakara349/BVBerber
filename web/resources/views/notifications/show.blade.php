@extends('layouts.app')
@section('title', 'Bildirim Detayı - B&V Barber')

@section('content')
@php
    $color = $notification->level->color();
    $subject = $notification->subject;
    $actionUrl = $notification->safeActionUrl();
@endphp

<div class="mb-4 d-flex align-items-center gap-3">
    <a href="{{ route('notifications.index') }}" class="btn btn-light border rounded-circle" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
        <i class="ti ti-arrow-left"></i>
    </a>
    <h1 class="fs-4 mb-0 fw-bold">Bildirim Detayı</h1>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-4 pb-3 border-bottom">
                    <div class="sn-icon rounded-circle bg-{{ $color }}-subtle text-{{ $color }} d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="ti {{ $notification->category->icon() }} fs-3"></i>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} rounded-pill px-2 py-1" style="font-size: 11px;">{{ $notification->category->label() }}</span>
                            <span class="text-muted" style="font-size: 12px;"><i class="ti ti-clock me-1"></i>{{ $notification->created_at?->translatedFormat('d F Y, H:i') }} ({{ $notification->created_at?->diffForHumans() }})</span>
                        </div>
                        <h2 class="fs-5 fw-bold mb-0 text-dark">{{ $notification->title }}</h2>
                    </div>
                </div>

                <div class="text-secondary mb-4" style="font-size: 14px; line-height: 1.6;">
                    {{ $notification->body }}
                </div>

                @if($subject)
                    <div class="bg-light rounded-4 p-3 mb-4">
                        <h5 class="fw-bold mb-3" style="font-size: 13px;">İlgili Kayıt Bilgileri</h5>
                        <ul class="list-unstyled mb-0" style="font-size: 13px;">
                            @if($subject instanceof \App\Models\Product)
                                <li class="mb-2"><span class="text-muted me-2">Ürün Adı:</span> <span class="fw-semibold">{{ $subject->name }}</span></li>
                                <li class="mb-0"><span class="text-muted me-2">Güncel Stok:</span> <span class="fw-semibold {{ $subject->stock_quantity <= 0 ? 'text-danger' : 'text-dark' }}">{{ $subject->stock_quantity }} adet</span></li>
                            @elseif($subject instanceof \App\Models\Appointment)
                                <li class="mb-2"><span class="text-muted me-2">Müşteri:</span> <span class="fw-semibold">{{ $subject->customer?->full_name ?? 'Bilinmiyor' }}</span></li>
                                <li class="mb-2"><span class="text-muted me-2">Tarih:</span> <span class="fw-semibold">{{ $subject->start_at?->translatedFormat('d F Y, H:i') }}</span></li>
                                <li class="mb-0"><span class="text-muted me-2">Güncel Durum:</span> <span class="badge bg-{{ $subject->status->color() }}-subtle text-{{ $subject->status->color() }} rounded-pill" style="font-size: 11px;">{{ $subject->status->label() }}</span></li>
                            @else
                                <li class="mb-0"><span class="text-muted me-2">Kayıt ID:</span> <span class="fw-semibold">{{ $subject->id ?? 'Bilinmiyor' }}</span></li>
                            @endif
                        </ul>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-4 pt-2">
                    @if($actionUrl)
                        <a href="{{ $actionUrl }}" class="btn btn-primary rounded-pill px-4 fw-semibold btn-sm">
                            {{ $notification->action_label ?? 'İlgili Kayda Git' }} <i class="ti ti-arrow-right ms-1"></i>
                        </a>
                    @endif
                    <form method="POST" action="{{ route('notifications.destroy', $notification) }}" onsubmit="return confirm('Bu bildirimi silmek istediğinize emin misiniz?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger rounded-pill px-4 fw-semibold btn-sm">
                            <i class="ti ti-trash me-1"></i> Sil
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
