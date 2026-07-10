<?php

namespace Modules\Admin\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Immutable reporting window. Reads optional Y-m-d `from`/`to` query params,
 * defaulting to the last N days. `to` is inclusive (end of day).
 */
class DateRange
{
    public function __construct(
        public readonly Carbon $from,
        public readonly Carbon $to,
    ) {}

    public static function fromRequest(Request $request, int $defaultDays = 30): self
    {
        $default = new self(
            Carbon::now()->subDays($defaultDays)->startOfDay(),
            Carbon::now()->endOfDay(),
        );

        $fromRaw = $request->query('from');
        $toRaw = $request->query('to');
        if (!is_string($fromRaw) || !is_string($toRaw)) {
            return $default;
        }

        try {
            $from = Carbon::createFromFormat('Y-m-d', $fromRaw)->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', $toRaw)->endOfDay();
        } catch (\Throwable $e) {
            return $default;
        }

        // Reject overflow dates that Carbon silently rolls over (e.g. 2026-13-40).
        if ($from->format('Y-m-d') !== $fromRaw || $to->format('Y-m-d') !== $toRaw) {
            return $default;
        }
        if ($from->greaterThan($to)) {
            return $default;
        }

        return new self($from, $to);
    }
}
