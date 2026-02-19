@php
    $paginator = $paginator ?? null;
@endphp
@if($paginator && $paginator->hasPages())
    <div class="pagination-bar">
        <span class="pagination-bar__info">
            نمایش {{ $paginator->firstItem() ?? 0 }} تا {{ $paginator->lastItem() ?? 0 }} از {{ $paginator->total() }}
        </span>
        <span class="pagination-bar__info">
            صفحه {{ $paginator->currentPage() }} از {{ $paginator->lastPage() }}
        </span>
        <div class="d-flex gap-2 align-items-center">
            @if($paginator->onFirstPage())
                <span class="pagination-bar__btn" aria-disabled="true" style="pointer-events:none;opacity:0.5;cursor:not-allowed;"><span class="pagination-bar__arrow pagination-bar__arrow--prev" aria-hidden="true"></span> قبلی</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-bar__btn" rel="prev"><span class="pagination-bar__arrow pagination-bar__arrow--prev" aria-hidden="true"></span> قبلی</a>
            @endif
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-bar__btn" rel="next">بعدی <span class="pagination-bar__arrow pagination-bar__arrow--next" aria-hidden="true"></span></a>
            @else
                <span class="pagination-bar__btn" aria-disabled="true" style="pointer-events:none;opacity:0.5;cursor:not-allowed;">بعدی <span class="pagination-bar__arrow pagination-bar__arrow--next" aria-hidden="true"></span></span>
            @endif
        </div>
    </div>
@endif
