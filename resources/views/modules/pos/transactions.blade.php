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
                        <th class="px-4 py-3 font-semibold">
                            <a href="{{ route('pos.transactions', ['sort_by' => 'id', 'sort_dir' => $sortBy === 'id' && $sortDir === 'asc' ? 'desc' : 'asc']) }}" class="inline-flex items-center gap-1 hover:text-slate-900">
                                ID
                                <span class="{{ $sortBy === 'id' ? 'text-slate-900' : 'text-slate-400' }}">
                                    {{ $sortBy === 'id' ? ($sortDir === 'asc' ? '↑' : '↓') : '↕' }}
                                </span>
                            </a>
                        </th>
                        <th class="px-4 py-3 font-semibold">
                            <a href="{{ route('pos.transactions', ['sort_by' => 'date', 'sort_dir' => $sortBy === 'date' && $sortDir === 'asc' ? 'desc' : 'asc']) }}" class="inline-flex items-center gap-1 hover:text-slate-900">
                                Date
                                <span class="{{ $sortBy === 'date' ? 'text-slate-900' : 'text-slate-400' }}">
                                    {{ $sortBy === 'date' ? ($sortDir === 'asc' ? '↑' : '↓') : '↕' }}
                                </span>
                            </a>
                        </th>
                        <th class="px-4 py-3 font-semibold">
                            <a href="{{ route('pos.transactions', ['sort_by' => 'total_amount', 'sort_dir' => $sortBy === 'total_amount' && $sortDir === 'asc' ? 'desc' : 'asc']) }}" class="inline-flex items-center gap-1 hover:text-slate-900">
                                Total Amount
                                <span class="{{ $sortBy === 'total_amount' ? 'text-slate-900' : 'text-slate-400' }}">
                                    {{ $sortBy === 'total_amount' ? ($sortDir === 'asc' ? '↑' : '↓') : '↕' }}
                                </span>
                            </a>
                        </th>
                        <th class="px-4 py-3 font-semibold">
                            <a href="{{ route('pos.transactions', ['sort_by' => 'payment_method', 'sort_dir' => $sortBy === 'payment_method' && $sortDir === 'asc' ? 'desc' : 'asc']) }}" class="inline-flex items-center gap-1 hover:text-slate-900">
                                Payment Method
                                <span class="{{ $sortBy === 'payment_method' ? 'text-slate-900' : 'text-slate-400' }}">
                                    {{ $sortBy === 'payment_method' ? ($sortDir === 'asc' ? '↑' : '↓') : '↕' }}
                                </span>
                            </a>
                        </th>
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
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-400">
                                No transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-slate-600">
                Showing <span class="font-semibold">{{ $transactions->firstItem() ?? 0 }}</span>
                to <span class="font-semibold">{{ $transactions->lastItem() ?? 0 }}</span>
                of <span class="font-semibold">{{ $transactions->total() }}</span> transactions
            </div>
            <div>
                {{ $transactions->links() }}
            </div>
        </div>
    </section>
</x-app-layout>
