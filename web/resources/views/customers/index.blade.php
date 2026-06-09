@extends('layouts.app')
@section('title', 'Müşteriler - B&V Barber')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fs-3 mb-1">Müşteriler</h1>
                <p class="text-muted">Kayıtlı tüm müşterilerinizi görüntüleyin ve yönetin.</p>
            </div>
            <a href="{{ route('customers.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="ti ti-plus me-1"></i> Yeni Müşteri Ekle
            </a>
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
                            <tr class="border-bottom">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        @if($customer->profile_photo)
                                            <img src="{{ asset($customer->profile_photo) }}" alt="{{ $customer->full_name }}" class="rounded-circle me-3 object-fit-cover" width="48" height="48">
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
                                    <div class="btn-group shadow-sm">
                                        <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-light text-info" title="Görüntüle">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-light text-primary" title="Düzenle">
                                            <i class="ti ti-pencil"></i>
                                        </a>
                                        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu müşteriyi silmek istediğinize emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" title="Sil">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                        <div class="bg-light rounded-circle p-4 mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                            <i class="ti ti-user-circle fs-1 text-secondary"></i>
                                        </div>
                                        <h5 class="fw-medium text-dark">Kayıtlı müşteri bulunamadı</h5>
                                        <p class="mb-0">Sisteme henüz bir müşteri kayıt olmamış.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($customers->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $customers->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
