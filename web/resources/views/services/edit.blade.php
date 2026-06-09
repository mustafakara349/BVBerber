@extends('layouts.app')
@section('title', 'Hizmet Düzenle - B&V Barber')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fs-3 mb-1">Hizmet Düzenle</h1>
                <p class="text-muted">{{ $service->name }} detaylarını güncelleyin.</p>
            </div>
            <a href="{{ route('services.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Geri Dön
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 col-lg-10">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4">
                <form action="{{ route('services.update', $service->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <h5 class="mb-3 text-primary border-bottom pb-2">Hizmet Bilgileri</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Hizmet Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $service->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Kategori</label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">Seçiniz</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $service->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Süre (Dakika) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" value="{{ old('duration_minutes', $service->duration_minutes) }}" required min="5">
                            @error('duration_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Hedef Kitle <span class="text-danger">*</span></label>
                            <select name="gender_type" class="form-select @error('gender_type') is-invalid @enderror" required>
                                <option value="unisex" {{ old('gender_type', $service->gender_type) == 'unisex' ? 'selected' : '' }}>Unisex</option>
                                <option value="male" {{ old('gender_type', $service->gender_type) == 'male' ? 'selected' : '' }}>Sadece Erkek</option>
                                <option value="female" {{ old('gender_type', $service->gender_type) == 'female' ? 'selected' : '' }}>Sadece Kadın</option>
                            </select>
                            @error('gender_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Fiyat (₺) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $service->price) }}" required min="0">
                            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">İndirimli Fiyat (₺)</label>
                            <input type="number" step="0.01" name="discounted_price" class="form-control @error('discounted_price') is-invalid @enderror" value="{{ old('discounted_price', $service->discounted_price) }}" min="0">
                            <small class="text-muted">İndirim yoksa boş bırakın.</small>
                            @error('discounted_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Açıklama</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $service->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="col-12 mt-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_popular" id="isPopular" value="1" {{ old('is_popular', $service->is_popular) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isPopular">Bu hizmeti Popüler (Çok Tercih Edilen) olarak işaretle</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" value="1" {{ old('is_featured', $service->is_featured) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isFeatured">Bu hizmeti Öne Çıkan olarak vitrinde göster</label>
                            </div>
                            <div class="form-check border-top pt-3 mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-success" for="isActive">Hizmet sistemde Aktif (Müşteriler randevu alabilir)</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                            <i class="ti ti-check me-1"></i> Değişiklikleri Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
