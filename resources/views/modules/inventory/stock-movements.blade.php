<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">Stock Movements</h2>
    </x-slot>

    <x-card>
        <!-- Filters Section -->
        <div class="mb-6">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">Filters</h3>

            <!-- Filters Section -->
            <div class="mb-6">
                <form method="GET" action="{{ route('inventory.stock-movements') }}">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
                        
                        <!-- Search Input -->
                        <div class="w-full flex-1">
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                </div>
                                <input
                                    type="text"
                                    name="search"
                                    value="{{ $search }}"
                                    placeholder="Search Product name or ID..."
                                    class="text-sm placeholder-gray-400 bg-white-100 border border-gray-200 rounded px-3 py-2 pr-8 w-full focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                />
                            </div>
                        </div>

                        <!-- Date From -->
                        <div class="w-full flex-shrink-0 lg:w-40">
                            <label class="mb-1 block text-xs font-medium text-slate-500">Date From</label>
                            <input
                                type="date"
                                name="date_from"
                                value="{{ $dateFrom }}"
                                class="block w-full rounded border border-slate-300 px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Date To -->
                        <div class="w-full flex-shrink-0 lg:w-40">
                            <label class="mb-1 block text-xs font-medium text-slate-500">Date To</label>
                            <input
                                type="date"
                                name="date_to"
                                value="{{ $dateTo }}"
                                class="block w-full rounded border border-slate-300 px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Movement Type -->
                        <div class="w-full flex-shrink-0 lg:w-44">
                            <label class="mb-1 block text-xs font-medium text-slate-500">Movement Type</label>
                            <select
                                name="type"
                                class="block w-full rounded border border-slate-300 px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            >
                                <option value="">All Types</option>
                                <option value="in" {{ $filterType === 'in' ? 'selected' : '' }}>Stock In</option>
                                <option value="out" {{ $filterType === 'out' ? 'selected' : '' }}>Stock Out</option>
                                <option value="adjustment" {{ $filterType === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                            </select>
                        </div>

                        <!-- Filter by Branch (Admin Only) -->
                        @if($isAdmin)
                            <div class="w-full flex-shrink-0 lg:w-44">
                                <label class="mb-1 block text-xs font-medium text-slate-500">Filter by Branch</label>
                                <select
                                    name="branch_id"
                                    class="block w-full rounded border border-slate-300 px-4 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                >
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $filterBranchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Filter Button -->
                        <div class="w-full flex-shrink-0 lg:w-auto">
                            <button type="submit" class="w-full rounded bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Filter
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <!-- Movements Table -->
         <div class="border border-gray-200 rounded overflow-hidden">
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
        </div>

        <!-- Pagination -->
        <x-table.pagination :paginator="$movements" />
    </x-card>
</x-app-layout>
