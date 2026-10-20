@extends('layouts.app')
@section('title', 'Stok Sayımları - B&V Barber')
@section('content')

<x-page-header title="Stok Sayımları" description="Depo ve salon stok sayımlarını görüntüleyin ve yeni sayım girin.">
    <x-btn-primary href="{{ route('stock-counts.create') }}" icon="ti ti-plus">
        Yeni Sayım Gir
    </x-btn-primary>
</x-page-header>

{{-- Filters --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <form action="{{ route('stock-counts.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label class="form-label text-secondary small fw-medium mb-1">Arama</label>
                    <x-search-input name="search" placeholder="Not veya personel adı ile ara..." :value="request('search')" :resetUrl="route('stock-counts.index')" />
                </div>
                <div class="col-6 col-md-2">
                    <x-date-input name="date_from" label="Başlangıç" :value="request('date_from')" :pill="true" />
                </div>
                <div class="col-6 col-md-2">
                    <x-date-input name="date_to" label="Bitiş" :value="request('date_to')" :pill="true" />
                </div>
                <div class="col-12 col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-1 fw-medium" style="height: 38px;">
                        <i class="ti ti-filter"></i> Filtrele
                    </button>
                    @if(request()->anyFilled(['search', 'date_from', 'date_to']))
                        <a href="{{ route('stock-counts.index') }}" class="btn btn-light rounded-pill px-2 shadow-sm d-inline-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="height: 38px; width: 38px;" title="Filtreleri Temizle">
                            <i class="ti ti-rotate"></i>
                        </a>
                    @endif
                </div>
            </form>
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
                            <x-empty-state icon="ti ti-clipboard-list" title="Kayıt bulunamadı" message="Yeni bir periyodik sayım girmek için 'Yeni Sayım Gir' butonunu kullanın." colspan="4" />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($counts->hasPages())
                <div class="card-footer bg-white border-top border-light-subtle py-3 px-4">
                    {{ $counts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
