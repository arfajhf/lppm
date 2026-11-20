@if ($paginator->hasPages())
<nav aria-label="Navigasi halaman">
    <ul class="pagination">
        <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            @if ($paginator->onFirstPage())<span aria-disabled="true">Sebelumnya</span>
            @else<a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>@endif
        </li>
        @foreach ($elements as $element)
            @if (is_string($element))<li class="page-item disabled"><span>{{ $element }}</span></li>@endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <li class="page-item {{ $page === $paginator->currentPage() ? 'active' : '' }}">
                        @if ($page === $paginator->currentPage())<span class="page-link" aria-current="page">{{ $page }}</span>
                        @else<a class="page-link" href="{{ $url }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>@endif
                    </li>
                @endforeach
            @endif
        @endforeach
        <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
            @if ($paginator->hasMorePages())<a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
            @else<span aria-disabled="true">Berikutnya</span>@endif
        </li>
    </ul>
</nav>
@endif
