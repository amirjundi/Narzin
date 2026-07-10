<?php

namespace Tests\Feature\Telemetry;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Telemetry\Models\CartEvent;
use Modules\Telemetry\Models\CheckoutEvent;
use Modules\Telemetry\Models\SearchLog;
use Modules\Telemetry\Models\VisitSession;
use Tests\TestCase;

class CaptureSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_four_capture_tables_accept_rows(): void
    {
        VisitSession::create([
            'session_id' => 'sess-1', 'utm_source' => 'google',
            'first_seen_at' => now(), 'last_seen_at' => now(),
        ]);
        CartEvent::create([
            'session_id' => 'sess-1', 'product_id' => 1, 'action' => 'add',
            'quantity' => 2, 'unit_price' => 9.99, 'occurred_at' => now(),
        ]);
        CheckoutEvent::create([
            'session_id' => 'sess-1', 'step' => 'checkout_start', 'occurred_at' => now(),
        ]);
        SearchLog::create([
            'query' => 'Blue Shirt', 'normalized_query' => 'blue shirt',
            'results_count' => 3, 'occurred_at' => now(),
        ]);

        $this->assertDatabaseHas('visit_sessions', ['session_id' => 'sess-1', 'utm_source' => 'google']);
        $this->assertDatabaseHas('cart_events', ['session_id' => 'sess-1', 'action' => 'add', 'quantity' => 2]);
        $this->assertDatabaseHas('checkout_events', ['step' => 'checkout_start']);
        $this->assertDatabaseHas('search_logs', ['normalized_query' => 'blue shirt', 'results_count' => 3]);
    }
}
