@props([
    'columns' => 5,
    'rows' => 6,
    'hasAvatar' => true,
    'hasActions' => true,
    'class' => '',
])

<div class="skeleton-table-wrapper {{ $class }}" aria-busy="true" aria-hidden="true">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    @for($c = 0; $c < $columns; $c++)
                        <th class="{{ $c === 0 ? 'ps-4' : '' }} {{ $c === $columns - 1 ? 'pe-4 text-end' : '' }}">
                            <div class="skeleton-text sm {{ $c === $columns - 1 ? 'ms-auto' : '' }}" style="width: {{ rand(60, 95) }}px;"></div>
                        </th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @for($r = 0; $r < $rows; $r++)
                    <tr>
                        @for($c = 0; $c < $columns; $c++)
                            <td class="{{ $c === 0 ? 'ps-4' : '' }} {{ $c === $columns - 1 ? 'pe-4 text-end' : '' }}">
                                @if($c === 0 && $hasAvatar)
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="skeleton-avatar sm"></div>
                                        <div class="d-flex flex-column gap-1 flex-grow-1" style="max-width: 140px;">
                                            <div class="skeleton-text md w-100"></div>
                                            <div class="skeleton-text xs w-75"></div>
                                        </div>
                                    </div>
                                @elseif($c === $columns - 1 && $hasActions)
                                    <div class="d-flex gap-2 justify-content-end align-items-center">
                                        <div class="skeleton-btn sm" style="width: 75px;"></div>
                                        <div class="skeleton-btn sm" style="width: 55px;"></div>
                                    </div>
                                @elseif($c === 1)
                                    <div class="skeleton-badge md"></div>
                                @elseif($c === 2)
                                    <div class="skeleton-text md" style="width: {{ rand(80, 130) }}px;"></div>
                                @else
                                    <div class="skeleton-text sm" style="width: {{ rand(60, 110) }}px;"></div>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>
