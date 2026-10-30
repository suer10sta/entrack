@if($items->hasPages())
<nav class="mq-pagination" aria-label="Pagination">
@if($items->onFirstPage())<span aria-disabled="true">{{ __('manager-surveys.previous') }}</span>@else<a href="{{ $items->previousPageUrl() }}">{{ __('manager-surveys.previous') }}</a>@endif
<span>{{ __('manager-surveys.page', ['page'=>$items->currentPage(), 'last'=>$items->lastPage()]) }}</span>
@if($items->hasMorePages())<a href="{{ $items->nextPageUrl() }}">{{ __('manager-surveys.next') }}</a>@else<span aria-disabled="true">{{ __('manager-surveys.next') }}</span>@endif
</nav>
@endif
