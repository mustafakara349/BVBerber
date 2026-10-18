@props([
    'class' => '',
])

<div class="skeleton-calendar-grid {{ $class }}" aria-busy="true" aria-hidden="true">
    {{-- Calendar Header Toolbar --}}
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-light">
        <div class="d-flex gap-2">
            <div class="skeleton-btn sm" style="width: 40px;"></div>
            <div class="skeleton-btn sm" style="width: 40px;"></div>
            <div class="skeleton-btn sm" style="width: 70px;"></div>
        </div>
        <div class="skeleton-text heading w-25 mb-0"></div>
        <div class="d-flex gap-2">
            <div class="skeleton-btn sm" style="width: 75px;"></div>
            <div class="skeleton-btn sm" style="width: 75px;"></div>
            <div class="skeleton-btn sm" style="width: 75px;"></div>
        </div>
    </div>

    {{-- Weekday Columns --}}
    <div class="skeleton-cal-header">
        @foreach(['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'] as $day)
            <div class="text-center py-2 bg-light rounded-2">
                <div class="skeleton-text xs w-50 mx-auto mb-1"></div>
                <div class="skeleton-text sm w-25 mx-auto mb-0"></div>
            </div>
        @endforeach
    </div>

    {{-- Time Grid / Day Cells --}}
    <div class="skeleton-cal-body">
        @for($i = 0; $i < 21; $i++)
            <div class="skeleton-cal-cell d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between">
                    <div class="skeleton-text xs" style="width: 20px;"></div>
                    @if($i % 3 === 0)
                        <div class="skeleton-badge sm" style="width: 35px; height: 16px;"></div>
                    @endif
                </div>
                @if($i % 2 === 0)
                    <div class="skeleton-shimmer rounded-2 p-1" style="height: 28px; width: 100%;"></div>
                @endif
            </div>
        @endfor
    </div>
</div>
