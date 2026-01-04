<x-app-shell>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Inventory</p>
                <h1 class="mt-2 text-2xl font-semibold text-slate-900">Daily Inventory</h1>
                <p class="text-sm text-slate-500">Record daily stock and usage.</p>
            </div>
            <div class="flex items-end gap-3">
                <div>
                    <label for="inventory-date" class="text-xs font-semibold uppercase tracking-widest text-slate-500">Date</label>
                    <input
                        id="inventory-date"
                        type="date"
                        value="{{ $selectedDate->toDateString() }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
                    >
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <x-alert variant="success" title="Success">
                {{ session('status') }}
            </x-alert>
        @endif

        @if ($errors->any())
            <x-alert variant="danger" title="Please review the form">
                Fix the highlighted fields and try again.
            </x-alert>
        @endif

        <form method="POST" action="{{ route('inventory.daily.bulk', ['date' => $selectedDate->toDateString()]) }}" class="space-y-4" data-bulk-form>
            @csrf

            @if ($items->isEmpty())
                <x-card>
                    <p class="text-sm text-slate-600">No active inventory items found. Add items first.</p>
                </x-card>
            @else
                @foreach ($items as $item)
                    @php
                        $entry = $entries->get($item->id);
                        $previous = $previousEntries->get($item->id);
                        $yesterdayRemaining = $previous?->today_remaining ?? 0;
                        $stockInValue = old("items.{$item->id}.stock_in", $entry?->stock_in ?? 0);
                        $todayRemainingValue = old("items.{$item->id}.today_remaining", $entry?->today_remaining);
                        $autoUsageValue = $entry?->auto_usage;
                        if ($autoUsageValue === null && $todayRemainingValue !== null && $todayRemainingValue !== '') {
                            $autoUsageValue = round($yesterdayRemaining + (float) $stockInValue - (float) $todayRemainingValue, 2);
                        }
                        $isLowStock = $item->min_level !== null
                            && $todayRemainingValue !== null
                            && $todayRemainingValue !== ''
                            && (float) $todayRemainingValue < $item->min_level;
                    @endphp

                    <x-card data-item-card data-item-id="{{ $item->id }}" data-route="{{ route('inventory.daily.store', ['date' => $selectedDate->toDateString(), 'inventoryItem' => $item]) }}">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-lg font-semibold text-slate-900">{{ $item->name }}</p>
                                <p class="text-sm text-slate-500">{{ $item->unit }}</p>
                                @if ($item->min_level !== null)
                                    <p class="text-xs text-slate-500">Min level: {{ number_format($item->min_level, 2, '.', ',') }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                @if ($isLowStock)
                                    <x-badge variant="warning">Low stock</x-badge>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-3">
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-widest text-slate-500">Yesterday Remaining</label>
                                <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                    {{ number_format($yesterdayRemaining, 2, '.', ',') }}
                                </div>
                            </div>

                            <div>
                                <x-input
                                    label="Stock In"
                                    name="items[{{ $item->id }}][stock_in]"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value="{{ $stockInValue }}"
                                    data-stock-in
                                />
                                <x-input-error :messages="$errors->get("items.{$item->id}.stock_in")" />
                            </div>

                            <div>
                                <x-input
                                    label="Today Remaining"
                                    name="items[{{ $item->id }}][today_remaining]"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value="{{ $todayRemainingValue }}"
                                    data-today-remaining
                                    required
                                />
                                <x-input-error :messages="$errors->get("items.{$item->id}.today_remaining")" />
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
                            <div>
                                Auto usage:
                                <span class="font-semibold text-slate-900" data-auto-usage>
                                    {{ $autoUsageValue !== null ? number_format($autoUsageValue, 2, '.', ',') : '-' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-button size="sm" type="button" variant="secondary" data-save-item>Quick Save</x-button>
                                <span class="text-xs text-slate-500" data-status></span>
                            </div>
                        </div>
                    </x-card>
                @endforeach

                <div class="flex flex-wrap gap-2">
                    <x-button type="submit">Save All</x-button>
                    <a href="{{ route('inventory.items.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                        Manage Items
                    </a>
                </div>
            @endif
        </form>
    </div>

    <script>
        const getToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        document.querySelectorAll('[data-save-item]').forEach((button) => {
            button.addEventListener('click', async () => {
                const card = button.closest('[data-item-card]');
                if (!card) {
                    return;
                }

                const route = card.dataset.route;
                const stockInInput = card.querySelector('[data-stock-in]');
                const todayRemainingInput = card.querySelector('[data-today-remaining]');
                const status = card.querySelector('[data-status]');
                const autoUsage = card.querySelector('[data-auto-usage]');
                const defaultLabel = button.dataset.defaultLabel || button.textContent?.trim() || 'Save';

                if (status) {
                    status.textContent = 'Saving...';
                }
                button.textContent = 'Saving...';
                button.disabled = true;

                try {
                    const response = await fetch(route, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getToken(),
                        },
                        body: JSON.stringify({
                            stock_in: stockInInput?.value ?? '',
                            today_remaining: todayRemainingInput?.value ?? '',
                        }),
                    });

                    if (!response.ok) {
                        const payload = await response.json();
                        throw new Error(payload?.message || 'Unable to save.');
                    }

                    const payload = await response.json();
                    if (autoUsage && payload?.auto_usage !== undefined) {
                        autoUsage.textContent = Number(payload.auto_usage).toFixed(2);
                    }
                    if (status) {
                        status.textContent = 'Saved';
                    }
                } catch (error) {
                    if (status) {
                        status.textContent = error.message || 'Error';
                    }
                } finally {
                    button.disabled = false;
                    button.textContent = defaultLabel;
                }
            });
        });

        const dateInput = document.getElementById('inventory-date');
        if (dateInput) {
            dateInput.addEventListener('change', () => {
                if (!dateInput.value) {
                    return;
                }
                window.location = `{{ route('inventory.daily.index') }}?date=${dateInput.value}`;
            });
        }
    </script>
</x-app-shell>

