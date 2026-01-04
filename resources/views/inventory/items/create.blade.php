<x-app-shell>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Inventory</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Add Inventory Item</h1>
            <p class="text-sm text-slate-500">Define a new ingredient or supply.</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if ($errors->any())
            <x-alert variant="danger" title="Please review the form">
                Fix the highlighted fields and try again.
            </x-alert>
        @endif

        <x-card>
            <form method="POST" action="{{ route('inventory.items.store') }}" class="space-y-5">
                @csrf

                <x-input label="Name" name="name" value="{{ old('name') }}" placeholder="Item name" />
                <x-input-error :messages="$errors->get('name')" />

                <x-input label="Unit" name="unit" value="{{ old('unit') }}" placeholder="kg, liter, pcs, etc" list="unit-options" />
                <datalist id="unit-options">
                    <option value="kg"></option>
                    <option value="liter"></option>
                    <option value="pcs"></option>
                </datalist>
                <x-input-error :messages="$errors->get('unit')" />

                <x-input label="Minimum Level" name="min_level" type="number" step="0.01" min="0" value="{{ old('min_level') }}" placeholder="Optional" />
                <x-input-error :messages="$errors->get('min_level')" />

                <label class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    <span class="font-medium">Active</span>
                    <input type="checkbox" name="active" class="h-5 w-5 rounded border-slate-300 text-slate-900 focus:ring-slate-200" {{ old('active', true) ? 'checked' : '' }}>
                </label>

                <div class="flex flex-wrap gap-2">
                    <x-button type="submit">Save Item</x-button>
                    <a href="{{ route('inventory.items.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                        Cancel
                    </a>
                </div>
            </form>
        </x-card>
    </div>
</x-app-shell>

