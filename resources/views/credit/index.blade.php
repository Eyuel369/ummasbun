<x-app-shell>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Credit</p>
                <h1 class="mt-2 text-2xl font-semibold text-slate-900">Credit List</h1>
                <p class="text-sm text-slate-500">Outstanding credit payments grouped by customer.</p>
            </div>
            @if (auth()->user()?->role === \App\Models\User::ROLE_OWNER)
                <a href="{{ route('credit.export') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                    Export CSV
                </a>
            @endif
        </div>
    </x-slot>

    @if ($credits->isEmpty())
        <x-card>
            <p class="text-sm text-slate-600">No credit entries yet.</p>
        </x-card>
    @else
        <div class="space-y-4">
            @foreach ($credits as $customer => $items)
                @php
                    $displayCustomer = $customer ?: 'Unknown';
                @endphp
                <x-card>
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-base font-semibold text-slate-900">{{ $displayCustomer }}</p>
                            <p class="text-sm text-slate-500">{{ $items->count() }} entries</p>
                        </div>
                        <x-badge variant="warning">Rp {{ number_format($totals[$customer] ?? 0, 0, '.', ',') }}</x-badge>
                    </div>

                    <div class="mt-4 space-y-2 text-sm text-slate-600">
                        @foreach ($items as $payment)
                            <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                                <span>{{ $payment->dailySale?->sale_date?->format('M j, Y') ?? 'Unknown date' }}</span>
                                <span class="font-medium text-slate-900">Rp {{ number_format($payment->amount, 0, '.', ',') }}</span>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
</x-app-shell>
