@props(['variant' => 'info', 'title' => null])

@php
    $variants = [
        'info' => 'border-sky-200 bg-sky-50 text-sky-700',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-700',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border px-4 py-3 text-sm '.($variants[$variant] ?? $variants['info'])]) }}>
    @if ($title)
        <p class="text-xs font-semibold uppercase tracking-widest">{{ $title }}</p>
    @endif
    <div class="mt-1 text-sm">
        {{ $slot }}
    </div>
</div>
