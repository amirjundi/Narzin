<?php

namespace Tests\Feature\Telemetry;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_track_cart_writes_a_cart_event(): void
    {
        $response = $this->postJson('/api/v1/track/cart', [
            'session_id' => 'sess-abc',
            'product_id' => 3,
            'action' => 'add',
            'quantity' => 2,
            'unit_price' => 12.00,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cart_events', [
            'session_id' => 'sess-abc', 'product_id' => 3, 'action' => 'add', 'quantity' => 2,
        ]);
    }

    public function test_malformed_cart_payload_is_acknowledged_but_not_stored(): void
    {
        $response = $this->postJson('/api/v1/track/cart', [
            'session_id' => 'sess-abc',
            'action' => 'teleport', // invalid enum, product_id missing
        ]);

        $response->assertStatus(200); // non-blocking: never error the client
        $this->assertDatabaseCount('cart_events', 0);
    }

    public function test_track_session_records_attribution(): void
    {
        $response = $this->postJson('/api/v1/track/session', [
            'session_id' => 'sess-xyz',
            'utm_source' => 'newsletter',
            'utm_campaign' => 'july_sale',
            'referrer' => 'https://example.com',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('visit_sessions', [
            'session_id' => 'sess-xyz', 'utm_source' => 'newsletter', 'utm_campaign' => 'july_sale',
        ]);
    }
}
