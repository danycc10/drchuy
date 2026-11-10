<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:backup --prune')->dailyAt(config('backup.time'))->timezone('America/Matamoros')
    ->withoutOverlapping(60)->when(fn () => config('backup.enabled'));

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
