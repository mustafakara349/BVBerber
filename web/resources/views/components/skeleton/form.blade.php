@props([
    'fields' => 4,
    'columns' => 2,
    'hasTextarea' => true,
    'class' => '',
])

@php
    $colClass = $columns === 1 ? 'col-12' : ($columns === 3 ? 'col-lg-4 col-12' : 'col-lg-6 col-12');
@endphp

<div class="skeleton-card {{ $class }}" aria-busy="true" aria-hidden="true">
    <div class="row g-3">
        @for($i = 0; $i < $fields; $i++)
            <div class="{{ $colClass }}">
                <div class="skeleton-text sm w-25 mb-2"></div>
                <div class="skeleton-input"></div>
            </div>
        @endfor

        @if($hasTextarea)
            <div class="col-12">
                <div class="skeleton-text sm w-25 mb-2"></div>
                <div class="skeleton-input" style="height: 90px;"></div>
            </div>
        @endif

        <div class="col-12 d-flex justify-content-end gap-2 pt-3 border-top border-light">
            <div class="skeleton-btn" style="width: 80px;"></div>
            <div class="skeleton-btn" style="width: 120px;"></div>
        </div>
    </div>
</div>
