@extends('layouts.app')
@section('title', 'Ürün Kategorileri')

@section('content')
<x-page-header title="Ürün Kategorileri" description="Ürünlerinizi gruplandırabileceğiniz kategorileri yönetin.">
    <x-btn-primary href="{{ route('product-categories.create') }}" icon="ti ti-plus">
        Yeni Kategori
    </x-btn-primary>
</x-page-header>

{{-- Filters --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <form action="{{ route('product-categories.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-6 col-lg-5">
                    <label class="form-label text-secondary small fw-medium mb-1">Arama</label>
                    <x-search-input name="search" placeholder="Kategori adı ile ara..." :value="request('search')" :resetUrl="route('product-categories.index')" />
                </div>
                <div class="col-6 col-md-3 col-lg-4">
                    <x-form-select name="status" label="Durum" :pill="true">
                        <option value="">Tümü</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Pasif</option>
                    </x-form-select>
                </div>
                <div class="col-6 col-md-3 col-lg-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-1 fw-medium" style="height: 38px;">
                        <i class="ti ti-filter"></i> Filtrele
                    </button>
                    @if(request()->anyFilled(['search', 'status']))
                        <a href="{{ route('product-categories.index') }}" class="btn btn-light rounded-pill px-2 shadow-sm d-inline-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="height: 38px; width: 38px;" title="Filtreleri Temizle">
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
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="ps-4" width="80">Sıra</th>
                            <th>Kategori Adı</th>
                            <th width="120">Durum</th>
                            <th class="pe-4 text-end" width="150">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="ps-4 text-muted fw-semibold">{{ $category->sort_order }}</td>
                                <td class="fw-bold text-dark">{{ $category->name }}</td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1.5">Aktif</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1.5">Pasif</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('product-categories.edit', $category) }}" class="btn btn-outline-primary btn-sm rounded-circle p-2 border-0" title="Düzenle">
                                            <i class="ti ti-pencil fs-5"></i>
                                        </a>
                                        <form action="{{ route('product-categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu kategoriyi silmek istediğinize emin misiniz? (Bağlı ürünler kategorisiz kalabilir)')">
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
                            <x-empty-state icon="ti ti-category" title="Henüz kategori eklenmemiş" message="Ürünleri sınıflandırmak için yeni bir kategori oluşturabilirsiniz." colspan="4" />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
