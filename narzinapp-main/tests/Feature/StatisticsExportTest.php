<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Tests\TestCase;

class StatisticsExportTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): self
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin' . uniqid() . '@t.test',
            'password' => 'secret123', 'email_verified_at' => now(),
        ]);
        UserAdmin::create(['user_id' => $admin->id, 'is_active' => 1]);

        $this->actingAs($admin);

        return $this;
    }

    public function test_profit_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.profit', ['export' => 'profit']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $res->headers->get('Content-Disposition'));
        $this->assertNotEmpty($res->streamedContent());
    }

    public function test_unknown_export_key_404s(): void
    {
        $this->actingAsAdmin()->get(route('statistics.profit', ['export' => 'nope']))->assertNotFound();
    }

    public function test_funnel_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.funnel', ['export' => 'funnel']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
        $this->assertNotEmpty($res->streamedContent());
    }

    public function test_funnel_attribution_channel_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.funnel', ['export' => 'attribution_channel']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_funnel_attribution_campaign_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.funnel', ['export' => 'attribution_campaign']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_funnel_unknown_export_key_404s(): void
    {
        $this->actingAsAdmin()->get(route('statistics.funnel', ['export' => 'nope']))->assertNotFound();
    }

    public function test_coupons_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.promotions', ['export' => 'coupons']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_promotions_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.promotions', ['export' => 'promotions']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_promotions_unknown_export_key_404s(): void
    {
        $this->actingAsAdmin()->get(route('statistics.promotions', ['export' => 'nope']))->assertNotFound();
    }

    public function test_payments_status_breakdown_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.payments', ['export' => 'status_breakdown']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_payments_methods_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.payments', ['export' => 'methods']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_payments_failure_reasons_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.payments', ['export' => 'failure_reasons']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_payments_unknown_export_key_404s(): void
    {
        $this->actingAsAdmin()->get(route('statistics.payments', ['export' => 'nope']))->assertNotFound();
    }

    public function test_returns_by_reason_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.returns', ['export' => 'by_reason']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_returns_summary_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.returns', ['export' => 'summary']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_returns_unknown_export_key_404s(): void
    {
        $this->actingAsAdmin()->get(route('statistics.returns', ['export' => 'nope']))->assertNotFound();
    }

    public function test_fulfillment_sla_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.fulfillment', ['export' => 'sla']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_fulfillment_cancellations_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.fulfillment', ['export' => 'cancellations']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_fulfillment_unknown_export_key_404s(): void
    {
        $this->actingAsAdmin()->get(route('statistics.fulfillment', ['export' => 'nope']))->assertNotFound();
    }

    public function test_inventory_valuation_by_category_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.inventory', ['export' => 'valuation_by_category']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_inventory_valuation_by_vendor_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.inventory', ['export' => 'valuation_by_vendor']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_inventory_reorder_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.inventory', ['export' => 'reorder']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_inventory_dead_stock_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.inventory', ['export' => 'dead_stock']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_inventory_expiring_export_streams_csv(): void
    {
        $res = $this->actingAsAdmin()->get(route('statistics.inventory', ['export' => 'expiring']));
        $res->assertOk();
        $this->assertStringStartsWith('text/csv', $res->headers->get('Content-Type'));
    }

    public function test_inventory_unknown_export_key_404s(): void
    {
        $this->actingAsAdmin()->get(route('statistics.inventory', ['export' => 'nope']))->assertNotFound();
    }
}
