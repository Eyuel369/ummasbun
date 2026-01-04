<x-app-shell>
    <x-slot name="header">
        <div class="space-y-4">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Sales</p>
                    <h1 class="mt-2 text-2xl font-semibold text-slate-900">Daily Sales</h1>
                    <p class="text-sm text-slate-500">Add items, adjust quantities, and keep totals tidy.</p>
                </div>
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="sale-date" class="text-xs font-semibold uppercase tracking-widest text-slate-500">Date</label>
                        <input
                            id="sale-date"
                            type="date"
                            value="{{ $selectedDate->toDateString() }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-400 focus:ring-2 focus:ring-slate-200"
                        >
                    </div>
                    <x-badge variant="{{ $canEdit ? 'success' : 'neutral' }}">
                        {{ $canEdit ? 'Editable' : 'Read only' }}
                    </x-badge>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Gross Sales</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">Rp {{ number_format($grossTotal, 0, '.', ',') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Total Expenses</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">Rp {{ number_format($todayExpenseTotal, 0, '.', ',') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Net Profit</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">Rp {{ number_format($netProfit, 0, '.', ',') }}</p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Cash</p>
                    <p class="mt-2 font-semibold text-slate-900">Rp {{ number_format($paymentTotals['cash'] ?? 0, 0, '.', ',') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Transfer</p>
                    <p class="mt-2 font-semibold text-slate-900">Rp {{ number_format($paymentTotals['transfer'] ?? 0, 0, '.', ',') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Credit</p>
                    <p class="mt-2 font-semibold text-slate-900">Rp {{ number_format($paymentTotals['credit'] ?? 0, 0, '.', ',') }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <x-alert variant="success" title="Success">
                {{ session('status') }}
            </x-alert>
        @endif

        @if ($errors->any())
            <x-alert variant="danger" title="Please review the form">
                Fix the highlighted fields and try again.
            </x-alert>
        @endif

        @if (! $canEdit)
            <x-alert variant="info" title="Viewing history">
                You can review past sales, but only owners can edit previous dates.
            </x-alert>
        @endif

        @if ($canEdit)
            @if ($products->isEmpty())
                <x-alert variant="warning" title="No active products">
                    Add active products before creating sales lines.
                </x-alert>
            @else
            <x-card title="Add Item" subtitle="Choose a product and quantity.">
                <form method="POST" action="{{ route('sales.lines.store', ['date' => $selectedDate->toDateString()]) }}" class="space-y-4" data-line-form data-mode="create">
                    @csrf

                    <x-select label="Product" name="product_id" data-product required>
                        <option value="">Select a product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}" {{ (int) old('product_id') === $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('product_id')" />

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <x-input label="Unit Price" name="unit_price" type="number" step="0.01" min="0" value="{{ old('unit_price') }}" data-unit-price required />
                            <x-input-error :messages="$errors->get('unit_price')" />
                        </div>
                        <div>
                            <x-input label="Qty" name="qty" type="number" step="0.01" min="0.01" value="{{ old('qty') }}" data-qty required />
                            <x-input-error :messages="$errors->get('qty')" />
                        </div>
                        <div>
                            <x-input label="Line Total" type="number" step="0.01" readonly data-line-total class="bg-slate-50" />
                        </div>
                    </div>

                    <x-button type="submit" data-submit>Add Line Item</x-button>
                </form>
            </x-card>
            @endif
        @endif

        <x-card title="Line Items" subtitle="Tap an item to adjust quantities.">
            @php
                $lines = $dailySale?->saleLines ?? collect();
            @endphp

            @if ($lines->isEmpty())
                <p class="text-sm text-slate-600">No items added yet. Start with a product and quantity.</p>
            @else
                <div class="space-y-4">
                    @foreach ($lines as $line)
                        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                            @if ($canEdit)
                                <form method="POST" action="{{ route('sales.lines.update', $line) }}" class="space-y-4" data-line-form>
                                    @csrf
                                    @method('PATCH')

                                    <x-select label="Product" name="product_id" data-product required>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}" {{ $line->product_id === $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </x-select>

                                    <div class="grid gap-4 sm:grid-cols-3">
                                        <x-input label="Unit Price" name="unit_price" type="number" step="0.01" min="0" value="{{ $line->unit_price }}" data-unit-price required />
                                        <x-input label="Qty" name="qty" type="number" step="0.01" min="0.01" value="{{ $line->qty }}" data-qty required />
                                        <x-input label="Line Total" type="number" step="0.01" readonly data-line-total class="bg-slate-50" value="{{ $line->line_total }}" />
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <x-button type="submit" size="sm">Save</x-button>
                                        <x-button type="submit" size="sm" variant="danger" form="delete-line-{{ $line->id }}">
                                            Delete
                                        </x-button>
                                    </div>
                                </form>
                                <form
                                    id="delete-line-{{ $line->id }}"
                                    method="POST"
                                    action="{{ route('sales.lines.delete', $line) }}"
                                    data-confirm
                                    data-confirm-title="Delete line item"
                                    data-confirm-message="Delete this line item from today's sales?"
                                    data-confirm-approve="Delete"
                                >
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @else
                                <div class="flex flex-wrap items-center justify-between gap-4">
                                    <div>
                                        <p class="text-base font-semibold text-slate-900">{{ $line->product?->name }}</p>
                                        <p class="text-sm text-slate-500">Qty {{ $line->qty }} - Rp {{ number_format($line->unit_price, 0, '.', ',') }}</p>
                                    </div>
                                    <x-badge variant="neutral">Rp {{ number_format($line->line_total, 0, '.', ',') }}</x-badge>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <x-card title="Payments" subtitle="Log cash, transfer, or credit payments.">
            @php
                $payments = $dailySale?->payments ?? collect();
                $remainingLabel = $remainingTotal < 0 ? 'Over' : 'Remaining';
            @endphp

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Gross Total</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">Rp {{ number_format($grossTotal, 0, '.', ',') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Paid Total</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">Rp {{ number_format($paidTotal, 0, '.', ',') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ $remainingLabel }}</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">Rp {{ number_format(abs($remainingTotal), 0, '.', ',') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 sm:col-span-3">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Today Expenses Total</p>
                    <p class="mt-2 text-lg font-semibold text-slate-900">Rp {{ number_format($todayExpenseTotal, 0, '.', ',') }}</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                @if ($canEdit)
                    <form method="POST" action="{{ route('sales.payments.store', ['date' => $selectedDate->toDateString()]) }}" class="space-y-4" data-payment-form>
                        @csrf

                        <div class="grid gap-4 sm:grid-cols-3">
                            <x-select label="Method" name="method" data-payment-method required>
                                <option value="">Select method</option>
                                <option value="cash" {{ old('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="transfer" {{ old('method') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                                <option value="credit" {{ old('method') === 'credit' ? 'selected' : '' }}>Credit</option>
                            </x-select>

                            <x-input label="Amount" name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount') }}" required />

                            <div data-credit-field>
                                <x-input label="Customer Name" name="customer_name" value="{{ old('customer_name') }}" placeholder="Required for credit" />
                            </div>
                        </div>

                        <x-input-error :messages="$errors->get('method')" />
                        <x-input-error :messages="$errors->get('amount')" />
                        <x-input-error :messages="$errors->get('customer_name')" />

                        <x-button type="submit">Add Payment</x-button>
                    </form>
                @endif

                @if ($payments->isEmpty())
                    <p class="text-sm text-slate-600">No payments recorded yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($payments as $payment)
                            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ ucfirst($payment->method) }}</p>
                                    @if ($payment->method === 'credit')
                                        <p class="text-xs text-slate-500">{{ $payment->customer_name }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-semibold text-slate-900">Rp {{ number_format($payment->amount, 0, '.', ',') }}</span>
                                    @if ($canEdit)
                                        <form
                                            method="POST"
                                            action="{{ route('sales.payments.delete', $payment) }}"
                                            data-confirm
                                            data-confirm-title="Delete payment"
                                            data-confirm-message="Delete this payment entry?"
                                            data-confirm-approve="Delete"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <x-button size="sm" variant="danger">Delete</x-button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-card>
    </div>

    <script>
        const setupLineForm = (form) => {
            const product = form.querySelector('[data-product]');
            const unitPrice = form.querySelector('[data-unit-price]');
            const qty = form.querySelector('[data-qty]');
            const lineTotal = form.querySelector('[data-line-total]');
            const submitButton = form.querySelector('[data-submit]');

            const parseNumber = (value) => {
                const parsed = parseFloat(value);
                return Number.isFinite(parsed) ? parsed : 0;
            };

            const updateTotal = () => {
                if (!lineTotal) {
                    return;
                }
                const total = parseNumber(unitPrice?.value) * parseNumber(qty?.value);
                lineTotal.value = total ? total.toFixed(2) : '';
            };

            const updateSubmit = () => {
                if (!submitButton) {
                    return;
                }
                const hasProduct = Boolean(product?.value);
                const quantity = parseNumber(qty?.value);
                submitButton.disabled = !hasProduct || quantity <= 0;
            };

            if (product && unitPrice) {
                product.addEventListener('change', () => {
                    const selected = product.selectedOptions[0];
                    if (selected && selected.dataset.price && !unitPrice.dataset.edited) {
                        unitPrice.value = selected.dataset.price;
                    }
                    updateTotal();
                    updateSubmit();
                });
            }

            if (unitPrice) {
                unitPrice.addEventListener('input', () => {
                    unitPrice.dataset.edited = 'true';
                    updateTotal();
                    updateSubmit();
                });
            }

            if (qty) {
                qty.addEventListener('input', () => {
                    updateTotal();
                    updateSubmit();
                });
            }

            updateTotal();
            updateSubmit();
        };

        document.querySelectorAll('[data-line-form]').forEach(setupLineForm);

        document.querySelectorAll('[data-payment-form]').forEach((form) => {
            const method = form.querySelector('[data-payment-method]');
            const creditField = form.querySelector('[data-credit-field]');
            const creditInput = creditField ? creditField.querySelector('input') : null;

            const toggleCredit = () => {
                if (!method || !creditField) {
                    return;
                }
                const isCredit = method.value === 'credit';
                creditField.classList.toggle('hidden', !isCredit);
                if (creditInput) {
                    creditInput.required = isCredit;
                }
            };

            if (method) {
                method.addEventListener('change', toggleCredit);
            }

            toggleCredit();
        });

        const dateInput = document.getElementById('sale-date');
        if (dateInput) {
            dateInput.addEventListener('change', () => {
                if (!dateInput.value) {
                    return;
                }
                window.location = `{{ url('/sales') }}/${dateInput.value}`;
            });
        }
    </script>
</x-app-shell>

