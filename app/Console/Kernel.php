<?php

namespace App\Console;

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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $graph = new \App\Graph(['fields' => ['message', 'link', 'updated_time', 'id', 'caption', 'attachments', 'from', 'child_attachments', 'likes{name,link}']]);

            $statuses = $graph->statuses;

            $statuses->reverse()->each( function($status) {
                $status->sendToSlack();
            });

        })->everyMinute();
    }
}
