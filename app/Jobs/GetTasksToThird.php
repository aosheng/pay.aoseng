<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Log;
use App\Http\Services\Cache\Api500EasyPayCacheService;
use App\Http\Services\Api\V1\Api500EasyPayService;

class GetTasksToThird implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $Api500EasyPayCacheService;
    protected $data;

    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    //todo send order to third
    public function handle(Api500EasyPayService $Api500EasyPayService)
    {
        //$this->cache_service = $Api500EasyPayCacheService;
        $this->service = $Api500EasyPayService;

        Log::info('start pay : ' . base_path() . 'LINE:' . __LINE__);
        $status = $this->service->pay(
            $this->data['url'],
            $this->data['data'],
            $this->data['config']['signKey'],
            $this->data['base_id']
        );
        Log::info('pay status : ' . print_r($status, true) . base_path() . 'LINE:' . __LINE__);
        Log::info('end pay : ' . base_path() . 'LINE"' . __LINE__);
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed()
    {
        // Called when the job is failing...
        Log::error('GetTasksToThird Job fail/n' . print_r($this->data, true) . base_path() . 'LINE:' . __LINE__);
    }
}
