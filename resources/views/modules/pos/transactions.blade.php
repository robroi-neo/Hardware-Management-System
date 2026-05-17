<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">POS Transactions</h2>
    </x-slot>

    {{-- x-data moved here so openDetail() is accessible to both the table rows and the modal --}}
    <x-card title="Transaction History" fullHeight x-data="transactionDetail()">

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
                </div>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by ID or cashier..."
                    class="text-sm placeholder-gray-400 bg-white-100 border border-gray-200 rounded px-3 py-2 pr-8 w-full focus:outline-none focus:ring-2 focus:ring-indigo-200"
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

            <x-filters.dropdown-filter
                :items="$allBranches"
                :selected="$filterBranchId"
                name="branch_id"
                label="Filter by Branch"
                placeholder="All Branches"
                valueField="id"
                displayField="name"
                :autoSubmit="true"
            />
            {{-- Actions --}}
            <div class="flex gap-2">
                <button
                    type="submit"
                    class="px-4 py-2 rounded bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition-colors"
                >
                    Filter
                </button>
            </div>
        </form>

        {{-- Active filter summary --}}
        <x-filters.active-summary
            :fields="['search', 'payment_method', 'date_from', 'date_to']"
        />

        <div id="transactions-table-container" class="border border-gray-200 rounded overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-indigo-100 text-left text-slate-700">
                    <tr>
                        <x-table.sortable-header label="ID"             :sortBy="$sortBy" :sortDir="$sortDir" column="id"             route="pos.transactions" />
                        <x-table.sortable-header label="Date"           :sortBy="$sortBy" :sortDir="$sortDir" column="date"           route="pos.transactions" />
                        <x-table.sortable-header label="Total Amount"   :sortBy="$sortBy" :sortDir="$sortDir" column="total_amount"   route="pos.transactions" />
                        <x-table.sortable-header label="Payment Method" :sortBy="$sortBy" :sortDir="$sortDir" column="payment_method" route="pos.transactions" />
                        <th class="px-4 py-3 font-medium">Processed By</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Action</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y border-t border-slate-200">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-900 cursor-pointer" @click="openDetail({{ $transaction->id }})">#{{ $transaction->id }}</td>
                            <td class="px-4 py-3 text-slate-600 cursor-pointer" @click="openDetail({{ $transaction->id }})">{{ $transaction->date->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3 text-slate-900 font-medium cursor-pointer" @click="openDetail({{ $transaction->id }})">₱{{ number_format($transaction->total_amount, 2) }}</td>
                            <td class="px-4 py-3 cursor-pointer" @click="openDetail({{ $transaction->id }})">
                                    <span class="rounded-xl inline-flex items-center rounded bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        {{ ucfirst($transaction->payment_method) }}
                                    </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 cursor-pointer" @click="openDetail({{ $transaction->id }})">{{ $transaction->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3 cursor-pointer" @click="openDetail({{ $transaction->id }})">
                                @if($transaction->refunded)
                                    <span class="inline-flex rounded-xl items-center rounded bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                                        Refunded
                                    </span>
                                @else
                                    <span class="inline-flex rounded-xl items-center rounded bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                                        Active
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 cursor-pointer text-left" @click="openDetail({{ $transaction->id }})">
                                @can('sales.refund')
                                    @if(!$transaction->refunded)
                                        <button
                                            type="button"
                                            @click.stop="openRefund({{ $transaction->id }})"
                                            class="text-slate-400 hover:text-red-600 transition-colors"
                                            title="Refund Transaction"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-banknote-arrow-up-icon lucide-banknote-arrow-up"><path d="M12 18H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5"/><path d="M18 12h.01"/><path d="M19 22v-6"/><path d="m22 19-3-3-3 3"/><path d="M6 12h.01"/><circle cx="12" cy="12" r="2"/></svg>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center text-slate-500">
                                            —
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <x-table.empty-state :colspan="7" message="No transactions found." />
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
                            <button
                                type="button"
                                @click="$dispatch('close-modal', 'transaction-detail')"
                                class="px-4 py-2 rounded border border-gray-300 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                            >
                                Close
                            </button>
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
                        Are you sure you want to refund Transaction #<span x-text="refundId" class="font-semibold text-slate-900"></span>?
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
                refundId: null,

                // 1. STANDARD VANILLA TOAST
                showToast(message, type = 'success') {
                    const toast = document.createElement('div');

                    toast.className = `fixed bottom-6 right-6 px-6 py-4 rounded border shadow-2xl z-[99999] font-medium text-sm transition-all duration-300 transform translate-y-0 opacity-100 flex items-center gap-3 ${
                        type === 'success' ? 'bg-green-50 border-green-200 text-green-800' :
                        type === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-800' :
                        'bg-red-50 border-red-200 text-red-800'
                    }`;

                    let icon = '';
                    if (type === 'success') {
                        icon = `<svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                    } else if (type === 'warning') {
                        icon = `<svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" /></svg>`;
                    } else {
                        icon = `<svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                    }

                    toast.innerHTML = `${icon} <span>${message}</span>`;
                    document.body.appendChild(toast);

                    setTimeout(() => {
                        toast.classList.remove('translate-y-0', 'opacity-100');
                        toast.classList.add('translate-y-4', 'opacity-0');
                        setTimeout(() => toast.remove(), 300);
                    }, 3000);
                },

                // 2. SEAMLESS BACKGROUND REFRESH
                async refreshTable() {
                    try {
                        const response = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        const html = await response.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        document.querySelector('#transactions-table-container').innerHTML = doc.querySelector('#transactions-table-container').innerHTML;
                    } catch (error) {
                        console.error('Seamless refresh failed, falling back to hard reload.', error);
                        window.location.reload();
                    }
                },

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

                openRefund(id) {
                    this.refundId = id;
                    this.$dispatch('open-modal', 'refund-confirm');
                },

                async processRefund() {
                    this.refunding = true;
                    const saleId = this.refundId;

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
                            this.$dispatch('close-modal', 'refund-confirm');

                            // CHANGED: Trigger our new JS toast and refresh smoothly!
                            this.showToast(data.message, 'success');
                            await this.refreshTable();
                        } else {
                            this.showToast(data.message || 'Error processing refund', 'error');
                        }
                    } catch (e) {
                        console.error(e);
                        this.showToast('Error processing refund', 'error');
                    } finally {
                        this.refunding = false;
                    }
                }
            };
        }
    </script>
</x-app-layout>
