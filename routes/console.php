<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Kuyruk birikmesini onle: her 5 dakikada bir birikmis isleri bosalt.
Schedule::command('queue:work --stop-when-empty --sleep=1 --tries=1 --max-time=120')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

