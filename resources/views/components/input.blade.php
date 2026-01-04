@props(['label' => null, 'name' => null, 'type' => 'text'])

@php
    $inputId = $attributes->get('id') ?? $name;
@endphp

<div class="space-y-2">
    @if ($label)
        <label class="text-xs font-semibold uppercase tracking-widest text-slate-500" @if($inputId) for="{{ $inputId }}" @endif>
            {{ $label }}
        </label>
    @endif
    <input
        type="{{ $type }}"
        @if($inputId) id="{{ $inputId }}" @endif
        @if($name) name="{{ $name }}" @endif
        {{ $attributes->merge(['class' => 'block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:border-slate-400 focus:ring-2 focus:ring-slate-200']) }}
    >
</div>
