@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white p-6 shadow-sm']) }}>
    @if ($title)
        <div class="mb-4">
            <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
            @if ($subtitle)
                <p class="text-sm text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
