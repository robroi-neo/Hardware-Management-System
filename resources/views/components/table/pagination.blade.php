{{--
    Reusable pagination component
    Shows pagination info and links

    Props:
    - $paginator (Paginator): Laravel paginator instance
--}}

@props([
    'paginator' => null,
])

@if($paginator && $paginator->hasPages())
    <div class="mt-6 flex items-center justify-between">
        {{-- Results info --}}
        <div class="text-sm text-slate-600">
            Showing <strong>{{ $paginator->firstItem() }}</strong> to <strong>{{ $paginator->lastItem() }}</strong>
            of <strong>{{ $paginator->total() }}</strong> results
        </div>

        {{-- Pagination links --}}
        <nav class="flex gap-1" aria-label="Pagination">
            {{-- Previous button --}}
            @if($paginator->onFirstPage())
                <span class="rounded border border-slate-300 px-3 py-2 text-sm text-slate-400 cursor-not-allowed">
                    Previous
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    class="rounded border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50 transition-colors"
                    rel="prev"
                >
                    Previous
                </a>
            @endif

            {{-- Page numbers --}}
            @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                @if($page == $paginator->currentPage())
                    <span
                        class="rounded border border-blue-500 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-600"
                        aria-label="Page {{ $page }}"
                        aria-current="page"
                    >
                        {{ $page }}
                    </span>
                @else
                    <a
                        href="{{ $url }}"
                        class="rounded border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50 transition-colors"
                        aria-label="Page {{ $page }}"
                    >
                        {{ $page }}
                    </a>
                @endif
            @endforeach

            {{-- Next button --}}
            @if($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    class="rounded border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50 transition-colors"
                    rel="next"
                >
                    Next
                </a>
            @else
                <span class="rounded border border-slate-300 px-3 py-2 text-sm text-slate-400 cursor-not-allowed">
                    Next
                </span>
            @endif
        </nav>
    </div>
@endif

