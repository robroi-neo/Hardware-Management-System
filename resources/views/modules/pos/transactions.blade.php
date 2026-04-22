<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-semibold leading-tight text-slate-900">POS Transactions</h2>
    </x-slot>

    <section class="rounded-xl border border-slate-200 bg-white p-6">
        <div class="mb-6">
            <h3 class="mt-2 text-xl font-semibold text-slate-900">Transaction History</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-left text-slate-700">
                    <tr>
                        <x-table.sortable-header
                            label="ID"
                            :sortBy="$sortBy"
                            :sortDir="$sortDir"
                            column="id"
                            route="pos.transactions"
                        />
                        <x-table.sortable-header
                            label="Date"
                            :sortBy="$sortBy"
                            :sortDir="$sortDir"
                            column="date"
                            route="pos.transactions"
                        />
                        <x-table.sortable-header
                            label="Total Amount"
                            :sortBy="$sortBy"
                            :sortDir="$sortDir"
                            column="total_amount"
                            route="pos.transactions"
                            align="right"
                        />
                        <x-table.sortable-header
                            label="Payment Method"
                            :sortBy="$sortBy"
                            :sortDir="$sortDir"
                            column="payment_method"
                            route="pos.transactions"
                        />
                        <th class="px-4 py-3 font-semibold">Processed By</th>
                    </tr>
                </thead>
                <tbody class="divide-y border-t border-slate-200">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-900">#{{ $transaction->id }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->date->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3 text-slate-900 font-medium">₱{{ number_format($transaction->total_amount, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
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

        <!-- Pagination -->
        <x-table.pagination :paginator="$transactions" />
    </section>
</x-app-layout>
