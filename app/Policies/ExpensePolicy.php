<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_CASHIER], true);
    }

    public function view(User $user, Expense $expense): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_CASHIER], true);
    }

    public function update(User $user, Expense $expense): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_CASHIER], true);
    }

    public function delete(User $user, Expense $expense): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_CASHIER], true);
    }

    public function export(User $user): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_CASHIER], true);
    }
}
