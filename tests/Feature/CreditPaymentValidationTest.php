<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditPaymentValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_payment_requires_customer_name(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CASHIER]);
        $date = now()->toDateString();

        $this->actingAs($user)
            ->from('/sales/'.$date)
            ->post(route('sales.payments.store', ['date' => $date]), [
                'method' => 'credit',
                'amount' => 10000,
                'customer_name' => '',
            ])
            ->assertSessionHasErrors('customer_name');
    }
}
