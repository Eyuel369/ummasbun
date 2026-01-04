<?php

namespace App\Http\Controllers;

use App\Models\DailySale;
use App\Models\Expense;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewReports', DailySale::class);

        $selectedDate = now()->startOfDay();
        $dailySale = DailySale::with('payments')
            ->whereDate('sale_date', $selectedDate->toDateString())
            ->first();

        $grossTotal = (float) ($dailySale?->gross_total ?? 0);
        $expenseTotal = (float) Expense::whereDate('expense_date', $selectedDate->toDateString())->sum('amount');
        $netProfit = $grossTotal - $expenseTotal;

        $payments = $dailySale?->payments ?? collect();
        $paymentTotals = [
            'cash' => (float) $payments->where('method', 'cash')->sum('amount'),
            'transfer' => (float) $payments->where('method', 'transfer')->sum('amount'),
            'credit' => (float) $payments->where('method', 'credit')->sum('amount'),
        ];

        return view('dashboard', [
            'selectedDate' => $selectedDate,
            'grossTotal' => $grossTotal,
            'expenseTotal' => $expenseTotal,
            'netProfit' => $netProfit,
            'paymentTotals' => $paymentTotals,
        ]);
    }
}
