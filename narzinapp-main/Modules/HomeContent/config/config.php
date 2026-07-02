<?php

return [
    'name' => 'HomeContent',
    'preview_token' => env('HOME_PREVIEW_TOKEN'),
    'storefront_url' => env('STOREFRONT_URL', env('APP_URL', 'http://localhost')),
];
