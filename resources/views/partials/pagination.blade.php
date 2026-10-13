@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="page-item disabled">‹ Prev</span>
        @else
            <a class="page-item" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Prev</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page-item disabled">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-item active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="page-item" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="page-item" href="{{ $paginator->nextPageUrl() }}" rel="next">Next ›</a>
        @else
            <span class="page-item disabled">Next ›</span>
        @endif
    </nav>
@endif
