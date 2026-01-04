<?php

namespace Database\Seeders;

use App\Models\DailySale;
use App\Models\Expense;
use App\Models\InventoryDaily;
use App\Models\InventoryItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SaleLine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::where('role', User::ROLE_OWNER)->first();
        if (! $owner) {
            $owner = User::create([
                'name' => 'Owner',
                'email' => 'owner@ummasbun.test',
                'role' => User::ROLE_OWNER,
                'active' => true,
                'password' => Hash::make('password'),
            ]);
        }

        if (Product::count() === 0) {
            $this->call(ProductsSeeder::class);
        }

        $products = Product::orderBy('name')->get();
        if ($products->isEmpty()) {
            return;
        }

        if (InventoryItem::count() === 0) {
            $this->seedInventoryItems();
        }

        $inventoryItems = InventoryItem::where('active', true)->orderBy('name')->get();
        $inventoryLevels = $inventoryItems->mapWithKeys(function (InventoryItem $item): array {
            return [$item->id => random_int(6, 24)];
        });

        $dates = collect(range(13, 0))
            ->map(fn (int $offset) => now()->subDays($offset)->startOfDay());

        foreach ($dates as $date) {
            $this->seedDailySales($date, $owner, $products);
            $this->seedExpenses($date, $owner);
            $this->seedInventoryDaily($date, $owner, $inventoryItems, $inventoryLevels);
        }
    }

    private function seedInventoryItems(): void
    {
        $items = [
            ['name' => 'Coffee Beans', 'unit' => 'kg', 'min_level' => 2],
            ['name' => 'Milk', 'unit' => 'liter', 'min_level' => 5],
            ['name' => 'Tea Leaves', 'unit' => 'kg', 'min_level' => 1],
            ['name' => 'Sugar', 'unit' => 'kg', 'min_level' => 3],
            ['name' => 'Flour', 'unit' => 'kg', 'min_level' => 4],
            ['name' => 'Butter', 'unit' => 'kg', 'min_level' => 1.5],
        ];

        foreach ($items as $item) {
            InventoryItem::updateOrCreate(
                ['name' => $item['name']],
                [
                    'unit' => $item['unit'],
                    'min_level' => $item['min_level'],
                    'active' => true,
                ]
            );
        }
    }

    private function seedDailySales(Carbon $date, User $owner, $products): void
    {
        $dailySale = DailySale::firstOrCreate(
            ['sale_date' => $date->toDateString()],
            [
                'gross_total' => 0,
                'created_by' => $owner->id,
            ]
        );

        if ($dailySale->saleLines()->exists()) {
            return;
        }

        $lineCount = random_int(2, min(5, $products->count()));
        $selected = collect($products->random($lineCount));
        $grossTotal = 0;

        foreach ($selected as $product) {
            $qty = round(random_int(1, 6) + random_int(0, 99) / 100, 2);
            $unitPrice = (float) $product->price;
            $lineTotal = round($unitPrice * $qty, 2);

            SaleLine::create([
                'daily_sale_id' => $dailySale->id,
                'product_id' => $product->id,
                'unit_price' => $unitPrice,
                'qty' => $qty,
                'line_total' => $lineTotal,
            ]);

            $grossTotal += $lineTotal;
        }

        $grossTotal = round($grossTotal, 2);
        $dailySale->update(['gross_total' => $grossTotal]);

        if (! $dailySale->payments()->exists() && $grossTotal > 0) {
            $methods = Arr::random(['cash', 'transfer', 'credit'], random_int(1, 3));
            $methods = is_array($methods) ? $methods : [$methods];
            $remaining = $grossTotal;

            foreach ($methods as $index => $method) {
                $amount = $index === array_key_last($methods)
                    ? $remaining
                    : round($grossTotal * random_int(20, 60) / 100, 2);
                $amount = min($amount, $remaining);
                $remaining = round($remaining - $amount, 2);

                Payment::create([
                    'daily_sale_id' => $dailySale->id,
                    'method' => $method,
                    'amount' => $amount,
                    'customer_name' => $method === 'credit'
                        ? Arr::random(['Asha', 'Rina', 'Faris', 'Joko', 'Nana'])
                        : null,
                ]);
            }
        }
    }

    private function seedExpenses(Carbon $date, User $owner): void
    {
        if (Expense::whereDate('expense_date', $date->toDateString())->exists()) {
            return;
        }

        $count = random_int(0, 3);
        for ($i = 0; $i < $count; $i++) {
            Expense::create([
                'expense_date' => $date->toDateString(),
                'category' => Arr::random(Expense::CATEGORIES),
                'amount' => round(random_int(30, 200) * 1000, 2),
                'description' => Arr::random([
                    'Supplies restock',
                    'Delivery fee',
                    'Utility top-up',
                    'Maintenance',
                    'Misc expense',
                ]),
                'created_by' => $owner->id,
            ]);
        }
    }

    private function seedInventoryDaily(
        Carbon $date,
        User $owner,
        $inventoryItems,
        $inventoryLevels
    ): void {
        foreach ($inventoryItems as $item) {
            if (InventoryDaily::whereDate('inv_date', $date->toDateString())
                ->where('inventory_item_id', $item->id)
                ->exists()) {
                continue;
            }

            $yesterdayRemaining = (float) ($inventoryLevels[$item->id] ?? 0);
            $stockIn = (float) random_int(0, 10);
            $usage = (float) random_int(0, 6);
            $todayRemaining = max(0, $yesterdayRemaining + $stockIn - $usage);
            $autoUsage = round($yesterdayRemaining + $stockIn - $todayRemaining, 2);

            InventoryDaily::create([
                'inv_date' => $date->toDateString(),
                'inventory_item_id' => $item->id,
                'yesterday_remaining' => $yesterdayRemaining,
                'stock_in' => $stockIn,
                'today_remaining' => $todayRemaining,
                'auto_usage' => $autoUsage,
                'created_by' => $owner->id,
            ]);

            $inventoryLevels[$item->id] = $todayRemaining;
        }
    }
}
