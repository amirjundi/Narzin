<?php

return [
    'name' => 'Telemetry',
    'abandoned_cart_hours' => env('ABANDONED_CART_HOURS', 24),
    'fulfillment_sla_hours' => env('FULFILLMENT_SLA_HOURS', 48),
    'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 5),
    'expiry_days_ahead' => env('EXPIRY_DAYS_AHEAD', 30),
];
