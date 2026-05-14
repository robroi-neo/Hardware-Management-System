<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">Stock Overview</h2>
    </x-slot>

    <x-card x-data="inventorySearch('{{ route('pos.api.products.search') }}', '{{ route('inventory.overview') }}', '{{ $filterBranchId }}')">
        <!-- Search Bar & Branch Filter -->
        <div class="mb-6 flex flex-col gap-4">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex-1">
                    <x-product-search-typeahead searchInputRef="inventorySearchInput" />
                </div>

                @if($isAdmin)
                    <form>
                        <x-filters.dropdown-filter
                            :items="$allBranches"
                            :selected="$filterBranchId"
                            name="branch_id"
                            label="Filter by Branch"
                            placeholder="All Branches"
                            valueField="id"
                            displayField="name"
                            :autoSubmit="true"
                        />
                    </form>
                @endif
                @if($isAdmin)
                    <form>
                        <x-filters.dropdown-filter
                            :items="$statuses"
                            :selected="$filterStatus"
                            name="status"
                            label="Filter by Status"
                            placeholder="All Statuses"
                            valueField="value"
                            displayField="label"
                            :autoSubmit="true"
                        />
                    </form>
                @endif
            </div>
        </div>

        <!-- Stats Summary -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm text-slate-600">Total Products</p>
                <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $inventories->total() }}</p>
            </div>
            <div class="rounded border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm text-slate-600">Total Value</p>
                <p class="mt-1 text-2xl font-semibold text-slate-900">
                    ₱{{ number_format($inventories->sum(fn($inv) => $inv->quantity * $inv->product->capital), 2) }}
                </p>
            </div>
            <div class="rounded border border-slate-200 bg-slate-50 p-4" >
                <p class="text-sm text-slate-600">Low Stock Items</p>
                <p class="mt-1 text-2xl font-semibold text-amber-600">
                    {{ $inventories->filter(fn($inv) => $inv->quantity < 5)->count() }}
                </p>
            </div>
        </div>

        <!-- Table -->
        <div class="border border-gray-200 rounded overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-indigo-100">
                            <th class="px-4 py-3 text-left font-medium text-slate-700">ID</th>
                            <x-table.sortable-header
                                label="Product Name"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="name"
                                route="inventory.overview"
                                :params="['search' => $search, 'branch_id' => $filterBranchId, 'status' => $filterStatus]"
                            />
                            <th class="px-4 py-3 text-left font-medium text-slate-700">Unit</th>
                            <x-table.sortable-header
                                label="Quantity"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="quantity"
                                route="inventory.overview"
                                :params="['search' => $search, 'branch_id' => $filterBranchId, 'status' => $filterStatus]"
                                align="center"
                            />
                            <th class="px-4 py-3 text-left font-medium text-slate-700">Unit Cost</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-700">Total Value</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-700">Branch</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-700">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-700">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventories as $inventory)
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $inventory->product_id }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $inventory->product->name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $inventory->product->unit }}</td>
                                <td class="px-4 py-3 text-center text-slate-700 font-semibold">{{ number_format($inventory->quantity, 2) }}</td>
                                <td class="px-6 py-3 text-slate-600">₱{{ number_format($inventory->product->capital, 2) }}</td>
                                <td class="px-4 py-3 text-slate-700 font-semibold">₱{{ number_format($inventory->quantity * $inventory->product->capital, 2) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $inventory->branch->name }}</td>
                                <td class="px-4 py-3">
                                    @if($inventory->product->status === 'inactive')
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                            Archived
                                        </span>
                                    @else
                                        @if($inventory->quantity < 5)
                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">
                                                Low Stock
                                            </span>
                                        @elseif($inventory->quantity < 10)
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-800">
                                                Warning
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                                In Stock
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @can('inventory.update')
                                        @php
                                            $productQuantity = $inventory->product->branchInventories->sum('quantity');
                                        @endphp
                                    <!--   -->
                                        @if($inventory->product->status !== 'inactive' && $productQuantity <= 0)
                                            <button
                                                type="button"
                                                @click="confirmArchive.open = true; confirmArchive.action = 'archive'; confirmArchive.product = {
                                                id: {{ $inventory->product->id }},
                                                name: '{{ addslashes($inventory->product->name) }}',
                                                unit: '{{ $inventory->product->unit }}',
                                                quantity: {{ $inventory->quantity }},
                                                branch: '{{ addslashes($inventory->branch->name) }}'
                                            }"
                                                class="rounded bg-gray-600 px-3 py-1 text-xs font-medium text-white hover:bg-gray-700"
                                                                                    >
                                                Archive
                                            </button>
                                        @elseif($inventory->product->status === 'inactive')
                                            <span class="text-xs text-slate-500">
                                            <button
                                                type="button"
                                                @click="confirmArchive.open = true; confirmArchive.action = 'restore'; confirmArchive.product = {
                                                id: {{ $inventory->product->id }},
                                                name: '{{ addslashes($inventory->product->name) }}',
                                                unit: '{{ $inventory->product->unit }}',
                                                quantity: {{ $inventory->quantity }},
                                                branch: '{{ addslashes($inventory->branch->name) }}'
                                            }"
                                                class="rounded bg-gray-600 px-3 py-1 text-xs font-medium text-white hover:bg-gray-700"
                                            >
                                                Restore
                                            </button>
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-500">No actions available</span>
                                        @endif

                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <x-table.empty-state
                                :colspan="9"
                                :message="$search ? 'No inventory records found. Try adjusting your search filters.' : 'No inventory records found.'"
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <x-table.pagination :paginator="$inventories" />

        <!-- Action Buttons -->
        <div class="mt-6 flex flex-wrap gap-3">
            @can('inventory.update')
                <a href="{{ route('inventory.manual-stock-in') }}" class="rounded bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                    + Stock In
                </a>
                <a href="{{ route('inventory.stock-out') }}" class="rounded bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    - Stock Out
                </a>
            @endcan
            @can('inventory.view-movements')
                <a href="{{ route('inventory.stock-movements') }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    View Movements
                </a>
            @endcan
        </div>

        <!-- Archive / Restore Confirmation Modal -->
        <div
            x-show="confirmArchive.open"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
        >
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg">
                <h2 class="text-lg font-semibold text-slate-900" x-text="confirmArchive.action === 'restore' ? 'Restore Product' : 'Archive Product'">
                    Archive Product
                </h2>

                <p class="mt-2 text-sm text-slate-600" x-text="confirmArchive.action === 'restore' ? 'Are you sure that you want to restore this product?' : 'Are you sure that you want to archive this product?'"></p>

                <div class="mt-4 space-y-1 rounded bg-slate-50 p-3 text-sm">
                    <p><span class="font-medium">Product:</span> <span x-text="confirmArchive.product?.name"></span></p>
                    <p><span class="font-medium">Unit:</span> <span x-text="confirmArchive.product?.unit"></span></p>
                    <p><span class="font-medium">Current Stock:</span> <span x-text="confirmArchive.product?.quantity"></span></p>
                    <p><span class="font-medium">Branch:</span> <span x-text="confirmArchive.product?.branch"></span></p>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        @click="confirmArchive.open = false"
                        class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </button>

                    <form
                        :action="confirmArchive.product ? `/inventory/products/${confirmArchive.product.id}/${confirmArchive.action}` : '#'"
                        method="POST"
                    >
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="rounded bg-gray-700 px-4 py-2 text-sm text-white hover:bg-gray-800"
                            x-text="confirmArchive.action === 'restore' ? 'Confirm Restore' : 'Confirm Archive'"
                        >
                            Confirm Archive
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </x-card>

