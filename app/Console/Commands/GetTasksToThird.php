<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

//use App\Jobs\GetTasksToThird;

class GetTasksToThird extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tothird:GetTasksToThird';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send data to 500EasyPay be get Qrcode';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $job = (new \App\Jobs\GetTasksToThird())
                    ->delay(Carbon::now()->addMinutes(1));

        dispatch($job);
        //dispatch(new \App\Jobs\GetTasksToThird());
        sleep(4);
        echo date("Y-m-d H:i:s")."\n";
    }
}
