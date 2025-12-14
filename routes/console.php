<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Here you can define all of your scheduled tasks. The scheduler will
| automatically run them at the specified intervals.
|
*/

// Process scheduled posts every minute
Schedule::command('posts:publish-due')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));
