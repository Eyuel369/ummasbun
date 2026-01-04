<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpensesController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Expense::class);

        $query = Expense::query();

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $category = $request->query('category');

        if ($startDate) {
            $query->whereDate('expense_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('expense_date', '<=', $endDate);
        }

        if ($category) {
            $query->where('category', $category);
        }

        $expenses = $query->orderByDesc('expense_date')->get();

        return view('expenses.index', [
            'expenses' => $expenses,
            'categories' => Expense::CATEGORIES,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'category' => $category,
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Expense::class);

        return view('expenses.create', [
            'categories' => Expense::CATEGORIES,
        ]);
    }

    public function edit(Expense $expense): View
    {
        $this->authorize('update', $expense);

        return view('expenses.edit', [
            'expense' => $expense,
            'categories' => Expense::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Expense::class);

        $data = $this->validatePayload($request);
        $data['created_by'] = $request->user()->id;

        Expense::create($data);

        return redirect()->route('expenses.index')->with('status', 'Expense added.');
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorize('update', $expense);

        $data = $this->validatePayload($request);
        $expense->update($data);

        return redirect()->route('expenses.index')->with('status', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return redirect()->route('expenses.index')->with('status', 'Expense removed.');
    }

    /**
     * @return array{expense_date: string, category: string, amount: float, description: ?string}
     */
    private function validatePayload(Request $request): array
    {
        return $request->validate(
            [
                'expense_date' => ['required', 'date'],
                'category' => ['required', 'in:'.implode(',', Expense::CATEGORIES)],
                'amount' => ['required', 'numeric', 'min:0.01'],
                'description' => ['nullable', 'string', 'max:500'],
            ],
            [
                'expense_date.required' => 'Select an expense date.',
                'category.required' => 'Select a category.',
                'amount.required' => 'Enter an amount.',
                'amount.min' => 'Amount must be greater than zero.',
            ]
        );
    }
}