</x-app-layout>

<script>
function inventorySearch(searchUrl, baseRoute, selectedBranch) {
    return {
        typeahead: {
            q: '',
            items: [],
            open: false,
            loading: false,
            activeIndex: -1,
            debounceHandle: null,
            limit: 8,
        },
        searchUrl: searchUrl,
        baseRoute: baseRoute,
        selectedBranch: selectedBranch,

        confirmArchive: {
            open: false,
            product: null,
            action: 'archive',
        },

        onTypeaheadInput() {
            if (this.typeahead.debounceHandle) {
                clearTimeout(this.typeahead.debounceHandle);
            }

            const query = this.typeahead.q.trim();
            if (!query) {
                this.typeahead.items = [];
                this.typeahead.open = false;
                this.typeahead.activeIndex = -1;
                return;
            }

            this.typeahead.debounceHandle = setTimeout(() => {
                this.fetchTypeahead(query);
            }, 250);
        },

        async fetchTypeahead(query) {
            this.typeahead.loading = true;
            this.typeahead.open = true;

            const params = new URLSearchParams({ q: query, limit: String(this.typeahead.limit) });

            try {
                const data = await this.getJson(`${this.searchUrl}?${params.toString()}`);
                this.typeahead.items = Array.isArray(data) ? data : [];
                this.typeahead.activeIndex = this.typeahead.items.length > 0 ? 0 : -1;
            } catch (error) {
                this.typeahead.items = [];
                this.typeahead.activeIndex = -1;
                console.error(error);
            } finally {
                this.typeahead.loading = false;
            }
        },

        reopenTypeahead() {
            if (this.typeahead.items.length > 0 || this.typeahead.loading) {
                this.typeahead.open = true;
            }
        },

        closeTypeahead() {
            this.typeahead.open = false;
            this.typeahead.activeIndex = -1;
        },

        moveTypeahead(step) {
            if (!this.typeahead.open || this.typeahead.items.length === 0) {
                return;
            }

            const count = this.typeahead.items.length;
            const current = this.typeahead.activeIndex < 0 ? 0 : this.typeahead.activeIndex;
            this.typeahead.activeIndex = (current + step + count) % count;
        },

        async onTypeaheadEnter() {
            if (this.typeahead.open && this.typeahead.items.length > 0) {
                const index = this.typeahead.activeIndex >= 0 ? this.typeahead.activeIndex : 0;
                await this.selectTypeaheadItem(index);
                return;
            }

            this.applySearch();
        },

        async selectTypeaheadItem(index) {
            const product = this.typeahead.items[index];
            if (!product) {
                return;
            }

            this.typeahead.q = product.name;
            this.typeahead.items = [];
            this.closeTypeahead();

            this.$nextTick(() => {
                this.$refs.inventorySearchInput?.focus();
            });
        },

        clearSearch() {
            this.typeahead.q = '';
            this.typeahead.items = [];
            this.closeTypeahead();
        },

        applySearch() {
            const query = this.typeahead.q.trim();
            const params = new URLSearchParams();

            if (query) {
                params.set('search', query);
            }

            if (this.selectedBranch) {
                params.set('branch_id', this.selectedBranch);
            }

            window.location.href = params.toString() ? `${this.baseRoute}?${params.toString()}` : this.baseRoute;
        },

        formatPrice(value) {
            return Number(value ?? 0).toFixed(2);
        },

        formatQty(value) {
            return String(Math.max(0, Math.floor(Number(value ?? 0))));
        },

        async getJson(url) {
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`Request failed (${response.status})`);
            }

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                throw new Error('Unexpected server response.');
            }

            return response.json();
        },
    };
}
</script>


