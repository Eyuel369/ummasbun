<x-app-shell>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Products</p>
                <h1 class="mt-2 text-2xl font-semibold text-slate-900">Product Catalog</h1>
                <p class="text-sm text-slate-500">Search and manage your menu items.</p>
            </div>
            @can('create', \App\Models\Product::class)
                <a href="{{ route('products.create') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-slate-900 px-5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-slate-800">
                    Add Product
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
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <x-input
                        label="Search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Search products"
                    />
                </div>
                <x-button type="submit" variant="secondary">Search</x-button>
            </form>
        </x-card>

        @if ($products->isEmpty())
            <x-card>
                <p class="text-sm text-slate-600">No products found. Try a different search or add a new item.</p>
            </x-card>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($products as $product)
                    <x-card>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-lg font-semibold text-slate-900">{{ $product->name }}</p>
                                <p class="text-sm text-slate-500">Rp {{ number_format($product->price, 0, '.', ',') }}</p>
                            </div>
                            <x-badge variant="{{ $product->active ? 'success' : 'neutral' }}">
                                {{ $product->active ? 'Active' : 'Inactive' }}
                            </x-badge>
                        </div>

                        @can('update', $product)
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="{{ route('products.edit', $product) }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('products.toggle', $product) }}">
                                    @csrf
                                    @method('PATCH')
                                    <x-button size="sm" variant="secondary">
                                        {{ $product->active ? 'Deactivate' : 'Activate' }}
                                    </x-button>
                                </form>
                                <form
                                    method="POST"
                                    action="{{ route('products.destroy', $product) }}"
                                    data-confirm
                                    data-confirm-title="Delete product"
                                    data-confirm-message="Delete this product and remove it from the catalog?"
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

