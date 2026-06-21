@extends('layouts.app')
@section('title', 'Tedarikçiler - Stok & Tedarik - B&V Barber')
@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Tedarikçiler</h1>
                <p class="text-muted mb-0">Mal alımı yaptığınız toptancı ve firmaları yönetin.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                    <i class="ti ti-plus fs-5"></i> Yeni Tedarikçi Ekle
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Filter Panel -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4 bg-light bg-opacity-30">
                <form action="{{ route('suppliers.index') }}" method="GET" class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label text-secondary fw-semibold small">Durum</label>
                        <select name="status" class="form-select border-0 shadow-sm rounded-3">
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Tümü</option>
                            <option value="active" {{ request('status') === 'active' || !request('status') ? 'selected' : '' }}>Aktif Tedarikçiler</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Pasif Tedarikçiler</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-secondary fw-semibold small">Arama</label>
                        <input type="text" name="search" class="form-control border-0 shadow-sm rounded-3" placeholder="Firma adı, e-posta, telefon..." value="{{ request('search') }}">
                    </div>

                    <div class="col-12 col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary rounded-3 w-100 py-2 shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="ti ti-search fs-5"></i> Ara
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Suppliers Table -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0">Firma / Kişi Adı</th>
                                <th class="py-3 border-0">İletişim</th>
                                <th class="py-3 border-0">Vergi No</th>
                                <th class="py-3 border-0 text-center">Durum</th>
                                <th class="pe-4 py-3 border-0 text-end">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $supplier)
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <div class="fw-semibold text-dark">{{ $supplier->name }}</div>
                                    @if($supplier->address)
                                    <small class="text-secondary"><i class="ti ti-map-pin"></i> {{ Str::limit($supplier->address, 40) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->phone)
                                    <div class="small fw-semibold"><i class="ti ti-phone"></i> {{ $supplier->phone }}</div>
                                    @endif
                                    @if($supplier->email)
                                    <div class="small text-secondary"><i class="ti ti-mail"></i> {{ $supplier->email }}</div>
                                    @endif
                                    @if(!$supplier->phone && !$supplier->email)
                                    <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">{{ $supplier->tax_number ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($supplier->is_active)
                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">Pasif</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-circle p-2 border-0 edit-supplier-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editSupplierModal"
                                            data-id="{{ $supplier->id }}"
                                            data-name="{{ $supplier->name }}"
                                            data-phone="{{ $supplier->phone }}"
                                            data-email="{{ $supplier->email }}"
                                            data-tax="{{ $supplier->tax_number }}"
                                            data-address="{{ $supplier->address }}"
                                            data-active="{{ $supplier->is_active ? 1 : 0 }}"
                                            title="Düzenle">
                                            <i class="ti ti-pencil fs-5"></i>
                                        </button>
                                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu tedarikçiyi silmek istediğinize emin misiniz?')">
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
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="ti ti-truck fs-1 mb-2 d-block text-secondary opacity-50"></i>
                                    <h5>Tedarikçi bulunamadı.</h5>
                                    <p class="small text-secondary mb-0">Mal alımı yapabilmek için sisteme tedarikçi ekleyin.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($suppliers->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $suppliers->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white py-3">
                <h5 class="modal-title fw-bold">Yeni Tedarikçi Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Firma / Kişi Adı *</label>
                        <input type="text" name="name" class="form-control border-0 bg-light" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Telefon (Opsiyonel)</label>
                            <input type="text" name="phone" class="form-control border-0 bg-light">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">E-posta (Opsiyonel)</label>
                            <input type="email" name="email" class="form-control border-0 bg-light">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Vergi Numarası (Opsiyonel)</label>
                            <input type="text" name="tax_number" class="form-control border-0 bg-light">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked value="1">
                                <label class="form-check-label fw-semibold" for="isActive">Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary">Adres (Opsiyonel)</label>
                        <textarea name="address" rows="2" class="form-control border-0 bg-light"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white py-3">
                <h5 class="modal-title fw-bold">Tedarikçi Düzenle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form id="editSupplierForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Firma / Kişi Adı *</label>
                        <input type="text" name="name" id="edit_name" class="form-control border-0 bg-light" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Telefon</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control border-0 bg-light">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">E-posta</label>
                            <input type="email" name="email" id="edit_email" class="form-control border-0 bg-light">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Vergi Numarası</label>
                            <input type="text" name="tax_number" id="edit_tax" class="form-control border-0 bg-light">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                                <label class="form-check-label fw-semibold" for="edit_is_active">Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary">Adres</label>
                        <textarea name="address" id="edit_address" rows="2" class="form-control border-0 bg-light"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const editModal = document.getElementById('editSupplierModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                
                document.getElementById('editSupplierForm').action = `/suppliers/${id}`;
                document.getElementById('edit_name').value = button.getAttribute('data-name');
                document.getElementById('edit_phone').value = button.getAttribute('data-phone');
                document.getElementById('edit_email').value = button.getAttribute('data-email');
                document.getElementById('edit_tax').value = button.getAttribute('data-tax');
                document.getElementById('edit_address').value = button.getAttribute('data-address');
                document.getElementById('edit_is_active').checked = button.getAttribute('data-active') === '1';
            });
        }
    });
</script>
@endpush
