<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Phase 12: Digital Document Rentention Execution
\Illuminate\Support\Facades\Schedule::command('digital:prune')
    ->dailyAt('02:30')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/digital_prune.log'));

// Phase D: Auto-reminder for overdue documents (48 business hours)
\Illuminate\Support\Facades\Schedule::command('documents:check-overdue')
    ->hourly()
    ->weekdays()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/overdue_check.log'));
