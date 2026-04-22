{{--
    Reusable sortable table header component

    Props:
    - $label (string): Column header label
    - $sortBy (string): Current sort column name
    - $sortDir (string): Current sort direction ('asc' or 'desc')
    - $column (string): Database column name for this header
    - $route (string): Route name to link to
    - $params (array): Additional query parameters to preserve (e.g., ['search' => $search])
    - $align (string): Text alignment ('left', 'center', 'right') - default: 'left'
--}}

@props([
    'label' => 'Column',
    'sortBy' => null,
    'sortDir' => 'asc',
    'column' => null,
    'route' => null,
    'params' => [],
    'align' => 'left',
])

@php
    $isActive = $sortBy === $column;
    $newDirection = $isActive && $sortDir === 'asc' ? 'desc' : 'asc';
    $alignClass = match($align) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };
    $flexAlign = match($align) {
        'center' => 'justify-center',
        'right' => 'justify-end',
        default => 'justify-start',
    };
@endphp

<th class="px-4 py-3 font-semibold text-slate-700 {{ $alignClass }}">
    <a
        href="{{ route($route, array_merge($params, ['sort_by' => $column, 'sort_dir' => $newDirection])) }}"
        class="inline-flex items-center gap-1 {{ $flexAlign }} w-full hover:text-blue-600 transition-colors"
    >
        {{ $label }}

        @if($isActive)
            {{-- Active sort indicator --}}
            <svg class="h-4 w-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                @if($sortDir === 'asc')
                    {{-- Up arrow --}}
                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l6 6a1 1 0 01-1.414 1.414L11 5.414V15a1 1 0 11-2 0V5.414L5.707 10.707a1 1 0 01-1.414-1.414l6-6A1 1 0 0110 3z" clip-rule="evenodd" />
                @else
                    {{-- Down arrow --}}
                    <path fill-rule="evenodd" d="M10 17a1 1 0 01-.707-.293l-6-6a1 1 0 011.414-1.414L9 14.586V5a1 1 0 112 0v9.586l4.293-4.293a1 1 0 011.414 1.414l-6 6A1 1 0 0110 17z" clip-rule="evenodd" />
                @endif
            </svg>
        @else
            {{-- Inactive sort indicator (always visible) --}}
            <svg class="h-4 w-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l6 6a1 1 0 01-1.414 1.414L11 5.414V15a1 1 0 11-2 0V5.414L5.707 10.707a1 1 0 01-1.414-1.414l6-6A1 1 0 0110 3z" clip-rule="evenodd" />
            </svg>
        @endif
    </a>
</th>

