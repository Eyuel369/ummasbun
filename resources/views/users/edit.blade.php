<x-app-shell>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Users</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Edit User</h1>
            <p class="text-sm text-slate-500">Update role, access, and password.</p>
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

        <x-card>
            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <x-input label="Name" name="name" value="{{ old('name', $user->name) }}" required />
                <x-input-error :messages="$errors->get('name')" />

                <x-input label="Email" name="email" type="email" value="{{ old('email', $user->email) }}" required />
                <x-input-error :messages="$errors->get('email')" />

                <x-select label="Role" name="role" required>
                    <option value="">Select a role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_', ' ', $role)) }}
                        </option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('role')" />

                @php
                    $activeChecked = old('active') !== null ? (bool) old('active') : (bool) $user->active;
                @endphp
                <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600">
                    <label class="flex items-center gap-2">
                        @if (! $isSelf)
                            <input type="hidden" name="active" value="0">
                        @endif
                        <input
                            type="checkbox"
                            name="active"
                            value="1"
                            class="rounded border-slate-300 text-slate-900 focus:ring-slate-300"
                            {{ $activeChecked ? 'checked' : '' }}
                            {{ $isSelf ? 'disabled' : '' }}
                        >
                        Active
                    </label>
                    @if ($isSelf)
                        <input type="hidden" name="active" value="1">
                        <span class="text-xs text-slate-500">You cannot deactivate your own account.</span>
                    @endif
                    <x-input-error :messages="$errors->get('active')" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input label="New Password (optional)" name="password" type="password" autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" />
                    </div>
                    <div>
                        <x-input label="Confirm Password" name="password_confirmation" type="password" autocomplete="new-password" />
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <x-button type="submit">Save Changes</x-button>
                    <a href="{{ route('users.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                        Back
                    </a>
                </div>
            </form>
        </x-card>

        <x-card title="Password Reset" subtitle="Send a reset link to the user's email address.">
            <form method="POST" action="{{ route('users.reset-link', $user) }}">
                @csrf
                <x-button type="submit" variant="secondary">Send Reset Link</x-button>
            </form>
        </x-card>
    </div>
</x-app-shell>
