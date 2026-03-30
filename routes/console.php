<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('neolifeporium:insights')->hourly();
Schedule::command('neolifeporium:admin-alerts')->everyThirtyMinutes();
Schedule::command('neolifeporium:forecast')->dailyAt('02:00');
Schedule::command('neolifeporium:inventory-intel')->dailyAt('03:00');
