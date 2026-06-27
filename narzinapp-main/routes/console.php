<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Schedule::command('orders:release-expired')
    ->everyFiveMinutes()
    ->withoutOverlapping();