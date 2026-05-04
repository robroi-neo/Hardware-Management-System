<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold leading-tight text-slate-900">Invoice History</h2>
    </x-slot>

    <div class="bg-white rounded shadow-sm p-6">
        <!-- Search & Filters -->
        <form method="GET" class="mb-6 flex flex-col md:flex-row items-start md:items-center gap-4">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Search by invoice ID, purchase ID, or supplier..."
                class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />

            @if($suppliers->count() >= 2)
            <x-filters.dropdown-filter
                :items="$suppliers"
                :selected="$filterSupplierId"
                route="purchasing.invoice-history"
                :params="['search' => $search, 'sort_by' => $sortBy, 'sort_dir' => $sortDir]"
                label="Filter by Supplier"
                filterName="supplier_id"
                displayField="supplier_name"
            />
            @endif

            <button type="submit" class="px-4 py-2 bg-indigo-900 text-white rounded hover:bg-indigo-800">
                Search
            </button>
        </form>

        <!-- Invoices Table -->
        <div class="border border-gray-200 rounded overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-gray-600 bg-gray-50">
                        <tr>
                            <x-table.sortable-header
                                label="Invoice ID"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="id"
                                route="purchasing.invoice-history"
                                :params="['search' => $search, 'supplier_id' => $filterSupplierId]"
                                align="left"
                            />
                            <x-table.sortable-header
                                label="Purchase ID"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="purchase_id"
                                route="purchasing.invoice-history"
                                :params="['search' => $search, 'supplier_id' => $filterSupplierId]"
                                align="left"
                            />
                            <x-table.sortable-header
                                label="Supplier"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="supplier_name"
                                route="purchasing.invoice-history"
                                :params="['search' => $search, 'supplier_id' => $filterSupplierId]"
                                align="left"
                            />
                            <x-table.sortable-header
                                label="Date Issued"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="date_issued"
                                route="purchasing.invoice-history"
                                :params="['search' => $search, 'supplier_id' => $filterSupplierId]"
                                align="left"
                            />
                            <x-table.sortable-header
                                label="Due Date"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="date_due"
                                route="purchasing.invoice-history"
                                :params="['search' => $search, 'supplier_id' => $filterSupplierId]"
                                align="left"
                            />
                            <x-table.sortable-header
                                label="Total Amount"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="total_amount"
                                route="purchasing.invoice-history"
                                :params="['search' => $search, 'supplier_id' => $filterSupplierId]"
                                align="right"
                            />
                            <th class="px-4 py-3">Items</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($invoices as $invoice)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono">#{{ $invoice->id }}</td>
                                <td class="px-4 py-3 font-mono">#{{ $invoice->purchase_id }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-medium">{{ $invoice->purchase->supplier->supplier_name }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $invoice->date_issued->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span @class([
                                        'px-2 py-1 rounded text-xs font-medium',
                                        'bg-red-100 text-red-800' => $invoice->date_due < now(),
                                        'bg-yellow-100 text-yellow-800' => $invoice->date_due >= now() && $invoice->date_due < now()->addDays(7),
                                        'bg-green-100 text-green-800' => $invoice->date_due >= now()->addDays(7),
                                    ])>
                                        {{ $invoice->date_due->format('M d, Y') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-medium">
                                    ₱{{ number_format($invoice->total_amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="text-gray-600">{{ $invoice->purchase->details_count ?? $invoice->purchase->details->count() }}</span>
                                </td>
                            </tr>
                        @empty
                            <x-table.empty-state colspan="7" message="No invoices found." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($invoices->count() > 0)
            <div class="mt-6">
                <x-table.pagination :paginator="$invoices" />
            </div>
        @endif
    </div>
</x-app-layout>
