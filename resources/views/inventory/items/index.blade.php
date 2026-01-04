<x-app-shell>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Inventory</p>
                <h1 class="mt-2 text-2xl font-semibold text-slate-900">Inventory Items</h1>
                <p class="text-sm text-slate-500">Manage ingredients and units.</p>
            </div>
            @can('create', \App\Models\InventoryItem::class)
                <a href="{{ route('inventory.items.create') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-slate-900 px-5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-slate-800">
                    Add Item
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <x-alert variant="success" title="Success">
                {{ session('status') }}
            </x-alert>
        @endif

        <x-card>
            <form method="GET" action="{{ route('inventory.items.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <x-input
                        label="Search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Search items or units"
                    />
                </div>
                <x-button type="submit" variant="secondary">Search</x-button>
            </form>
        </x-card>

        @if ($items->isEmpty())
            <x-card>
                <p class="text-sm text-slate-600">No inventory items found. Add a new item to get started.</p>
            </x-card>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($items as $item)
                    @php
                        $latestDaily = $item->inventoryDailies->first();
                        $currentStock = $latestDaily?->today_remaining;
                        $isLowStock = $item->min_level !== null && $currentStock !== null && $currentStock <= $item->min_level;
                    @endphp
                    <x-card>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-lg font-semibold text-slate-900">{{ $item->name }}</p>
                                <p class="text-sm text-slate-500">{{ $item->unit }}</p>
                                @if ($item->min_level !== null)
                                    <p class="text-sm text-slate-500">Min level: {{ number_format($item->min_level, 2, '.', ',') }}</p>
                                @endif
                                @if ($currentStock !== null)
                                    <p class="text-sm text-slate-500">Latest stock: {{ number_format($currentStock, 2, '.', ',') }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <x-badge variant="{{ $item->active ? 'success' : 'neutral' }}">
                                    {{ $item->active ? 'Active' : 'Inactive' }}
                                </x-badge>
                                @if ($isLowStock)
                                    <x-badge variant="warning">Low stock</x-badge>
                                @endif
                            </div>
                        </div>

                        @can('update', $item)
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="{{ route('inventory.items.edit', $item) }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                                    Edit
                                </a>
                                <form
                                    method="POST"
                                    action="{{ route('inventory.items.destroy', $item) }}"
                                    data-confirm
                                    data-confirm-title="Delete inventory item"
                                    data-confirm-message="Delete this inventory item?"
                                    data-confirm-approve="Delete"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <x-button size="sm" variant="danger">Delete</x-button>
                                </form>
                            </div>
                        @endcan
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
</x-app-shell>

