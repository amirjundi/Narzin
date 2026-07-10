<?php

namespace Tests\Feature\Analytics;

use Illuminate\Http\Request;
use Modules\Admin\Support\DateRange;
use Tests\TestCase;

class DateRangeTest extends TestCase
{
    public function test_defaults_to_last_30_days_when_no_params(): void
    {
        $r = DateRange::fromRequest(new Request());
        $this->assertSame(now()->subDays(30)->startOfDay()->toDateString(), $r->from->toDateString());
        $this->assertSame(now()->endOfDay()->toDateString(), $r->to->toDateString());
    }

    public function test_parses_valid_from_and_to(): void
    {
        $r = DateRange::fromRequest(new Request(['from' => '2026-01-01', 'to' => '2026-01-31']));
        $this->assertSame('2026-01-01', $r->from->toDateString());
        $this->assertSame('2026-01-31', $r->to->toDateString());
        $this->assertSame('00:00:00', $r->from->format('H:i:s'));
        $this->assertSame('23:59:59', $r->to->format('H:i:s'));
    }

    public function test_invalid_input_falls_back_to_default(): void
    {
        $r = DateRange::fromRequest(new Request(['from' => 'not-a-date', 'to' => '2026-01-31']));
        $this->assertSame(now()->subDays(30)->startOfDay()->toDateString(), $r->from->toDateString());
    }

    public function test_from_after_to_falls_back_to_default(): void
    {
        $r = DateRange::fromRequest(new Request(['from' => '2026-02-01', 'to' => '2026-01-01']));
        $this->assertSame(now()->subDays(30)->startOfDay()->toDateString(), $r->from->toDateString());
    }
}
