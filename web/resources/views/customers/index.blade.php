@extends('layouts.app')
@section('title', 'Müşteriler - B&V Barber')
@section('content')
<x-page-header title="Müşteriler" description="Kayıtlı tüm müşterilerinizi görüntüleyin ve yönetin.">
    <x-btn-primary href="{{ route('customers.create') }}" icon="ti ti-plus">
        Yeni Müşteri Ekle
    </x-btn-primary>
</x-page-header>

{{-- Filters --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <form method="GET" action="{{ route('customers.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-8 col-lg-6">
                    <label class="form-label text-secondary small fw-medium mb-1">Arama</label>
                    <x-search-input name="search" placeholder="İsim, e-posta veya telefon ile ara..." :value="request('search')" :resetUrl="route('customers.index')" />
                </div>
                <div class="col-12 col-md-4 col-lg-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-1 fw-medium" style="height: 38px;">
                        <i class="ti ti-filter"></i> Filtrele
                    </button>
                    @if(request()->filled('search'))
                        <a href="{{ route('customers.index') }}" class="btn btn-light rounded-pill px-2 shadow-sm d-inline-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="height: 38px; width: 38px;" title="Filtreleri Temizle">
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
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless align-middle mb-0">
                        <thead class="table-light text-muted">
                            <tr>
                                <th class="ps-4 py-3 rounded-start">Müşteri</th>
                                <th class="py-3">İletişim</th>
                                <th class="py-3">Cinsiyet</th>
                                <th class="py-3">Randevu Sayısı</th>
                                <th class="py-3">Durum</th>
                                <th class="text-end pe-4 py-3 rounded-end">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                            <tr class="border-bottom" style="cursor:pointer;" onclick="if(!event.target.closest('.btn, form')){window.location='{{ route('customers.show', $customer) }}'}" title="Müşteri Detayını Gör">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        @if($customer->profile_photo_url)
                                            <img src="{{ $customer->profile_photo_url }}" alt="{{ $customer->full_name }}" class="rounded-circle me-3 object-fit-cover" width="48" height="48">
                                        @else
                                            <div class="avatar bg-primary-subtle text-primary me-3 rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 48px; height: 48px;">
                                                {{ mb_substr($customer->first_name, 0, 1) }}{{ mb_substr($customer->last_name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0 fw-semibold text-dark">{{ $customer->full_name }}</h6>
                                            <small class="text-muted">Kayıt: {{ $customer->created_at->format('d.m.Y') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex align-items-center text-dark">
                                            <i class="ti ti-mail text-muted me-2"></i>
                                            <span class="fs-7">{{ $customer->email }}</span>
                                        </div>
                                        @if($customer->phone)
                                        <div class="d-flex align-items-center text-dark">
                                            <i class="ti ti-phone text-muted me-2"></i>
                                            <span class="fs-7">{{ $customer->phone }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($customer->gender == App\Enums\Gender::Male)
                                        <span class="text-primary d-flex align-items-center"><i class="ti ti-gender-male me-1"></i> Erkek</span>
                                    @elseif($customer->gender == App\Enums\Gender::Female)
                                        <span class="text-danger d-flex align-items-center"><i class="ti ti-gender-female me-1"></i> Kadın</span>
                                    @else
                                        <span class="text-success d-flex align-items-center"><i class="ti ti-users me-1"></i> Diğer/Belirtilmemiş</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-secondary-subtle text-secondary rounded-pill px-3 py-1 fw-semibold d-flex align-items-center">
                                            <i class="ti ti-calendar me-2"></i> {{ $customer->appointments_count }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($customer->status == App\Enums\UserStatus::Active)
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2"><i class="ti ti-check-circle me-1"></i>Aktif</span>
                                    @elseif($customer->status == App\Enums\UserStatus::Inactive)
                                        <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-2"><i class="ti ti-na me-1"></i>Pasif</span>
                                    @elseif($customer->status == App\Enums\UserStatus::Blocked)
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2"><i class="ti ti-ban me-1"></i>Engelli</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-primary btn-sm rounded-circle p-2 border-0" title="Düzenle">
                                            <i class="ti ti-pencil fs-5"></i>
                                        </a>
                                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu müşteriyi silmek istediğinize emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-2 border-0" title="Sil">
                                                <i class="ti ti-trash fs-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <x-empty-state icon="ti ti-user-circle" title="Kayıtlı müşteri bulunamadı" message="Sisteme henüz bir müşteri kayıt olmamış veya arama kriterinize uygun müşteri bulunamadı." colspan="6" />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($customers->hasPages())
            <div class="card-footer bg-white border-top border-light-subtle py-3 px-4">
                {{ $customers->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
