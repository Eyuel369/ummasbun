<x-app-shell>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Export Center</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Exports</h1>
            <p class="text-sm text-slate-500">Download Excel reports for your role.</p>
        </div>
    </x-slot>

    @php
        $userRole = $role ?? auth()->user()?->role;
    @endphp

    <div class="space-y-4">
        <x-card title="Export Filters" subtitle="Choose a date range for filtered exports.">
            <form method="GET" action="{{ route('exports.index') }}" class="grid gap-4 sm:grid-cols-3 sm:items-end">
                <x-input label="From" name="start" type="date" value="{{ $startDate->toDateString() }}" />
                <x-input label="To" name="end" type="date" value="{{ $endDate->toDateString() }}" />
                <div class="flex flex-wrap gap-2">
                    @if ($userRole === \App\Models\User::ROLE_OWNER)
                        <x-button type="submit" formaction="{{ route('exports.owner.range') }}">Export Date Range</x-button>
                        <a
                            href="{{ route('exports.owner.full') }}"
                            class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900"
                        >
                            Export Everything
                        </a>
                    @elseif ($userRole === \App\Models\User::ROLE_CASHIER)
                        <x-button type="submit" formaction="{{ route('exports.cashier.range') }}">Export Sales + Expenses + Credit</x-button>
                    @elseif ($userRole === \App\Models\User::ROLE_STOCK_MANAGER)
                        <x-button type="submit" formaction="{{ route('exports.inventory.range') }}">Export Inventory</x-button>
                    @endif
                </div>
            </form>
        </x-card>

        <x-card title="What gets exported" subtitle="Each export includes a metadata sheet.">
            @if ($userRole === \App\Models\User::ROLE_OWNER)
                <div class="grid gap-3 sm:grid-cols-2 text-sm text-slate-600">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Products</div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Daily Sales</div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Sale Lines</div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Payments</div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Expenses</div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Inventory Items</div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Inventory Daily</div>
                </div>
            @elseif ($userRole === \App\Models\User::ROLE_CASHIER)
                <div class="grid gap-3 sm:grid-cols-2 text-sm text-slate-600">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Daily Sales</div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Sale Lines</div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Expenses</div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Credit Payments</div>
                </div>
            @elseif ($userRole === \App\Models\User::ROLE_STOCK_MANAGER)
                <div class="grid gap-3 sm:grid-cols-2 text-sm text-slate-600">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Inventory Items</div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">Inventory Daily</div>
                </div>
            @else
                <p class="text-sm text-slate-600">No exports available for this account.</p>
            @endif
        </x-card>
    </div>
</x-app-shell>
