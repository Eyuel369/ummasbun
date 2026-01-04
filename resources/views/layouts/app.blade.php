<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @php
            $user = auth()->user();
            $navItems = [
                ['label' => 'Sales', 'route' => 'sales.today', 'ability' => 'viewAny', 'model' => \App\Models\DailySale::class],
                ['label' => 'Expenses', 'route' => 'expenses.index', 'ability' => 'viewAny', 'model' => \App\Models\Expense::class],
                ['label' => 'Inventory', 'route' => 'inventory.today', 'ability' => 'viewAny', 'model' => \App\Models\InventoryDaily::class],
                ['label' => 'Reports', 'route' => 'reports.index', 'ability' => 'viewReports', 'model' => \App\Models\DailySale::class],
                ['label' => 'Settings', 'route' => 'settings.index', 'ability' => 'viewAny', 'model' => \App\Models\User::class],
            ];
            $visibleNav = array_values(array_filter($navItems, function (array $item): bool {
                return \Illuminate\Support\Facades\Gate::allows($item['ability'], $item['model']);
            }));
        @endphp

        <div class="flex min-h-screen flex-col bg-gradient-to-b from-slate-50 to-white text-slate-900">
            <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/85 backdrop-blur">
                <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-sm font-semibold text-white">
                            UA
                        </div>
                        <div class="leading-tight">
                            <p class="text-sm font-semibold">{{ config('app.name', 'Ummasbun') }}</p>
                            @if ($user)
                                <p class="text-xs uppercase tracking-widest text-slate-500">
                                    {{ ucwords(str_replace('_', ' ', $user->role)) }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <nav class="hidden items-center gap-1 md:flex">
                        @foreach ($visibleNav as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                class="{{ request()->routeIs($item['route']) ? 'text-slate-900' : 'text-slate-500' }} rounded-full px-3 py-1.5 text-sm font-medium transition hover:text-slate-900"
                            >
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </nav>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-slate-500 transition hover:text-slate-900">
                            Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-full border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 pb-24">
                <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
                    @isset($header)
                        <div class="mb-6">
                            {{ $header }}
                        </div>
                    @endisset

                    {{ $slot }}
                </div>
            </main>

            @if (count($visibleNav))
                <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 backdrop-blur md:hidden">
                    <div class="mx-auto flex max-w-md items-center justify-between px-4 py-2">
                        @foreach ($visibleNav as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                class="{{ request()->routeIs($item['route']) ? 'text-slate-900' : 'text-slate-500' }} flex flex-col items-center gap-1 px-2 py-1 text-xs font-semibold uppercase tracking-widest transition"
                            >
                                <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs($item['route']) ? 'bg-slate-900' : 'bg-slate-300' }}"></span>
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </nav>
            @endif
        </div>
    </body>
</html>
