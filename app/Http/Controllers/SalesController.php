<?php

namespace App\Http\Controllers;

use App\Models\DailySale;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SaleLine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function today(): RedirectResponse
    {
        return redirect()->route('sales.show', ['date' => now()->toDateString()]);
    }

    public function show(Request $request, string $date): View
    {
        $this->authorize('viewAny', DailySale::class);

        $selectedDate = $this->parseDate($date);
        $dailySale = DailySale::with(['saleLines.product', 'payments'])
            ->whereDate('sale_date', $selectedDate->toDateString())
            ->first();

        $productQuery = Product::query()->where('active', true);
        if ($dailySale) {
            $productQuery->orWhereIn('id', $dailySale->saleLines->pluck('product_id'));
        }
        $products = $productQuery->orderBy('name')->get();

        $grossTotal = $dailySale?->saleLines->sum('line_total') ?? 0;
        $payments = $dailySale?->payments ?? collect();
        $paidTotal = $payments->sum('amount');
        $remainingTotal = $grossTotal - $paidTotal;
        $todayExpenseTotal = Expense::whereDate('expense_date', $selectedDate->toDateString())->sum('amount');
        $netProfit = $grossTotal - $todayExpenseTotal;
        $paymentTotals = [
            'cash' => (float) $payments->where('method', 'cash')->sum('amount'),
            'transfer' => (float) $payments->where('method', 'transfer')->sum('amount'),
            'credit' => (float) $payments->where('method', 'credit')->sum('amount'),
        ];
        $canEdit = $request->user()?->role === User::ROLE_OWNER || $selectedDate->isToday();

        return view('sales.today', [
            'selectedDate' => $selectedDate,
            'dailySale' => $dailySale,
            'products' => $products,
            'grossTotal' => $grossTotal,
            'paidTotal' => $paidTotal,
            'remainingTotal' => $remainingTotal,
            'todayExpenseTotal' => $todayExpenseTotal,
            'netProfit' => $netProfit,
            'paymentTotals' => $paymentTotals,
            'canEdit' => $canEdit,
        ]);
    }

    public function history(): View
    {
        $this->authorize('viewAny', DailySale::class);

        $sales = DailySale::orderByDesc('sale_date')->get();

        return view('sales.history', [
            'sales' => $sales,
        ]);
    }

    public function edit(DailySale $dailySale): View
    {
        $this->authorize('update', $dailySale);

        return view('sales.edit', compact('dailySale'));
    }

    public function storeLine(Request $request, string $date): RedirectResponse
    {
        $this->authorize('create', DailySale::class);

        $selectedDate = $this->parseDate($date);
        $this->ensureEditableDate($request->user(), $selectedDate);

        $data = $this->validateLine($request);
        $lineTotal = $this->calculateLineTotal($data['unit_price'], $data['qty']);

        $dailySale = DB::transaction(function () use ($request, $selectedDate, $data, $lineTotal): DailySale {
            $sale = DailySale::firstOrCreate(
                ['sale_date' => $selectedDate->toDateString()],
                [
                    'gross_total' => 0,
                    'created_by' => $request->user()->id,
                ]
            );

            $sale->saleLines()->create([
                'product_id' => $data['product_id'],
                'unit_price' => $data['unit_price'],
                'qty' => $data['qty'],
                'line_total' => $lineTotal,
            ]);

            $this->recalculateGrossTotal($sale);

            return $sale;
        });

        return redirect()->route('sales.show', ['date' => $selectedDate->toDateString()])
            ->with('status', 'Line item added.');
    }

    public function updateLine(Request $request, SaleLine $saleLine): RedirectResponse
    {
        $dailySale = $saleLine->dailySale;
        $this->authorize('update', $dailySale);

        $data = $this->validateLine($request);
        $lineTotal = $this->calculateLineTotal($data['unit_price'], $data['qty']);

        DB::transaction(function () use ($saleLine, $dailySale, $data, $lineTotal): void {
            $saleLine->update([
                'product_id' => $data['product_id'],
                'unit_price' => $data['unit_price'],
                'qty' => $data['qty'],
                'line_total' => $lineTotal,
            ]);

            $this->recalculateGrossTotal($dailySale);
        });

        return redirect()->route('sales.show', ['date' => $dailySale->sale_date->toDateString()])
            ->with('status', 'Line item updated.');
    }

    public function deleteLine(SaleLine $saleLine): RedirectResponse
    {
        $dailySale = $saleLine->dailySale;
        $this->authorize('update', $dailySale);

        DB::transaction(function () use ($saleLine, $dailySale): void {
            $saleLine->delete();
            $this->recalculateGrossTotal($dailySale);
        });

        return redirect()->route('sales.show', ['date' => $dailySale->sale_date->toDateString()])
            ->with('status', 'Line item removed.');
    }

    public function storePayment(Request $request, string $date): RedirectResponse
    {
        $this->authorize('create', DailySale::class);

        $selectedDate = $this->parseDate($date);
        $this->ensureEditableDate($request->user(), $selectedDate);

        $data = $this->validatePayment($request);
        if ($data['method'] !== 'credit') {
            $data['customer_name'] = null;
        }

        $dailySale = DailySale::firstOrCreate(
            ['sale_date' => $selectedDate->toDateString()],
            [
                'gross_total' => 0,
                'created_by' => $request->user()->id,
            ]
        );

        $dailySale->payments()->create($data);

        return redirect()->route('sales.show', ['date' => $selectedDate->toDateString()])
            ->with('status', 'Payment recorded.');
    }

    public function deletePayment(Payment $payment): RedirectResponse
    {
        $dailySale = $payment->dailySale;
        $this->authorize('update', $dailySale);

        $payment->delete();

        return redirect()->route('sales.show', ['date' => $dailySale->sale_date->toDateString()])
            ->with('status', 'Payment removed.');
    }

    public function debtors(): View
    {
        Gate::authorize('view-debtors');

        return redirect()->route('credit.index');
    }

    private function recalculateGrossTotal(DailySale $dailySale): void
    {
        $grossTotal = $dailySale->saleLines()->sum('line_total');
        $dailySale->update(['gross_total' => $grossTotal]);
    }

    private function calculateLineTotal(float $unitPrice, float $qty): float
    {
        return round($unitPrice * $qty, 2);
    }

    private function parseDate(string $date): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Exception $exception) {
            abort(404);
        }
    }

    private function ensureEditableDate(?User $user, Carbon $date): void
    {
        if (! $user) {
            abort(403);
        }

        if ($user->role !== User::ROLE_OWNER && ! $date->isToday()) {
            abort(403);
        }
    }

    /**
     * @return array{product_id: int, unit_price: float, qty: float}
     */
    private function validateLine(Request $request): array
    {
        return $request->validate(
            [
                'product_id' => ['required', 'integer', 'exists:products,id'],
                'unit_price' => ['required', 'numeric', 'min:0'],
                'qty' => ['required', 'numeric', 'min:0.01'],
            ],
            [
                'product_id.required' => 'Select a product to add.',
                'unit_price.required' => 'Enter a unit price.',
                'qty.required' => 'Enter a quantity.',
                'qty.min' => 'Quantity must be greater than zero.',
            ]
        );
    }

    /**
     * @return array{method: string, amount: float, customer_name: ?string}
     */
    private function validatePayment(Request $request): array
    {
        return $request->validate(
            [
                'method' => ['required', 'in:cash,transfer,credit'],
                'amount' => ['required', 'numeric', 'min:0.01'],
                'customer_name' => ['nullable', 'string', 'max:255', 'required_if:method,credit'],
            ],
            [
                'method.required' => 'Select a payment method.',
                'amount.required' => 'Enter a payment amount.',
                'amount.min' => 'Payment amount must be greater than zero.',
                'customer_name.required_if' => 'Customer name is required for credit payments.',
            ]
        );
    }
}
