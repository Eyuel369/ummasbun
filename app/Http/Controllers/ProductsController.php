<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::query();

        if ($request->user()?->role !== User::ROLE_OWNER) {
            $query->where('active', true);
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $products = $query->orderBy('name')->get();

        return view('products.index', [
            'products' => $products,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('products.create');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('products.edit', compact('product'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $data = $this->validatePayload($request);
        $data['active'] = $request->boolean('active');

        Product::create($data);

        return redirect()->route('products.index')->with('status', 'Product added.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = $this->validatePayload($request);
        $data['active'] = $request->boolean('active');

        $product->update($data);

        return redirect()->route('products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return redirect()->route('products.index')->with('status', 'Product removed.');
    }

    public function toggle(Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $product->update(['active' => ! $product->active]);

        return redirect()->route('products.index')->with('status', 'Product status updated.');
    }

    /**
     * @return array{name: string, price: float}
     */
    private function validatePayload(Request $request): array
    {
        return $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'price' => ['required', 'numeric', 'min:0'],
            ],
            [
                'name.required' => 'Please enter a product name.',
                'price.required' => 'Please enter a price.',
                'price.numeric' => 'Price must be a number.',
                'price.min' => 'Price must be zero or higher.',
            ]
        );
    }
}
