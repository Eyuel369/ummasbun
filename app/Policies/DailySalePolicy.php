<?php

namespace App\Policies;

use App\Models\DailySale;
use App\Models\User;

class DailySalePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_CASHIER], true);
    }

    public function view(User $user, DailySale $dailySale): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_CASHIER], true);
    }

    public function update(User $user, DailySale $dailySale): bool
    {
        if ($user->role !== User::ROLE_CASHIER) {
            return $user->role === User::ROLE_OWNER;
        }

        return $dailySale->sale_date?->isToday() ?? false;
    }

    public function delete(User $user, DailySale $dailySale): bool
    {
        return $user->role === User::ROLE_OWNER;
    }

    public function export(User $user): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_CASHIER], true);
    }

    public function viewReports(User $user): bool
    {
        return $user->role === User::ROLE_OWNER;
    }
}
