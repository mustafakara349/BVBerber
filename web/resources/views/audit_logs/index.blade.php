@extends('layouts.app')

@section('title', 'İşlem & Hata Günlükleri - B&V Barber')

@push('styles')
<style>
    /* Kurumsal Oturum (Auth) & Güvenlik Rozet Renkleri */
    .bg-indigo-subtle {
        background-color: #eef2ff !important;
    }
    .text-indigo {
        color: #3730a3 !important;
    }
    .border-indigo-subtle {
        border-color: #c7d2fe !important;
    }
    /* Rozetlerde beyaz üzeri beyaz (kontrast kaybı) olmasını önleyen koruma kuralı */
    .table .badge:not(.bg-primary):not(.bg-danger):not(.bg-success):not(.bg-dark):not(.bg-secondary),
    .modal .badge:not(.bg-primary):not(.bg-danger):not(.bg-success):not(.bg-dark):not(.bg-secondary) {
        --bs-badge-color: #212529;
    }
    .table .badge {
        letter-spacing: 0.2px;
    }
    /* Reverb Canlı Satır Giriş Animasyonu */
    @keyframes liveRowSlideIn {
        0% {
            opacity: 0;
            transform: translateY(-20px);
            background-color: rgba(230, 98, 57, 0.22);
        }
        40% {
            background-color: rgba(230, 98, 57, 0.12);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
            background-color: transparent;
        }
    }
    .live-row-enter {
        animation: liveRowSlideIn 1.1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="ti ti-history text-primary fs-3"></i>
                    İşlem & Hata Günlükleri
                </h4>
            </div>
            <p class="text-secondary small mb-0">Sistemdeki tüm veri hareketleri, kullanıcı aktiviteleri, güvenlik olayları ve hata raporları.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary rounded-pill px-3 d-inline-flex align-items-center gap-2 fw-medium shadow-xs" data-bs-toggle="modal" data-bs-target="#archiveFilterModal">
                <i class="ti ti-archive fs-5"></i>
                <span>Eski Kayıtları İncele</span>
            </button>
            <a href="{{ route('audit-logs.export', request()->query()) }}" class="btn btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center gap-2 fw-medium shadow-xs" title="Filtrelenen kayıtları CSV olarak indir">
                <i class="ti ti-file-spreadsheet fs-5 text-success"></i>
                <span>CSV Olarak İndir</span>
            </a>
        </div>
    </div>

    {{-- Top Summary Metric Cards (Interactive Filter Shortcuts) --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('audit-logs.index', ['tab' => 'all']) }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-decoration-none hover-lift transition-all">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary small fw-medium d-block mb-1">Toplam Hareket</span>
                        <h4 class="fw-bold text-dark mb-0" id="metricTotal">{{ number_format($metrics['total'], 0, ',', '.') }}</h4>
                    </div>
                    <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="ti ti-database fs-3"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('audit-logs.index', ['tab' => 'all', 'start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d')]) }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-decoration-none hover-lift transition-all">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary small fw-medium d-block mb-1">Bugünkü İşlemler</span>
                        <h4 class="fw-bold text-dark mb-0" id="metricToday">{{ number_format($metrics['today'], 0, ',', '.') }}</h4>
                    </div>
                    <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="ti ti-bolt fs-3"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('audit-logs.index', ['tab' => 'errors']) }}" class="card border-0 shadow-sm rounded-4 p-3 {{ $metrics['errors'] > 0 ? 'bg-danger-subtle border border-danger-subtle' : 'bg-white' }} h-100 text-decoration-none hover-lift transition-all">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary small fw-medium d-block mb-1">Son 24 Saat Hataları</span>
                        <h4 class="fw-bold {{ $metrics['errors'] > 0 ? 'text-danger' : 'text-dark' }} mb-0" id="metricErrors">{{ number_format($metrics['errors'], 0, ',', '.') }}</h4>
                    </div>
                    <div class="rounded-3 {{ $metrics['errors'] > 0 ? 'bg-danger text-white' : 'bg-light text-secondary' }} d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="ti ti-alert-triangle fs-3"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('audit-logs.index', ['tab' => 'security']) }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-decoration-none hover-lift transition-all">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary small fw-medium d-block mb-1">Güvenlik Olayları (7 Gün)</span>
                        <h4 class="fw-bold text-dark mb-0" id="metricSecurity">{{ number_format($metrics['security'], 0, ',', '.') }}</h4>
                    </div>
                    <div class="rounded-3 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                        <i class="ti ti-shield-lock fs-3"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Tabs & Filters Card --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        {{-- Navigation Tabs --}}
        <div class="border-bottom bg-light bg-opacity-50 px-3 pt-2">
            <ul class="nav nav-pills gap-2 border-0" id="auditTabs">
                <li class="nav-item">
                    <a href="{{ route('audit-logs.index', array_merge(request()->except('page'), ['tab' => 'all'])) }}"
                        class="nav-link py-2 px-3 rounded-pill fw-semibold small {{ $activeTab === 'all' ? 'active shadow-xs' : 'text-secondary hover-bg-light' }}">
                        <i class="ti ti-layout-grid me-1"></i> Tüm Hareketler
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('audit-logs.index', array_merge(request()->except('page'), ['tab' => 'models'])) }}"
                        class="nav-link py-2 px-3 rounded-pill fw-semibold small {{ $activeTab === 'models' ? 'active shadow-xs' : 'text-secondary hover-bg-light' }}">
                        <i class="ti ti-database me-1"></i> Model Değişiklikleri (CRUD)
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('audit-logs.index', array_merge(request()->except('page'), ['tab' => 'errors'])) }}"
                        class="nav-link py-2 px-3 rounded-pill fw-semibold small {{ $activeTab === 'errors' ? 'active shadow-xs bg-danger text-white' : 'text-secondary hover-bg-light' }}">
                        <i class="ti ti-alert-triangle me-1"></i> Sistem Hataları & İstisnalar
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('audit-logs.index', array_merge(request()->except('page'), ['tab' => 'security'])) }}"
                        class="nav-link py-2 px-3 rounded-pill fw-semibold small {{ $activeTab === 'security' ? 'active shadow-xs' : 'text-secondary hover-bg-light' }}">
                        <i class="ti ti-shield-lock me-1"></i> Güvenlik & Girişler
                    </a>
                </li>
            </ul>
        </div>

        {{-- Filter Toolbar --}}
        <div class="card-body p-3 bg-white">
            <form method="GET" action="{{ route('audit-logs.index') }}" class="row g-2 align-items-end">
                <input type="hidden" name="tab" value="{{ $activeTab }}">

                <div class="col-12 col-md-3">
                    <label class="form-label small text-secondary fw-semibold mb-1">Arama</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted rounded-start-pill py-2">
                            <i class="ti ti-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control border-start-0 rounded-end-pill py-2 text-dark"
                            placeholder="İşlem, model, IP veya açıklama ara...">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-secondary fw-semibold mb-1">Kullanıcı</label>
                    <select name="user_id" class="form-select rounded-pill py-2 text-dark">
                        <option value="">Tüm Kullanıcılar</option>
                        @foreach($filterUsers as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-secondary fw-semibold mb-1">Durum</label>
                    <select name="status" class="form-select rounded-pill py-2 text-dark">
                        <option value="">Tüm Durumlar</option>
                        <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Başarılı</option>
                        <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Hata / Exception</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Başarısız / Uyarı</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-secondary fw-semibold mb-1">Başlangıç Tarihi</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control rounded-pill py-2 text-dark">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-secondary fw-semibold mb-1">Bitiş Tarihi</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control rounded-pill py-2 text-dark">
                </div>

                <div class="col-12 col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 d-flex align-items-center justify-content-center fw-medium shadow-xs" title="Filtrele">
                        <i class="ti ti-filter"></i>
                    </button>
                    @if(request()->anyFilled(['search', 'user_id', 'status', 'start_date', 'end_date']))
                        <a href="{{ route('audit-logs.index', ['tab' => $activeTab]) }}" class="btn btn-light rounded-pill py-2 px-3 border text-secondary flex-shrink-0" title="Filtreleri Temizle">
                            <i class="ti ti-rotate"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="card-body p-0 border-top">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-dark">
                    <thead class="bg-light text-secondary small">
                        <tr>
                            <th class="ps-4 py-3 border-0">Zaman</th>
                            <th class="py-3 border-0">İşlemi Yapan</th>
                            <th class="py-3 border-0">Eylem</th>
                            <th class="py-3 border-0">Modül</th>
                            <th class="py-3 border-0" style="min-width: 280px;">Açıklama / Hedef Kayıt</th>
                            <th class="py-3 border-0 text-center">Durum</th>
                            <th class="pe-4 py-3 border-0 text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody id="auditTableBody">
                        @forelse($logs as $log)
                            @php
                                $badge = $log->action_badge;
                                $isError = $log->status === 'error' || $log->status === 'failed' || $log->model_type === 'exception';
                            @endphp
                            <tr class="border-bottom border-light {{ $isError ? 'bg-danger bg-opacity-10' : '' }}">
                                {{-- Zaman --}}
                                <td class="ps-4 py-3">
                                    <div class="fw-semibold text-dark text-nowrap" style="font-size: 13.5px;">
                                        {{ $log->created_at ? $log->created_at->format('d.m.Y H:i:s') : '-' }}
                                    </div>
                                    <small class="text-secondary d-block text-nowrap" style="font-size: 11px;">
                                        {{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}
                                    </small>
                                </td>

                                {{-- İşlemi Yapan --}}
                                <td class="py-3">
                                    @if($log->user)
                                        <div class="d-flex align-items-center gap-2">
                                            @if($log->user->profile_photo_url)
                                                <img src="{{ $log->user->profile_photo_url }}" alt="" class="rounded-circle object-fit-cover flex-shrink-0" width="32" height="32">
                                            @else
                                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 12px;">
                                                    {{ mb_substr($log->user->first_name, 0, 1) }}{{ mb_substr($log->user->last_name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <div class="fw-semibold text-dark text-truncate" style="font-size: 13px;">{{ $log->user->full_name }}</div>
                                                <small class="text-secondary text-truncate d-block" style="font-size: 11px;">
                                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                                </small>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                                                <i class="ti ti-robot" style="font-size: 14px;"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="fw-semibold text-secondary" style="font-size: 13px;">Sistem / Anonim</div>
                                                <small class="text-muted d-block" style="font-size: 11px;">{{ $log->ip_address ?? '127.0.0.1' }}</small>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                {{-- Eylem --}}
                                <td class="py-3 text-nowrap">
                                    <span class="badge {{ $badge['class'] }} rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold border-0" style="font-size: 11.5px;">
                                        <i class="{{ $badge['icon'] }}"></i>
                                        <span>{{ $badge['label'] }}</span>
                                    </span>
                                </td>

                                {{-- Modül --}}
                                <td class="py-3 text-nowrap">
                                    <span class="badge bg-secondary bg-opacity-10 text-dark rounded-pill px-2.5 py-1.5 fw-medium border-0" style="font-size: 11.5px;">
                                        {{ $log->model_name_tr }}
                                    </span>
                                </td>

                                {{-- Açıklama / Hedef Kayıt --}}
                                <td class="py-3">
                                    <div class="text-wrap text-break" style="max-width: 440px; font-size: 12.5px; line-height: 1.45;">
                                        @if($log->target_url)
                                            <a href="{{ $log->target_url }}" class="text-decoration-none fw-semibold text-primary hover-underline me-1 d-inline-flex align-items-center gap-1">
                                                <span>{{ $log->model_name_tr }} #{{ $log->model_id }}</span>
                                                <i class="ti ti-external-link" style="font-size: 11px;"></i>
                                            </a>
                                        @elseif($log->model_id)
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-0.5 me-1 border-0 fw-semibold" style="font-size: 11px;">#{{ $log->model_id }}</span>
                                        @endif

                                        <span class="{{ $isError ? 'text-danger fw-semibold' : 'text-secondary' }}">
                                            {{ $log->human_description }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Durum --}}
                                <td class="py-3 text-center text-nowrap">
                                    @if($log->status === 'success')
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 fw-semibold border-0" style="font-size: 11px;">
                                            <i class="ti ti-check me-1"></i> Başarılı
                                        </span>
                                    @elseif($log->status === 'error')
                                        <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-semibold shadow-xs border-0" style="font-size: 11px;">
                                            <i class="ti ti-alert-triangle me-1"></i> Hata
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2.5 py-1 fw-semibold border-0" style="font-size: 11px;">
                                            <i class="ti ti-alert-circle me-1"></i> {{ ucfirst($log->status) }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Detay Butonu --}}
                                <td class="pe-4 py-3 text-end text-nowrap">
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium btn-audit-detail"
                                        data-url="{{ route('audit-logs.show', $log, false) }}"
                                        data-id="{{ $log->id }}">
                                        <i class="ti ti-eye me-1"></i> İncele
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <x-empty-state 
                                colspan="7" 
                                title="İşlem kaydı bulunamadı" 
                                description="Seçili filtrelere uygun herhangi bir sistem hareket veya hata kaydı bulunamadı." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
            <div class="card-footer bg-white border-top border-light-subtle py-3 px-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Visual Diff & Exception Detail Modal --}}
<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-labelledby="auditDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4 bg-light bg-opacity-75">
                <div class="d-flex align-items-center gap-2">
                    <div id="modalBadgeIconContainer" class="rounded-circle d-flex align-items-center justify-content-center p-2 bg-primary-subtle text-primary">
                        <i class="ti ti-history fs-5" id="modalBadgeIcon"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="auditDetailModalLabel">İşlem Günlüğü Detayı</h6>
                        <small class="text-secondary" id="modalSubtitle">Yükleniyor...</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>

            <div class="modal-body p-4" id="modalBodyContent">
                {{-- Dynamic AJAX content will render here --}}
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Yükleniyor...</span>
                    </div>
                    <div class="text-secondary small mt-2">Detaylar hazırlanıyor...</div>
                </div>
            </div>

            <div class="modal-footer border-top py-2.5 px-4 bg-light bg-opacity-50 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="btnCopyJson">
                    <i class="ti ti-copy me-1"></i> Ham JSON'ı Kopyala
                </button>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

{{-- Archive Filter Modal ("Eski Kayıtları İncele") --}}
<div class="modal fade" id="archiveFilterModal" tabindex="-1" aria-labelledby="archiveFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4 bg-light bg-opacity-75">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center p-2 bg-primary-subtle text-primary">
                        <i class="ti ti-archive fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="archiveFilterModalLabel">Eski Kayıtları & Arşivi İncele</h6>
                        <small class="text-secondary">Geçmişe dönük sistem ve işlem hareketlerini filtreleyin</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form method="GET" action="{{ route('audit-logs.index') }}" id="archiveFilterForm">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <input type="hidden" name="all_time" id="archiveAllTimeInput" value="0">
                <div class="modal-body p-4">
                    {{-- Hızlı Aralık Seçimleri --}}
                    <label class="form-label small fw-bold text-secondary text-uppercase mb-2">Hızlı Aralık Seçin</label>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 btn-quick-range" data-days="60">
                            Son 60 Gün (Varsayılan)
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 btn-quick-range" data-days="180">
                            Son 6 Ay
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 btn-quick-range" data-days="365">
                            Son 1 Yıl
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 btn-quick-range" data-all="1">
                            Tüm Zamanlar (Arşiv)
                        </button>
                    </div>

                    <div class="border-top pt-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-2">Veya Özel Tarih Aralığı Belirleyin</label>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label small text-secondary fw-semibold mb-1">Başlangıç Tarihi</label>
                                <input type="date" name="start_date" id="archiveStartDate" value="{{ request('start_date') }}" class="form-control rounded-pill py-2 text-dark">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-secondary fw-semibold mb-1">Bitiş Tarihi</label>
                                <input type="date" name="end_date" id="archiveEndDate" value="{{ request('end_date') }}" class="form-control rounded-pill py-2 text-dark">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 bg-light bg-opacity-50 d-flex justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-3 border" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-medium shadow-xs">
                        <i class="ti ti-filter me-1"></i> Kayıtları Getir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ $cspNonce }}">
document.addEventListener('DOMContentLoaded', function () {
    const detailModalEl = document.getElementById('auditDetailModal');
    if (!detailModalEl) return;

    const modal = new bootstrap.Modal(detailModalEl);
    const modalBody = document.getElementById('modalBodyContent');
    const modalSubtitle = document.getElementById('modalSubtitle');
    const btnCopyJson = document.getElementById('btnCopyJson');
    const tableBody = document.getElementById('auditTableBody');
    const activeTab = '{{ $activeTab }}';
    const isFirstPage = {{ request('page', 1) == 1 ? 'true' : 'false' }};
    const hasSearchFilter = {{ request()->filled('search') ? 'true' : 'false' }};
    let currentLogData = null;

    // Helper: Detail modal event listener binder
    function bindAuditDetailButton(button) {
        button.addEventListener('click', function () {
            let url = this.getAttribute('data-url');
            if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
                try {
                    const parsed = new URL(url);
                    url = parsed.pathname + parsed.search;
                } catch (e) {}
            }
            if (!url || url === '#') {
                const id = this.getAttribute('data-id');
                if (id) {
                    url = `/audit-logs/${id}`;
                }
            }

            modal.show();

            modalSubtitle.textContent = 'Veriler alınıyor...';
            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="text-secondary small mt-2">Detaylar yükleniyor...</div>
                </div>
            `;

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('HTTP ' + res.status);
                }
                return res.json();
            })
            .then(data => {
                currentLogData = data;
                renderModalContent(data);
            })
            .catch(err => {
                console.error('Audit detail load error:', err);
                modalBody.innerHTML = `
                    <div class="alert alert-danger rounded-3 p-3">
                        <div class="fw-bold"><i class="ti ti-alert-circle me-1"></i> Detaylar yüklenemedi</div>
                        <div class="small text-danger-emphasis">Bir bağlantı hatası oluştu. Lütfen tekrar deneyiniz.</div>
                    </div>
                `;
            });
        });
    }

    // Bind existing buttons
    document.querySelectorAll('.btn-audit-detail').forEach(bindAuditDetailButton);

    // Archive Modal quick range buttons
    const archiveForm = document.getElementById('archiveFilterForm');
    const archiveStartDate = document.getElementById('archiveStartDate');
    const archiveEndDate = document.getElementById('archiveEndDate');
    const archiveAllTimeInput = document.getElementById('archiveAllTimeInput');

    document.querySelectorAll('.btn-quick-range').forEach(btn => {
        btn.addEventListener('click', function () {
            const days = this.getAttribute('data-days');
            const all = this.getAttribute('data-all');

            if (all === '1') {
                archiveAllTimeInput.value = '1';
                archiveStartDate.value = '';
                archiveEndDate.value = '';
            } else if (days) {
                archiveAllTimeInput.value = '0';
                const today = new Date();
                const pastDate = new Date();
                pastDate.setDate(today.getDate() - parseInt(days));

                archiveEndDate.value = today.toISOString().split('T')[0];
                archiveStartDate.value = pastDate.toISOString().split('T')[0];
            }

            // Highlight selected button
            document.querySelectorAll('.btn-quick-range').forEach(b => b.classList.replace('btn-primary', 'btn-outline-secondary'));
            this.classList.replace('btn-outline-secondary', 'btn-primary');
        });
    });

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        if (typeof str !== 'string') str = String(str);
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('tr-TR').format(num);
    }

    function parseFormattedNumber(str) {
        return parseInt(str.replace(/\./g, ''), 10) || 0;
    }

    function incrementCounter(elementId) {
        const el = document.getElementById(elementId);
        if (el) {
            const current = parseFormattedNumber(el.textContent);
            el.textContent = formatNumber(current + 1);
        }
    }

    function formatDiffValue(val) {
        if (val === undefined || val === null || val === '') {
            return '<span class="text-muted fst-italic opacity-75">boş (null)</span>';
        }
        if (typeof val === 'boolean') {
            return val ? '<span class="badge bg-success-subtle text-success border-0">true</span>' : '<span class="badge bg-secondary-subtle text-secondary border-0">false</span>';
        }
        if (typeof val === 'object') {
            return `<pre class="mb-0 font-monospace p-1 rounded bg-white bg-opacity-75 text-dark" style="font-size: 11px;">${escapeHtml(JSON.stringify(val, null, 2))}</pre>`;
        }
        return `<span class="fw-medium">${escapeHtml(String(val))}</span>`;
    }

    function renderModalContent(log) {
        modalSubtitle.textContent = `Log #${log.id} • ${log.created_at} (${log.created_at_human})`;

        // Update modal header badge icon & color dynamically
        const badgeContainer = document.getElementById('modalBadgeIconContainer');
        const badgeIcon = document.getElementById('modalBadgeIcon');
        if (badgeContainer && badgeIcon && log.action_badge) {
            badgeContainer.className = 'rounded-circle d-flex align-items-center justify-content-center p-2 ' + (log.action_badge.class || 'bg-primary-subtle text-primary');
            badgeIcon.className = (log.action_badge.icon || 'ti ti-history') + ' fs-5';
        }

        let html = '';

        // 1. Meta Bilgileri Şeridi
        html += `
            <div class="card border rounded-3 p-3 bg-light bg-opacity-50 mb-3 shadow-none">
                <div class="row g-2.5 small">
                    <div class="col-sm-6">
                        <span class="text-secondary">İşlemi Yapan:</span>
                        <strong class="text-dark ms-1">${log.user ? escapeHtml(log.user.name) + ' (' + escapeHtml(log.user.email) + ')' : 'Sistem / Anonim'}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-secondary">IP Adresi:</span>
                        <code class="text-dark bg-white px-2 py-0.5 rounded border ms-1">${escapeHtml(log.ip_address)}</code>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-secondary">Eylem:</span>
                        <span class="badge ${log.action_badge ? log.action_badge.class : 'bg-secondary-subtle text-secondary'} ms-1 border-0">${escapeHtml(log.action_label)}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-secondary">Modül:</span>
                        <strong class="text-dark ms-1">${escapeHtml(log.model_name)} ${log.model_id ? '#' + log.model_id : ''}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-secondary">Teknik Eylem Kodu:</span>
                        <code class="text-dark bg-white px-2 py-0.5 rounded border ms-1 font-monospace small">${escapeHtml(log.action)}</code>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-secondary">İşlem Özeti:</span>
                        <span class="text-dark ms-1 fw-medium">${escapeHtml(log.human_description || log.action_label)}</span>
                    </div>
                </div>
            </div>
        `;

        // 2. Eğer Hata / Exception ise Özel Görünüm
        if (log.is_exception && log.exception_details) {
            const ex = log.exception_details;
            html += `
                <div class="alert alert-danger rounded-3 p-3 mb-3 border-danger-subtle">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-bold fs-6 text-danger d-flex align-items-center gap-1.5">
                            <i class="ti ti-alert-triangle fs-5"></i>
                            ${escapeHtml(ex.class)}
                        </div>
                        <span class="badge bg-danger text-white rounded-pill px-2.5 py-1">HTTP ${escapeHtml(String(ex.status_code))}</span>
                    </div>
                    <div class="text-dark fw-semibold mb-2" style="font-size: 13.5px;">${escapeHtml(ex.message)}</div>
                    <div class="small font-monospace text-secondary">
                        <i class="ti ti-file-code me-1"></i> ${escapeHtml(ex.file)} : <strong>Satır ${escapeHtml(String(ex.line))}</strong>
                    </div>
                </div>
            `;

            if (ex.trace && ex.trace.length > 0) {
                html += `
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between">
                            <span><i class="ti ti-code me-1"></i> Stack Trace (İlk ${ex.trace.length} Adım)</span>
                        </label>
                        <pre class="bg-dark text-light p-3 rounded-3 small font-monospace overflow-auto" style="max-height: 240px; font-size: 11px; line-height: 1.45;">${escapeHtml(ex.trace.join("\n"))}</pre>
                    </div>
                `;
            }
        }

        // 3. Model Değişimleri (Diff - Eski Değer vs Yeni Değer)
        const oldVal = log.old_values;
        const newVal = log.new_values ? (log.new_values.data || log.new_values) : null;

        if (oldVal && newVal && typeof oldVal === 'object' && typeof newVal === 'object') {
            html += `
                <div class="mb-3">
                    <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-1">
                        <i class="ti ti-git-compare text-primary"></i> Alan Değişiklikleri (Diff)
                    </h6>
                    <div class="table-responsive border rounded-3 overflow-hidden">
                        <table class="table table-bordered mb-0 small">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th style="width: 25%;">Alan Adı</th>
                                    <th style="width: 37.5%;" class="bg-danger-subtle text-danger">Eski Değer</th>
                                    <th style="width: 37.5%;" class="bg-success-subtle text-success">Yeni Değer</th>
                                </tr>
                            </thead>
                            <tbody>
            `;

            const allKeys = Array.from(new Set([...Object.keys(oldVal), ...Object.keys(newVal)]));
            allKeys.forEach(k => {
                if (k === 'updated_at' || k === 'created_at' || k === 'correlation_id' || k === 'status' || k === 'details') return;
                const o = oldVal[k];
                const n = newVal[k];

                html += `
                    <tr>
                        <td class="fw-semibold text-dark font-monospace">${escapeHtml(k)}</td>
                        <td class="bg-danger bg-opacity-10 text-danger text-break">${formatDiffValue(o)}</td>
                        <td class="bg-success bg-opacity-10 text-success text-break">${formatDiffValue(n)}</td>
                    </tr>
                `;
            });

            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        } else if (newVal && typeof newVal === 'object') {
            html += `
                <div class="mb-3">
                    <h6 class="fw-bold text-dark mb-2"><i class="ti ti-file-text me-1 text-primary"></i> İşlem Verisi (Payload)</h6>
                    <pre class="bg-light border p-3 rounded-3 small font-monospace overflow-auto" style="max-height: 200px; font-size: 11.5px;">${escapeHtml(JSON.stringify(newVal, null, 2))}</pre>
                </div>
            `;
        }

        // 4. Tarayıcı & İstemci Bilgisi
        html += `
            <div class="border-top pt-2 mt-2">
                <small class="text-muted d-block text-truncate" title="${escapeHtml(log.user_agent)}">
                    <i class="ti ti-device-laptop me-1"></i> İstemci: ${escapeHtml(log.user_agent)}
                </small>
            </div>
        `;

        modalBody.innerHTML = html;
    }

    // JSON Kopyalama Butonu
    btnCopyJson.addEventListener('click', function () {
        if (!currentLogData) return;
        const jsonStr = JSON.stringify(currentLogData, null, 2);
        navigator.clipboard.writeText(jsonStr).then(() => {
            const originalHtml = btnCopyJson.innerHTML;
            btnCopyJson.innerHTML = '<i class="ti ti-check text-success me-1"></i> Kopyalandı!';
            btnCopyJson.classList.replace('btn-outline-secondary', 'btn-success');
            btnCopyJson.classList.add('text-white');
            setTimeout(() => {
                btnCopyJson.innerHTML = originalHtml;
                btnCopyJson.classList.replace('btn-success', 'btn-outline-secondary');
                btnCopyJson.classList.remove('text-white');
            }, 2000);
        });
    });

    // ==========================================
    // Laravel Reverb WebSocket Canlı Akış
    // ==========================================
    function initReverbLiveStream() {
        if (!window.Echo || initReverbLiveStream.initialized) return;
        initReverbLiveStream.initialized = true;

        console.log('[AuditLogs Live] Echo private kanala (audit-logs) abone olunuyor...');

        // Listen on private audit-logs channel silently in the background
        window.Echo.private('audit-logs')
            .listen('.audit.created', function (log) {
                console.log('[AuditLogs Live] Canlı log alındı:', log);
                handleIncomingLiveLog(log);
            })
            .error(function (err) {
                console.warn('[AuditLogs Live] Kanal yetkilendirme veya bağlantı uyarısı:', err);
            });
    }

    function handleIncomingLiveLog(log) {
        if (!log || typeof log !== 'object') return;

        // 1. Canlı Sayaç Güncellemeleri
        incrementCounter('metricTotal');
        incrementCounter('metricToday');
        if (log.is_error) {
            incrementCounter('metricErrors');
        }
        if (log.tab === 'security') {
            incrementCounter('metricSecurity');
        }

        // 2. Sekme filtre kontrolü: Mevcut sekmeye uygun mu?
        if (activeTab !== 'all' && activeTab !== log.tab) {
            return;
        }

        // 3. Yalnızca ilk sayfada ve arama filtresi yokken doğrudan tabloya ekle
        const tbody = tableBody || document.getElementById('auditTableBody');
        if (!isFirstPage || hasSearchFilter || !tbody) {
            return;
        }

        // 4. Boş durum satırı varsa kaldır
        const emptyRow = tbody.querySelector('td[colspan]');
        if (emptyRow) {
            emptyRow.closest('tr')?.remove();
        }

        // 5. Yeni satırı DOM öğesi olarak oluştur
        const tr = document.createElement('tr');
        tr.className = `border-bottom border-light live-row-enter ${log.is_error ? 'bg-danger bg-opacity-10' : ''}`;

        // Zaman Hücresi
        const tdTime = document.createElement('td');
        tdTime.className = 'ps-4 py-3';
        tdTime.innerHTML = `
            <div class="fw-semibold text-dark text-nowrap" style="font-size: 13.5px;">${escapeHtml(log.created_at)}</div>
            <small class="text-secondary d-block text-nowrap" style="font-size: 11px;">${escapeHtml(log.created_at_human)}</small>
        `;

        // İşlemi Yapan Hücresi
        const tdUser = document.createElement('td');
        tdUser.className = 'py-3';
        if (log.user) {
            const avatarHtml = log.user.photo_url 
                ? `<img src="${escapeHtml(log.user.photo_url)}" alt="" class="rounded-circle object-fit-cover flex-shrink-0" width="32" height="32">`
                : `<div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 12px;">${escapeHtml(log.user.initials || 'U')}</div>`;

            tdUser.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    ${avatarHtml}
                    <div class="min-w-0">
                        <div class="fw-semibold text-dark text-truncate" style="font-size: 13px;">${escapeHtml(log.user.name)}</div>
                        <small class="text-secondary text-truncate d-block" style="font-size: 11px;">${escapeHtml(log.ip_address)}</small>
                    </div>
                </div>
            `;
        } else {
            tdUser.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                        <i class="ti ti-robot" style="font-size: 14px;"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="fw-semibold text-secondary" style="font-size: 13px;">Sistem / Anonim</div>
                        <small class="text-muted d-block" style="font-size: 11px;">${escapeHtml(log.ip_address)}</small>
                    </div>
                </div>
            `;
        }

        // Eylem Hücresi
        const tdAction = document.createElement('td');
        tdAction.className = 'py-3 text-nowrap';
        tdAction.innerHTML = `
            <span class="badge ${escapeHtml(log.action_badge.class)} rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold border-0" style="font-size: 11.5px;">
                <i class="${escapeHtml(log.action_badge.icon)}"></i>
                <span>${escapeHtml(log.action_badge.label)}</span>
            </span>
        `;

        // Modül Hücresi
        const tdModel = document.createElement('td');
        tdModel.className = 'py-3 text-nowrap';
        tdModel.innerHTML = `
            <span class="badge bg-secondary bg-opacity-10 text-dark rounded-pill px-2.5 py-1.5 fw-medium border-0" style="font-size: 11.5px;">
                ${escapeHtml(log.model_name)}
            </span>
        `;

        // Açıklama / Hedef Kayıt Hücresi
        const tdDesc = document.createElement('td');
        tdDesc.className = 'py-3';
        let targetHtml = '';
        if (log.target_url) {
            targetHtml = `
                <a href="${escapeHtml(log.target_url)}" class="text-decoration-none fw-semibold text-primary hover-underline me-1 d-inline-flex align-items-center gap-1">
                    <span>${escapeHtml(log.model_name)} #${escapeHtml(String(log.model_id))}</span>
                    <i class="ti ti-external-link" style="font-size: 11px;"></i>
                </a>
            `;
        } else if (log.model_id) {
            targetHtml = `<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-0.5 me-1 border-0 fw-semibold" style="font-size: 11px;">#${escapeHtml(String(log.model_id))}</span>`;
        }

        tdDesc.innerHTML = `
            <div class="text-wrap text-break" style="max-width: 440px; font-size: 12.5px; line-height: 1.45;">
                ${targetHtml}
                <span class="${log.is_error ? 'text-danger fw-semibold' : 'text-secondary'}">
                    ${escapeHtml(log.human_description)}
                </span>
            </div>
        `;

        // Durum Hücresi
        const tdStatus = document.createElement('td');
        tdStatus.className = 'py-3 text-center text-nowrap';
        if (log.status === 'success') {
            tdStatus.innerHTML = `
                <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 fw-semibold border-0" style="font-size: 11px;">
                    <i class="ti ti-check me-1"></i> Başarılı
                </span>
            `;
        } else if (log.status === 'error') {
            tdStatus.innerHTML = `
                <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-semibold shadow-xs border-0" style="font-size: 11px;">
                    <i class="ti ti-alert-triangle me-1"></i> Hata
                </span>
            `;
        } else {
            tdStatus.innerHTML = `
                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2.5 py-1 fw-semibold border-0" style="font-size: 11px;">
                    <i class="ti ti-alert-circle me-1"></i> ${escapeHtml(log.status)}
                </span>
            `;
        }

        // Detay Butonu Hücresi
        const tdBtn = document.createElement('td');
        tdBtn.className = 'pe-4 py-3 text-end text-nowrap';
        const detailBtn = document.createElement('button');
        detailBtn.type = 'button';
        detailBtn.className = 'btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium btn-audit-detail';
        let detailUrl = log.show_url;
        if (detailUrl && (detailUrl.startsWith('http://') || detailUrl.startsWith('https://'))) {
            try {
                const parsed = new URL(detailUrl);
                detailUrl = parsed.pathname + parsed.search;
            } catch (e) {}
        }
        if (!detailUrl || detailUrl === '#') {
            detailUrl = `/audit-logs/${log.id}`;
        }
        detailBtn.setAttribute('data-url', detailUrl);
        detailBtn.setAttribute('data-id', log.id);
        detailBtn.innerHTML = '<i class="ti ti-eye me-1"></i> İncele';
        bindAuditDetailButton(detailBtn);
        tdBtn.appendChild(detailBtn);

        // Birleştir ve En Başa Ekle
        tr.appendChild(tdTime);
        tr.appendChild(tdUser);
        tr.appendChild(tdAction);
        tr.appendChild(tdModel);
        tr.appendChild(tdDesc);
        tr.appendChild(tdStatus);
        tr.appendChild(tdBtn);

        tbody.prepend(tr);

        // Sayfada 25'ten fazla satır oluşursa sonuncuyu temizle
        if (tbody.children.length > 25) {
            tbody.lastElementChild?.remove();
        }
    }

    // Echo hazır olduğunda başlat
    if (window.Echo) {
        initReverbLiveStream();
    } else {
        document.addEventListener('echo:ready', initReverbLiveStream, { once: true });
    }
});
</script>
@endpush
@endsection

