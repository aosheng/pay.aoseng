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
            $schedule->command('Tothird:GetTasksToThird')
                ->cron('0/2 * * * * *');

            $schedule->command('Redis_Action:GetData Api500EasyPay send')
            ->hourlyAt(5);

            $schedule->command('Redis_Action:GetData Api500EasyPay response get_qrcode')
            ->hourlyAt(10);

            $schedule->command('Redis_Action:GetData Api500EasyPay save call_back')
            ->hourlyAt(15);
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
