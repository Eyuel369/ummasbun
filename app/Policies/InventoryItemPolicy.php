<?php

namespace App\Policies;

use App\Models\InventoryItem;
use App\Models\User;

class InventoryItemPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_STOCK_MANAGER], true);
    }

    public function view(User $user, InventoryItem $inventoryItem): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_STOCK_MANAGER], true);
    }

    public function update(User $user, InventoryItem $inventoryItem): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_STOCK_MANAGER], true);
    }

    public function delete(User $user, InventoryItem $inventoryItem): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_STOCK_MANAGER], true);
    }
}
