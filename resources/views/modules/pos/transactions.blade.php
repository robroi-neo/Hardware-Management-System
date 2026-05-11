<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">POS Transactions</h2>
    </x-slot>

    {{-- x-data moved here so openDetail() is accessible to both the table rows and the modal --}}
    <x-card title="Transaction History" fullHeight x-data="transactionDetail()">
        <div class="border border-gray-200 rounded overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-indigo-50 text-left text-slate-700">
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
                    <h3 class="text-lg font-semibold">Transaction #<span x-text="detail.id"></span></h3>
                    <button @click="$dispatch('close-modal', 'transaction-detail')" class="text-sm text-slate-500 hover:text-slate-700">Close</button>
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

                        <div class="flex justify-end border-t pt-3 font-semibold text-base">
                            Total: ₱<span x-text="detail.total_amount.toFixed(2)"></span>
                        </div>
                    </div>
                </template>
            </div>
        </x-modal>
    </x-card>

    <script>
        function transactionDetail() {
            return {
                detail: {},
                loading: false,

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
                }
            };
        }
    </script>
</x-app-layout>
