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
                <span class="pagination-bar__btn" aria-disabled="true" style="pointer-events:none;opacity:0.5;cursor:not-allowed;">← قبلی</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-bar__btn" rel="prev">← قبلی</a>
            @endif
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-bar__btn" rel="next">بعدی →</a>
            @else
                <span class="pagination-bar__btn" aria-disabled="true" style="pointer-events:none;opacity:0.5;cursor:not-allowed;">بعدی →</span>
            @endif
        </div>
    </div>
@endif
