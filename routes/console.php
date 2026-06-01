<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Chạy job kiểm tra auto backup mỗi ngày lúc 1:00 sáng
Schedule::command('app:auto-backup-database')->dailyAt('01:00');
