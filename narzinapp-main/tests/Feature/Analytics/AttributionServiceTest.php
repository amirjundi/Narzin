<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\AttributionService;
use Modules\Admin\Support\DateRange;
use Modules\Checkout\Models\Order;
use Tests\TestCase;

class AttributionServiceTest extends TestCase
{
    use RefreshDatabase;

    private function range(): DateRange
    {
        return new DateRange(now()->subDays(30)->startOfDay(), now()->endOfDay());
    }

    private function order(array $attrs): void
    {
        $user = User::factory()->create();

        // orders.address_id is NOT NULL with an FK to user_address; seed a row
        // to satisfy it (mirrors tests/Feature/Analytics/OrderAttributionColumnsTest.php).
        $addressId = DB::table('user_address')->insertGetId([
            'user_id' => $user->id,
            'address' => '123 Test Street',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Order::create(array_merge([
            'user_id' => $user->id,
            'address_id' => $addressId,
            'order_number' => 'T-' . uniqid(),
            'order_status' => 'completed',
            'total_amount' => 100.00,
        ], $attrs));
    }

    public function test_by_channel_aggregates_revenue_orders_aov(): void
    {
        $this->order(['utm_source' => 'google', 'utm_medium' => 'cpc', 'total_amount' => 100]);
        $this->order(['utm_source' => 'google', 'utm_medium' => 'cpc', 'total_amount' => 300]);
        $this->order(['utm_source' => 'facebook', 'utm_medium' => 'social', 'total_amount' => 50]);

        $rows = (new AttributionService())->byChannel($this->range());
        $google = $rows->firstWhere('source', 'google');

        $this->assertSame(2, $google['orders']);
        $this->assertEquals(400.00, $google['revenue']);
        $this->assertEquals(200.00, $google['aov']);
    }

    public function test_null_utm_groups_as_none(): void
    {
        $this->order(['total_amount' => 20]); // no utm

        $rows = (new AttributionService())->byChannel($this->range());
        $none = $rows->firstWhere('source', '(none)');
        $this->assertNotNull($none);
        $this->assertSame(1, $none['orders']);
    }

    public function test_by_campaign_groups_by_campaign(): void
    {
        $this->order(['utm_campaign' => 'july', 'total_amount' => 100]);
        $this->order(['utm_campaign' => 'july', 'total_amount' => 100]);

        $rows = (new AttributionService())->byCampaign($this->range());
        $july = $rows->firstWhere('campaign', 'july');
        $this->assertSame(2, $july['orders']);
        $this->assertEquals(200.00, $july['revenue']);
    }
}
