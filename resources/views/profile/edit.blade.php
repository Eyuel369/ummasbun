<x-app-shell>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Account</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Profile</h1>
            <p class="text-sm text-slate-500">Update your details and security preferences.</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-shell>

