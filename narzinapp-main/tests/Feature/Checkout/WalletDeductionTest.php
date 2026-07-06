<?php

namespace Tests\Feature\Checkout;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Modules\Checkout\Models\UserWallet;
use Modules\Checkout\Services\NassPaymentService;
use Tests\TestCase;

/**
 * Covers the wallet debit in CheckoutController::applyWalletDeduction (exercised
 * via the verify-payment flow). The debit must be exact, must never over-spend
 * (no negative balance / double-spend), must flag shortfalls, and must be
 * idempotent per order.
 */
class WalletDeductionTest extends TestCase
{
    use RefreshDatabase;

    private function seedOrder(User $user, float $walletUsage, string $paymentId): int
    {
        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id, 'address' => 'X',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('orders')->insertGetId([
            'user_id' => $user->id,
            'address_id' => $addressId,
            'order_number' => 'ORD-' . uniqid(),
            'total_amount' => 100,
            'payment_id' => $paymentId,
            'payment_status' => 'not_paid',
            'order_status' => 'pending_payment',
            'wallet_usage' => $walletUsage,
            'final_price' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function mockNassApproved(): void
    {
        // Nass confirms the payment (responseCode "00") — server-verified, not client-supplied.
        $this->mock(NassPaymentService::class, function ($mock) {
            $mock->shouldReceive('checkTransactionStatus')->andReturn([
                'success' => true,
                'data' => ['responseCode' => '00', 'rrn' => 'R1', 'intRef' => 'I1'],
            ]);
        });
    }

    public function test_wallet_debit_deducts_exactly_and_records_a_transaction(): void
    {
        Notification::fake();
        $this->mockNassApproved();

        $user = User::factory()->create();
        UserWallet::create(['user_id' => $user->id, 'balance' => 50]);
        $orderId = $this->seedOrder($user, walletUsage: 30, paymentId: '10000001');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/verify-payment', ['orderId' => '10000001'])
            ->assertStatus(200)->assertJson(['status' => true]);

        $this->assertEquals(20, UserWallet::where('user_id', $user->id)->value('balance'));
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id, 'order_id' => $orderId, 'amount' => 30, 'type' => 'order',
        ]);
        $this->assertNotNull(DB::table('orders')->where('id', $orderId)->value('wallet_deducted_at'));
    }

    public function test_wallet_debit_never_overspends_when_balance_is_insufficient(): void
    {
        Notification::fake();
        $this->mockNassApproved();

        $user = User::factory()->create();
        UserWallet::create(['user_id' => $user->id, 'balance' => 10]); // less than the 30 reserved
        $orderId = $this->seedOrder($user, walletUsage: 30, paymentId: '10000002');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/verify-payment', ['orderId' => '10000002'])
            ->assertStatus(200);

        // Balance must NOT go negative and must be untouched; no phantom transaction.
        $this->assertEquals(10, UserWallet::where('user_id', $user->id)->value('balance'));
        $this->assertDatabaseMissing('wallet_transactions', ['order_id' => $orderId]);
        $this->assertNull(DB::table('orders')->where('id', $orderId)->value('wallet_deducted_at'));
        // The shortfall is flagged for admin review rather than silently absorbed.
        $this->assertDatabaseHas('order_audits', [
            'order_id' => $orderId, 'action' => 'wallet_deduction_shortfall',
        ]);
        // CRITICAL: the order is STILL confirmed when Nass approves — the wallet
        // shortfall must never block the "mark the order done once paid" flow.
        $this->assertEquals('processing', DB::table('orders')->where('id', $orderId)->value('payment_status'));
    }

    public function test_wallet_debit_is_idempotent_across_confirmations(): void
    {
        Notification::fake();
        $this->mockNassApproved();

        $user = User::factory()->create();
        UserWallet::create(['user_id' => $user->id, 'balance' => 50]);
        $orderId = $this->seedOrder($user, walletUsage: 30, paymentId: '10000003');

        // First confirmation deducts once.
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/verify-payment', ['orderId' => '10000003'])->assertStatus(200);
        $this->assertEquals(20, UserWallet::where('user_id', $user->id)->value('balance'));

        // A second confirmation (e.g. the webhook arriving after the user returned)
        // must never debit the same order twice.
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/verify-payment', ['orderId' => '10000003'])->assertStatus(200);
        $this->assertEquals(20, UserWallet::where('user_id', $user->id)->value('balance'));
        $this->assertEquals(1, DB::table('wallet_transactions')->where('order_id', $orderId)->count());
    }
}
