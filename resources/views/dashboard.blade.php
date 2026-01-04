<x-app-shell>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Owner</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Dashboard</h1>
            <p class="text-sm text-slate-500">Quick pulse on today's performance.</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        <x-card title="Daily Summary" subtitle="For {{ $selectedDate->format('M j, Y') }}">
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Gross Sales</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">Rp {{ number_format($grossTotal, 0, '.', ',') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Total Expenses</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">Rp {{ number_format($expenseTotal, 0, '.', ',') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Net Profit</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">Rp {{ number_format($netProfit, 0, '.', ',') }}</p>
                </div>
            </div>

            <div class="mt-4">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Payment Split</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Cash</p>
                        <p class="mt-2 font-semibold text-slate-900">Rp {{ number_format($paymentTotals['cash'] ?? 0, 0, '.', ',') }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Transfer</p>
                        <p class="mt-2 font-semibold text-slate-900">Rp {{ number_format($paymentTotals['transfer'] ?? 0, 0, '.', ',') }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Credit</p>
                        <p class="mt-2 font-semibold text-slate-900">Rp {{ number_format($paymentTotals['credit'] ?? 0, 0, '.', ',') }}</p>
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</x-app-shell>

