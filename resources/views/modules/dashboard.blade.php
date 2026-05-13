<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-medium leading-tight text-slate-900">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <article class="rounded-xl border border-slate-200 bg-white p-6 lg:col-span-1">
                <p class="text-lg text-slate-500"> Weekly Earnings</p>
                <h3 class="mt-3 text-4xl font-semibold tracking-tight text-indigo-600">₱ {{ number_format($earnings_last_week, 2) }}</h3>
                <p class="mt-3 text-sm text-slate-500">&nbsp;</p>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 lg:col-span-1">
                <p class="text-lg text-slate-500">Earnings Today</p>
                <h3 class="mt-3 text-4xl font-semibold tracking-tight text-indigo-600">₱ {{ number_format($earnings_today, 2) }}</h3>
                <p class="mt-3 text-sm text-slate-500">&nbsp;</p>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-6 lg:col-span-1">
                <p class="text-lg text-slate-500">Transactions Today</p>
                <h3 class="mt-3 text-4xl font-semibold tracking-tight text-indigo-600">{{ number_format($transactions_today) }}</h3>
                <p class="mt-3 text-sm text-slate-500">POS transactions</p>
            </article>
        </section>

        <section>
            <h3 class="mb-4 text-xl font-medium text-slate-800">Inventory Summary</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <article class="rounded-xl border border-slate-200 bg-white p-6">
                    <p class="text-sm text-slate-500">In Stock Items</p>
                    <p class="mt-5 text-4xl font-semibold text-emerald-600">{{ number_format($in_stock_items) }}</p>
                    <p class="mt-1 text-sm text-slate-500">Products available</p>
                </article>

                <article class="rounded-xl border border-slate-200 bg-white p-6">
                    <p class="text-sm text-slate-500">Low Stock Items</p>
                    <p class="mt-5 text-4xl font-semibold text-amber-500">{{ number_format($low_stock_items) }}</p>
                    <p class="mt-1 text-sm text-slate-500">Need restocking</p>
                </article>

                <article class="rounded-xl border border-slate-200 bg-white p-6">
                    <p class="text-sm text-slate-500">Out of Stock Items</p>
                    <p class="mt-5 text-4xl font-semibold text-rose-500">{{ number_format($out_of_stock_items) }}</p>
                    <p class="mt-1 text-sm text-slate-500">Require immediate action</p>
                </article>
            </div>
        </section>

        <section>
            <h3 class="mb-4 text-xl font-medium text-slate-800">
                Weekly Transaction Overview
            </h3>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                <article class="rounded-xl border border-slate-200 bg-white p-6">
                    <p class="mb-4 text-sm text-slate-500">
                        Weekly Transactions Count
                    </p>

                    <div class="relative h-52">
                        <canvas id="transactionsChart"></canvas>
                    </div>
                </article>

                <article class="rounded-xl border border-slate-200 bg-white p-6">
                    <p class="mb-4 text-sm text-slate-500">
                        Weekly Revenue
                    </p>

                    <div class="relative h-52">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </article>
            </div>
        </section>
    </div>

    {{-- Charts data and scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const labels = @json($weekly_labels);
            const transactions = @json($weekly_transactions);
            const revenue = @json($weekly_revenue);

            const txCtx = document.getElementById('transactionsChart').getContext('2d');
            new Chart(txCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Transactions',
                        data: transactions,
                        backgroundColor: 'rgba(99,102,241,0.8)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });

            const revCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue',
                        data: revenue,
                        borderColor: 'rgba(16,185,129,0.9)',
                        backgroundColor: 'rgba(16,185,129,0.9)',
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        })();
    </script>
</x-app-layout>
