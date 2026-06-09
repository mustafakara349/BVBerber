@extends('layouts.app')
@section('title', 'Giderler - B&V Barber')
@section('content')

<!-- Header Area -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="fs-3 fw-bold mb-1 text-dark">Giderler</h1>
                <p class="text-muted mb-0">İşletme masraflarını, faturaları, tedarik ve personel giderlerini kaydedip analiz edin.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary rounded-pill px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="ti ti-folder-plus fs-5"></i> Yeni Kategori Ekle
                </button>
                <button type="button" class="btn btn-danger rounded-pill px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="ti ti-plus fs-5"></i> Yeni Gider Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Summary Cards -->
<div class="row g-4 mb-4">
    <!-- Expense This Month -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute end-0 bottom-0 opacity-10 mb-n3 me-n2">
                    <i class="ti ti-calendar" style="font-size: 110px; line-height: 1;"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-2 rounded-3" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="ti ti-calendar fs-4 text-white"></i>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 text-white" style="background-color: rgba(255, 255, 255, 0.25);">Bu Ay</span>
                </div>
                <h6 class="text-white text-opacity-75 small mb-1 fw-medium text-uppercase tracking-wider">Bu Ayki Toplam Gider</h6>
                <h3 class="fs-2 fw-bold mb-0">₺{{ number_format($totalExpenseThisMonth, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- Expense All Time -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute end-0 bottom-0 opacity-10 mb-n3 me-n2">
                    <i class="ti ti-coins" style="font-size: 110px; line-height: 1;"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-2 rounded-3" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="ti ti-report-money fs-4 text-white"></i>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 text-white" style="background-color: rgba(255, 255, 255, 0.25);">Filtrelenmiş Toplam</span>
                </div>
                <h6 class="text-white text-opacity-75 small mb-1 fw-medium text-uppercase tracking-wider">Filtrelenmiş Toplam Gider</h6>
                <h3 class="fs-2 fw-bold mb-0">₺{{ number_format($totalExpenseAllTime, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- Top Spending Category -->
    <div class="col-12 col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute end-0 bottom-0 opacity-10 mb-n3 me-n2">
                    <i class="ti ti-chart-pie" style="font-size: 110px; line-height: 1;"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-2 rounded-3" style="background-color: rgba(255, 255, 255, 0.2);">
                        <i class="ti ti-chart-bar fs-4 text-white"></i>
                    </div>
                    <span class="badge rounded-pill px-3 py-1 text-white" style="background-color: rgba(255, 255, 255, 0.25);">En Çok Harcanan</span>
                </div>
                <h6 class="text-white text-opacity-75 small mb-1 fw-medium text-uppercase tracking-wider">En Yüksek Gider Kategorisi</h6>
                <h3 class="fs-4 fw-bold mb-0 text-truncate">
                    {{ $topCategoryName }} 
                    @if($topCategoryAmount > 0)
                        <span class="fs-6 fw-normal text-white text-opacity-80">(₺{{ number_format($topCategoryAmount, 2, ',', '.') }})</span>
                    @endif
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- Alert and Error Handling Messages -->
@if(session('success'))
<div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="ti ti-circle-check fs-4"></i>
    <div>{{ session('success') }}</div>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4" role="alert">
    <div class="d-flex align-items-center gap-2 mb-2 fw-semibold">
        <i class="ti ti-alert-circle fs-4"></i>
        <span>Bir Hata Oluştu:</span>
    </div>
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- Advanced Filter Panel -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-filter text-danger fs-4"></i>
                    <h5 class="card-title fw-bold text-dark mb-0 fs-6">Giderleri Filtrele</h5>
                </div>
                @if(request()->anyFilled(['category_id', 'start_date', 'end_date']))
                    <a href="{{ route('finance.expenses') }}" class="btn btn-light rounded-pill btn-sm text-secondary d-flex align-items-center gap-1">
                        <i class="ti ti-rotate"></i> Filtreleri Temizle
                    </a>
                @endif
            </div>
            <div class="card-body p-4 bg-light bg-opacity-30">
                <form action="{{ route('finance.expenses') }}" method="GET" class="row g-3">
                    <!-- Category Filter -->
                    <div class="col-12 col-md-4">
                        <label class="form-label text-secondary fw-semibold small">Gider Kategorisi</label>
                        <select name="category_id" class="form-select border-0 shadow-sm rounded-3">
                            <option value="">Tüm Gider Kategorileri</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Start Date Filter -->
                    <div class="col-12 col-md-3">
                        <label class="form-label text-secondary fw-semibold small">Başlangıç Tarihi</label>
                        <input type="date" name="start_date" class="form-control border-0 shadow-sm rounded-3" value="{{ request('start_date') }}">
                    </div>

                    <!-- End Date Filter -->
                    <div class="col-12 col-md-3">
                        <label class="form-label text-secondary fw-semibold small">Bitiş Tarihi</label>
                        <input type="date" name="end_date" class="form-control border-0 shadow-sm rounded-3" value="{{ request('end_date') }}">
                    </div>

                    <!-- Submit Button -->
                    <div class="col-12 col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-danger rounded-3 w-100 py-2 shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="ti ti-search fs-5"></i> Giderleri Bul
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Expenses Table List -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-dark">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 border-0">Gider Kategori</th>
                                <th class="py-3 border-0">Harcanan Tutar</th>
                                <th class="py-3 border-0">Harcama Tarihi</th>
                                <th class="py-3 border-0">Açıklama</th>
                                <th class="py-3 border-0">Ekleyen Personel</th>
                                <th class="py-3 border-0">Fiş / Fatura</th>
                                <th class="pe-4 py-3 text-end border-0">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-bold">
                                        <i class="ti ti-tag me-1"></i> {{ $expense->category->name ?? 'Belirtilmemiş' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-extrabold text-danger fs-6">
                                        ₺{{ number_format($expense->amount, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $expense->expense_date->format('d.m.Y') }}</span>
                                </td>
                                <td>
                                    <span class="text-secondary text-wrap" style="max-width: 300px; display: inline-block;">
                                        {{ $expense->description ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-secondary small d-flex align-items-center gap-1">
                                        <i class="ti ti-user-circle"></i> {{ $expense->createdBy->full_name ?? 'Bilinmiyor' }}
                                    </span>
                                </td>
                                <td>
                                    @if($expense->receipt_file)
                                        <a href="{{ asset('storage/' . $expense->receipt_file) }}" target="_blank" class="btn btn-light btn-sm rounded-pill border d-inline-flex align-items-center gap-1 text-primary">
                                            <i class="ti ti-file-text"></i> Belgeyi Gör
                                        </a>
                                    @else
                                        <span class="text-muted small">Yok</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <form action="{{ route('finance.expenses.destroy', $expense) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Bu gider kaydını sildiğinizde ilgili kasa işlemi de otomatik olarak iptal edilecektir. Emin misiniz?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-2 border-0" title="Gideri Sil">
                                            <i class="ti ti-trash fs-5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="ti ti-mood-empty fs-1 mb-2 d-block text-secondary opacity-50"></i>
                                    <h5>Hiç gider kaydı bulunamadı.</h5>
                                    <p class="small text-secondary mb-0">İlk gider kaydınızı eklemek için sağ üst köşedeki "Yeni Gider Kaydet" butonuna tıklayabilirsiniz.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($expenses->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Elegant Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-danger text-white py-3">
                <h5 class="modal-title fw-bold" id="addExpenseModalLabel">Yeni Gider Kaydet</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="{{ route('finance.expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <!-- Category selection -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Gider Kategorisi</label>
                        <select name="category_id" class="form-select border-0 bg-light" required>
                            <option value="">Kategori Seçin</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <span class="small text-muted mt-1 d-block">Aradığınız kategori yoksa, arkadaki "Yeni Kategori Ekle" butonundan oluşturabilirsiniz.</span>
                    </div>

                    <!-- Amount & Date -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-secondary">Tutar (₺)</label>
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light">₺</span>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control border-0 bg-light rounded-end-3" placeholder="0,00" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-secondary">Ödeme Tarihi</label>
                            <input type="date" name="expense_date" class="form-control border-0 bg-light" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <!-- Payment method selection (for automatic transaction logic) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Ödeme Kaynağı (Nereden Ödendi?)</label>
                        <select name="payment_method" class="form-select border-0 bg-light" required>
                            <option value="cash">Nakit (Kasa)</option>
                            <option value="credit_card">Kredi Kartı / Banka Kartı</option>
                            <option value="bank_transfer">Banka Havalesi / EFT</option>
                        </select>
                    </div>

                    <!-- Receipt Upload -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Fatura / Fiş Fotoğrafı (İsteğe Bağlı)</label>
                        <input type="file" name="receipt_file" class="form-control border-0 bg-light" accept="image/*,application/pdf">
                        <span class="small text-muted mt-1 d-block">Maksimum dosya boyutu: 2MB (Görsel veya PDF)</span>
                    </div>

                    <!-- Description -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary">Açıklama</label>
                        <textarea name="description" rows="3" class="form-control border-0 bg-light" placeholder="Gidere dair detaylar yazın (örn: Kira ödemesi, Elektrik faturası)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">Gider Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Category Management Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="addCategoryModalLabel">Yeni Gider Kategorisi Oluştur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="{{ route('finance.expenses.categories.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <!-- Category Name -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Kategori Adı</label>
                        <input type="text" name="name" class="form-control border-0 bg-light" placeholder="Örn: Kira, Fatura, Temizlik Malzemeleri, Maaş..." required>
                    </div>

                    <!-- Category Description -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary">Kategori Açıklaması (İsteğe Bağlı)</label>
                        <textarea name="description" rows="3" class="form-control border-0 bg-light" placeholder="Bu kategoriye dair açıklayıcı notlar girin..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 text-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Kategoriyi Oluştur</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
