<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_OWNER, User::ROLE_CASHIER, User::ROLE_STOCK_MANAGER], true);
    }

    public function view(User $user, Product $product): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->role === User::ROLE_OWNER;
    }

    public function update(User $user, Product $product): bool
    {
        return $user->role === User::ROLE_OWNER;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->role === User::ROLE_OWNER;
    }
}
