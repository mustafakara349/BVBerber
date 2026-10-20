@extends('layouts.app')
@section('title', 'Kafe Kategorileri - B&V Barber')

@section('content')
<x-page-header title="Kafe Kategorileri" description="Cafe menünüzdeki kategori ağacını ve sıralamasını yönetin.">
    <x-btn-primary href="{{ route('cafe-categories.create') }}" icon="ti ti-plus">
        Yeni Kategori Ekle
    </x-btn-primary>
</x-page-header>

{{-- Filters --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <form action="{{ route('cafe-categories.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-6 col-lg-5">
                    <label class="form-label text-secondary small fw-medium mb-1">Arama</label>
                    <x-search-input name="search" placeholder="Kategori adı ile ara..." :value="request('search')" :resetUrl="route('cafe-categories.index')" />
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
                        <a href="{{ route('cafe-categories.index') }}" class="btn btn-light rounded-pill px-2 shadow-sm d-inline-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="height: 38px; width: 38px;" title="Filtreleri Temizle">
                            <i class="ti ti-rotate"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-dark">
                <thead class="bg-light text-secondary">
                    <tr>
                        <th class="ps-4 py-3 border-0" width="100">Sıra</th>
                        <th class="py-3 border-0">Kategori Adı</th>
                        <th class="py-3 border-0 text-center" width="140">Durum</th>
                        <th class="pe-4 py-3 border-0 text-end" width="160">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr class="border-bottom border-light">
                            <td class="ps-4 py-3 fw-semibold text-secondary">
                                <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">{{ $category->sort_order }}</span>
                            </td>
                            <td class="fw-bold text-dark">{{ $category->name }}</td>
                            <td class="text-center">
                                @if($category->is_active)
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold">Aktif</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-bold">Pasif</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-inline-flex gap-2 align-items-center">
                                    <a href="{{ route('cafe-categories.edit', $category) }}" class="btn btn-outline-primary btn-sm rounded-circle p-2 border-0" title="Düzenle">
                                        <i class="ti ti-pencil fs-5"></i>
                                    </a>
                                    <form action="{{ route('cafe-categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu kategoriyi silmek istediğinize emin misiniz? (Bağlı ürünler kategorisiz kalabilir)')">
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
                        <x-empty-state 
                            colspan="4" 
                            title="Henüz kategori eklenmemiş" 
                            description="Cafe menünüz için yeni bir kategori oluşturabilirsiniz.">
                            <x-slot:action>
                                <x-btn-primary href="{{ route('cafe-categories.create') }}" icon="ti ti-plus">
                                    İlk Kategoriyi Ekle
                                </x-btn-primary>
                            </x-slot:action>
                        </x-empty-state>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
