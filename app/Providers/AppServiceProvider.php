<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability, array $arguments = []) {
            return $user->role === User::ROLE_OWNER ? true : null;
        });

        Gate::define('view-debtors', function (User $user): bool {
            return in_array($user->role, [User::ROLE_OWNER, User::ROLE_CASHIER], true);
        });
    }
}
