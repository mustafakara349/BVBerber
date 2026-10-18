@props([
    'hasTable' => true,
    'class' => '',
])

<div class="row g-4 {{ $class }}" aria-busy="true" aria-hidden="true">
    {{-- Left or Header Profile Card --}}
    <div class="col-lg-4 col-12">
        <div class="skeleton-card text-center d-flex flex-column align-items-center">
            <div class="skeleton-avatar xl mb-3"></div>
            <div class="skeleton-text heading w-75 mb-2"></div>
            <div class="skeleton-badge md mb-3"></div>
            <div class="skeleton-text sm w-50 mb-4"></div>

            <div class="w-100 pt-3 border-top border-light d-flex flex-column gap-2 text-start">
                <div class="d-flex justify-content-between">
                    <div class="skeleton-text xs w-25"></div>
                    <div class="skeleton-text xs w-50"></div>
                </div>
                <div class="d-flex justify-content-between">
                    <div class="skeleton-text xs w-25"></div>
                    <div class="skeleton-text xs w-50"></div>
                </div>
                <div class="d-flex justify-content-between">
                    <div class="skeleton-text xs w-25"></div>
                    <div class="skeleton-text xs w-50"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Content Area --}}
    <div class="col-lg-8 col-12">
        <div class="skeleton-card mb-4">
            <div class="skeleton-text heading w-25 mb-4"></div>
            <div class="row g-3">
                @for($i = 0; $i < 6; $i++)
                    <div class="col-md-6 col-12">
                        <div class="skeleton-text xs w-50 mb-1"></div>
                        <div class="skeleton-text md w-75"></div>
                    </div>
                @endfor
            </div>
        </div>

        @if($hasTable)
            <x-skeleton.table :columns="4" :rows="4" :hasAvatar="false" />
        @endif
    </div>
</div>
