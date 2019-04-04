<?php

namespace App\Console;

use App\Console\Commands\SyncNotExistantDrivers;
use App\Console\Commands\SyncAwsSns;
use App\Console\Commands\SyncAwsIOS;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
         \App\Listeners\DumpRecordedRequests::class,
        SyncNotExistantDrivers::class,
        SyncAwsSns::class,
        SyncAwsIOS::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // you can set how often you want it to dump your requests to the Dashboard
        // every minute is the most frequent mode
        $schedule->command('http_analyzer:dump')->everyMinute();
    }
}
