<?php

namespace App\Exports;

use App\Exports\Sheets\GenericSheet;
use App\Exports\Sheets\MetadataSheet;
use App\Models\DailySale;
use App\Models\Expense;
use App\Models\InventoryDaily;
use App\Models\InventoryItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SaleLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ExportData
{
    public static function metadataSheet(string $scope, ?Carbon $startDate, ?Carbon $endDate): MetadataSheet
    {
        return new MetadataSheet($scope, $startDate, $endDate);
    }

    public static function productsSheet(): GenericSheet
    {
        $rows = Product::orderBy('name')
            ->get()
            ->map(function (Product $product): array {
                return [
                    $product->id,
                    $product->name,
                    (float) $product->price,
                    $product->active ? 'Yes' : 'No',
                    $product->created_at?->toDateTimeString(),
                    $product->updated_at?->toDateTimeString(),
                ];
            });

        return new GenericSheet('Products', [
            'ID',
            'Name',
            'Price',
            'Active',
            'Created At',
            'Updated At',
        ], $rows);
    }

    public static function dailySalesSheet(?Carbon $startDate, ?Carbon $endDate): GenericSheet
    {
        $query = DailySale::query()->orderBy('sale_date');
        self::applyDateRange($query, 'sale_date', $startDate, $endDate);

        $rows = $query->get()
            ->map(function (DailySale $sale): array {
                return [
                    $sale->id,
                    $sale->sale_date?->toDateString(),
                    (float) $sale->gross_total,
                    $sale->created_by,
                    $sale->created_at?->toDateTimeString(),
                ];
            });

        return new GenericSheet('Daily Sales', [
            'ID',
            'Sale Date',
            'Gross Total',
            'Created By',
            'Created At',
        ], $rows);
    }

    public static function saleLinesSheet(?Carbon $startDate, ?Carbon $endDate): GenericSheet
    {
        $query = SaleLine::query()
            ->join('daily_sales', 'daily_sales.id', '=', 'sale_lines.daily_sale_id')
            ->join('products', 'products.id', '=', 'sale_lines.product_id')
            ->select([
                'sale_lines.id',
                'daily_sales.sale_date',
                'sale_lines.daily_sale_id',
                'sale_lines.product_id',
                'products.name as product_name',
                'sale_lines.unit_price',
                'sale_lines.qty',
                'sale_lines.line_total',
                'sale_lines.created_at',
            ])
            ->orderBy('daily_sales.sale_date')
            ->orderBy('sale_lines.id');

        self::applyDateRange($query, 'daily_sales.sale_date', $startDate, $endDate);

        $rows = $query->get()
            ->map(function ($row): array {
                return [
                    $row->id,
                    $row->sale_date,
                    $row->daily_sale_id,
                    $row->product_id,
                    $row->product_name,
                    (float) $row->unit_price,
                    (float) $row->qty,
                    (float) $row->line_total,
                    $row->created_at,
                ];
            });

        return new GenericSheet('Sale Lines', [
            'ID',
            'Sale Date',
            'Daily Sale ID',
            'Product ID',
            'Product Name',
            'Unit Price',
            'Qty',
            'Line Total',
            'Created At',
        ], $rows);
    }

    public static function paymentsSheet(?Carbon $startDate, ?Carbon $endDate): GenericSheet
    {
        $query = Payment::query()
            ->join('daily_sales', 'daily_sales.id', '=', 'payments.daily_sale_id')
            ->select([
                'payments.id',
                'daily_sales.sale_date',
                'payments.daily_sale_id',
                'payments.method',
                'payments.amount',
                'payments.customer_name',
                'payments.created_at',
            ])
            ->orderBy('daily_sales.sale_date')
            ->orderBy('payments.id');

        self::applyDateRange($query, 'daily_sales.sale_date', $startDate, $endDate);

        $rows = $query->get()
            ->map(function ($row): array {
                return [
                    $row->id,
                    $row->sale_date,
                    $row->daily_sale_id,
                    $row->method,
                    (float) $row->amount,
                    $row->customer_name,
                    $row->created_at,
                ];
            });

        return new GenericSheet('Payments', [
            'ID',
            'Sale Date',
            'Daily Sale ID',
            'Method',
            'Amount',
            'Customer Name',
            'Created At',
        ], $rows);
    }

    public static function creditPaymentsSheet(?Carbon $startDate, ?Carbon $endDate): GenericSheet
    {
        $query = Payment::query()
            ->join('daily_sales', 'daily_sales.id', '=', 'payments.daily_sale_id')
            ->where('payments.method', 'credit')
            ->select([
                'payments.id',
                'daily_sales.sale_date',
                'payments.daily_sale_id',
                'payments.customer_name',
                'payments.amount',
                'payments.created_at',
            ])
            ->orderBy('daily_sales.sale_date')
            ->orderBy('payments.id');

        self::applyDateRange($query, 'daily_sales.sale_date', $startDate, $endDate);

        $rows = $query->get()
            ->map(function ($row): array {
                return [
                    $row->id,
                    $row->sale_date,
                    $row->daily_sale_id,
                    $row->customer_name,
                    (float) $row->amount,
                    $row->created_at,
                ];
            });

        return new GenericSheet('Credit Payments', [
            'ID',
            'Sale Date',
            'Daily Sale ID',
            'Customer Name',
            'Amount',
            'Created At',
        ], $rows);
    }

    public static function expensesSheet(?Carbon $startDate, ?Carbon $endDate): GenericSheet
    {
        $query = Expense::query()->orderBy('expense_date')->orderBy('id');
        self::applyDateRange($query, 'expense_date', $startDate, $endDate);

        $rows = $query->get()
            ->map(function (Expense $expense): array {
                return [
                    $expense->id,
                    $expense->expense_date?->toDateString(),
                    $expense->category,
                    (float) $expense->amount,
                    $expense->description,
                    $expense->created_by,
                    $expense->created_at?->toDateTimeString(),
                ];
            });

        return new GenericSheet('Expenses', [
            'ID',
            'Expense Date',
            'Category',
            'Amount',
            'Description',
            'Created By',
            'Created At',
        ], $rows);
    }

    public static function inventoryItemsSheet(): GenericSheet
    {
        $rows = InventoryItem::orderBy('name')
            ->get()
            ->map(function (InventoryItem $item): array {
                return [
                    $item->id,
                    $item->name,
                    $item->unit,
                    $item->min_level,
                    $item->active ? 'Yes' : 'No',
                    $item->created_at?->toDateTimeString(),
                    $item->updated_at?->toDateTimeString(),
                ];
            });

        return new GenericSheet('Inventory Items', [
            'ID',
            'Name',
            'Unit',
            'Min Level',
            'Active',
            'Created At',
            'Updated At',
        ], $rows);
    }

    public static function inventoryDailySheet(?Carbon $startDate, ?Carbon $endDate): GenericSheet
    {
        $query = InventoryDaily::query()
            ->join('inventory_items', 'inventory_items.id', '=', 'inventory_daily.inventory_item_id')
            ->select([
                'inventory_daily.id',
                'inventory_daily.inv_date',
                'inventory_daily.inventory_item_id',
                'inventory_items.name as item_name',
                'inventory_daily.yesterday_remaining',
                'inventory_daily.stock_in',
                'inventory_daily.today_remaining',
                'inventory_daily.auto_usage',
                'inventory_daily.created_by',
                'inventory_daily.created_at',
            ])
            ->orderBy('inventory_daily.inv_date')
            ->orderBy('inventory_daily.id');

        self::applyDateRange($query, 'inventory_daily.inv_date', $startDate, $endDate);

        $rows = $query->get()
            ->map(function ($row): array {
                return [
                    $row->id,
                    $row->inv_date,
                    $row->inventory_item_id,
                    $row->item_name,
                    (float) $row->yesterday_remaining,
                    (float) $row->stock_in,
                    (float) $row->today_remaining,
                    (float) $row->auto_usage,
                    $row->created_by,
                    $row->created_at,
                ];
            });

        return new GenericSheet('Inventory Daily', [
            'ID',
            'Date',
            'Inventory Item ID',
            'Item Name',
            'Yesterday Remaining',
            'Stock In',
            'Today Remaining',
            'Auto Usage',
            'Created By',
            'Created At',
        ], $rows);
    }

    private static function applyDateRange($query, string $column, ?Carbon $startDate, ?Carbon $endDate): void
    {
        if ($startDate && $endDate) {
            $query->whereBetween($column, [$startDate->toDateString(), $endDate->toDateString()]);
        }
    }
}
