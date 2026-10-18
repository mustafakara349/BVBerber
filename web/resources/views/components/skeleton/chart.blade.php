@props([
    'height' => '350px',
    'title' => true,
    'class' => '',
])

<div class="skeleton-chart-box {{ $class }}" style="min-height: {{ $height }};" aria-busy="true" aria-hidden="true">
    @if($title)
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-light">
            <div class="skeleton-text heading w-25 mb-0"></div>
            <div class="d-flex gap-2">
                <div class="skeleton-badge sm"></div>
                <div class="skeleton-badge sm"></div>
            </div>
        </div>
    @endif

    <div class="skeleton-chart-bars" style="height: calc({{ $height }} - 100px);">
        @php
            $barHeights = [45, 75, 60, 90, 55, 80, 65, 95, 70, 85, 50, 78];
        @endphp
        @foreach($barHeights as $h)
            <div class="skeleton-bar" style="height: {{ $h }}%;"></div>
        @endforeach
    </div>

    <div class="d-flex justify-content-between align-items-center pt-3 border-top border-light">
        @for($i = 0; $i < 6; $i++)
            <div class="skeleton-text xs" style="width: 35px;"></div>
        @endfor
    </div>
</div>
