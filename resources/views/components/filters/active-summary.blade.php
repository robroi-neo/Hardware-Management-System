@props([
    'fields' => [],
    'label' => 'Showing results for:',
])

@php
    $hasAny = collect($fields)->contains(function ($field) {
        return request()->filled($field);
    });
@endphp

@if($hasAny)
    <div class="mb-3 flex flex-wrap items-center gap-2 text-xs text-slate-500">
        <span>{{ $label }}</span>

        @foreach($fields as $field)
            @if(request($field))
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 font-medium
                    @switch($field)
                        @case('search') bg-indigo-50 text-indigo-700 @break
                        @case('supplier_id') bg-blue-50 text-blue-700 @break
                        @case('date_from')
                        @case('date_to') bg-amber-50 text-amber-700 @break
                        @case('sort_by') bg-slate-100 text-slate-700 @break
                        @default bg-gray-100 text-gray-700
                    @endswitch
                ">
                    @if($field === 'search')
                        "{{ request($field) }}"
                    @elseif($field === 'supplier_id')
                        Supplier #{{ request($field) }}
                    @elseif($field === 'date_from' || $field === 'date_to')
                        {{ request('date_from', '...') }} → {{ request('date_to', '...') }}
                        @break
                    @elseif($field === 'sort_by')
                        Sorted by {{ request('sort_by') }} ({{ request('sort_dir', 'desc') }})
                    @else
                        {{ ucfirst(str_replace('_', ' ', request($field))) }}
                    @endif
                </span>
            @endif
        @endforeach
    </div>
@endif
