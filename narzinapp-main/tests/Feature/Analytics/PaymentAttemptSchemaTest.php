<?php

namespace Tests\Feature\Analytics;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Checkout\Models\PaymentAttempt;
use Tests\TestCase;

class PaymentAttemptSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_attempts_accepts_a_row(): void
    {
        PaymentAttempt::create([
            'order_id' => null, 'user_id' => null, 'gateway' => 'nass',
            'status' => 'failed', 'response_code' => '51', 'amount' => 25.50,
            'occurred_at' => now(),
        ]);

        $this->assertDatabaseHas('payment_attempts', [
            'gateway' => 'nass', 'status' => 'failed', 'response_code' => '51',
        ]);
    }
}
