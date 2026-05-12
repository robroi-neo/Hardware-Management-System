<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">Invoice History</h2>
    </x-slot>

    <x-card title="Invoice History" fullHeight>
        {{-- Search Form --}}
        <form method="GET" class="mb-4 flex flex-col sm:flex-row gap-3 items-end">
            {{-- Preserve filter/sort params as hidden inputs --}}
            <input type="hidden" name="supplier_id" value="{{ $filterSupplierId }}" />
            {{-- Search Input --}}
            <div class="flex-1 relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803 7.5 7.5 0 0 0 15.803 15.803Z" />
                    </svg>
                </div>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by ID or cashier..."
                    class="w-full rounded border-gray-200 pl-9 pr-4 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
            </div>

            {{-- Date From --}}
            <div class="flex flex-col">
                <span class="text-xs text-gray-500 mb-1">Date From</span>
                <input
                    type="date"
                    name="date_from"
                    value="{{ request('date_from') }}"
                    class="rounded border-gray-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    title="From date"
                />
            </div>

            {{-- Date To --}}
            <div class="flex flex-col">
                <span class="text-xs text-gray-500 mb-1">Date To</span>
                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="rounded border-gray-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    title="To date"
                />
            </div>

            <div class="flex flex-col">
                @if($suppliers->count() >= 2)
                    <x-filters.dropdown-filter
                        :items="$suppliers"
                        :selected="$filterSupplierId"
                        name="supplier_id"
                        label="Filter by Supplier"
                        displayField="supplier_name"
                        placeholder="All Suppliers"
                        onchange="this.form.submit()"
                    />
                @endif
            </div>
            {{-- Actions --}}
            <div class="flex gap-2">
                <button
                    type="submit"
                    class="px-4 py-2 rounded bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition-colors"
                >
                    Filter
                </button>
                @if(request()->hasAny(['search', 'payment_method', 'date_from', 'date_to']))
                    <a
                        href="{{ route('purchasing.invoice-history', array_filter(['sort_by' => request('sort_by'), 'sort_dir' => request('sort_dir')])) }}"
                        class="px-4 py-2 rounded border border-gray-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-colors"
                    >
                        Clear
                    </a>
                @endif
            </div>


        </form>



        <!-- Invoices Table -->
        <div class="border border-gray-200 rounded overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-gray-600 bg-indigo-50">
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
                            <th class="px-4 py-3 font-medium">Items</th>
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
    </x-card>
</x-app-layout>
