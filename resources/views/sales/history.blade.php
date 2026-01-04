<x-app-shell>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Sales</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Sales History</h1>
            <p class="text-sm text-slate-500">Review past days and totals.</p>
        </div>
    </x-slot>

    @if ($sales->isEmpty())
        <x-card>
            <p class="text-sm text-slate-600">No historical sales to display yet.</p>
        </x-card>
    @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($sales as $sale)
                <x-card>
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-base font-semibold text-slate-900">{{ $sale->sale_date->format('M j, Y') }}</p>
                            <p class="text-sm text-slate-500">Gross total: Rp {{ number_format($sale->gross_total, 0, '.', ',') }}</p>
                        </div>
                        <a href="{{ route('sales.show', ['date' => $sale->sale_date->toDateString()]) }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                            View
                        </a>
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
</x-app-shell>

