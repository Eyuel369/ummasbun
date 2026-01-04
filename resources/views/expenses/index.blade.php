<x-app-shell>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Expenses</p>
                <h1 class="mt-2 text-2xl font-semibold text-slate-900">Expenses</h1>
                <p class="text-sm text-slate-500">Log and review daily expenses.</p>
            </div>
            @can('create', \App\Models\Expense::class)
                <a href="{{ route('expenses.create') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-slate-900 px-5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-slate-800">
                    Add Expense
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <x-alert variant="success" title="Success">
                {{ session('status') }}
            </x-alert>
        @endif

        <x-card>
            <form method="GET" action="{{ route('expenses.index') }}" class="grid gap-4 md:grid-cols-4">
                <x-input label="Start Date" name="start_date" type="date" value="{{ $filters['start_date'] ?? '' }}" />
                <x-input label="End Date" name="end_date" type="date" value="{{ $filters['end_date'] ?? '' }}" />
                <x-select label="Category" name="category">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" {{ ($filters['category'] ?? '') === $category ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </x-select>
                <div class="flex items-end gap-2">
                    <x-button type="submit" variant="secondary">Filter</x-button>
                    <a href="{{ route('expenses.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                        Reset
                    </a>
                </div>
            </form>
        </x-card>

        @if ($expenses->isEmpty())
            <x-card>
                <p class="text-sm text-slate-600">No expenses found for the selected filters.</p>
            </x-card>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($expenses as $expense)
                    <x-card>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-lg font-semibold text-slate-900">Rp {{ number_format($expense->amount, 0, '.', ',') }}</p>
                                <p class="text-sm text-slate-500">{{ $expense->expense_date->format('M j, Y') }}</p>
                                @if ($expense->description)
                                    <p class="mt-2 text-sm text-slate-600">{{ $expense->description }}</p>
                                @endif
                            </div>
                            <x-badge variant="neutral">{{ $expense->category }}</x-badge>
                        </div>

                        @can('update', $expense)
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="{{ route('expenses.edit', $expense) }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                                    Edit
                                </a>
                                <form
                                    method="POST"
                                    action="{{ route('expenses.destroy', $expense) }}"
                                    data-confirm
                                    data-confirm-title="Delete expense"
                                    data-confirm-message="Delete this expense entry?"
                                    data-confirm-approve="Delete"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <x-button size="sm" variant="danger">Delete</x-button>
                                </form>
                            </div>
                        @endcan
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
</x-app-shell>

