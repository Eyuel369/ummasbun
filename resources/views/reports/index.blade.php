<x-app-shell>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Reports</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Reports</h1>
            <p class="text-sm text-slate-500">Useful summaries for the date range.</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        <x-card title="Filters" subtitle="Adjust the date range to update each panel.">
            <form method="GET" action="{{ route('reports.index') }}" class="grid gap-4 sm:grid-cols-3 sm:items-end">
                <x-input label="Start Date" name="start" type="date" value="{{ $startDate->toDateString() }}" />
                <x-input label="End Date" name="end" type="date" value="{{ $endDate->toDateString() }}" />
                <x-button type="submit">Apply</x-button>
            </form>
        </x-card>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-card title="Sales Trend" subtitle="Daily gross totals">
                <div class="h-64">
                    <canvas id="sales-trend-chart" class="h-full w-full"></canvas>
                </div>
            </x-card>

            <x-card title="Expense by Category" subtitle="Totals by category">
                <div class="h-64">
                    <canvas id="expense-category-chart" class="h-full w-full"></canvas>
                </div>
                <div class="mt-4 grid gap-2 sm:grid-cols-2 text-sm">
                    @foreach ($expenseByCategory['labels'] as $index => $label)
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                            <span class="text-slate-600">{{ $label }}</span>
                            <span class="font-semibold text-slate-900">
                                Rp {{ number_format($expenseByCategory['values'][$index] ?? 0, 0, '.', ',') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-card title="Payment Split" subtitle="Cash, transfer, and credit totals">
                <div class="h-64">
                    <canvas id="payment-split-chart" class="h-full w-full"></canvas>
                </div>
                <div class="mt-4 grid gap-2 sm:grid-cols-3 text-sm">
                    @foreach ($paymentSplit['labels'] as $index => $label)
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                            <span class="text-slate-600">{{ $label }}</span>
                            <span class="font-semibold text-slate-900">
                                Rp {{ number_format($paymentSplit['values'][$index] ?? 0, 0, '.', ',') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <x-card title="Best Sellers" subtitle="Top products by revenue">
                @if ($bestSellers->isEmpty())
                    <p class="text-sm text-slate-600">No sales lines yet for this range.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($bestSellers as $seller)
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $seller->name }}</p>
                                    <p class="text-xs text-slate-500">Qty {{ number_format($seller->total_qty ?? 0, 2, '.', ',') }}</p>
                                </div>
                                <div class="font-semibold text-slate-900">
                                    Rp {{ number_format($seller->total_revenue ?? 0, 0, '.', ',') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>

        <x-card title="Credit Customers" subtitle="Total owed per customer">
            @if ($creditTotals->isEmpty())
                <p class="text-sm text-slate-600">No credit payments in this range.</p>
            @else
                <div class="space-y-3">
                    @foreach ($creditTotals as $credit)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                            <div class="font-semibold text-slate-900">{{ $credit->customer }}</div>
                            <div class="font-semibold text-slate-900">
                                Rp {{ number_format($credit->total ?? 0, 0, '.', ',') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const salesTrend = @json($salesTrend);
        const expenseByCategory = @json($expenseByCategory);
        const paymentSplit = @json($paymentSplit);

        const buildLineChart = (canvasId, labels, data) => {
            const canvas = document.getElementById(canvasId);
            if (!canvas) {
                return;
            }

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        borderColor: '#0f172a',
                        backgroundColor: 'rgba(15, 23, 42, 0.08)',
                        borderWidth: 2,
                        tension: 0.25,
                        fill: true,
                        pointRadius: 3,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { ticks: { precision: 0 } },
                    },
                },
            });
        };

        const buildBarChart = (canvasId, labels, data) => {
            const canvas = document.getElementById(canvasId);
            if (!canvas) {
                return;
            }

            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: '#0f172a',
                        borderRadius: 12,
                        barThickness: 24,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { ticks: { precision: 0 } },
                    },
                },
            });
        };

        const buildDoughnutChart = (canvasId, labels, data) => {
            const canvas = document.getElementById(canvasId);
            if (!canvas) {
                return;
            }

            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: ['#0f172a', '#475569', '#94a3b8'],
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                    },
                    cutout: '70%',
                },
            });
        };

        buildLineChart('sales-trend-chart', salesTrend.labels, salesTrend.values);
        buildBarChart('expense-category-chart', expenseByCategory.labels, expenseByCategory.values);
        buildDoughnutChart('payment-split-chart', paymentSplit.labels, paymentSplit.values);
    </script>
</x-app-shell>
