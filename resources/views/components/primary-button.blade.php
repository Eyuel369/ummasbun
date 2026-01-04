<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex h-12 items-center justify-center rounded-xl bg-slate-900 px-5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 active:bg-slate-950']) }}>
    {{ $slot }}
</button>
