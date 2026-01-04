<?php

namespace Tests\Feature;

use App\Models\DailySale;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesGrossTotalTest extends TestCase
{
    use RefreshDatabase;

    public function test_gross_total_recalculates_after_line_added(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_OWNER]);
        $product = Product::create([
            'name' => 'Test Product',
            'price' => 15000,
            'active' => true,
        ]);

        $date = now()->toDateString();

        $this->actingAs($user)->post(route('sales.lines.store', ['date' => $date]), [
            'product_id' => $product->id,
            'unit_price' => 15000,
            'qty' => 2,
        ])->assertRedirect();

        $dailySale = DailySale::whereDate('sale_date', $date)->first();
        $this->assertNotNull($dailySale);
        $this->assertEqualsWithDelta(30000, (float) $dailySale->gross_total, 0.01);
    }
}
