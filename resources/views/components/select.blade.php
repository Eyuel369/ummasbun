@props(['label' => null, 'name' => null])

@php
    $selectId = $attributes->get('id') ?? $name;
@endphp

<div class="space-y-2">
    @if ($label)
        <label class="text-xs font-semibold uppercase tracking-widest text-slate-500" @if($selectId) for="{{ $selectId }}" @endif>
            {{ $label }}
        </label>
    @endif
    <select
        @if($selectId) id="{{ $selectId }}" @endif
        @if($name) name="{{ $name }}" @endif
        {{ $attributes->merge(['class' => 'block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-400 focus:ring-2 focus:ring-slate-200']) }}
    >
        {{ $slot }}
    </select>
</div>
