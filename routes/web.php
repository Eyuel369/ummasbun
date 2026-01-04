<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\ExportsController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryDailyController;
use App\Http\Controllers\InventoryItemsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/sales/today', [SalesController::class, 'today'])->name('sales.today');
    Route::get('/sales/{date}', [SalesController::class, 'show'])
        ->where('date', '\\d{4}-\\d{2}-\\d{2}')
        ->name('sales.show');
    Route::get('/sales/history', [SalesController::class, 'history'])->name('sales.history');
    Route::get('/sales/debtors', [SalesController::class, 'debtors'])->name('sales.debtors');
    Route::get('/sales/{dailySale}/edit', [SalesController::class, 'edit'])->name('sales.edit');
    Route::post('/sales/{date}/lines', [SalesController::class, 'storeLine'])
        ->where('date', '\\d{4}-\\d{2}-\\d{2}')
        ->name('sales.lines.store');
    Route::patch('/sales/lines/{saleLine}', [SalesController::class, 'updateLine'])->name('sales.lines.update');
    Route::delete('/sales/lines/{saleLine}', [SalesController::class, 'deleteLine'])->name('sales.lines.delete');
    Route::post('/sales/{date}/payments', [SalesController::class, 'storePayment'])
        ->where('date', '\\d{4}-\\d{2}-\\d{2}')
        ->name('sales.payments.store');
    Route::delete('/sales/payments/{payment}', [SalesController::class, 'deletePayment'])->name('sales.payments.delete');

    Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductsController::class, 'create'])->name('products.create');
    Route::get('/products/{product}/edit', [ProductsController::class, 'edit'])->name('products.edit');
    Route::post('/products', [ProductsController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductsController::class, 'update'])->name('products.update');
    Route::patch('/products/{product}/toggle', [ProductsController::class, 'toggle'])->name('products.toggle');
    Route::delete('/products/{product}', [ProductsController::class, 'destroy'])->name('products.destroy');

    Route::get('/expenses', [ExpensesController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [ExpensesController::class, 'create'])->name('expenses.create');
    Route::get('/expenses/{expense}/edit', [ExpensesController::class, 'edit'])->name('expenses.edit');
    Route::post('/expenses', [ExpensesController::class, 'store'])->name('expenses.store');
    Route::put('/expenses/{expense}', [ExpensesController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpensesController::class, 'destroy'])->name('expenses.destroy');

    Route::get('/inventory/today', [InventoryController::class, 'today'])->name('inventory.today');
    Route::get('/inventory/items', [InventoryItemsController::class, 'index'])->name('inventory.items.index');
    Route::get('/inventory/items/create', [InventoryItemsController::class, 'create'])->name('inventory.items.create');
    Route::get('/inventory/items/{inventoryItem}/edit', [InventoryItemsController::class, 'edit'])->name('inventory.items.edit');
    Route::post('/inventory/items', [InventoryItemsController::class, 'store'])->name('inventory.items.store');
    Route::put('/inventory/items/{inventoryItem}', [InventoryItemsController::class, 'update'])->name('inventory.items.update');
    Route::delete('/inventory/items/{inventoryItem}', [InventoryItemsController::class, 'destroy'])->name('inventory.items.destroy');
    Route::get('/inventory/daily', [InventoryDailyController::class, 'index'])->name('inventory.daily.index');
    Route::get('/inventory/daily/{inventoryDaily}/edit', [InventoryDailyController::class, 'edit'])->name('inventory.daily.edit');
    Route::post('/inventory/daily/{date}/items/{inventoryItem}', [InventoryDailyController::class, 'store'])
        ->where('date', '\\d{4}-\\d{2}-\\d{2}')
        ->name('inventory.daily.store');
    Route::post('/inventory/daily/{date}/bulk', [InventoryDailyController::class, 'bulkStore'])
        ->where('date', '\\d{4}-\\d{2}-\\d{2}')
        ->name('inventory.daily.bulk');

    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');

    Route::get('/credit', [CreditController::class, 'index'])->name('credit.index');
    Route::get('/credit/export', [CreditController::class, 'export'])->name('credit.export');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    Route::get('/exports', [ExportsController::class, 'index'])->name('exports.index');
    Route::get('/exports/sales-expenses', [ExportsController::class, 'salesExpenses'])->name('exports.sales-expenses');
    Route::get('/exports/inventory', [ExportsController::class, 'inventory'])->name('exports.inventory');
    Route::get('/exports/owner/full', [ExportsController::class, 'ownerFull'])->name('exports.owner.full');
    Route::get('/exports/owner/range', [ExportsController::class, 'ownerRange'])->name('exports.owner.range');
    Route::get('/exports/cashier/range', [ExportsController::class, 'cashierRange'])->name('exports.cashier.range');
    Route::get('/exports/inventory/range', [ExportsController::class, 'inventoryRange'])->name('exports.inventory.range');

    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
    Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::post('/users', [UsersController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/reset-link', [UsersController::class, 'sendResetLink'])->name('users.reset-link');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
