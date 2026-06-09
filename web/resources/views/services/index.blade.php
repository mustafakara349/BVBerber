@extends('layouts.app')
@section('title', 'Hizmetler - B&V Barber')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fs-3 mb-1">Hizmetler</h1>
                <p class="text-muted">Tüm hizmetleri görüntüleyin ve yönetin.</p>
            </div>
            <a href="{{ route('services.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="ti ti-plus me-1"></i> Yeni Hizmet Ekle
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
                                <th class="ps-4 py-3 rounded-start">Hizmet Adı</th>
                                <th class="py-3">Kategori</th>
                                <th class="py-3">Süre</th>
                                <th class="py-3">Fiyat</th>
                                <th class="py-3">Hedef Kitle</th>
                                <th class="py-3">Durum</th>
                                <th class="text-end pe-4 py-3 rounded-end">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($services as $service)
                            <tr class="border-bottom">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary-subtle text-primary me-3 rounded p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="ti ti-cut fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold text-dark">{{ $service->name }}</h6>
                                            <div class="d-flex gap-1 mt-1">
                                                @if($service->is_popular)
                                                    <span class="badge bg-danger-subtle text-danger" style="font-size: 0.7rem;">Popüler</span>
                                                @endif
                                                @if($service->is_featured)
                                                    <span class="badge bg-warning-subtle text-warning" style="font-size: 0.7rem;">Öne Çıkan</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($service->category)
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1">{{ $service->category->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-clock text-muted me-1"></i>
                                        <span class="fw-medium text-dark">{{ $service->duration_minutes }} dk</span>
                                    </div>
                                </td>
                                <td>
                                    @if($service->discounted_price)
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-success">{{ number_format($service->discounted_price, 2) }} ₺</span>
                                            <small class="text-decoration-line-through text-muted" style="font-size: 0.75rem">{{ number_format($service->price, 2) }} ₺</small>
                                        </div>
                                    @else
                                        <span class="fw-bold text-dark">{{ number_format($service->price, 2) }} ₺</span>
                                    @endif
                                </td>
                                <td>
                                    @if($service->gender_type == 'male')
                                        <div class="d-flex align-items-center text-primary" title="Erkek">
                                            <i class="ti ti-gender-male fs-5 me-1"></i>
                                        </div>
                                    @elseif($service->gender_type == 'female')
                                        <div class="d-flex align-items-center text-danger" title="Kadın">
                                            <i class="ti ti-gender-female fs-5 me-1"></i>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center text-success" title="Unisex">
                                            <i class="ti ti-users fs-5 me-1"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input status-toggle" type="checkbox" role="switch" data-id="{{ $service->id }}" {{ $service->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" id="statusLabel_{{ $service->id }}">
                                            @if($service->is_active)
                                                <span class="badge bg-success-subtle text-success rounded-pill px-2">Aktif</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2">Pasif</span>
                                            @endif
                                        </label>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm">
                                        <a href="{{ route('services.edit', $service->id) }}" class="btn btn-sm btn-light text-primary" title="Düzenle">
                                            <i class="ti ti-pencil"></i>
                                        </a>
                                        <form action="{{ route('services.destroy', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu hizmeti silmek istediğinize emin misiniz?');">
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
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                        <div class="bg-light rounded-circle p-4 mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                            <i class="ti ti-cut fs-1 text-secondary"></i>
                                        </div>
                                        <h5 class="fw-medium text-dark">Kayıtlı hizmet bulunamadı</h5>
                                        <p class="mb-0">Sisteme henüz bir hizmet eklenmemiş. Yeni hizmet ekleyerek başlayabilirsiniz.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($services->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $services->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('.status-toggle');
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const serviceId = this.dataset.id;
            const isChecked = this.checked;
            const label = document.getElementById('statusLabel_' + serviceId);
            
            fetch(`/services/${serviceId}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    if(data.is_active) {
                        label.innerHTML = '<span class="badge bg-success-subtle text-success rounded-pill px-2">Aktif</span>';
                    } else {
                        label.innerHTML = '<span class="badge bg-danger-subtle text-danger rounded-pill px-2">Pasif</span>';
                    }
                } else {
                    // Revert if failed
                    this.checked = !isChecked;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !isChecked;
            });
        });
    });
});
</script>
@endpush
