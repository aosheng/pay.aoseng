<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;
use App\Http\Services\Cache\Api500EasyPayCacheService;

class SendCallBackToAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   // protected $Api500EasyPayService;
    protected $Api500EasyPayCacheService;
    protected $call_back_data;
    protected $base_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($base_id, $call_back_data)
    {
        //dd([$base_id, $call_back_data]);
        
        $this->base_id = $base_id;
        $this->call_back_data = $call_back_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Api500EasyPayCacheService $Api500EasyPayCacheService)
    {
        $this->cache_service = $Api500EasyPayCacheService;
        //$this->service = $Api500EasyPayService;
        //dd([$this->base_id, $this->call_back_data]);
        $this->cache_service->saveCallBackCache('Api500EasyPay', 'save_call_back', $this->base_id, $this->call_back_data);
        Log::info('start save_call_back cache : ' 
            . 'base_id = ' . $this->base_id
            . 'call_back_data = ' . print_r($this->call_back_data, true)
            . __FILE__ . 'LINE:' . __LINE__);
    }
}
