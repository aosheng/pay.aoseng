<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\GetTasksToThird::class,
        Commands\GetRedisData::class,
        Commands\ClearTimeOutCache::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        if (env('APP_ENV') == 'production') {
            $schedule->command('Tothird:GetTasksToThird EasyPay')
                ->cron('* * * * sleep 2');

            $schedule->command('Redis_Action:GetData EasyPay send')
                ->hourlyAt(5);

            $schedule->command('Redis_Action:GetData EasyPay response get_qrcode')
                ->hourlyAt(10);

            $schedule->command('Redis_Action:GetData EasyPay save call_back')
                ->hourlyAt(15);

            $schedule->command('Clear:TimeOutCache EasyPay')
                ->dailyAt('03:00');
        }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
