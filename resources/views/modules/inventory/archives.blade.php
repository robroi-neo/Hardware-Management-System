<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">Inventory Archives</h2>
    </x-slot>

    <x-card>
        <!-- Navigation Tabs -->
        <div class="mb-6">
            <nav class="flex space-x-1">
                <a href="{{ route('inventory.overview') }}" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 border-b-2 border-transparent hover:border-slate-300">
                    Stock Overview
                </a>
                <a href="{{ route('inventory.archives') }}" class="px-4 py-2 text-sm font-medium text-slate-900 border-b-2 border-blue-500">
                    Archive List
                </a>
            </nav>
        </div>

        <!-- Search and Filters -->
        <div class="mb-6 space-y-4" x-data="archiveFilters()">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex-1 max-w-md">
                    <input
                        type="text"
                        x-model="search"
                        @input.debounce.300ms="applyFilters"
                        placeholder="Search archived products..."
                        class="w-full rounded border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    />
                </div>

                @if($isAdmin)
                    <div class="flex gap-2">
                        <select
                            x-model="branchId"
                            @change="applyFilters"
                            class="rounded border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        >
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>

        <!-- Table -->
        <div class="border border-gray-200 rounded overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-indigo-100">
                            <x-table.sortable-header
                                label="ID"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="id"
                                route="inventory.archives"
                                :params="['search' => $search, 'branch_id' => $filterBranchId]"
                            />
                            <x-table.sortable-header
                                label="Product Name"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="name"
                                route="inventory.archives"
                                :params="['search' => $search, 'branch_id' => $filterBranchId]"
                            />
                            <x-table.sortable-header
                                label="Unit"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="unit"
                                route="inventory.archives"
                                :params="['search' => $search, 'branch_id' => $filterBranchId]"
                            />
                            <x-table.sortable-header
                                label="Cost"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="capital"
                                route="inventory.archives"
                                :params="['search' => $search, 'branch_id' => $filterBranchId]"
                                align="right"
                            />
                            <x-table.sortable-header
                                label="Date Archived"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="archived_at"
                                route="inventory.archives"
                                :params="['search' => $search, 'branch_id' => $filterBranchId]"
                            />
                            <th class="px-4 py-3 text-left font-medium text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($archivedProducts as $product)
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $product->id }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $product->unit }}</td>
                                <td class="px-4 py-3 text-right text-slate-600">₱{{ number_format($product->capital, 2) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $product->archived_at ? $product->archived_at->format('M d, Y H:i') : 'N/A' }}</td>
                                <td class="px-4 py-3">
                                    <button
                                        @click="unarchiveProduct({{ $product->id }})"
                                        class="inline-flex items-center rounded bg-green-100 px-3 py-1 text-xs font-medium text-green-800 hover:bg-green-200 transition-colors"
                                        title="Restore this product"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Unarchive
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty-state
                                :colspan="6"
                                :message="$search ? 'No archived products found. Try adjusting your search.' : 'No archived products found.'"
                            />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <x-table.pagination :paginator="$archivedProducts" />
    </x-card>

    @push('scripts')
    <script>
        function archiveFilters() {
            return {
                search: '{{ $search }}',
                branchId: '{{ $filterBranchId }}',

                applyFilters() {
                    const params = new URLSearchParams();

                    if (this.search.trim()) {
                        params.set('search', this.search.trim());
                    }

                    if (this.branchId) {
                        params.set('branch_id', this.branchId);
                    }

                    const url = params.toString() ? `{{ route('inventory.archives') }}?${params.toString()}` : '{{ route('inventory.archives') }}';
                    window.location.href = url;
                },

                async unarchiveProduct(productId) {
                    if (!confirm('Are you sure you want to unarchive this product? It will be restored to active stock.')) {
                        return;
                    }

                    try {
                        const response = await fetch(`{{ route('inventory.api.unarchive', ':id') }}`.replace(':id', productId), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showToast(data.message || 'Product unarchived successfully', 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            this.showToast(data.message || 'Failed to unarchive product', 'error');
                        }
                    } catch (error) {
                        console.error('Unarchive error:', error);
                        this.showToast('An error occurred while unarchiving the product', 'error');
                    }
                },

                showToast(message, type = 'info') {
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
    @endpush
</x-app-layout>
