<?php

namespace Tests\Feature;

use App\Models\InventoryDaily;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAutoUsageTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_auto_usage_uses_previous_day(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_OWNER]);
        $item = InventoryItem::create([
            'name' => 'Test Item',
            'unit' => 'kg',
            'min_level' => 1,
            'active' => true,
        ]);

        $yesterday = now()->subDay()->toDateString();
        InventoryDaily::create([
            'inv_date' => $yesterday,
            'inventory_item_id' => $item->id,
            'yesterday_remaining' => 0,
            'stock_in' => 0,
            'today_remaining' => 5,
            'auto_usage' => 0,
            'created_by' => $user->id,
        ]);

        $today = now()->toDateString();

        $this->actingAs($user)->post(route('inventory.daily.store', [
            'date' => $today,
            'inventoryItem' => $item->id,
        ]), [
            'stock_in' => 3,
            'today_remaining' => 4,
        ])->assertRedirect();

        $entry = InventoryDaily::whereDate('inv_date', $today)
            ->where('inventory_item_id', $item->id)
            ->first();

        $this->assertNotNull($entry);
        $this->assertEqualsWithDelta(4, (float) $entry->auto_usage, 0.01);
    }
}
