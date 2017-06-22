<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Http\Services\Cache\Api500EasyPayCacheService;

class GetTasksToThird implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $Api500EasyPayCacheService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tags)
    {
        //$this->redis = $Redis;
        //$this->cache_service = $Api500EasyPayCacheService;
        $this->tags = $tags;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Api500EasyPayCacheService $Api500EasyPayCacheService)
    {
        $this->cache_service = $Api500EasyPayCacheService;
        //dd('123');
        $task_data = $this->cache_service->getCache($this->tags);
        foreach ($task_data as $base_id => $data) {
            $status = $this->cache_service->pay($data['url'], $data['data'], $data['config']['signKey']);
        }
       
    }
}
