@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4']) }}>
    <div>
        <h1 class="fs-3 fw-bold text-dark mb-1">{{ $title }}</h1>
        @if($description)
            <p class="text-muted mb-0 small">{{ $description }}</p>
        @endif
    </div>
    @if($slot->isNotEmpty())
        <div class="d-flex flex-wrap align-items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
