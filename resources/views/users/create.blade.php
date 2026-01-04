<x-app-shell>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Users</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Add User</h1>
            <p class="text-sm text-slate-500">Create a new account and assign a role.</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if ($errors->any())
            <x-alert variant="danger" title="Please review the form">
                Fix the highlighted fields and try again.
            </x-alert>
        @endif

        <x-card>
            <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
                @csrf

                <x-input label="Name" name="name" value="{{ old('name') }}" required />
                <x-input-error :messages="$errors->get('name')" />

                <x-input label="Email" name="email" type="email" value="{{ old('email') }}" required />
                <x-input-error :messages="$errors->get('email')" />

                <x-select label="Role" name="role" required>
                    <option value="">Select a role</option>
                    <option value="owner" {{ old('role') === 'owner' ? 'selected' : '' }}>Owner</option>
                    <option value="cashier" {{ old('role', 'cashier') === 'cashier' ? 'selected' : '' }}>Cashier</option>
                    <option value="stock_manager" {{ old('role') === 'stock_manager' ? 'selected' : '' }}>Stock Manager</option>
                </x-select>
                <x-input-error :messages="$errors->get('role')" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input label="Temporary Password (optional)" name="password" type="password" autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" />
                    </div>
                    <div>
                        <x-input label="Confirm Password" name="password_confirmation" type="password" autocomplete="new-password" />
                    </div>
                </div>

                @php
                    $activeChecked = old('active') !== null ? (bool) old('active') : true;
                    $resetChecked = old('send_reset_link') !== null ? (bool) old('send_reset_link') : true;
                @endphp

                <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600">
                    <label class="flex items-center gap-2">
                        <input type="hidden" name="active" value="0">
                        <input type="checkbox" name="active" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300" {{ $activeChecked ? 'checked' : '' }}>
                        Active
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="hidden" name="send_reset_link" value="0">
                        <input type="checkbox" name="send_reset_link" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300" {{ $resetChecked ? 'checked' : '' }}>
                        Send password reset link
                    </label>
                </div>

                <div class="flex flex-wrap gap-2">
                    <x-button type="submit">Create User</x-button>
                    <a href="{{ route('users.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                        Cancel
                    </a>
                </div>
            </form>
        </x-card>
    </div>
</x-app-shell>
