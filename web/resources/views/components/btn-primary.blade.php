@props(['href' => null, 'icon' => null, 'type' => 'button'])

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'btn btn-primary rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-2 text-decoration-none fw-medium']) }}>
        @if($icon)<i class="{{ $icon }} fs-5"></i>@endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'btn btn-primary rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-2 fw-medium']) }}>
        @if($icon)<i class="{{ $icon }} fs-5"></i>@endif
        <span>{{ $slot }}</span>
    </button>
@endif
