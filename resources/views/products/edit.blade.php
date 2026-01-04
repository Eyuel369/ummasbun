<x-app-shell>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Products</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Edit Product</h1>
            <p class="text-sm text-slate-500">Update pricing and availability.</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if ($errors->any())
            <x-alert variant="danger" title="Please review the form">
                Fix the highlighted fields and try again.
            </x-alert>
        @endif

        <x-card>
            <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <x-input label="Name" name="name" value="{{ old('name', $product->name) }}" placeholder="Product name" />
                <x-input-error :messages="$errors->get('name')" />

                <x-input label="Price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $product->price) }}" placeholder="0.00" />
                <x-input-error :messages="$errors->get('price')" />

                <label class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    <span class="font-medium">Active</span>
                    <input type="checkbox" name="active" class="h-5 w-5 rounded border-slate-300 text-slate-900 focus:ring-slate-200" {{ old('active', $product->active) ? 'checked' : '' }}>
                </label>

                <div class="flex flex-wrap gap-2">
                    <x-button type="submit">Save Changes</x-button>
                    <a href="{{ route('products.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                        Cancel
                    </a>
                </div>
            </form>
        </x-card>
    </div>
</x-app-shell>

