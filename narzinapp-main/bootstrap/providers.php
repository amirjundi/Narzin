<?php

return [
    App\Providers\AppServiceProvider::class,
    // Explicitly registered so the HomeContent module always boots its routes
    // even when nwidart/laravel-modules' *_module.php cache is stale or missing.
    Modules\HomeContent\Providers\HomeContentServiceProvider::class,
];