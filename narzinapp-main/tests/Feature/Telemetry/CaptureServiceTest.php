<?php

namespace Tests\Feature\Telemetry;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Telemetry\Models\VisitSession;
use Modules\Telemetry\Services\CaptureService;
use Tests\TestCase;
use App\Models\User;

class CaptureServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create test users for foreign key constraints
        User::factory()->create(['id' => 5]);
        User::factory()->create(['id' => 42]);
        User::factory()->create(['id' => 77]);
    }

    public function test_record_search_normalizes_and_stores_count(): void
    {
        CaptureService::recordSearch('sess-1', null, '  Blue SHIRT ', 4);

        $this->assertDatabaseHas('search_logs', [
            'session_id' => 'sess-1',
            'query' => 'Blue SHIRT',
            'normalized_query' => 'blue shirt',
            'results_count' => 4,
        ]);
    }

    public function test_record_search_ignores_blank_query(): void
    {
        CaptureService::recordSearch('sess-1', null, '   ', 0);
        $this->assertDatabaseCount('search_logs', 0);
    }

    public function test_record_cart_event_stores_row(): void
    {
        CaptureService::recordCartEvent('sess-1', null, 7, 12, 'add', 3, 19.50);

        $this->assertDatabaseHas('cart_events', [
            'session_id' => 'sess-1', 'product_id' => 7, 'variant_id' => 12,
            'action' => 'add', 'quantity' => 3,
        ]);
    }

    public function test_record_checkout_event_stores_row(): void
    {
        CaptureService::recordCheckoutEvent('sess-1', 5, 'placed', 99);

        $this->assertDatabaseHas('checkout_events', [
            'session_id' => 'sess-1', 'user_id' => 5, 'step' => 'placed', 'order_id' => 99,
        ]);
    }

    public function test_record_session_sets_attribution_only_on_first_touch(): void
    {
        CaptureService::recordSession('sess-1', null, ['utm_source' => 'google']);
        CaptureService::recordSession('sess-1', 42, ['utm_source' => 'facebook']); // later touch

        $this->assertDatabaseCount('visit_sessions', 1);
        $session = VisitSession::where('session_id', 'sess-1')->first();
        $this->assertSame('google', $session->utm_source); // first touch wins
        $this->assertSame(42, $session->user_id);          // user backfilled on later touch
    }

    public function test_backfill_user_sets_user_on_null_session(): void
    {
        CaptureService::recordSession('sess-1', null, []);
        CaptureService::backfillUser('sess-1', 77);

        $this->assertDatabaseHas('visit_sessions', ['session_id' => 'sess-1', 'user_id' => 77]);
    }

    public function test_record_session_stores_long_referrer_and_caps_utm_source(): void
    {
        $longReferrer = str_repeat('a', 300);
        $longUtmSource = str_repeat('b', 300);

        CaptureService::recordSession('sess-1', null, [
            'referrer' => $longReferrer,
            'utm_source' => $longUtmSource,
        ]);

        $this->assertDatabaseHas('visit_sessions', ['session_id' => 'sess-1']);
        $session = VisitSession::where('session_id', 'sess-1')->first();
        $this->assertNotNull($session);
        $this->assertSame($longReferrer, $session->referrer); // text column, not truncated
        $this->assertSame(str_repeat('b', 255), $session->utm_source); // capped to 255
    }
}
