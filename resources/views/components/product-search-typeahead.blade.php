@props([
    'searchInputRef' => 'topSearchInput',
])

<div class="w-full max-w-md relative" @click.outside="closeTypeahead()">
    <label class="relative block">
        <span class="sr-only">Search</span>
        <input
            x-ref="{{ $searchInputRef }}"
            x-model="typeahead.q"
            @input="onTypeaheadInput()"
            @keydown.enter.prevent="onTypeaheadEnter()"
            @keydown.arrow-down.prevent="moveTypeahead(1)"
            @keydown.arrow-up.prevent="moveTypeahead(-1)"
            @keydown.escape.prevent="closeTypeahead()"
            @focus="reopenTypeahead()"
            placeholder="Scan or Search Product ID, name, or unit..."
            class="placeholder-gray-400 bg-gray-100 border border-gray-200 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-200"
        />
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

            <template x-for="(product, index) in typeahead.items" :key="product.id">
                <button
                    type="button"
                    @mousedown.prevent="selectTypeaheadItem(index)"
                    class="w-full text-left px-3 py-2 border-b border-slate-100 last:border-b-0"
                    :class="index === typeahead.activeIndex ? 'bg-slate-100' : 'hover:bg-slate-50'"
                >
                    <div class="font-medium text-slate-900">
                        #<span x-text="product.id"></span> - <span x-text="product.name"></span>
                    </div>
                    <div class="text-xs text-slate-600">
                        <span x-text="product.unit"></span>
                        | P<span x-text="formatPrice(product.capital)"></span>
                        <span x-show="product.available_quantity !== undefined">
                            | Stock: <span x-text="formatQty(product.available_quantity)"></span>
                        </span>
                    </div>
                </button>
            </template>
        </div>
    </div>
</div>

