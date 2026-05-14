<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">POS Transactions</h2>
    </x-slot>

    {{-- x-data moved here so openDetail() is accessible to both the table rows and the modal --}}
    <x-card title="Transaction History" fullHeight x-data="transactionDetail()">

        {{-- Toast Notifications --}}
        <div x-data="{ show: false, message: '', type: 'success' }"
             @show-toast.window="show = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => show = false, 3000)"
             x-show="show"
             :class="type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'"
             class="fixed top-4 right-4 border rounded px-4 py-3 shadow-lg z-50">
            <span x-text="message"></span>
        </div>

        {{-- Search & Filter Bar --}}
        <form method="GET" action="{{ route('pos.transactions') }}" class="mb-4 flex flex-col sm:flex-row gap-3 items-end">
            {{-- Preserve existing sort state --}}
            @if(request('sort_by'))
                <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
            @endif
            @if(request('sort_dir'))
                <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">
            @endif

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
                        href="{{ route('pos.transactions', array_filter(['sort_by' => request('sort_by'), 'sort_dir' => request('sort_dir')])) }}"
                        class="px-4 py-2 rounded border border-gray-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-colors"
                    >
                        Clear
                    </a>
                @endif
            </div>
        </form>

        {{-- Active filter summary --}}
        <x-filters.active-summary
            :fields="['search', 'payment_method', 'date_from', 'date_to']"
        />

        <div class="border border-gray-200 rounded overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-indigo-100 text-left text-slate-700">
                    <tr>
                        <x-table.sortable-header label="ID"             :sortBy="$sortBy" :sortDir="$sortDir" column="id"             route="pos.transactions" />
                        <x-table.sortable-header label="Date"           :sortBy="$sortBy" :sortDir="$sortDir" column="date"           route="pos.transactions" />
                        <x-table.sortable-header label="Total Amount"   :sortBy="$sortBy" :sortDir="$sortDir" column="total_amount"   route="pos.transactions" />
                        <x-table.sortable-header label="Payment Method" :sortBy="$sortBy" :sortDir="$sortDir" column="payment_method" route="pos.transactions" />
                        <th class="px-4 py-3 font-medium">Processed By</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y border-t border-slate-200">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-slate-50 cursor-pointer" @click="openDetail({{ $transaction->id }})">
                            <td class="px-4 py-3 text-slate-900">#{{ $transaction->id }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->date->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3 text-slate-900 font-medium">₱{{ number_format($transaction->total_amount, 2) }}</td>
                            <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        {{ ucfirst($transaction->payment_method) }}
                                    </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->user->name ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <x-table.empty-state :colspan="5" message="No transactions found." />
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <x-table.pagination :paginator="$transactions" />

        {{-- Modal is now inside the same x-data scope --}}
        <x-modal name="transaction-detail" maxWidth="2xl" focusable>
            <div class="p-6 text-sm text-slate-800">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">Transaction #<span x-text="detail.id"></span></h3>
                        <template x-if="detail.refunded">
                            <p class="text-xs text-amber-600 mt-1">✓ Refunded on <span x-text="detail.refunded_at"></span> by <span x-text="detail.refunded_by"></span></p>
                        </template>
                    </div>
                    <button
                        @click="$dispatch('close-modal', 'transaction-detail')"
                        class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800 transition-colors"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div x-show="loading" class="py-8 text-center text-slate-400">Loading...</div>

                <template x-if="!loading && detail.id">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-2 text-sm border-b pb-4">
                            <div><span class="text-slate-500">Date:</span> <span x-text="detail.date"></span></div>
                            <div><span class="text-slate-500">Payment:</span> <span x-text="detail.payment_method"></span></div>
                            <div><span class="text-slate-500">Cashier:</span> <span x-text="detail.cashier"></span></div>
                            <div><span class="text-slate-500">Branch:</span> <span x-text="detail.branch_name"></span></div>
                        </div>

                        <table class="min-w-full text-sm">
                            <thead class="text-left text-slate-500 border-b">
                            <tr>
                                <th class="py-2 font-medium">Product</th>
                                <th class="py-2 font-medium">Unit</th>
                                <th class="py-2 font-medium text-right">Qty</th>
                                <th class="py-2 font-medium text-right">Capital</th>
                                <th class="py-2 font-medium text-right">Markup</th>
                                <th class="py-2 font-medium text-right">Price</th>
                                <th class="py-2 font-medium text-right">Subtotal</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y">
                            <template x-for="item in detail.items" :key="item.product_id">
                                <tr>
                                    <td class="py-2" x-text="item.product_name"></td>
                                    <td class="py-2 text-slate-500" x-text="item.unit"></td>
                                    <td class="py-2 text-right" x-text="item.quantity"></td>
                                    <td class="py-2 text-right">₱<span x-text="item.cost.toFixed(2)"></span></td>
                                    <td class="py-2 text-right">₱<span x-text="item.markup.toFixed(2)"></span></td>
                                    <td class="py-2 text-right">₱<span x-text="item.unit_price.toFixed(2)"></span></td>
                                    <td class="py-2 text-right font-medium">₱<span x-text="item.subtotal.toFixed(2)"></span></td>
                                </tr>
                            </template>
                            </tbody>
                        </table>

                        <div class="flex justify-between items-end border-t pt-3">
                            <div class="flex-1"></div>
                            <div class="font-semibold text-base">
                                Total: ₱<span x-text="detail.total_amount.toFixed(2)"></span>
                            </div>
                        </div>

                        {{-- Modal Footer Actions --}}
                        <div class="border-t pt-4 mt-6 flex items-center justify-end gap-3">
                            {{-- Cancel / Close Button (Always visible) --}}
                            <button
                                type="button"
                                @click="$dispatch('close-modal', 'transaction-detail')"
                                class="px-4 py-2 rounded border border-gray-300 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                            >
                                Cancel
                            </button>

                            {{-- Refund Button (Conditionally visible) --}}
                            @can('sales.refund')
                            <template x-if="!detail.refunded">
                                <button
                                    type="button"
                                    @click="$dispatch('open-modal', 'refund-confirm')"
                                    :disabled="refunding"
                                    class="px-4 py-2 rounded bg-red-600 text-white text-sm font-medium hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    Refund Transaction
                                </button>
                            </template>
                            @endcan
                        </div>
                    </div>
                </template>
            </div>
        </x-modal>

        <!-- Refund Confirmation Modal -->
        <x-modal name="refund-confirm" maxWidth="md" focusable>
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Confirm Refund</h3>
                    <button @click="$dispatch('close-modal', 'refund-confirm')" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mb-6">
                    <p class="text-sm text-slate-600">
                        Are you sure you want to refund Transaction #<span x-text="detail.id" class="font-semibold text-slate-900"></span>?
                    </p>
                    <p class="text-sm text-red-600 mt-2 font-medium">
                        Warning: This action cannot be undone and will restore the inventory items.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button
                        @click="$dispatch('close-modal', 'refund-confirm')"
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
        function transactionDetail() {
            return {
                detail: {},
                loading: false,
                refunding: false,

                async openDetail(id) {
                    this.detail = {};
                    this.loading = true;
                    this.$dispatch('open-modal', 'transaction-detail');

                    try {
                        const res = await fetch(`/pos/transactions/${id}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });
                        this.detail = await res.json();
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.loading = false;
                    }
                },

                async processRefund() {
                    // No more browser confirm() needed!
                    this.refunding = true;
                    const saleId = this.detail.id; // Gets the ID from the currently open transaction

                    try {
                        const res = await fetch(`/pos/transactions/${saleId}/refund`, {
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
                            // Show success toast
                            this.$dispatch('show-toast', {
                                message: data.message,
                                type: 'success'
                            });

                            // Update detail to show refunded status
                            this.detail.refunded = true;
                            this.detail.refunded_at = new Date().toLocaleString();
                            this.detail.refunded_by = '{{ auth()->user()->name }}';

                            // Close the confirmation modal immediately
                            this.$dispatch('close-modal', 'refund-confirm');

                            // Close the main detail modal after 2 seconds
                            setTimeout(() => {
                                this.$dispatch('close-modal', 'transaction-detail');
                                // Reload the page to show updated transaction list
                                window.location.reload();
                            }, 2000);
                        } else {
                            this.$dispatch('show-toast', {
                                message: data.message || 'Error processing refund',
                                type: 'error'
                            });
                        }
                    } catch (e) {
                        console.error(e);
                        this.$dispatch('show-toast', {
                            message: 'Error processing refund',
                            type: 'error'
                        });
                    } finally {
                        this.refunding = false;
                    }
                }
            };
        }
    </script>
</x-app-layout>
