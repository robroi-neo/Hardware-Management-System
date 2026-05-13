<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">Stock Movements</h2>
    </x-slot>

    <x-card>
        <!-- Filters Section -->
        <div class="mb-6">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">Filters</h3>

            <form method="GET" action="{{ route('inventory.stock-movements') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Search Product</label>
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Product name or ID..."
                            class="mt-1 block w-full rounded border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Movement Type -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Movement Type</label>
                        <select
                            onchange="this.form.submit()"
                            name="type"
                            class="mt-1 block w-full rounded border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        >
                            <option value="">All Types</option>
                            <option value="in" {{ $filterType === 'in' ? 'selected' : '' }}>Stock In</option>
                            <option value="out" {{ $filterType === 'out' ? 'selected' : '' }}>Stock Out</option>
                            <option value="adjustment" {{ $filterType === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                        </select>
                    </div>
                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700">From Date</label>
                        <input
                            type="date"
                            name="date_from"
                            value="{{ $dateFrom }}"
                            class="mt-1 block w-full rounded border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        />
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700">To Date</label>
                        <input
                            type="date"
                            name="date_to"
                            value="{{ $dateTo }}"
                            class="mt-1 block w-full rounded border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        />
                    </div>
                    <!-- Branch Filter (Admin Only) -->
                    @if($isAdmin)
                        <div class="pt-2">
                            <x-filters.dropdown-filter
                                :items="$branches"
                                :selected="$filterBranchId"
                                name="branch_id"
                                label="Filter by Branch"
                                valueField="id"
                                displayField="name"
                                :autoSubmit="true"
                            />
                        </div>
                    @endif
                </div>


            </form>
        </div>

        <!-- Movements Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-indigo-100">
                        <x-table.sortable-header
                            label="Date & Time"
                            :sortBy="$sortBy"
                            :sortDir="$sortDir"
                            column="created_at"
                            route="inventory.stock-movements"
                            :params="['search' => $search, 'type' => $filterType, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'branch_id' => $filterBranchId]"
                        />
                        <x-table.sortable-header
                            label="Product"
                            :sortBy="$sortBy"
                            :sortDir="$sortDir"
                            column="product_id"
                            route="inventory.stock-movements"
                            :params="['search' => $search, 'type' => $filterType, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'branch_id' => $filterBranchId]"
                        />
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Unit</th>
                        <x-table.sortable-header
                            label="Type"
                            :sortBy="$sortBy"
                            :sortDir="$sortDir"
                            column="type"
                            route="inventory.stock-movements"
                            :params="['search' => $search, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'branch_id' => $filterBranchId]"
                            align="left"
                        />
                        <x-table.sortable-header
                            label="Quantity Change"
                            :sortBy="$sortBy"
                            :sortDir="$sortDir"
                            column="quantity_change"
                            route="inventory.stock-movements"
                            :params="['search' => $search, 'type' => $filterType, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'branch_id' => $filterBranchId]"
                            align="center"
                        />
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Branch</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-600">{{ $movement->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3 text-slate-900 font-medium">#{{ $movement->product_id }} - {{ $movement->product->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $movement->product->unit }}</td>
                            <td class="px-4 py-3 text-left">
                                @if($movement->type === 'in')
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                        Stock In
                                    </span>
                                @elseif($movement->type === 'out')
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800">
                                        Stock Out
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-800">
                                        Adjustment
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-semibold">
                                <span class="{{ $movement->quantity_change > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $movement->quantity_change > 0 ? '+' : '' }}{{ number_format($movement->quantity_change, 2) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $movement->branch->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $movement->user?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <x-table.empty-state
                            :colspan="7"
                            message="No movements found. Adjust your filters or start with stock-in operations."
                        />
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <x-table.pagination :paginator="$movements" />
    </x-card>
</x-app-layout>
