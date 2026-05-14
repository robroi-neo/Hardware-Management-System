<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">Stock Overview</h2>
    </x-slot>

    <x-card x-data="inventorySearch('{{ route('pos.api.products.search') }}', '{{ route('inventory.overview') }}', '{{ $filterBranchId }}')">
        <!-- Search Bar & Branch Filter -->
        <div class="mb-6 flex flex-col gap-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
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
                            valueField="id"
                            displayField="name"
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
            <div class="rounded border border-slate-200 bg-slate-50 p-4">
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
                                :params="['search' => $search]"
                            />
                            <th class="px-4 py-3 text-left font-medium text-slate-700">Unit</th>
                            <x-table.sortable-header
                                label="Quantity"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="quantity"
                                route="inventory.overview"
                                :params="['search' => $search]"
                                align="right"
                            />
                            <th class="px-4 py-3 text-left font-medium text-slate-700">Unit Cost</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-700">Total Value</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-700">Branch</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-700">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventories as $inventory)
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $inventory->product_id }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $inventory->product->name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $inventory->product->unit }}</td>
                                <td class="px-4 py-3 text-center text-slate-700 font-semibold">{{ number_format($inventory->quantity, 2) }}</td>
                                <td class="px-4 py-3 text-slate-600">₱{{ number_format($inventory->product->capital, 2) }}</td>
                                <td class="px-4 py-3 text-slate-700 font-semibold">₱{{ number_format($inventory->quantity * $inventory->product->capital, 2) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $inventory->branch->name }}</td>
                                <td class="px-4 py-3">
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
                                </td>
                                <td class="px-4 py-3">
                                    @can('inventory.archive')
                                        <button
                                            type="button"
                                            @click="archiveProduct"
                                            data-product-id="{{ $inventory->product_id }}"
                                            data-product-name="{{ $inventory->product->name }}"
                                            data-product-unit="{{ $inventory->product->unit }}"
                                            data-quantity="{{ $inventory->quantity }}"
                                            data-branch-name="{{ $inventory->branch->name }}"
                                            class="inline-flex items-center rounded bg-red-100 px-3 py-1 text-xs font-medium text-red-800 hover:bg-red-200 transition-colors"
                                            title="Archive this product"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                            </svg>
                                            Archive
                                        </button>
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
            @can('inventory.archive')
                <a href="{{ route('inventory.archives') }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    View Archives
                </a>
            @endcan
        </div>

        <!-- Archive Confirmation Modal -->
        <div :class="archiveModal.open ? 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50' : 'hidden'" x-cloak>
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-gray-900">Archive Product</h3>
                    </div>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-500">
                        Are you sure you want to archive this product? This action will remove it from active stock and move it to the archive list.
                    </p>
                    <div class="mt-4 bg-gray-50 p-4 rounded-md">
                        <div class="text-sm">
                            <p><strong>Product:</strong> <span x-text="archiveModal.product?.name || 'Loading...'"></span></p>
                            <p><strong>Unit:</strong> <span x-text="archiveModal.product?.unit || 'Loading...'"></span></p>
                            <p><strong>Current Stock:</strong> <span x-text="archiveModal.product?.quantity ? archiveModal.product.quantity + ' ' + archiveModal.product.unit : 'Loading...'"></span></p>
                            <p><strong>Branch:</strong> <span x-text="archiveModal.product?.branch || 'Loading...'"></span></p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        @click="closeArchiveModal()"
                        :disabled="archiveModal.loading"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="confirmArchive()"
                        :disabled="archiveModal.loading"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 disabled:opacity-50"
                    >
                        <span x-show="!archiveModal.loading">Archive Product</span>
                        <span x-show="archiveModal.loading" x-cloak>
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Archiving...
                        </span>
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
        archiveModal: {
            open: false,
            loading: false,
            productId: null,
            product: null,
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

            const url = params.toString() ? `${this.baseRoute}?${params.toString()}` : this.baseRoute;
            window.location.href = url;
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

        async archiveProduct(event) {
            const button = event.target.closest('button');
            const productId = button.dataset.productId;
            const productName = button.dataset.productName;
            const productUnit = button.dataset.productUnit;
            const quantity = button.dataset.quantity;
            const branchName = button.dataset.branchName;
            
            this.archiveModal.productId = productId;
            this.archiveModal.product = {
                name: productName,
                unit: productUnit,
                quantity: quantity,
                branch: branchName,
            };
            this.archiveModal.open = true;
        },

        closeArchiveModal() {
            if (this.archiveModal.loading) return;
            this.archiveModal.open = false;
            this.archiveModal.productId = null;
            this.archiveModal.product = null;
        },

        async confirmArchive() {
            if (!this.archiveModal.productId || this.archiveModal.loading) return;

            this.archiveModal.loading = true;

            try {
                const response = await fetch(`{{ route('inventory.api.archive', ':id') }}`.replace(':id', this.archiveModal.productId), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (response.ok) {
                    this.showToast(data.message || 'Product archived successfully', 'success');
                    this.closeArchiveModal();
                    // Reload the page to update the table
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.showToast(data.message || 'Failed to archive product', 'error');
                }
            } catch (error) {
                console.error('Archive error:', error);
                this.showToast('An error occurred while archiving the product', 'error');
            } finally {
                this.archiveModal.loading = false;
            }
        },

        showToast(message, type = 'info') {
            // Simple toast implementation - you can enhance this
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-4 py-2 rounded shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.remove();
            }, 3000);
        },
    };
}
</script>


