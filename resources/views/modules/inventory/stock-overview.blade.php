<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold leading-tight text-slate-900">Stock Overview</h2>
    </x-slot>

    <section class="rounded-xl border border-slate-200 bg-white p-6">
        <!-- Search Bar & Branch Filter -->
        <div class="mb-6 flex flex-col gap-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex-1">
                    <form method="GET" action="{{ route('inventory.overview') }}" class="flex gap-2">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Search by product name or unit..."
                            class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        />
                        <button
                            type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            Search
                        </button>
                        @if($search)
                            <a
                                href="{{ route('inventory.overview') }}"
                                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                Clear
                            </a>
                        @endif
                    </form>
                </div>

                @if($isAdmin)
                    <x-filters.branch-select
                        :branches="$allBranches"
                        :selected="$filterBranchId"
                        route="inventory.overview"
                        :params="['search' => $search, 'sort_by' => $sortBy, 'sort_dir' => $sortDir]"
                        label="Filter by Branch"
                    />
                @endif
            </div>
        </div>

        <!-- Stats Summary -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm text-slate-600">Total Products</p>
                <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $inventories->total() }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm text-slate-600">Total Value</p>
                <p class="mt-1 text-2xl font-semibold text-slate-900">
                    ₱{{ number_format($inventories->sum(fn($inv) => $inv->quantity * $inv->product->capital), 2) }}
                </p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm text-slate-600">Low Stock Items</p>
                <p class="mt-1 text-2xl font-semibold text-amber-600">
                    {{ $inventories->filter(fn($inv) => $inv->quantity < 5)->count() }}
                </p>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">ID</th>
                        <x-table.sortable-header
                            label="Product Name"
                            :sortBy="$sortBy"
                            :sortDir="$sortDir"
                            column="name"
                            route="inventory.overview"
                            :params="['search' => $search]"
                        />
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Unit</th>
                        <x-table.sortable-header
                            label="Quantity"
                            :sortBy="$sortBy"
                            :sortDir="$sortDir"
                            column="quantity"
                            route="inventory.overview"
                            :params="['search' => $search]"
                            align="right"
                        />
                        <th class="px-4 py-3 text-right font-semibold text-slate-700">Unit Cost</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-700">Total Value</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Branch</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventories as $inventory)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $inventory->product_id }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $inventory->product->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $inventory->product->unit }}</td>
                            <td class="px-4 py-3 text-right text-slate-700 font-semibold">{{ number_format($inventory->quantity, 2) }}</td>
                            <td class="px-4 py-3 text-right text-slate-600">₱{{ number_format($inventory->product->capital, 2) }}</td>
                            <td class="px-4 py-3 text-right text-slate-700 font-semibold">₱{{ number_format($inventory->quantity * $inventory->product->capital, 2) }}</td>
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
                        </tr>
                    @empty
                        <x-table.empty-state
                            :colspan="8"
                            :message="$search ? 'No inventory records found. Try adjusting your search filters.' : 'No inventory records found.'"
                        />
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <x-table.pagination :paginator="$inventories" />

        <!-- Action Buttons -->
        <div class="mt-6 flex flex-wrap gap-3">
            @can('inventory.update')
                <a href="{{ route('inventory.manual-stock-in') }}" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                    + Stock In
                </a>
                <a href="{{ route('inventory.stock-out') }}" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    - Stock Out
                </a>
            @endcan
            @can('inventory.view-movements')
                <a href="{{ route('inventory.stock-movements') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    View Movements
                </a>
            @endcan
        </div>
    </section>
</x-app-layout>
