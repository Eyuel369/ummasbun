@props(['title' => null])

@php
    $user = auth()->user();
    $role = $user?->role;
    $today = now()->format('D, M j');
    $initials = 'UA';

    if ($user?->name) {
        $initials = '';
        foreach (preg_split('/\s+/', trim($user->name)) as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        $initials = substr($initials, 0, 2);
    }

    $navByRole = [
        \App\Models\User::ROLE_OWNER => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => ['dashboard'], 'icon' => 'home'],
            ['label' => 'Sales', 'route' => 'sales.today', 'active' => ['sales.*'], 'icon' => 'receipt'],
            ['label' => 'Expenses', 'route' => 'expenses.index', 'active' => ['expenses.*'], 'icon' => 'cash'],
            ['label' => 'Inventory', 'route' => 'inventory.today', 'active' => ['inventory.*'], 'icon' => 'boxes'],
            ['label' => 'Reports', 'route' => 'reports.index', 'active' => ['reports.*'], 'icon' => 'chart'],
            ['label' => 'Export', 'route' => 'exports.index', 'active' => ['exports.*'], 'icon' => 'arrow-down'],
            ['label' => 'Users', 'route' => 'users.index', 'active' => ['users.*'], 'icon' => 'users'],
            ['label' => 'Settings', 'route' => 'settings.index', 'active' => ['settings.*'], 'icon' => 'cog'],
        ],
        \App\Models\User::ROLE_CASHIER => [
            ['label' => 'Sales', 'route' => 'sales.today', 'active' => ['sales.*'], 'icon' => 'receipt'],
            ['label' => 'Expenses', 'route' => 'expenses.index', 'active' => ['expenses.*'], 'icon' => 'cash'],
            ['label' => 'Credit', 'route' => 'credit.index', 'active' => ['credit.*'], 'icon' => 'credit-card'],
            ['label' => 'Export', 'route' => 'exports.index', 'active' => ['exports.*'], 'icon' => 'arrow-down'],
        ],
        \App\Models\User::ROLE_STOCK_MANAGER => [
            ['label' => 'Inventory', 'route' => 'inventory.today', 'active' => ['inventory.*'], 'icon' => 'boxes'],
            ['label' => 'Export', 'route' => 'exports.index', 'active' => ['exports.*'], 'icon' => 'arrow-down'],
        ],
    ];

    $navItems = $navByRole[$role] ?? [];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title.' | '.config('app.name', 'Ummasbun') : config('app.name', 'Ummasbun') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 text-slate-900 antialiased font-sans">
        <div class="page-shell flex min-h-screen flex-col">
            <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
                <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-900 text-xs font-semibold text-white">
                            {{ $initials }}
                        </div>
                        <div class="leading-tight">
                            <p class="text-sm font-semibold">{{ config('app.name', 'Ummasbun') }}</p>
                            <p class="text-xs text-slate-500">{{ $today }}</p>
                        </div>
                    </div>

                    @if ($user)
                        <details class="relative">
                            <summary class="flex cursor-pointer items-center gap-2 rounded-full border border-slate-200 px-2 py-1 text-sm text-slate-600 transition hover:text-slate-900">
                                <span class="hidden sm:inline">{{ $user->name }}</span>
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-700">
                                    {{ $initials }}
                                </span>
                                <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                                </svg>
                            </summary>
                            <div class="absolute right-0 mt-2 w-48 rounded-2xl border border-slate-200 bg-white p-2 text-sm shadow-lg">
                                <div class="px-3 py-2 text-xs uppercase tracking-widest text-slate-400">
                                    {{ $role ? ucwords(str_replace('_', ' ', $role)) : 'User' }}
                                </div>
                                <a href="{{ route('profile.edit') }}" class="block rounded-xl px-3 py-2 text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                                    Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="mt-1 w-full rounded-xl px-3 py-2 text-left text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                                        Log out
                                    </button>
                                </form>
                            </div>
                        </details>
                    @endif
                </div>

                @if (count($navItems))
                    <nav class="hidden border-t border-slate-200 bg-white md:block">
                        <div class="mx-auto flex max-w-6xl items-center gap-2 overflow-x-auto px-4 py-2 text-sm sm:px-6 lg:px-8">
                            @foreach ($navItems as $item)
                                @php
                                    $isActive = false;
                                    foreach ($item['active'] as $pattern) {
                                        if (request()->routeIs($pattern)) {
                                            $isActive = true;
                                            break;
                                        }
                                    }
                                @endphp
                                <a
                                    href="{{ route($item['route']) }}"
                                    class="{{ $isActive ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900' }} rounded-full px-4 py-1.5 text-xs font-semibold uppercase tracking-widest transition"
                                >
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </nav>
                @endif
            </header>

            <main class="flex-1 pb-28">
                <div class="loading-zone relative">
                    <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
                        <div class="loading-content">
                            @isset($header)
                                <div class="mb-6">
                                    {{ $header }}
                                </div>
                            @endisset

                            {{ $slot }}
                        </div>
                    </div>

                    <div class="loading-ghosts" aria-hidden="true">
                        <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
                            <div class="loading-surface loading-surface-full min-h-full">
                            <div class="mt-6 flex flex-wrap gap-2">
                                <div class="loading-skeleton h-8 w-20 rounded-full"></div>
                                <div class="loading-skeleton h-8 w-24 rounded-full"></div>
                                <div class="loading-skeleton h-8 w-16 rounded-full"></div>
                                <div class="loading-skeleton h-8 w-24 rounded-full"></div>
                                <div class="loading-skeleton h-8 w-20 rounded-full"></div>
                            </div>
                                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div class="loading-skeleton h-24 rounded-2xl"></div>
                                    <div class="loading-skeleton h-24 rounded-2xl"></div>
                                    <div class="loading-skeleton h-24 rounded-2xl"></div>
                                </div>
                                <div class="mt-6 space-y-3">
                                    <div class="loading-skeleton h-4 w-5/6 rounded-full"></div>
                                    <div class="loading-skeleton h-4 w-full rounded-full"></div>
                                    <div class="loading-skeleton h-4 w-4/6 rounded-full"></div>
                                    <div class="loading-skeleton h-4 w-3/6 rounded-full"></div>
                                </div>
                                <div class="mt-6 flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-slate-500">
                                    <span class="loading-dot loading-pulse inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                                    Loading
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            @if (count($navItems))
                <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 backdrop-blur md:hidden">
                    <div class="mx-auto flex max-w-md items-center gap-2 overflow-x-auto px-3 py-2">
                        @foreach ($navItems as $item)
                            @php
                                $isActive = false;
                                foreach ($item['active'] as $pattern) {
                                    if (request()->routeIs($pattern)) {
                                        $isActive = true;
                                        break;
                                    }
                                }
                            @endphp
                            <a
                                href="{{ route($item['route']) }}"
                                class="{{ $isActive ? 'text-slate-900' : 'text-slate-500' }} flex min-w-[72px] flex-1 flex-col items-center gap-1 rounded-2xl px-2 py-2 text-[10px] font-semibold uppercase tracking-widest transition"
                            >
                                <span class="{{ $isActive ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500' }} flex h-9 w-9 items-center justify-center rounded-2xl">
                                    @switch($item['icon'])
                                        @case('home')
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-7.41a1.5 1.5 0 011.92 0L22.5 12M4.5 9.75V20.25A1.5 1.5 0 006 21.75h4.5a.75.75 0 00.75-.75V16.5a.75.75 0 01.75-.75h2.5a.75.75 0 01.75.75V21a.75.75 0 00.75.75H18a1.5 1.5 0 001.5-1.5V9.75" />
                                            </svg>
                                            @break
                                        @case('receipt')
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25h6m-6-4.5h6M5.25 3.75h13.5a.75.75 0 01.75.75v16.5l-3-1.5-3 1.5-3-1.5-3 1.5-3-1.5-3 1.5V4.5a.75.75 0 01.75-.75z" />
                                            </svg>
                                            @break
                                        @case('cash')
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5v9H2.25z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 8.25a3 3 0 003 3h6a3 3 0 003-3" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 12.75h0" />
                                            </svg>
                                            @break
                                        @case('boxes')
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h9v9h-9z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h6v6H3zM15 15h6v6h-6z" />
                                            </svg>
                                            @break
                                        @case('chart')
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 15.75V10.5m4.5 5.25V7.5m4.5 8.25V12" />
                                            </svg>
                                            @break
                                        @case('arrow-down')
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l4.5-4.5M12 15l-4.5-4.5M4.5 21h15" />
                                            </svg>
                                            @break
                                        @case('users')
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5a6 6 0 10-6 0M19.5 21a9 9 0 00-15 0" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            @break
                                        @case('cog')
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75a2.25 2.25 0 100 4.5 2.25 2.25 0 000-4.5z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12a7.5 7.5 0 01-.12 1.34l2.03 1.58-1.5 2.6-2.39-.96a7.52 7.52 0 01-2.31 1.34l-.36 2.55h-3l-.36-2.55a7.52 7.52 0 01-2.31-1.34l-2.39.96-1.5-2.6 2.03-1.58A7.5 7.5 0 014.5 12c0-.46.04-.91.12-1.34L2.59 9.08l1.5-2.6 2.39.96a7.52 7.52 0 012.31-1.34l.36-2.55h3l.36 2.55a7.52 7.52 0 012.31 1.34l2.39-.96 1.5 2.6-2.03 1.58c.08.43.12.88.12 1.34z" />
                                            </svg>
                                            @break
                                        @case('credit-card')
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75A1.5 1.5 0 014.5 5.25h15A1.5 1.5 0 0121 6.75v10.5a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 17.25V6.75z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75h18" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 15h3" />
                                            </svg>
                                            @break
                                    @endswitch
                                </span>
                                <span class="truncate text-[10px]">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </nav>
            @endif
        </div>

        <div id="app-loading" class="app-loading" aria-hidden="true" role="status">
            <div class="loading-bar"></div>
        </div>

        <div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
            <div class="absolute inset-0 bg-slate-900/40"></div>
            <div class="relative z-10 w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                <p id="confirm-modal-title" class="text-sm font-semibold uppercase tracking-widest text-slate-500">Confirm action</p>
                <p id="confirm-modal-message" class="mt-3 text-base font-semibold text-slate-900">Are you sure?</p>
                <div class="mt-6 flex flex-wrap justify-end gap-2">
                    <button id="confirm-modal-cancel" type="button" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                        Cancel
                    </button>
                    <button id="confirm-modal-approve" type="button" class="inline-flex h-10 items-center justify-center rounded-xl bg-rose-600 px-4 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-rose-500">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <script>
            const confirmModal = document.getElementById('confirm-modal');
            const confirmTitle = document.getElementById('confirm-modal-title');
            const confirmMessage = document.getElementById('confirm-modal-message');
            const confirmCancel = document.getElementById('confirm-modal-cancel');
            const confirmApprove = document.getElementById('confirm-modal-approve');
            let pendingForm = null;

            const closeConfirmModal = () => {
                if (!confirmModal) {
                    return;
                }
                confirmModal.classList.add('hidden');
                confirmModal.classList.remove('flex');
                pendingForm = null;
            };

            const openConfirmModal = (form) => {
                if (!confirmModal) {
                    return;
                }
                pendingForm = form;
                if (confirmTitle) {
                    confirmTitle.textContent = form.dataset.confirmTitle || 'Confirm action';
                }
                if (confirmMessage) {
                    confirmMessage.textContent = form.dataset.confirmMessage || 'Are you sure you want to continue?';
                }
                if (confirmApprove) {
                    confirmApprove.textContent = form.dataset.confirmApprove || 'Confirm';
                }
                confirmModal.classList.remove('hidden');
                confirmModal.classList.add('flex');
            };

            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (form instanceof HTMLFormElement && form.dataset.confirm !== undefined) {
                    event.preventDefault();
                    openConfirmModal(form);
                }
            });

            if (confirmCancel) {
                confirmCancel.addEventListener('click', closeConfirmModal);
            }
            if (confirmModal) {
                confirmModal.addEventListener('click', (event) => {
                    if (event.target === confirmModal) {
                        closeConfirmModal();
                    }
                });
            }
            if (confirmApprove) {
                confirmApprove.addEventListener('click', () => {
                    if (pendingForm) {
                        pendingForm.dataset.confirmApproved = 'true';
                        pendingForm.submit();
                    }
                    closeConfirmModal();
                });
            }
        </script>
    </body>
</html>
