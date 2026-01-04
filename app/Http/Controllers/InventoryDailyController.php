<?php

namespace App\Http\Controllers;

use App\Models\InventoryDaily;
use App\Models\InventoryItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class InventoryDailyController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryDaily::class);

        $selectedDate = $this->parseDate($request->query('date', now()->toDateString()));
        $previousDate = $selectedDate->copy()->subDay();

        $items = InventoryItem::where('active', true)
            ->orderBy('name')
            ->get();

        $entries = InventoryDaily::whereDate('inv_date', $selectedDate->toDateString())
            ->get()
            ->keyBy('inventory_item_id');

        $previousEntries = InventoryDaily::whereDate('inv_date', $previousDate->toDateString())
            ->get()
            ->keyBy('inventory_item_id');

        return view('inventory.daily.index', [
            'selectedDate' => $selectedDate,
            'items' => $items,
            'entries' => $entries,
            'previousEntries' => $previousEntries,
        ]);
    }

    public function edit(InventoryDaily $inventoryDaily): View
    {
        $this->authorize('update', $inventoryDaily);

        return view('inventory.daily.edit', compact('inventoryDaily'));
    }

    public function store(Request $request, string $date, InventoryItem $inventoryItem)
    {
        $this->authorize('create', InventoryDaily::class);

        if (! $inventoryItem->active) {
            abort(404);
        }

        $selectedDate = $this->parseDate($date);
        $data = $this->validatePayload($request);

        $stockIn = (float) ($data['stock_in'] ?? 0);
        $todayRemaining = (float) $data['today_remaining'];
        $yesterdayRemaining = $this->getYesterdayRemaining($inventoryItem, $selectedDate);
        $autoUsage = $this->calculateAutoUsage($yesterdayRemaining, $stockIn, $todayRemaining);

        if ($autoUsage < 0 && ! $this->allowNegativeUsage($request->user())) {
            return $this->validationError($request, 'today_remaining', 'Auto usage cannot be negative. Review today remaining or contact an owner.');
        }

        $entry = DB::transaction(function () use ($request, $selectedDate, $inventoryItem, $yesterdayRemaining, $stockIn, $todayRemaining, $autoUsage): InventoryDaily {
            return InventoryDaily::updateOrCreate(
                [
                    'inv_date' => $selectedDate->toDateString(),
                    'inventory_item_id' => $inventoryItem->id,
                ],
                [
                    'yesterday_remaining' => $yesterdayRemaining,
                    'stock_in' => $stockIn,
                    'today_remaining' => $todayRemaining,
                    'auto_usage' => $autoUsage,
                    'created_by' => $request->user()->id,
                ]
            );
        });

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'auto_usage' => $entry->auto_usage,
                'today_remaining' => $entry->today_remaining,
            ]);
        }

        return redirect()
            ->route('inventory.daily.index', ['date' => $selectedDate->toDateString()])
            ->with('status', 'Inventory entry saved.');
    }

    public function bulkStore(Request $request, string $date): RedirectResponse
    {
        $this->authorize('create', InventoryDaily::class);

        $selectedDate = $this->parseDate($date);
        $items = InventoryItem::where('active', true)->get()->keyBy('id');

        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array'],
            'items.*.stock_in' => ['nullable', 'numeric', 'min:0'],
            'items.*.today_remaining' => ['required', 'numeric', 'min:0'],
        ]);

        $validator->after(function ($validator) use ($request, $items, $selectedDate): void {
            foreach ((array) $request->input('items', []) as $itemId => $payload) {
                $item = $items->get((int) $itemId);
                if (! $item) {
                    $validator->errors()->add("items.$itemId", 'Invalid inventory item.');
                    continue;
                }

                $stockIn = (float) ($payload['stock_in'] ?? 0);
                $todayRemaining = (float) ($payload['today_remaining'] ?? 0);
                $yesterdayRemaining = $this->getYesterdayRemaining($item, $selectedDate);
                $autoUsage = $this->calculateAutoUsage($yesterdayRemaining, $stockIn, $todayRemaining);

                if ($autoUsage < 0 && ! $this->allowNegativeUsage($request->user())) {
                    $validator->errors()->add("items.$itemId.today_remaining", 'Auto usage cannot be negative.');
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request, $items, $selectedDate): void {
            foreach ((array) $request->input('items', []) as $itemId => $payload) {
                $item = $items->get((int) $itemId);
                if (! $item) {
                    continue;
                }

                $stockIn = (float) ($payload['stock_in'] ?? 0);
                $todayRemaining = (float) ($payload['today_remaining'] ?? 0);
                $yesterdayRemaining = $this->getYesterdayRemaining($item, $selectedDate);
                $autoUsage = $this->calculateAutoUsage($yesterdayRemaining, $stockIn, $todayRemaining);

                InventoryDaily::updateOrCreate(
                    [
                        'inv_date' => $selectedDate->toDateString(),
                        'inventory_item_id' => $item->id,
                    ],
                    [
                        'yesterday_remaining' => $yesterdayRemaining,
                        'stock_in' => $stockIn,
                        'today_remaining' => $todayRemaining,
                        'auto_usage' => $autoUsage,
                        'created_by' => $request->user()->id,
                    ]
                );
            }
        });

        return redirect()
            ->route('inventory.daily.index', ['date' => $selectedDate->toDateString()])
            ->with('status', 'Inventory entries saved.');
    }

    private function parseDate(string $date): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Exception $exception) {
            abort(404);
        }
    }

    private function getYesterdayRemaining(InventoryItem $item, Carbon $date): float
    {
        $previous = InventoryDaily::whereDate('inv_date', $date->copy()->subDay()->toDateString())
            ->where('inventory_item_id', $item->id)
            ->first();

        return (float) ($previous?->today_remaining ?? 0);
    }

    private function calculateAutoUsage(float $yesterday, float $stockIn, float $todayRemaining): float
    {
        return round($yesterday + $stockIn - $todayRemaining, 2);
    }

    private function allowNegativeUsage(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return config('inventory.allow_negative_usage', false) && $user->role === User::ROLE_OWNER;
    }

    /**
     * @return array{stock_in: ?float, today_remaining: float}
     */
    private function validatePayload(Request $request): array
    {
        return $request->validate(
            [
                'stock_in' => ['nullable', 'numeric', 'min:0'],
                'today_remaining' => ['required', 'numeric', 'min:0'],
            ],
            [
                'today_remaining.required' => 'Enter today remaining.',
            ]
        );
    }

    private function validationError(Request $request, string $field, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => [$field => [$message]],
            ], 422);
        }

        return back()->withErrors([$field => $message])->withInput();
    }
}
