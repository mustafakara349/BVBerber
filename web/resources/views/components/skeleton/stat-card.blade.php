@props([
    'count' => 4,
    'colClass' => 'col-lg-3 col-md-6 col-12',
])

<div class="row g-3 mb-4" aria-busy="true" aria-hidden="true">
    @for($i = 0; $i < $count; $i++)
        <div class="{{ $colClass }}">
            <div class="skeleton-stat-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="flex-grow-1 pe-3">
                        <div class="skeleton-text sm w-50 mb-2"></div>
                        <div class="skeleton-text title w-75 mb-0"></div>
                    </div>
                    <div class="skeleton-icon"></div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top border-light">
                    <div class="skeleton-text xs w-50 mb-0"></div>
                    <div class="skeleton-badge sm"></div>
                </div>
            </div>
        </div>
    @endfor
</div>
