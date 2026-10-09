@extends('layouts.app')
@section('title', 'Kafe Kategorileri')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">Kafe Kategorileri</h2>
        <a href="{{ route('cafe-categories.create') }}" class="btn btn-primary">
            <i class="ti ti-plus"></i> Yeni Kategori
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="80">Sıra</th>
                            <th>Kategori Adı</th>
                            <th width="100">Durum</th>
                            <th width="150">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->sort_order }}</td>
                                <td>{{ $category->name }}</td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Pasif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('cafe-categories.edit', $category) }}" class="btn btn-sm btn-info text-white">
                                            <i class="ti ti-pencil"></i>
                                        </a>
                                        <form action="{{ route('cafe-categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu kategoriyi silmek istediğinize emin misiniz? (Bağlı ürünler kategorisiz kalabilir)')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Henüz kategori eklenmemiş.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
