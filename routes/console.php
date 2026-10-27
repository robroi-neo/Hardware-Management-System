<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Demo data reset. Where the host runs `php artisan schedule:run` every minute,
// this resets on time; otherwise the first visit after the interval does it.
Schedule::command('demo:reset --if-due')
    ->everyMinute()
    ->when(fn () => config('demo.auto_reset'))
    ->withoutOverlapping();
