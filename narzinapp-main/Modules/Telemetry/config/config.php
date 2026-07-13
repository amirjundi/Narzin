<?php

return [
    'name' => 'Telemetry',
    'abandoned_cart_hours' => env('ABANDONED_CART_HOURS', 24),
    'fulfillment_sla_hours' => env('FULFILLMENT_SLA_HOURS', 48),
];
