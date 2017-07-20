<?php

namespace App\Jobs;

use App\Http\Services\Api\V1\Api500EasyPayService;
use App\Http\Services\Cache\Api500EasyPayCacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class GetTasksToThird implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tags;
    protected $type;
    protected $data;

    public $tries = 3;
    public $timeout = 20;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $args = func_get_args();
        $this->tags = $args[0];
        $this->type = $args[1];
        $this->data = $args[2];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Api500EasyPayService $Api500EasyPayService, Api500EasyPayCacheService $Api500EasyPayCacheService)
    {
        $this->cache_service = $Api500EasyPayCacheService;
        $this->service = $Api500EasyPayService;

        Log::info('# start send cache #' 
            . ', base_id = ' . $this->data['base_id']
            . ', data = ' . print_r($this->data, true)
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
        $this->cache_service->setSendCache(
            $this->tags,
            $this->type,
            $this->data['base_id'],
            $this->data
        );
        
        Log::info('# start pay # FILE: ' . __FILE__ . 'LINE: ' . __LINE__);
        $status = $this->service->pay(
            $this->data['url'],
            $this->data['data'],
            $this->data['config']['signKey'],
            $this->data['base_id']
        );
        Log::info('# end pay # FILE: ' . __FILE__ . 'LINE: ' . __LINE__);
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed()
    {
        // Called when the job is failing...
        Log::error('# GetTasksToThird Job fail #'
            . ', tags = ' . print_r($this->tags, true)  
            . ', data = ' . print_r($this->data, true) 
            . ', FILE = ' . __FILE__ . 'LINE:' . __LINE__
        );
    }
}
