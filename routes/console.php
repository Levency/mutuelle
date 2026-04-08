<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Planificateur Mutulle ────────────────────────────────────────────────────
// Calcul quotidien des pénalités de retard (prêts + cotisations)
Schedule::command('mutuelle:calculate-penalties')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/penalties.log'));
