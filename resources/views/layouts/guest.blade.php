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
    <body class="font-sans text-slate-900 antialiased">
        <div class="page-shell min-h-screen bg-gradient-to-b from-slate-50 to-white">
            <div class="loading-zone relative mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-6 py-10">
                <div class="loading-content">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-sm font-semibold text-white">
                            UA
                        </div>
                        <div class="leading-tight">
                            <p class="text-sm font-semibold">{{ config('app.name', 'Ummasbun') }}</p>
                            <p class="text-xs uppercase tracking-widest text-slate-500">Welcome</p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        {{ $slot }}
                    </div>
                </div>

                <div class="loading-ghosts" aria-hidden="true">
                    <div class="loading-surface loading-surface-full min-h-full">
                    <div class="mt-6 space-y-3">
                        <div class="loading-skeleton h-4 w-4/6 rounded-full"></div>
                        <div class="loading-skeleton h-10 w-full rounded-2xl"></div>
                        <div class="loading-skeleton h-4 w-2/6 rounded-full"></div>
                        <div class="loading-skeleton h-10 w-full rounded-2xl"></div>
                    </div>
                        <div class="mt-6 flex items-center justify-between">
                            <div class="loading-skeleton h-4 w-24 rounded-full"></div>
                            <div class="loading-skeleton h-10 w-28 rounded-full"></div>
                        </div>
                        <div class="mt-6 flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-slate-500">
                            <span class="loading-dot loading-pulse inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            Loading
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="app-loading" class="app-loading" aria-hidden="true" role="status">
            <div class="loading-bar"></div>
        </div>
    </body>
</html>
