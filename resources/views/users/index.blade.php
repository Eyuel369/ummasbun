<x-app-shell>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Users</p>
                <h1 class="mt-2 text-2xl font-semibold text-slate-900">User Management</h1>
                <p class="text-sm text-slate-500">Manage access, roles, and activity.</p>
            </div>
            <a href="{{ route('users.create') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-slate-900 px-5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-slate-800">
                Add User
            </a>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <x-alert variant="success" title="Success">
                {{ session('status') }}
            </x-alert>
        @endif

        <x-card>
            <form method="GET" action="{{ route('users.index') }}" class="grid gap-4 sm:grid-cols-3 sm:items-end">
                <x-input label="Search" name="q" value="{{ $search }}" placeholder="Name or email" />
                <div class="flex items-center gap-2 sm:col-span-2">
                    <x-button type="submit">Search</x-button>
                    <a href="{{ route('users.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                        Clear
                    </a>
                </div>
            </form>
        </x-card>

        @if ($users->isEmpty())
            <x-card>
                <p class="text-sm text-slate-600">No users found.</p>
            </x-card>
        @else
            <div class="space-y-3">
                @foreach ($users as $user)
                    <x-card>
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-lg font-semibold text-slate-900">{{ $user->name }}</p>
                                <p class="text-sm text-slate-500">{{ $user->email }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-badge variant="neutral">{{ ucwords(str_replace('_', ' ', $user->role)) }}</x-badge>
                                <x-badge variant="{{ $user->active ? 'success' : 'danger' }}">
                                    {{ $user->active ? 'Active' : 'Inactive' }}
                                </x-badge>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('users.edit', $user) }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                                Edit
                            </a>
                            @if (auth()->id() === $user->id)
                                <span class="text-xs text-slate-500">This is your account.</span>
                            @endif
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
</x-app-shell>
