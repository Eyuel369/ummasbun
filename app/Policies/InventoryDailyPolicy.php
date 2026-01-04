<?php

namespace App\Policies;

use App\Models\InventoryDaily;
use App\Models\User;

class InventoryDailyPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_STOCK_MANAGER], true);
    }

    public function view(User $user, InventoryDaily $inventoryDaily): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_STOCK_MANAGER], true);
    }

    public function update(User $user, InventoryDaily $inventoryDaily): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_STOCK_MANAGER], true);
    }

    public function delete(User $user, InventoryDaily $inventoryDaily): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_STOCK_MANAGER], true);
    }

    public function export(User $user): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_STOCK_MANAGER], true);
    }
}
