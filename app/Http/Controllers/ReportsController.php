<?php

namespace App\Http\Controllers;

use App\Models\DailySale;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\SaleLine;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewReports', DailySale::class);

        $startDate = $this->parseDate($request->query('start', now()->subDays(6)->toDateString()));
        $endDate = $this->parseDate($request->query('end', now()->toDateString()));
        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $salesRows = DailySale::whereBetween('sale_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('sale_date')
            ->get(['sale_date', 'gross_total']);
        $salesByDate = $salesRows->mapWithKeys(function (DailySale $sale): array {
            return [$sale->sale_date->toDateString() => (float) $sale->gross_total];
        });

        $salesTrendLabels = [];
        $salesTrendValues = [];
        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $key = $date->toDateString();
            $salesTrendLabels[] = $date->format('M j');
            $salesTrendValues[] = (float) ($salesByDate[$key] ?? 0);
        }

        $expenseTotals = array_fill_keys(Expense::CATEGORIES, 0.0);
        $expenseRows = Expense::select('category')
            ->selectRaw('SUM(amount) as total')
            ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('category')
            ->orderBy('category')
            ->get();
        foreach ($expenseRows as $row) {
            $expenseTotals[$row->category] = (float) $row->total;
        }

        $paymentTotals = [
            'cash' => 0.0,
            'transfer' => 0.0,
            'credit' => 0.0,
        ];
        $paymentRows = Payment::select('method')
            ->selectRaw('SUM(amount) as total')
            ->join('daily_sales', 'daily_sales.id', '=', 'payments.daily_sale_id')
            ->whereBetween('daily_sales.sale_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('method')
            ->get();
        foreach ($paymentRows as $row) {
            $paymentTotals[$row->method] = (float) $row->total;
        }

        $bestSellers = SaleLine::select('products.name')
            ->selectRaw('SUM(sale_lines.qty) as total_qty')
            ->selectRaw('SUM(sale_lines.line_total) as total_revenue')
            ->join('daily_sales', 'daily_sales.id', '=', 'sale_lines.daily_sale_id')
            ->join('products', 'products.id', '=', 'sale_lines.product_id')
            ->whereBetween('daily_sales.sale_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('products.name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        $creditTotals = Payment::selectRaw("COALESCE(NULLIF(customer_name, ''), 'Unknown') as customer")
            ->selectRaw('SUM(amount) as total')
            ->join('daily_sales', 'daily_sales.id', '=', 'payments.daily_sale_id')
            ->where('method', 'credit')
            ->whereBetween('daily_sales.sale_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('customer')
            ->orderByDesc('total')
            ->get();

        return view('reports.index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'salesTrend' => [
                'labels' => $salesTrendLabels,
                'values' => $salesTrendValues,
            ],
            'expenseByCategory' => [
                'labels' => array_keys($expenseTotals),
                'values' => array_values($expenseTotals),
            ],
            'paymentSplit' => [
                'labels' => ['Cash', 'Transfer', 'Credit'],
                'values' => [
                    $paymentTotals['cash'],
                    $paymentTotals['transfer'],
                    $paymentTotals['credit'],
                ],
            ],
            'bestSellers' => $bestSellers,
            'creditTotals' => $creditTotals,
        ]);
    }

    private function parseDate(string $date): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Exception $exception) {
            abort(404);
        }
    }
}
