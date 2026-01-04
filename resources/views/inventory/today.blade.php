<x-app-shell>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Inventory</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Today's Inventory</h1>
            <p class="text-sm text-slate-500">Monitor stock levels and restocks.</p>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Low Stock</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">0 items</p>
            <p class="text-sm text-slate-500">Everything looks stocked.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Incoming</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">0 deliveries</p>
            <p class="text-sm text-slate-500">Schedule restocks as needed.</p>
        </div>
    </div>
</x-app-shell>

