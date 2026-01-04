<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-xs font-semibold uppercase tracking-widest text-slate-700 shadow-sm transition hover:border-slate-300 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 disabled:opacity-25']) }}>
    {{ $slot }}
</button>
