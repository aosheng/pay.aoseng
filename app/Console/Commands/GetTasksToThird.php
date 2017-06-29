<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

use Log;
use App\Http\Services\Cache\Api500EasyPayCacheService;
use App\jobs\GetTasksToThird as GetQrcode;

class GetTasksToThird extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Tothird:GetTasksToThird';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send data to 500EasyPay be get Qrcode';

    protected $Api500EasyPayCacheService;
    protected $tags;
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Api500EasyPayCacheService $Api500EasyPayCacheService)
    {
        parent::__construct();
        $this->tags = 'Api500EasyPay';
        $this->cache_service = $Api500EasyPayCacheService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //todo get redis input data to send order
        $task_data = $this->cache_service->getCache($this->tags, 'input_base_id');
       
        Log::info('Tothird:GetTasksToThird start: ' . __FILE__ . 'LINE:' . __LINE__);
        if (empty($task_data)) {
            Log::warning('# Tothird:GetTasksToThird warning # No data' 
                . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__);
            return false;
        }

        Log::info('get input redis order : ' . __FILE__ . 'LINE:' . __LINE__);
        foreach ($task_data as $data) {
            dispatch((new getQrcode($data))
                ->onQueue('get_qrcode'));
        }

        // $job = (new \App\Jobs\GetTasksToThird())
        //             ->delay(Carbon::now()->addMinutes(1));

        // dispatch($job);
        //dispatch(new \App\Jobs\GetTasksToThird());
        //sleep(2);
        echo date("Y-m-d H:i:s")."\n";
    }
}
