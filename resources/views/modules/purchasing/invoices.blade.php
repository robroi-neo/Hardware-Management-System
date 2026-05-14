<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">Invoice History</h2>
    </x-slot>

    <x-card title="Invoice History" fullHeight x-data="invoiceRefundHistory()">
        {{-- Toast Notifications --}}
        <div x-data="{ show: false, message: '', type: 'success' }"
             @show-toast.window="show = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => show = false, 3000)"
             x-show="show"
             x-cloak
             :class="type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'"
             class="fixed top-4 right-4 border rounded px-4 py-3 shadow-lg z-50">
            <span x-text="message"></span>
        </div>

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

        {{-- Active filter summary --}}
        <x-filters.active-summary
            :fields="['search', 'supplier_id', 'date_from', 'date_to']"
        />

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
                                :params="['search' => $search, 'supplier_id' => $filterSupplierId, 'date_from' => request('date_from'), 'date_to' => request('date_to')]"
                                align="left"
                            />
                            <x-table.sortable-header
                                label="Purchase ID"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="purchase_id"
                                route="purchasing.invoice-history"
                                :params="['search' => $search, 'supplier_id' => $filterSupplierId, 'date_from' => request('date_from'), 'date_to' => request('date_to')]"
                                align="left"
                            />
                            <x-table.sortable-header
                                label="Supplier"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="supplier_name"
                                route="purchasing.invoice-history"
                                :params="['search' => $search, 'supplier_id' => $filterSupplierId, 'date_from' => request('date_from'), 'date_to' => request('date_to')]"
                                align="left"
                            />
                            <x-table.sortable-header
                                label="Date Issued"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="date_issued"
                                route="purchasing.invoice-history"
                                :params="['search' => $search, 'supplier_id' => $filterSupplierId, 'date_from' => request('date_from'), 'date_to' => request('date_to')]"
                                align="left"
                            />
                            <x-table.sortable-header
                                label="Due Date"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="date_due"
                                route="purchasing.invoice-history"
                                :params="['search' => $search, 'supplier_id' => $filterSupplierId, 'date_from' => request('date_from'), 'date_to' => request('date_to')]"
                                align="left"
                            />
                            <x-table.sortable-header
                                label="Total Amount"
                                :sortBy="$sortBy"
                                :sortDir="$sortDir"
                                column="total_amount"
                                route="purchasing.invoice-history"
                                :params="['search' => $search, 'supplier_id' => $filterSupplierId, 'date_from' => request('date_from'), 'date_to' => request('date_to')]"
                                align="right"
                            />
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Items</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
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
                                    {{ $invoice->date_issued?->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span @class([
                                        'px-2 py-1 rounded text-xs font-medium',
                                        'bg-red-100 text-red-800' => $invoice->date_due < now(),
                                        'bg-yellow-100 text-yellow-800' => $invoice->date_due >= now() && $invoice->date_due < now()->addDays(7),
                                        'bg-green-100 text-green-800' => $invoice->date_due >= now()->addDays(7),
                                    ])>
                                        {{ $invoice->date_due?->format('M d, Y') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-medium">
                                    ₱{{ number_format($invoice->total_amount, 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($invoice->refunded)
                                        <span class="inline-flex items-center rounded bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                                            Refunded
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                                            Active
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="text-gray-600">{{ $invoice->purchase->details_count ?? $invoice->purchase->details->count() }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @can('purchases.refund')
                                        @if(! $invoice->refunded)
                                            <button
                                                type="button"
                                                @click="prepareRefund(@js([
                                                    'id' => $invoice->id,
                                                    'purchase_id' => $invoice->purchase_id,
                                                    'supplier_name' => $invoice->purchase->supplier->supplier_name,
                                                    'date_issued' => $invoice->date_issued?->format('M d, Y'),
                                                    'date_due' => $invoice->date_due?->format('M d, Y'),
                                                    'total_amount' => $invoice->total_amount,
                                                    'items_count' => $invoice->purchase->details_count ?? $invoice->purchase->details->count(),
                                                    'refunded' => (bool) $invoice->refunded,
                                                ]))"
                                                class="inline-flex items-center rounded bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700"
                                            >
                                                Refund
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-400">Already refunded</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">No actions</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <x-table.empty-state colspan="9" message="No invoices found." />
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

        <x-modal name="invoice-refund-confirm" maxWidth="md" focusable>
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Confirm Invoice Refund</h3>
                    <button @click="$dispatch('close-modal', 'invoice-refund-confirm')" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mb-6 space-y-2 text-sm text-slate-600">
                    <p>
                        Are you sure you want to refund Invoice #<span class="font-semibold text-slate-900" x-text="selectedInvoice?.id"></span>?
                    </p>
                    <p>
                        Purchase #: <span class="font-semibold text-slate-900" x-text="selectedInvoice?.purchase_id"></span>
                    </p>
                    <p>
                        Supplier: <span class="font-semibold text-slate-900" x-text="selectedInvoice?.supplier_name"></span>
                    </p>
                    <p class="text-red-600 font-medium">
                        This will only reverse inventory. Product capital will not be changed.
                    </p>
                </div>

                <p x-show="refundError" class="mb-4 text-sm text-red-600" x-text="refundError"></p>

                <div class="flex items-center justify-end gap-3">
                    <button
                        @click="$dispatch('close-modal', 'invoice-refund-confirm')"
                        class="px-4 py-2 rounded border border-gray-300 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                        :disabled="refunding"
                    >
                        Cancel
                    </button>
                    <button
                        @click="processRefund()"
                        :disabled="refunding"
                        class="px-4 py-2 rounded bg-red-600 text-white text-sm font-medium hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span x-show="!refunding">Confirm Refund</span>
                        <span x-show="refunding">Processing...</span>
                    </button>
                </div>
            </div>
        </x-modal>
    </x-card>

    <script>
        function invoiceRefundHistory() {
            return {
                refunding: false,
                selectedInvoice: null,
                refundError: '',
                refundUrlTemplate: @js(route('purchasing.invoices.refund', ['invoice' => '__INVOICE__'])),

                prepareRefund(invoice) {
                    this.selectedInvoice = invoice;
                    this.refundError = '';
                    this.$dispatch('open-modal', 'invoice-refund-confirm');
                },

                async processRefund() {
                    if (!this.selectedInvoice || this.refunding) return;

                    this.refunding = true;
                    this.refundError = '';

                    try {
                        const url = this.refundUrlTemplate.replace('__INVOICE__', this.selectedInvoice.id);
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });

                        const data = await res.json();

                        if (data.success) {
                            this.$dispatch('show-toast', {
                                message: data.message,
                                type: 'success',
                            });

                            this.$dispatch('close-modal', 'invoice-refund-confirm');

                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            this.refundError = data.message || 'Error processing refund';
                            this.$dispatch('show-toast', {
                                message: this.refundError,
                                type: 'error',
                            });
                        }
                    } catch (error) {
                        console.error(error);
                        this.refundError = 'Error processing refund';
                        this.$dispatch('show-toast', {
                            message: this.refundError,
                            type: 'error',
                        });
                    } finally {
                        this.refunding = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>
