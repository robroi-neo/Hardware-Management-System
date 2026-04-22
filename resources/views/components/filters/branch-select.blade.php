{{--
    Reusable branch filter dropdown component
    Shows only for admins with multiple branches

    Props:
    - $branches (Collection): Branch collection to display
    - $selected (int|null): Currently selected branch_id
    - $route (string): Route name to submit to
    - $params (array): Additional query parameters to preserve (e.g., ['search' => $search, 'sort_by' => $sortBy])
    - $label (string): Dropdown label - default: 'Filter by Branch'
--}}

@props([
    'branches' => collect(),
    'selected' => null,
    'route' => null,
    'params' => [],
    'label' => 'Filter by Branch',
])

@if($branches->count() > 1)
    <form method="GET" action="{{ route($route) }}" class="flex gap-2 sm:w-auto">
        {{-- Preserve additional parameters as hidden inputs --}}
        @foreach($params as $key => $value)
            @if($value !== null && $value !== '')
                <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
            @endif
        @endforeach

        <select
            name="branch_id"
            onchange="this.form.submit()"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 hover:border-slate-400 transition-colors"
            aria-label="{{ $label }}"
        >
            <option value="">{{ $label }}</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ $selected == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
    </form>
@endif

