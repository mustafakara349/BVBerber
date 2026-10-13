@if ($paginator->hasPages())
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
        <div class="text-secondary small fw-medium" style="font-size: 13.5px;">
            <span>Showing</span>
            <span class="fw-bold text-dark">{{ $paginator->firstItem() }}</span>
            <span>to</span>
            <span class="fw-bold text-dark">{{ $paginator->lastItem() }}</span>
            <span>of</span>
            <span class="fw-bold text-dark">{{ $paginator->total() }}</span>
            <span>results</span>
        </div>

        <nav aria-label="Sayfalama">
            <div class="sn-pagination-box">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span class="sn-page-link disabled" aria-hidden="true">&lsaquo;</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="sn-page-link" rel="prev" aria-label="Önceki">&lsaquo;</a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="sn-page-link disabled">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="sn-page-link is-active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="sn-page-link">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="sn-page-link" rel="next" aria-label="Sonraki">&rsaquo;</a>
                @else
                    <span class="sn-page-link disabled" aria-hidden="true">&rsaquo;</span>
                @endif
            </div>
        </nav>
    </div>
@endif
