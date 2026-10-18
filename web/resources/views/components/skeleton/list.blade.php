@props([
    'items' => 5,
    'hasIcon' => true,
    'class' => '',
])

<div class="skeleton-card {{ $class }}" aria-busy="true" aria-hidden="true">
    <div class="d-flex flex-column gap-3">
        @for($i = 0; $i < $items; $i++)
            <div class="d-flex align-items-center justify-content-between p-2 rounded-3 {{ $i < $items - 1 ? 'border-bottom border-light pb-3' : '' }}">
                <div class="d-flex align-items-center gap-3 flex-grow-1 pe-3">
                    @if($hasIcon)
                        <div class="skeleton-icon sm"></div>
                    @endif
                    <div class="d-flex flex-column gap-1 flex-grow-1">
                        <div class="skeleton-text md" style="width: {{ rand(50, 80) }}%;"></div>
                        <div class="skeleton-text xs" style="width: {{ rand(30, 60) }}%;"></div>
                    </div>
                </div>
                <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                    <div class="skeleton-badge sm"></div>
                    <div class="skeleton-text xs" style="width: 45px;"></div>
                </div>
            </div>
        @endfor
    </div>
</div>
