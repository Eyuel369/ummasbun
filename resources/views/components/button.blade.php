@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button'])

@php
    $base = 'inline-flex items-center justify-center rounded-xl font-semibold uppercase tracking-widest transition focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';
    $sizes = [
        'sm' => 'h-10 px-4 text-xs',
        'md' => 'h-12 px-5 text-xs',
        'lg' => 'h-14 px-6 text-sm',
    ];
    $variants = [
        'primary' => 'bg-slate-900 text-white hover:bg-slate-800 active:bg-slate-950',
        'secondary' => 'border border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:text-slate-900',
        'ghost' => 'text-slate-600 hover:text-slate-900',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-500 active:bg-rose-700',
    ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $base.' '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['primary'])]) }}>
    {{ $slot }}
</button>
