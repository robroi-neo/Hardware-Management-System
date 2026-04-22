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

                @if($isAdmin && $allBranches->count() > 1)
                    <form method="GET" action="{{ route('inventory.overview') }}" class="flex gap-2 sm:w-auto">
                        <!-- Preserve search and sort params -->
                        <input type="hidden" name="search" value="{{ $search }}" />
                        <input type="hidden" name="sort_by" value="{{ $sortBy }}" />
                        <input type="hidden" name="sort_dir" value="{{ $sortDir }}" />

                        <select
                            name="branch_id"
                            onchange="this.form.submit()"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        >
                            <option value="">All Branches</option>
                            @foreach($allBranches as $branch)
                                <option value="{{ $branch->id }}" {{ $filterBranchId == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
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
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">
                            <a href="{{ route('inventory.overview', ['sort_by' => 'name', 'sort_dir' => $sortBy === 'name' && $sortDir === 'asc' ? 'desc' : 'asc', 'search' => $search]) }}" class="flex items-center gap-1 hover:text-blue-600">
                                Product Name
                                @if($sortBy === 'name')
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDir === 'asc')
                                            <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                        @else
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l6 6a1 1 0 01-1.414 1.414L11 5.414V15a1 1 0 11-2 0V5.414L5.707 10.707a1 1 0 01-1.414-1.414l6-6A1 1 0 0110 3z" clip-rule="evenodd" />
                                        @endif
                                    </svg>
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Unit</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-700">
                            <a href="{{ route('inventory.overview', ['sort_by' => 'quantity', 'sort_dir' => $sortBy === 'quantity' && $sortDir === 'asc' ? 'desc' : 'asc', 'search' => $search]) }}" class="flex items-center justify-end gap-1 hover:text-blue-600">
                                Quantity
                                @if($sortBy === 'quantity')
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDir === 'asc')
                                            <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                        @else
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l6 6a1 1 0 01-1.414 1.414L11 5.414V15a1 1 0 11-2 0V5.414L5.707 10.707a1 1 0 01-1.414-1.414l6-6A1 1 0 0110 3z" clip-rule="evenodd" />
                                        @endif
                                    </svg>
                                @endif
                            </a>
                        </th>
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
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                No inventory records found. @if($search) Try adjusting your search filters. @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($inventories->hasPages())
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-slate-600">
                    Showing <strong>{{ $inventories->firstItem() }}</strong> to <strong>{{ $inventories->lastItem() }}</strong>
                    of <strong>{{ $inventories->total() }}</strong> results
                </div>
                <nav class="flex gap-1">
                    @if($inventories->onFirstPage())
                        <span class="rounded border border-slate-300 px-3 py-2 text-sm text-slate-400">Previous</span>
                    @else
                        <a href="{{ $inventories->previousPageUrl() }}" class="rounded border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">Previous</a>
                    @endif

                    @foreach($inventories->getUrlRange(1, $inventories->lastPage()) as $page => $url)
                        @if($page == $inventories->currentPage())
                            <span class="rounded border border-blue-500 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="rounded border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($inventories->hasMorePages())
                        <a href="{{ $inventories->nextPageUrl() }}" class="rounded border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">Next</a>
                    @else
                        <span class="rounded border border-slate-300 px-3 py-2 text-sm text-slate-400">Next</span>
                    @endif
                </nav>
            </div>
        @endif

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
