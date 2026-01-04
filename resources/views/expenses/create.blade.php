<x-app-shell>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Expenses</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Log Expense</h1>
            <p class="text-sm text-slate-500">Record today's expense entries.</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if ($errors->any())
            <x-alert variant="danger" title="Please review the form">
                Fix the highlighted fields and try again.
            </x-alert>
        @endif

        <x-card>
            <form method="POST" action="{{ route('expenses.store') }}" class="space-y-5">
                @csrf

                <x-input label="Date" name="expense_date" type="date" value="{{ old('expense_date', now()->toDateString()) }}" />
                <x-input-error :messages="$errors->get('expense_date')" />

                <x-select label="Category" name="category">
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('category')" />

                <x-input label="Amount" name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount') }}" placeholder="0.00" />
                <x-input-error :messages="$errors->get('amount')" />

                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-widest text-slate-500" for="description">
                        Description
                    </label>
                    <textarea id="description" name="description" rows="3" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-400 focus:ring-2 focus:ring-slate-200" placeholder="Optional notes">{{ old('description') }}</textarea>
                </div>
                <x-input-error :messages="$errors->get('description')" />

                <div class="flex flex-wrap gap-2">
                    <x-button type="submit">Save Expense</x-button>
                    <a href="{{ route('expenses.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                        Cancel
                    </a>
                </div>
            </form>
        </x-card>
    </div>
</x-app-shell>

