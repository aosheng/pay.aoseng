<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Http\Services\Cache\Api500EasyPayCacheService;
use App\Http\Services\Api\V1\Api500EasyPayService;

class GetTasksToThird implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $Api500EasyPayCacheService;
    protected $tags;

     public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->redis = $Redis;
        //$this->cache_service = $Api500EasyPayCacheService;
        $this->tags = 'Api500EasyPay_input';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Api500EasyPayCacheService $Api500EasyPayCacheService,
        Api500EasyPayService $Api500EasyPayService)
    {
        $this->cache_service = $Api500EasyPayCacheService;
        $this->service = $Api500EasyPayService;

        //dd('123');
        //dd($this->tags);
        $task_data = $this->cache_service->getCache($this->tags);
        //dd($task_data);
        foreach ($task_data as $base_id => $data) {
            var_dump($data);
            //dd([$data['url'], $data['data'], $data['config']['signKey']]);
            $status = $this->service->pay($data['url'], $data['data'], $data['config']['signKey'], $data['base_id']);
        }
       
    }
}
