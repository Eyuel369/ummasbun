<?php

namespace App\Providers;

use App\Models\DailySale;
use App\Models\Expense;
use App\Models\InventoryDaily;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\User;
use App\Policies\DailySalePolicy;
use App\Policies\ExpensePolicy;
use App\Policies\InventoryDailyPolicy;
use App\Policies\InventoryItemPolicy;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        DailySale::class => DailySalePolicy::class,
        Expense::class => ExpensePolicy::class,
        InventoryItem::class => InventoryItemPolicy::class,
        InventoryDaily::class => InventoryDailyPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
