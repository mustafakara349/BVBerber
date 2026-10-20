@extends('layouts.app')
@section('title', 'İzin Yönetimi - B&V Barber')

@section('content')
<x-page-header title="İzin Yönetimi" description="Personellerin acil işlerini, saatlik bloklarını veya tüm gün izinlerini buradan yönetebilirsiniz.">
    <x-btn-outline icon="ti ti-bolt" data-bs-toggle="modal" data-bs-target="#quickActionModal">
        Hızlı İşlemler
    </x-btn-outline>
    <x-btn-primary icon="ti ti-plus" data-bs-toggle="modal" data-bs-target="#timeBlockModal">
        Yeni İzin Ekle
    </x-btn-primary>
</x-page-header>

{{-- Filters --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <form action="{{ route('employee-time-blocks.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <x-form-select name="employee_id" label="Personel" :pill="true">
                        <option value="">Tüm Personeller</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->user->full_name }}</option>
                        @endforeach
                    </x-form-select>
                </div>

                <div class="col-12 col-md-3">
                    <x-form-select name="type" label="İzin Türü" :pill="true">
                        <option value="">Tüm Türler</option>
                        <option value="partial_time" {{ request('type') === 'partial_time' ? 'selected' : '' }}>Saatlik Kısıtlama</option>
                        <option value="full_day" {{ request('type') === 'full_day' ? 'selected' : '' }}>Tüm Gün İzin</option>
                    </x-form-select>
                </div>

                <div class="col-6 col-md-2">
                    <x-date-input name="date_from" label="Başlangıç" :value="request('date_from')" :pill="true" />
                </div>

                <div class="col-6 col-md-2">
                    <x-date-input name="date_to" label="Bitiş" :value="request('date_to')" :pill="true" />
                </div>

                <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 shadow-sm w-100 d-inline-flex align-items-center justify-content-center gap-1 fw-medium" style="height: 38px;">
                        <i class="ti ti-filter"></i> Filtrele
                    </button>
                    @if(request()->anyFilled(['employee_id', 'type', 'date_from', 'date_to']))
                        <a href="{{ route('employee-time-blocks.index') }}" class="btn btn-light rounded-pill px-2 shadow-sm d-inline-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="height: 38px; width: 38px;" title="Filtreleri Temizle">
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
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3">Tarih</th>
                                    <th class="py-3">Çalışan</th>
                                    <th class="py-3">Tür</th>
                                    <th class="py-3">Saat Aralığı</th>
                                    <th class="py-3">Açıklama</th>
                                    <th class="text-end pe-4 py-3">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($timeBlocks as $block)
                                    <tr>
                                        <td class="ps-4 fw-bold text-nowrap">
                                            @php
                                                $startDateStr = \Carbon\Carbon::parse($block->date)->format('Y-m-d');
                                                $endDateStr = \Carbon\Carbon::parse($block->end_date_for_ui)->format('Y-m-d');
                                            @endphp
                                            @if($startDateStr != $endDateStr)
                                                {{ \Carbon\Carbon::parse($startDateStr)->format('d.m.Y') }}
                                                <span class="text-muted mx-1">-</span>
                                                {{ \Carbon\Carbon::parse($endDateStr)->format('d.m.Y') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($startDateStr)->format('d.m.Y') }}
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($block->employee->user->profile_photo)
                                                    <img src="{{ asset('storage/' . $block->employee->user->profile_photo) }}"
                                                        class="rounded-circle me-3 object-fit-cover shadow-sm" width="40" height="40" alt="">
                                                @else
                                                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center me-3 fw-bold shadow-sm"
                                                        style="width: 40px; height: 40px; font-size: 1rem;">
                                                        {{ mb_substr($block->employee->user->first_name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <span class="fw-semibold text-dark">{{ $block->employee->user->full_name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($block->type == 'full_day')
                                                <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill"><i class="ti ti-sun me-1"></i> Tüm Gün</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill"><i class="ti ti-clock me-1"></i> Saatlik</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($block->type == 'partial_time')
                                                <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($block->start_time)->format('H:i') }}</span>
                                                <span class="text-muted mx-1">-</span>
                                                <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($block->end_time)->format('H:i') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-muted">{{ $block->reason ?? '-' }}</td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <!-- Düzenle Butonu -->
                                                <button type="button" class="btn btn-sm btn-light border-0 text-primary rounded-circle shadow-sm" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"
                                                    title="Düzenle" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal{{ $block->id }}">
                                                    <i class="ti ti-pencil fs-5"></i>
                                                </button>

                                                <!-- Sil Butonu -->
                                                <form action="{{ route('employee-time-blocks.destroy', $block->id) }}" method="POST"
                                                    onsubmit="return confirm('Bu kaydı (varsa bağlı olduğu tüm seri günleri) silmek istediğinize emin misiniz?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="grouped_ids" value="{{ implode(',', $block->grouped_ids) }}">
                                                    <button type="submit"
                                                        class="btn btn-sm btn-light border-0 text-danger rounded-circle shadow-sm" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"
                                                        title="Kaldır"><i class="ti ti-trash fs-5"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                <x-empty-state icon="ti ti-calendar-off" title="Henüz izin eklenmemiş" message="Personeller için eklenen kısıtlamalar burada listelenecektir." colspan="6" />
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals in a separate loop outside the table -->
    @foreach($timeBlocks as $block)
        <!-- Edit Modal for block ID {{ $block->id }} -->
        <div class="modal fade" id="editModal{{ $block->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $block->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form action="{{ route('employee-time-blocks.update', $block->id) }}" method="POST" class="modal-content border-0 rounded-4 shadow-lg overflow-hidden ajax-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="grouped_ids" value="{{ implode(',', $block->grouped_ids) }}">
                    <div class="modal-header border-0 bg-primary text-white py-3 px-4">
                        <h5 class="modal-title fw-bold" id="editModalLabel{{ $block->id }}"><i class="ti ti-edit me-2"></i>İzni Düzenle</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
                    </div>
                    <!-- Error Alert -->
                    <div class="alert alert-danger d-none m-4 mb-0 modal-error-alert shadow-sm border-0" role="alert"></div>
                    
                    <div class="modal-body p-4 p-md-5 bg-white text-start">
                        
                        <!-- Grid layout for better organization -->
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">Personel Seçin <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-select bg-light border-0 shadow-none p-3" required>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ $block->employee_id == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->user->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Başlangıç Tarihi <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control bg-light border-0 shadow-none p-3" required
                                    value="{{ \Carbon\Carbon::parse($block->date)->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Bitiş Tarihi <span class="text-muted fw-normal" style="font-size: 0.85em;">(Birden fazla gün ise)</span></label>
                                @php
                                    $mStartDate = \Carbon\Carbon::parse($block->date)->format('Y-m-d');
                                    $mEndDate = \Carbon\Carbon::parse($block->end_date_for_ui)->format('Y-m-d');
                                @endphp
                                <input type="date" name="end_date" class="form-control bg-light border-0 shadow-none p-3"
                                    value="{{ $mStartDate != $mEndDate ? $mEndDate : '' }}">
                            </div>

                            <div class="col-12">
                                <div class="bg-light p-4 rounded-4 border border-light">
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold text-dark">Kısıtlama Türü <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select bg-white border-0 shadow-sm p-3 edit_block_type" required data-target-row="edit_time_range_row_{{ $block->id }}" data-target-start="edit_block_start_{{ $block->id }}" data-target-end="edit_block_end_{{ $block->id }}">
                                            <option value="partial_time" {{ $block->type == 'partial_time' ? 'selected' : '' }}>Saatlik Kısıtlama (Örn: 13:00-15:00 arası kapalı)</option>
                                            <option value="full_day" {{ $block->type == 'full_day' ? 'selected' : '' }}>Tüm Gün (O gün hiç randevu alınamaz)</option>
                                        </select>
                                    </div>
                                    <div class="row g-3 {{ $block->type == 'full_day' ? 'd-none' : '' }}" id="edit_time_range_row_{{ $block->id }}">
                                        <div class="col-6">
                                            <label class="form-label fw-semibold text-dark"><i class="ti ti-clock-play me-1 text-primary"></i>Başlangıç Saati</label>
                                            <input type="time" name="start_time" id="edit_block_start_{{ $block->id }}" class="form-control bg-white border-0 shadow-sm p-3"
                                                value="{{ $block->start_time ? \Carbon\Carbon::parse($block->start_time)->format('H:i') : '' }}" {{ $block->type == 'partial_time' ? 'required' : '' }}>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-semibold text-dark"><i class="ti ti-clock-stop me-1 text-danger"></i>Bitiş Saati</label>
                                            <input type="time" name="end_time" id="edit_block_end_{{ $block->id }}" class="form-control bg-white border-0 shadow-sm p-3"
                                                value="{{ $block->end_time ? \Carbon\Carbon::parse($block->end_time)->format('H:i') : '' }}" {{ $block->type == 'partial_time' ? 'required' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">Neden / Açıklama (İsteğe Bağlı)</label>
                                <textarea name="reason" class="form-control bg-light border-0 shadow-none p-3" rows="2"
                                    placeholder="Örn: Acil banka işi, hastane ziyareti vb.">{{ $block->reason }}</textarea>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-0 p-4 pt-0 px-md-5 bg-white">
                        <button type="button" class="btn btn-light rounded-pill px-4 py-2 text-dark fw-bold" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 shadow-sm fw-bold"><i class="ti ti-check me-2"></i>Değişiklikleri Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <!-- Modal for adding Time Block -->
    <div class="modal fade" id="timeBlockModal" tabindex="-1" aria-labelledby="timeBlockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ route('employee-time-blocks.store') }}" method="POST"
                class="modal-content border-0 rounded-4 shadow-lg overflow-hidden ajax-form">
                @csrf
                <div class="modal-header border-0 bg-danger text-white py-3 px-4">
                    <h5 class="modal-title fw-bold" id="timeBlockModalLabel"><i class="ti ti-calendar-plus me-2"></i>Yeni
                        İzin / Kısıtlama Ekle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Kapat"></button>
                </div>
                <!-- Error Alert -->
                <div class="alert alert-danger d-none m-4 mb-0 modal-error-alert shadow-sm border-0" role="alert"></div>

                <div class="modal-body p-4 p-md-5 bg-white text-start">
                    
                    <!-- Kapsamlı ve Profesyonel Grid -->
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Personel Seçin <span
                                    class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select bg-light border-0 shadow-none p-3" required>
                                <option value="">Lütfen listeden seçin...</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->user->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Başlangıç Tarihi <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="start_date_input" class="form-control bg-light border-0 shadow-none p-3" required
                                min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Bitiş Tarihi <span class="text-muted fw-normal" style="font-size: 0.85em;">(Birden fazla gün ise)</span></label>
                            <input type="date" name="end_date" id="end_date_input" class="form-control bg-light border-0 shadow-none p-3"
                                min="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-12">
                            <div class="bg-light p-4 rounded-4 border border-light">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-dark">Kısıtlama Türü <span
                                            class="text-danger">*</span></label>
                                    <select name="type" id="block_type" class="form-select bg-white border-0 shadow-sm p-3" required>
                                        <option value="partial_time">Saatlik Kısıtlama (Belirli saatler arası randevu kapalı)</option>
                                        <option value="full_day">Tüm Gün (Seçilen tarihte tam gün randevu kapalı)</option>
                                    </select>
                                </div>
                                <div class="row g-3" id="time_range_row">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-dark"><i class="ti ti-clock-play me-1 text-primary"></i>Başlangıç Saati</label>
                                        <input type="time" name="start_time" id="block_start" class="form-control bg-white border-0 shadow-sm p-3"
                                            required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-dark"><i class="ti ti-clock-stop me-1 text-danger"></i>Bitiş Saati</label>
                                        <input type="time" name="end_time" id="block_end" class="form-control bg-white border-0 shadow-sm p-3"
                                            required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Neden / Açıklama (İsteğe Bağlı)</label>
                            <textarea name="reason" class="form-control bg-light border-0 shadow-none p-3" rows="2"
                                placeholder="Örn: Acil banka işi, hastane ziyareti vb."></textarea>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer border-0 p-4 pt-0 px-md-5 bg-white">
                    <button type="button" class="btn btn-light rounded-pill px-4 py-2 text-dark fw-bold"
                        data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-5 py-2 shadow-sm fw-bold"><i class="ti ti-check me-2"></i>Kayıt Oluştur</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Action Modal -->
    <div class="modal fade" id="quickActionModal" tabindex="-1" aria-labelledby="quickActionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('employee-time-blocks.quick-store') }}" method="POST"
                class="modal-content border-0 rounded-4 shadow-lg overflow-hidden ajax-form">
                @csrf
                <div class="modal-header border-0 bg-warning py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark" id="quickActionModalLabel"><i class="ti ti-bolt me-2"></i>Hızlı İşlemler</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <!-- Error Alert -->
                <div class="alert alert-danger d-none m-4 mb-0 modal-error-alert shadow-sm border-0" role="alert"></div>

                <div class="modal-body p-4 p-md-5 bg-white text-start">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Personel Seçin <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select bg-light border-0 shadow-none p-3" required>
                                <option value="">Lütfen listeden seçin...</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->user->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Hızlı İşlem <span class="text-danger">*</span></label>
                            <select name="quick_action" id="quick_action_select" class="form-select bg-light border-0 shadow-none p-3" required>
                                <option value="">İşlem Seçiniz...</option>
                                <option value="today_rest">Günün geri kalanı için randevuya kapat (Şu an - 23:59)</option>
                                <option value="tomorrow">Yarın için tüm gün randevuya kapat</option>
                                <option value="next_xdays">Önümüzdeki belirtilen gün sayısı kadar kapat</option>
                            </select>
                        </div>

                        <div class="col-12 d-none" id="days_count_row">
                            <label class="form-label fw-semibold text-dark">Gün Sayısı <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="input-group shadow-sm rounded-3">
                                        <span class="input-group-text bg-white border-0 px-3"><i class="ti ti-calendar-event text-muted"></i></span>
                                        <input type="number" name="days_count" id="days_count_input" class="form-control border-0 py-2 bg-light" 
                                            min="1" max="30" placeholder="Örn: 3">
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">Bugün de dahil edilerek belirtilen gün sayısı kadar izin eklenecektir.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 px-md-5 bg-white">
                    <button type="button" class="btn btn-light rounded-pill px-4 py-2 text-dark fw-bold" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-5 py-2 shadow-sm fw-bold text-dark"><i class="ti ti-bolt me-2"></i>Hızlıca Uygula</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script nonce="{{ $cspNonce }}">
        document.addEventListener('DOMContentLoaded', function () {
            // New Create Modal logic
            const blockType = document.getElementById('block_type');
            const timeRangeRow = document.getElementById('time_range_row');
            const blockStart = document.getElementById('block_start');
            const blockEnd = document.getElementById('block_end');

            if (blockType) {
                blockType.addEventListener('change', function () {
                    if (this.value === 'full_day') {
                        timeRangeRow.classList.add('d-none');
                        blockStart.removeAttribute('required');
                        blockEnd.removeAttribute('required');
                        blockStart.value = '';
                        blockEnd.value = '';
                    } else {
                        timeRangeRow.classList.remove('d-none');
                        blockStart.setAttribute('required', 'required');
                        blockEnd.setAttribute('required', 'required');
                    }
                });
            }

            // Edit Modals logic
            const editBlockTypes = document.querySelectorAll('.edit_block_type');
            editBlockTypes.forEach(function(selectElement) {
                selectElement.addEventListener('change', function() {
                    const targetRowId = this.getAttribute('data-target-row');
                    const targetStartId = this.getAttribute('data-target-start');
                    const targetEndId = this.getAttribute('data-target-end');
                    
                    const row = document.getElementById(targetRowId);
                    const startInput = document.getElementById(targetStartId);
                    const endInput = document.getElementById(targetEndId);

                    if (this.value === 'full_day') {
                        row.classList.add('d-none');
                        startInput.removeAttribute('required');
                        endInput.removeAttribute('required');
                        startInput.value = '';
                        endInput.value = '';
                    } else {
                        row.classList.remove('d-none');
                        startInput.setAttribute('required', 'required');
                        endInput.setAttribute('required', 'required');
                    }
                });
            });

            // Date logic for Create Modal
            const startDateInput = document.getElementById('start_date_input');
            const endDateInput = document.getElementById('end_date_input');
            if(startDateInput && endDateInput) {
                startDateInput.addEventListener('change', function() {
                    endDateInput.min = this.value;
                    if(endDateInput.value && endDateInput.value < this.value) {
                        endDateInput.value = this.value;
                    }
                });
            }

            // Quick Action logic
            const quickActionSelect = document.getElementById('quick_action_select');
            const daysCountRow = document.getElementById('days_count_row');
            const daysCountInput = document.getElementById('days_count_input');

            if(quickActionSelect) {
                quickActionSelect.addEventListener('change', function() {
                    if(this.value === 'next_xdays') {
                        daysCountRow.classList.remove('d-none');
                        daysCountInput.setAttribute('required', 'required');
                    } else {
                        daysCountRow.classList.add('d-none');
                        daysCountInput.removeAttribute('required');
                        daysCountInput.value = '';
                    }
                });
            }

            // AJAX Form Submit Logic
            document.querySelectorAll('.ajax-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const btn = this.querySelector('button[type="submit"]');
                    const errorAlert = this.querySelector('.modal-error-alert');
                    const originalBtnHtml = btn.innerHTML;
                    
                    errorAlert.classList.add('d-none');
                    errorAlert.innerText = '';
                    
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>İşleniyor...';

                    const formData = new FormData(this);
                    
                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            errorAlert.classList.remove('d-none');
                            errorAlert.innerText = data.message || 'Bir hata oluştu.';
                            btn.disabled = false;
                            btn.innerHTML = originalBtnHtml;
                        }
                    })
                    .catch(error => {
                        errorAlert.classList.remove('d-none');
                        errorAlert.innerText = 'Sunucuyla iletişim kurarken bir hata oluştu veya bağlantı koptu.';
                        btn.disabled = false;
                        btn.innerHTML = originalBtnHtml;
                    });
                });
            });

        });
    </script>
@endpush