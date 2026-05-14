@if ($paginator->hasPages())
<div class="pagination">
  @if ($paginator->onFirstPage())
    <span class="page-link" style="opacity:.4">← Prev</span>
  @else
    <a href="{{ $paginator->previousPageUrl() }}" class="page-link">← Prev</a>
  @endif

  @foreach ($elements as $element)
    @if (is_string($element))
      <span class="page-link">{{ $element }}</span>
    @endif
    @if (is_array($element))
      @foreach ($element as $page => $url)
        <a href="{{ $url }}" class="page-link {{ $page == $paginator->currentPage() ? 'active' : '' }}">{{ $page }}</a>
      @endforeach
    @endif
  @endforeach

  @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}" class="page-link">Next →</a>
  @else
    <span class="page-link" style="opacity:.4">Next →</span>
  @endif
</div>
@endif
