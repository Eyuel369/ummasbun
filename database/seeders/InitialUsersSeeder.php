<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Owner',
                'email' => 'owner@ummasbun.test',
                'role' => User::ROLE_OWNER,
            ],
            [
                'name' => 'Cashier',
                'email' => 'cashier@ummasbun.test',
                'role' => User::ROLE_CASHIER,
            ],
            [
                'name' => 'Stock Manager',
                'email' => 'stock_manager@ummasbun.test',
                'role' => User::ROLE_STOCK_MANAGER,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
