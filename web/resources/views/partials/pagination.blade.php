@if ($paginator->hasPages())
    <div class="bv-pagination-wrapper">
        <div class="text-secondary small fw-medium">
            <span>Toplam</span>
            <span class="fw-bold text-dark">{{ number_format($paginator->total(), 0, ',', '.') }}</span>
            <span>kayıttan</span>
            <span class="fw-bold text-dark">{{ number_format($paginator->firstItem(), 0, ',', '.') }} - {{ number_format($paginator->lastItem(), 0, ',', '.') }}</span>
            <span>arası gösteriliyor</span>
        </div>

        <nav aria-label="Sayfalama">
            <ul class="bv-pagination">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="bv-page-item">
                        <span class="bv-page-link disabled" aria-hidden="true" title="Önceki">
                            <i class="ti ti-chevron-left"></i>
                        </span>
                    </li>
                @else
                    <li class="bv-page-item">
                        <a href="{{ $paginator->previousPageUrl() }}" class="bv-page-link" rel="prev" aria-label="Önceki" title="Önceki">
                            <i class="ti ti-chevron-left"></i>
                        </a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="bv-page-item">
                            <span class="bv-page-link disabled">{{ $element }}</span>
                        </li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="bv-page-item">
                                    <span class="bv-page-link is-active" aria-current="page">{{ $page }}</span>
                                </li>
                            @else
                                <li class="bv-page-item">
                                    <a href="{{ $url }}" class="bv-page-link">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="bv-page-item">
                        <a href="{{ $paginator->nextPageUrl() }}" class="bv-page-link" rel="next" aria-label="Sonraki" title="Sonraki">
                            <i class="ti ti-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="bv-page-item">
                        <span class="bv-page-link disabled" aria-hidden="true" title="Sonraki">
                            <i class="ti ti-chevron-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@endif
