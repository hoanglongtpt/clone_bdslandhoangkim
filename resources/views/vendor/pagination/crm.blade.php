@if ($paginator->hasPages())
    <nav class="crm-pagination" role="navigation" aria-label="Phân trang">
        @if ($paginator->onFirstPage())
            <span class="disabled" aria-disabled="true">«</span>
            <span class="disabled" aria-disabled="true">‹</span>
        @else
            <a href="{{ $paginator->url(1) }}" aria-label="Trang đầu">«</a>
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Trang trước">‹</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="ellipsis">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Trang sau">›</a>
            <a href="{{ $paginator->url($paginator->lastPage()) }}" aria-label="Trang cuối">»</a>
        @else
            <span class="disabled" aria-disabled="true">›</span>
            <span class="disabled" aria-disabled="true">»</span>
        @endif
    </nav>
@endif
