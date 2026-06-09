@extends('layouts.app')

@section('title', 'Randevular - B&V Barber')

@push('styles')
<style>
    /* Custom premium styling for FullCalendar */
    #calendar {
        font-family: 'Outfit', 'Inter', sans-serif !important;
        border: none !important;
    }
    .fc {
        --fc-border-color: #f1f3f9 !important;
        --fc-page-bg-color: #ffffff !important;
        --fc-neutral-text-color: #475569 !important;
    }
    /* Time slot grid styling */
    .fc .fc-timegrid-slot {
        height: 3.5rem !important; /* Spaciously tall slots */
        border-bottom: 1px dashed #f1f5f9 !important;
    }
    .fc .fc-timegrid-slot-minor {
        border-bottom: none !important;
    }
    /* Days and header styling for Month/Week/Day view unity */
    .fc .fc-col-header-cell {
        background: #f8fafc !important;
        padding: 12px 8px !important;
        border: none !important;
        border-bottom: 2px solid #e2e8f0 !important;
    }
    .fc .fc-col-header-cell-cushion {
        color: #475569 !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        text-decoration: none !important;
    }
    .fc .fc-col-header-cell.fc-day-today {
        background: #fff8f5 !important;
    }
    .fc .fc-col-header-cell.fc-day-today .fc-col-header-cell-cushion {
        color: #e66239 !important;
    }
    /* Modern Premium Event Pill Styling */
    .fc-v-event {
        border: none !important;
        border-left: 4px solid rgba(255, 255, 255, 0.4) !important; /* Elegant semi-transparent white left border */
        padding: 6px 10px !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -1px rgba(0, 0, 0, 0.04) !important;
        transition: all 0.2s ease !important;
        margin: 2px 4px !important;
    }
    .fc-v-event:hover {
        transform: translateY(-1px) scale(1.02) !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.12), 0 4px 6px -2px rgba(0, 0, 0, 0.08) !important;
        filter: brightness(0.9) !important;
    }
    .fc-v-event .fc-event-main {
        padding: 0 !important;
        color: #ffffff !important; /* All text is white */
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        gap: 2px !important;
        height: 100%;
        overflow: hidden;
    }
    .fc-v-event .fc-event-time {
        font-size: 10px !important;
        font-weight: 700 !important;
        color: rgba(255, 255, 255, 0.9) !important;
        margin: 0 !important;
    }
    .fc-v-event .fc-event-title {
        font-size: 11.5px !important;
        font-weight: 700 !important;
        color: #ffffff !important;
        line-height: 1.2 !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .fc-v-event .fc-event-desc {
        font-size: 9.5px !important;
        font-weight: 500 !important;
        color: rgba(255, 255, 255, 0.95) !important; /* Services description is white too! */
        margin-top: 1px !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Month day-grid event styling */
    .fc-h-event {
        border: none !important;
        border-left: 3px solid rgba(255, 255, 255, 0.4) !important;
        padding: 3px 6px !important;
        border-radius: 6px !important;
        margin-bottom: 3px !important;
        color: #ffffff !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04) !important;
    }
    .fc-h-event .fc-event-main {
        color: #ffffff !important;
    }
    .fc-h-event .fc-event-title {
        font-weight: 700 !important;
        font-size: 11px !important;
    }
    
    /* Hide standard headers */
    .fc-header-toolbar {
        display: none !important;
    }
    
    /* DayGridMonth cell styling */
    .fc .fc-daygrid-day {
        background: #ffffff !important;
    }
    .fc .fc-daygrid-day-number {
        font-weight: 600 !important;
        color: #475569 !important;
        font-size: 13px !important;
        padding: 8px !important;
        text-decoration: none !important;
    }
    .fc .fc-daygrid-day.fc-day-today {
        background: #fff8f5 !important;
    }
    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        color: #e66239 !important;
        background: #ffebe3 !important;
        border-radius: 50% !important;
        width: 28px !important;
        height: 28px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 4px !important;
        padding: 0 !important;
    }

    /* Custom Header Hover Effects */
    #calPrev:hover, #calNext:hover, #calToday:hover {
        background-color: #f1f5f9 !important;
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fs-3 mb-1">Randevular</h1>
                <p class="mb-0">Tüm randevuları yönetin</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="btn-group p-1 bg-light rounded-pill border border-light" role="group">
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold border-0 btn-view-mode active bg-white text-dark shadow-sm" data-mode="list" style="font-size: 12px; transition: all 0.2s ease;">Liste</button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold border-0 btn-view-mode" data-mode="calendar" style="font-size: 12px; transition: all 0.2s ease;">Takvim</button>
                </div>
                <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Yeni Randevu
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="row mb-3" id="filtersRow">
    <div class="col-12">
        <div class="card p-3">
            <form method="GET" action="{{ route('appointments.index') }}" class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small">Ara</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Kod, isim, telefon..." value="{{ request('search') }}">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small">Durum</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small">Berber</label>
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->user->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small">Başlangıç</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small">Bitiş</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-lg-1 col-md-6">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="ti ti-filter me-1"></i> Filtrele</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="row" id="listView">
    <div class="col-12">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Kod</th>
                            <th>Müşteri</th>
                            <th>Berber</th>
                            <th>Tarih / Saat</th>
                            <th>Hizmetler</th>
                            <th>Tutar</th>
                            <th>Durum</th>
                            <th>Ödeme</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $apt)
                        <tr>
                            <td><span class="fw-semibold">{{ $apt->appointment_code }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($apt->customer?->profile_photo)
                                        <img src="{{ $apt->customer->profile_photo }}" class="avatar avatar-xs rounded-circle" alt="">
                                    @else
                                        <div class="avatar avatar-xs rounded-circle avatar-primary">
                                            <span class="avatar-initials small">{{ substr($apt->customer?->first_name ?? '?', 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="mb-0 small">{{ $apt->customer?->full_name }}</p>
                                        <small class="text-muted">{{ $apt->customer?->phone }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $apt->employee?->user?->full_name }}</td>
                            <td>
                                <div>{{ $apt->start_at->format('d.m.Y') }}</div>
                                <small class="text-muted">{{ $apt->start_at->format('H:i') }} - {{ $apt->end_at->format('H:i') }}</small>
                            </td>
                            <td>
                                @foreach($apt->appointmentServices as $as)
                                    <span class="badge bg-light text-dark border mb-1">{{ $as->service?->name }}</span>
                                @endforeach
                            </td>
                            <td class="fw-semibold">₺{{ number_format($apt->total_price, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-{{ $apt->status->color() }}-subtle text-{{ $apt->status->color() }} border border-{{ $apt->status->color() }}">
                                    {{ $apt->status->label() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $apt->payment_status->color() }}-subtle text-{{ $apt->payment_status->color() }}">
                                    {{ $apt->payment_status->label() }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group shadow-sm">
                                    <a href="{{ route('appointments.show', $apt) }}" class="btn btn-sm btn-light text-primary" title="Görüntüle">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    @if($apt->status->value != 'cancelled' && $apt->status->value != 'completed')
                                    <form action="{{ route('appointments.update-status', $apt) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu randevuyu iptal etmek istediğinize emin misiniz?');">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-sm btn-light text-danger" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" title="İptal Et">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="ti ti-calendar-off fs-1 d-block mb-2"></i>
                                Randevu bulunamadı
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($appointments->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $appointments->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Calendar View --}}
<div class="row d-none" id="calendarView">
    <div class="col-12">
        <div class="card border-0 shadow-sm p-4">
            <!-- Custom Calendar Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4">
                <!-- Left: Today and Prev/Next Navigation -->
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light border-0 rounded-circle p-2 shadow-sm d-flex align-items-center justify-content-center" id="calPrev" style="width: 32px; height: 32px; transition: all 0.2s;">
                        <i class="ti ti-chevron-left fs-5"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light border-0 rounded-circle p-2 shadow-sm d-flex align-items-center justify-content-center" id="calNext" style="width: 32px; height: 32px; transition: all 0.2s;">
                        <i class="ti ti-chevron-right fs-5"></i>
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold border-0 btn-light shadow-sm" id="calToday" style="font-size: 12px; transition: all 0.2s;">Bugün</button>
                </div>
                
                <!-- Center: Dynamic Month/Year/Date Title -->
                <h4 class="mb-0 fw-bold text-dark text-capitalize" id="calTitle" style="font-family: 'Outfit', sans-serif;"></h4>
                
                <!-- Right: Modern View Switcher Pill -->
                <div class="btn-group p-1 bg-light rounded-pill border border-light" role="group">
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold border-0 btn-cal-view active bg-white text-dark shadow-sm" data-view="timeGridWeek" style="font-size: 12px; transition: all 0.2s ease;">Hafta</button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold border-0 btn-cal-view" data-view="dayGridMonth" style="font-size: 12px; transition: all 0.2s ease;">Ay</button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold border-0 btn-cal-view" data-view="timeGridDay" style="font-size: 12px; transition: all 0.2s ease;">Gün</button>
                </div>
            </div>
            
            <!-- Calendar Element -->
            <div id="calendar"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/tr.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnList = document.querySelector('[data-mode="list"]');
        const btnCalendar = document.querySelector('[data-mode="calendar"]');
        const listView = document.getElementById('listView');
        const calendarView = document.getElementById('calendarView');
        const filtersRow = document.getElementById('filtersRow');
        
        let calendar = null;

        btnList.addEventListener('click', function() {
            btnCalendar.classList.remove('active', 'bg-white', 'text-dark', 'shadow-sm');
            btnList.classList.add('active', 'bg-white', 'text-dark', 'shadow-sm');
            calendarView.classList.add('d-none');
            listView.classList.remove('d-none');
            if (filtersRow) filtersRow.classList.remove('d-none');
        });

        btnCalendar.addEventListener('click', function() {
            btnList.classList.remove('active', 'bg-white', 'text-dark', 'shadow-sm');
            btnCalendar.classList.add('active', 'bg-white', 'text-dark', 'shadow-sm');
            listView.classList.add('d-none');
            if (filtersRow) filtersRow.classList.add('d-none');
            calendarView.classList.remove('d-none');

            if (!calendar) {
                const calendarEl = document.getElementById('calendar');
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'timeGridWeek',
                    locale: 'tr',
                    firstDay: 1, // Pazartesi
                    slotMinTime: '{{ isset($activeBranch) && $activeBranch->opening_time ? $activeBranch->opening_time->format("H:i:s") : "08:00:00" }}',
                    slotMaxTime: '{{ isset($activeBranch) && $activeBranch->closing_time ? $activeBranch->closing_time->format("H:i:s") : "22:00:00" }}',
                    allDaySlot: false,
                    headerToolbar: false, // Hide default header toolbar completely!
                    slotEventOverlap: false, // Fix overlap completely by laying events side-by-side!
                    eventMinHeight: 54, // Prevent events from collapsing into unreadable thin lines
                    events: '{{ route("appointments.events") }}',
                    eventClick: function(info) {
                        if (info.event.url) {
                            info.jsEvent.preventDefault();
                            window.location.href = info.event.url;
                        }
                    },
                    datesSet: function(info) {
                        // Dynamically update custom title
                        document.getElementById('calTitle').innerText = info.view.title;
                    },
                    eventDidMount: function(info) {
                        // Add description to time grid event view dynamically if it exists
                        const desc = info.event.extendedProps.description;
                        if (desc) {
                            const mainEl = info.el.querySelector('.fc-event-main');
                            if (mainEl && !info.el.querySelector('.fc-event-desc')) {
                                const descEl = document.createElement('div');
                                descEl.className = 'fc-event-desc';
                                descEl.innerText = desc;
                                mainEl.appendChild(descEl);
                            }
                        }
                    },
                    height: 'auto',
                    themeSystem: 'standard'
                });
                calendar.render();

                // Bind custom header navigation controls
                document.getElementById('calPrev').addEventListener('click', () => {
                    calendar.prev();
                });
                document.getElementById('calNext').addEventListener('click', () => {
                    calendar.next();
                });
                document.getElementById('calToday').addEventListener('click', () => {
                    calendar.today();
                });

                // Bind custom view switcher buttons
                const viewButtons = document.querySelectorAll('.btn-cal-view');
                viewButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        viewButtons.forEach(b => b.classList.remove('active', 'bg-white', 'text-dark', 'shadow-sm'));
                        this.classList.add('active', 'bg-white', 'text-dark', 'shadow-sm');
                        const targetView = this.getAttribute('data-view');
                        calendar.changeView(targetView);
                    });
                });
            } else {
                calendar.updateSize();
            }
        });
    });
</script>
@endpush
