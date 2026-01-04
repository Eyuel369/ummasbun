<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryItemsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $search = trim((string) $request->query('q', ''));

        $query = InventoryItem::query();
        if ($search !== '') {
            $query->where(function ($subQuery) use ($search): void {
                $subQuery
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('unit', 'like', '%'.$search.'%');
            });
        }

        $items = $query
            ->orderBy('name')
            ->with(['inventoryDailies' => function ($dailyQuery): void {
                $dailyQuery->orderByDesc('inv_date')->limit(1);
            }])
            ->get();

        return view('inventory.items.index', [
            'items' => $items,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', InventoryItem::class);

        return view('inventory.items.create');
    }

    public function edit(InventoryItem $inventoryItem): View
    {
        $this->authorize('update', $inventoryItem);

        return view('inventory.items.edit', compact('inventoryItem'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', InventoryItem::class);

        $data = $this->validatePayload($request);
        $data['active'] = $request->boolean('active');

        InventoryItem::create($data);

        return redirect()->route('inventory.items.index')->with('status', 'Inventory item added.');
    }

    public function update(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $this->authorize('update', $inventoryItem);

        $data = $this->validatePayload($request);
        $data['active'] = $request->boolean('active');

        $inventoryItem->update($data);

        return redirect()->route('inventory.items.index')->with('status', 'Inventory item updated.');
    }

    public function destroy(InventoryItem $inventoryItem): RedirectResponse
    {
        $this->authorize('delete', $inventoryItem);

        $inventoryItem->delete();

        return redirect()->route('inventory.items.index')->with('status', 'Inventory item removed.');
    }

    /**
     * @return array{name: string, unit: string, min_level: ?float}
     */
    private function validatePayload(Request $request): array
    {
        return $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'unit' => ['required', 'string', 'max:50'],
                'min_level' => ['nullable', 'numeric', 'min:0'],
            ],
            [
                'name.required' => 'Please enter an item name.',
                'unit.required' => 'Please enter a unit.',
                'min_level.min' => 'Minimum level must be zero or higher.',
            ]
        );
    }
}
