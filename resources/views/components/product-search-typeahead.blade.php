@props([
    'searchInputRef' => 'topSearchInput',
    'placeholder' => 'Scan or Search Product ID, name, or unit...',
    'idField' => 'id',
    'primaryField' => 'name',
    'secondaryField' => 'unit',
    'showMeta' => true,
    'showPrice' => true,
    'priceField' => 'capital',
    'showStock' => true,
    'stockField' => 'available_quantity',
])

<div class="w-full relative" @click.outside="() => closeTypeahead()">
    <label class="relative block">
        <span class="sr-only">Search</span>
        <input
            x-ref="{{ $searchInputRef }}"
            x-model="typeahead.q"
            @input="onTypeaheadInput()"
            @keydown.enter.prevent="onTypeaheadEnter()"
            @keydown.arrow-down.prevent="moveTypeahead(1)"
            @keydown.arrow-up.prevent="moveTypeahead(-1)"
            @keydown.escape.prevent="() => closeTypeahead()"
            @focus="reopenTypeahead()"
            placeholder="{{ $placeholder }}"
            class="placeholder-gray-400 bg-white-100 border border-gray-200 rounded px-3 py-2 pr-8 w-full focus:outline-none focus:ring-2 focus:ring-indigo-200"
        />
        <button
            type="button"
            @click="typeahead.q = ''; closeTypeahead(); typeahead.items = []; $refs['{{ $searchInputRef }}']?.focus()"
            x-show="typeahead.q.trim().length > 0"
            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none transition-colors"
            aria-label="Clear search"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </label>

    <div
        x-show="typeahead.open"
        x-cloak
        class="absolute left-0 right-0 z-50 mt-1 rounded border border-slate-200 bg-white shadow-lg"
    >
        <div class="max-h-72 overflow-auto text-sm">
            <template x-if="typeahead.loading">
                <div class="px-3 py-2 text-slate-500">Searching...</div>
            </template>

            <template x-if="!typeahead.loading && typeahead.items.length === 0">
                <div class="px-3 py-2 text-slate-500">No matches found.</div>
            </template>

            <template x-for="(item, index) in typeahead.items" :key="item['{{ $idField }}'] ?? item.id">
                <button
                    type="button"
                    @mousedown.prevent="selectTypeaheadItem(index)"
                    class="w-full text-left px-3 py-2 border-b border-slate-100 last:border-b-0"
                    :class="index === typeahead.activeIndex ? 'bg-slate-100' : 'hover:bg-slate-50'"
                >
                    <div class="font-medium text-slate-900">
                        #<span x-text="item['{{ $idField }}']"></span> - <span x-text="item['{{ $primaryField }}']"></span>
                    </div>
                    @if($showMeta)
                        <div class="text-xs text-slate-600">
                            <span x-show="item['{{ $secondaryField }}']" x-text="item['{{ $secondaryField }}']"></span>
                            @if($showPrice)
                                <span x-show="item['{{ $priceField }}'] !== undefined">
                                    | ₱<span x-text="formatPrice(item['{{ $priceField }}'])"></span>
                                </span>
                            @endif
                            @if($showStock)
                                <span x-show="item['{{ $stockField }}'] !== undefined">
                                    | Stock: <span x-text="formatQty(item['{{ $stockField }}'])"></span>
                                </span>
                            @endif
                        </div>
                    @endif
                </button>
            </template>
        </div>
    </div>
</div>

