@if ($paginator->hasPages())
<nav aria-label="Navigasi halaman">
    <ul class="pagination">
        <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            @if ($paginator->onFirstPage())<span aria-disabled="true">Sebelumnya</span>
            @else<a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>@endif
        </li>
        <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
            @if ($paginator->hasMorePages())<a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
            @else<span aria-disabled="true">Berikutnya</span>@endif
        </li>
    </ul>
</nav>
@endif
