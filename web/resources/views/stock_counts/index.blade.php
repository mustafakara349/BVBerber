@extends('layouts.app')
@section('title', 'Stok Sayımları - B&V Barber')
@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Stok Sayımları</h1>
                <p class="text-muted mb-0">Depo ve salon stok sayımlarını görüntüleyin ve yeni sayım girin.</p>
            </div>
            <div>
                <a href="{{ route('stock-counts.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm d-flex align-items-center gap-2">
                    <i class="ti ti-plus fs-5"></i> Yeni Sayım Gir
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0">Sayım Tarihi</th>
                                <th class="py-3 border-0">İşleyen</th>
                                <th class="py-3 border-0">Notlar</th>
                                <th class="pe-4 py-3 border-0 text-center">Kayıt Zamanı</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($counts as $count)
                            <tr class="border-bottom border-light" style="cursor:pointer;" onclick="window.location='{{ route('stock-counts.show', $count) }}'" title="Detayı Gör">
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($count->count_date)->format('d.m.Y') }}</div>
                                </td>
                                <td>
                                    <div class="small fw-semibold">{{ $count->creator ? $count->creator->first_name . ' ' . $count->creator->last_name : 'Sistem' }}</div>
                                </td>
                                <td>
                                    @if($count->notes)
                                        <span class="text-secondary small">{{ Str::limit($count->notes, 40) }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center text-secondary small pe-4">
                                    {{ $count->created_at->format('H:i') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="ti ti-clipboard-list fs-1 mb-2 d-block text-secondary opacity-50"></i>
                                    <h5>Kayıt bulunamadı.</h5>
                                    <p class="small text-secondary mb-0">Yeni bir periyodik sayım girmek için "Yeni Sayım Gir" butonunu kullanın.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($counts->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $counts->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
