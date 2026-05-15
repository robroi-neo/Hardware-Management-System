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
                    ₱{{ number_format($totalValue, 2) }}
                </p>
            </div>
            <div class="rounded border border-slate-200 bg-slate-50 p-4" >
                <p class="text-sm text-slate-600">Low Stock Items</p>
                <p class="mt-1 text-2xl font-semibold text-amber-600">
                    {{ $lowStockCount }}
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
                                        @if($inventory->quantity <= 0)
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800">
                                                Out of Stock
                                            </span>
                                        @elseif($inventory->quantity < 10)
                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">
                                                Low Stock
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                                In Stock
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @can('inventory.update')
                                        @php
                                            $productQuantity = $inventory->product->branchInventories->sum('quantity');
                                        @endphp
                                        
                                        <div class="flex justify-center gap-2">
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
                                                    class="text-slate-400 hover:text-red-600 transition-colors"
                                                    title="Archive Product"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-archive-icon lucide-archive"><rect width="20" height="5" x="2" y="3" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/></svg>
                                                </button>
                                            @elseif($inventory->product->status === 'inactive')
                                                <button
                                                    type="button"
                                                    @click="confirmArchive.open = true; confirmArchive.action = 'restore'; confirmArchive.product = {
                                                        id: {{ $inventory->product->id }},
                                                        name: '{{ addslashes($inventory->product->name) }}',
                                                        unit: '{{ $inventory->product->unit }}',
                                                        quantity: {{ $inventory->quantity }},
                                                        branch: '{{ addslashes($inventory->branch->name) }}'
                                                    }"
                                                    class="text-slate-400 hover:text-emerald-600 transition-colors"
                                                    title="Restore Product"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-archive-restore-icon lucide-archive-restore"><rect width="20" height="5" x="2" y="3" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h2"/><path d="M20 8v11a2 2 0 0 1-2 2h-2"/><path d="m9 15 3-3 3 3"/><path d="M12 12v9"/></svg>
                                                </button>
                                            @else
                                                <span class="text-xs text-slate-400">—</span>
                                            @endif
                                        </div>
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
                        :disabled="submitting"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        @click="executeArchive()"
                        class="rounded px-4 py-2 text-sm text-white disabled:opacity-50"
                        :class="confirmArchive.action === 'restore' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700'"
                        x-text="confirmArchive.action === 'restore' ? 'Confirm Restore' : 'Confirm Archive'"
                        :disabled="submitting"
                    >
                        Confirm Archive
                    </button>
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
        submitting: false,
        confirmArchive: {
            open: false,
            product: null,
            action: 'archive',
        },

        // NEW: Standard Toast Function
        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            
            toast.className = `fixed bottom-6 right-6 px-6 py-4 rounded border shadow-2xl z-[99999] font-medium text-sm transition-all duration-300 transform translate-y-0 opacity-100 flex items-center gap-3 ${
                type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 
                type === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-800' : 
                'bg-red-50 border-red-200 text-red-800'
            }`;

            let icon = '';
            if (type === 'success') {
                icon = `<svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
            } else if (type === 'warning') {
                icon = `<svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" /></svg>`;
            } else {
                icon = `<svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
            }
            
            toast.innerHTML = `${icon} <span>${message}</span>`;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        },

        // NEW: Seamless Table Background Refresh
        async refreshTable() {
            try {
                const response = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                document.querySelector('#inventory-table-container').innerHTML = doc.querySelector('#inventory-table-container').innerHTML;
            } catch (error) {
                console.error('Seamless refresh failed, falling back to hard reload.', error);
                window.location.reload();
            }
        },

        // NEW: Background Execution for Archive/Restore
        async executeArchive() {
            if (!this.confirmArchive.product) return;
            this.submitting = true;

            try {
                const url = `/inventory/products/${this.confirmArchive.product.id}/${this.confirmArchive.action}`;
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.confirmArchive.open = false;
                    
                    // Show specific toast based on action!
                    const toastType = this.confirmArchive.action === 'restore' ? 'success' : 'warning';
                    this.showToast(data.message, toastType);
                    
                    await this.refreshTable();
                } else {
                    this.confirmArchive.open = false;
                    this.showToast(data.message || 'Error processing request', 'error');
                }
            } catch (error) {
                this.confirmArchive.open = false;
                this.showToast('Error: ' + error.message, 'error');
            } finally {
                this.submitting = false;
            }
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

            const params = new URLSearchParams(window.location.search);
            params.delete('search');
            window.location.href = params.toString() ? `${this.baseRoute}?${params.toString()}` : this.baseRoute;
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


